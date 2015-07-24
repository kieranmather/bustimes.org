<?php

class TimetableController extends BaseController {
	private function getNationalData($stop){
		$stopData = Stop::where('id', '=', $stop)->get();
		if (!$stopData->isEmpty()){
			$url = 'http://nextbus.mxdata.co.uk/nextbuses/beta';
			$date =  date("Y-m-d\TH:i:s\Z");
			$id = rand(100000, 999999);
			$request = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
						<Siri version="1.0" xmlns="http://www.siri.org.uk/">
						<ServiceRequest>
						<RequestTimestamp>' . $date . '</RequestTimestamp>
						<RequestorRef>' . Config::get('traveline.username') . '</RequestorRef>
						<StopMonitoringRequest version="1.0">
						<RequestTimestamp>' . $date . '</RequestTimestamp>
						<MessageIdentifier>' . $id . '</MessageIdentifier>
						<MonitoringRef>' . $stop . '</MonitoringRef>
						</StopMonitoringRequest>
						</ServiceRequest>
						</Siri>';
			$response = Httpful::post($url)->body($request)->authenticateWith(Config::get('traveline.username'), Config::get('traveline.password'))->sendsXml()->expectsXml()->send();
			$timetable = $response->body->ServiceDelivery->StopMonitoringDelivery;
			$standardTimetable = array();
			foreach($timetable->MonitoredStopVisit as $trip){
				if (isset($trip->MonitoredVehicleJourney->MonitoredCall->ExpectedDepartureTime)){
					$standardTimetable[] = ['BusName' => (string) $trip->MonitoredVehicleJourney->PublishedLineName, 'BusHeading' => (string) $trip->MonitoredVehicleJourney->DirectionName, 'ArrivalTime' => strtotime((string) $trip->MonitoredVehicleJourney->MonitoredCall->ExpectedDepartureTime)];
				} else {
					$standardTimetable[] = ['BusName' => (string) $trip->MonitoredVehicleJourney->PublishedLineName, 'BusHeading' => (string) $trip->MonitoredVehicleJourney->DirectionName, 'ArrivalTime' => strtotime((string) $trip->MonitoredVehicleJourney->MonitoredCall->AimedDepartureTime)];
				}
			}
			if (isset($standardTimetable[0]['ArrivalTime'])){
				if ( $standardTimetable[0]['ArrivalTime'] - time() > 0){ //cover for errors in data or redis gets angry at a negative expiry
					Redis::setex($stop, $standardTimetable[0]['ArrivalTime'] - time(), json_encode($standardTimetable));
				}
				return $standardTimetable;
			} else {
				return FALSE;
			}
		} else {
			return FALSE;
		}
	}
	private function getManchesterData($stop){
		$timetable = DB::connection('pgsql2')->table('manchester.calendar')
			->select('stop_times.departure_time', 'trips.trip_id', 'routes.route_short_name', 'trips.trip_headsign')
			->leftJoin('manchester.trips', 'calendar.service_id', '=', 'trips.service_id')
			->leftJoin('manchester.routes', 'trips.route_id', '=', 'routes.route_id')
			->leftJoin('manchester.stop_times', 'trips.trip_id', '=', 'stop_times.trip_id')
			->where('stop_id', '=', $stop)
			->where(strtolower(date('l')), '=', '1')
			->where('start_date', '<=', date('Ymd'))
			->where('end_date', '>=', date('Ymd'))
			->orderBy('departure_time', 'asc')
			->get();
		if (count($timetable) > 0) {
			$standardTimetable = array();
			foreach($timetable as $trip){
				if (strtotime($trip->departure_time) > time()){
					$standardTimetable[] = ['BusName' => $trip->route_short_name, 'BusHeading' => $trip->trip_headsign, 'ArrivalTime' => strtotime($trip->departure_time)];
				}
			}
			if (count($standardTimetable) > 0){
				return $standardTimetable;
			} else {
				return FALSE;
			}
		} else {
			return FALSE;
		}
	}
	private function getLondonData($stop){
		$baseUrl = Config::get('traveline.tflurl');
		$urlparameters = '?StopCode2=' . $stop . '&ReturnList=LineName,DestinationText,EstimatedTime';
		$response = Httpful::get($baseUrl . $urlparameters)
			->parsewith(function($body) {
				$messages = explode("\n", $body);
				foreach ($messages as $message) {
					$message = json_decode($message, TRUE);
					if ($message[0] === 1) {
						//Hacky as shit
						$stoppingtimes[$message[3]] = ['BusName' => $message[1], 'BusHeading' => $message[2], 'ArrivalTime' => $message[3]/1000];
					}
				}
				if (!empty($stoppingtimes)){
					ksort($stoppingtimes);
					return $stoppingtimes;
				} else {
					return FALSE;
				}
			})
			->send();
		return $response->body;
	}
	public function produceTimetable($stop, $forceLive = NULL){
		// Check for stop existence
		$stopData = Stop::select(DB::raw('*, ST_AsText(location) AS location'))->where('id', '=', $stop)->get();
		$plannedData = FALSE;
		if ($stopData->isEmpty()){
			return View::make('timetable')->withTitle('Timetable')->withError('Invalid stop entered');
		} else {
			$stopData = Stop::select(DB::raw('*, ST_AsText(location) AS location'))->where('id', '=', $stop)->get();
			$coords = explode(' ', $stopData[0]['location']);
			$stopData[0]['lon'] = filter_var($coords[0], FILTER_SANITIZE_NUMBER_FLOAT, ['flags' => FILTER_FLAG_ALLOW_FRACTION]);
			$stopData[0]['lat'] = filter_var($coords[1], FILTER_SANITIZE_NUMBER_FLOAT, ['flags' => FILTER_FLAG_ALLOW_FRACTION]);
		}
		if(Redis::exists($stop)){
			$timedata = json_decode(Redis::get($stop), TRUE);
			$creditMessage = 'Retrieved from cache. Public sector information from Traveline licensed under the Open Government Licence v2.0.';
		} else if (Session::has('foreign')) {
			return Redirect::to('/regionblock');
		} else {
			// Determine the data provider to use
			if (isset($forceLive)){
				$timedata = TimetableController::getNationalData($stop);
				$creditMessage = 'Retrieved from live data per user request. Public sector information from Traveline licensed under the Open Government Licence v2.0.';
			} else if (substr($stop, 0, 3) === '180'){
				$timedata = TimetableController::getManchesterData($stop);
				$creditMessage = 'Retrieved from planned timetables and may not take into account sudden service changes. Public sector information from Transport for Greater Manchester. Contains Ordnance Survey data &copy; Crown copyright and database rights 2014';
				$plannedData = TRUE;
			} else if (substr($stop, 0, 3) === '490'){
				$timedata = TimetableController::getLondonData($stop);
				$creditMessage = 'Retrieved from live data. Data provided by Transport for London';
			} else {
				$timedata = TimetableController::getNationalData($stop);
				$creditMessage = 'Retrieved from live data. Public sector information from Traveline licensed under the Open Government Licence v2.0.';
			}
		}
		// Extract the location info
		if ($timedata === FALSE){
			return View::make('timetable')->withTitle('Timetable')->withScheduled($plannedData)->withStop($stopData)->withError('No services found at this stop.');
		} else {
			return View::make('timetable')->withTimetable($timedata)->withScheduled($plannedData)->withStop($stopData)->withTitle('Timetable')->withCredit($creditMessage);
		}
	}
}
