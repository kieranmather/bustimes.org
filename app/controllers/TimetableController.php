<?php

class TimetableController extends BaseController {
	private function getNationalData($stop){
		$stopData = Stop::where('id', '=', $stop)->get();
		if (!$stopData->isEmpty()){
			$url = 'http://nextbus.mxdata.co.uk/nextbuses/1.0/1';
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
				$standardTimetable[] = ['BusName' => (string) $trip->MonitoredVehicleJourney->PublishedLineName, 'BusHeading' => (string) $trip->MonitoredVehicleJourney->DirectionName, 'ArrivalTime' => (string) $trip->MonitoredVehicleJourney->MonitoredCall->AimedDepartureTime];
			}
			if (isset($standardTimetable[0]['ArrivalTime'])){
				Redis::setex($stop, strtotime($standardTimetable[0]['ArrivalTime']) - time(), json_encode($standardTimetable));
			} else {
				return View::make('timetable')->withTitle('Timetable')->withError('No services found at this stop.');
			}
			return $standardTimetable;
		} else {
			return false;
		}
	}
	private function getManchesterData($stop){
		$stopData = Stop::where('id', '=', $stop)->get();
		$timetable = DB::connection('mysql')->select('SELECT `stop_times`.`departure_time`,`trips`.`trip_id`,`routes`.`route_short_name`,`routes`.`route_long_name` FROM calendar
			LEFT JOIN `gtfs`.`trips` ON `calendar`.`service_id` = `trips`.`service_id` 
			LEFT JOIN `gtfs`.`routes` ON `trips`.`route_id` = `routes`.`route_id` 
			LEFT JOIN `gtfs`.`stop_times` ON `trips`.`trip_id` = `stop_times`.`trip_id` 
			WHERE(( stop_id = ?) AND ( sunday = 1) AND ( start_date <= 20140531) AND ( end_date >= 20140531))
			ORDER BY departure_time', [$stop]);
		$standardTimetable = array();
		foreach($timetable as $trip){
			if (strtotime($trip->departure_time) > time()){
				$standardTimetable[] = ['BusName' => $trip->route_short_name, 'BusHeading' => $trip->route_long_name, 'ArrivalTime' => $trip->departure_time];
			}
		}
		return $standardTimetable;
	}
	public function produceTimetable($stop){
		// Check for stop existence
		$stopData = Stop::where('id', '=', $stop)->get();
		if ($stopData->isEmpty()){
			return View::make('timetable')->withTitle('Timetable')->withError('Invalid stop entered');
		}
		if(Redis::exists($stop)) {
			$timedata = json_decode(Redis::get($stop), TRUE);
			$creditMessage = 'Retrieved from cache. Public sector information from Traveline licensed under the Open Government Licence v2.0.';
		} else if (Session::has('foreign')) {
			return Redirect::to('/regionblock');
		} else {
			// Determine the data provider to use (NOT READY YET so we're just using Traveline). London (live) and Manchester (timetabled) planned.
			if (substr($stop, 0, 3) === '180'){
				$timedata = TimetableController::getManchesterData($stop);
				$creditMessage = 'Retrieved from planned timetables and may not take into account sudden service changes. Public sector information from Traveline licensed under the Open Government Licence v2.0.';
			} else {
				$timedata = TimetableController::getNationalData($stop);
				$creditMessage = 'Retrieved from live data. Public sector information from Traveline licensed under the Open Government Licence v2.0.';
			}
		}
		return View::make('timetable')->withTimetable($timedata)->withStop($stopData)->withTitle('Timetable')->withCredit($creditMessage);
	}
}