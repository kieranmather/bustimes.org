<?php

class TimetableController extends BaseController {
	private function getNationalData($stop){
		$stopData = Stop::where('id', '=', $stop)->get();
		if (!$stopData->isEmpty()){
			if(Redis::exists($stop)){
				$timetable = json_decode(Redis::get($stop));
				$cachedMessage = 'Retrieved from cache.';
			} else {
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
				if (isset($timetable->MonitoredStopVisit[0]->MonitoredVehicleJourney->MonitoredCall->ExpectedDepartureTime)){
					Redis::setex($stop, strtotime($timetable->MonitoredStopVisit[0]->MonitoredVehicleJourney->MonitoredCall->ExpectedDepartureTime) - time(), json_encode($timetable));
				} elseif (isset($timetable->MonitoredStopVisit[0]->MonitoredVehicleJourney->MonitoredCall->AimedDepartureTime)) {
					Redis::setex($stop, strtotime($timetable->MonitoredStopVisit[0]->MonitoredVehicleJourney->MonitoredCall->AimedDepartureTime) - time(), json_encode($timetable));
				}
			}
			return $timetable;
		} else {
			return false;
		}
	}
	public function produceTimetable($stop){
		// Check for stop existence
		$stopData = Stop::where('id', '=', $stop)->get();
		if ($stopData->isEmpty()){
			return View::make('timetable')->withTitle('Timetable')->withError('Invalid stop entered');
		}
		if(Redis::exists($stop)) {
			$timedata = json_decode(Redis::get($stop));
			$cachedMessage = 'Retrieved from cache. Public sector information from Traveline licensed under the Open Government Licence v2.0.';
		} else if (Session::has('foreign')) {
			return Redirect::to('/regionblock');
		} else {
			// Determine the data provider to use (NOT READY YET so we're just using Traveline). London (live) and Manchester (timetabled) planned.
			//if (substr($stop, 0, 3) === '180'){
			//	$timedata = TimetableController::getManchesterData($stop);
			//	$cachedMessage = 'Retrieved from planned timetables and may not take into account sudden service changes. Public sector information from Traveline licensed under the Open Government Licence v2.0.';
			//} else {
				$timedata = TimetableController::getNationalData($stop);
				$cachedMessage = 'Retrieved from live data. Public sector information from Traveline licensed under the Open Government Licence v2.0.';
			//}
		}
		return View::make('timetable')->withTimetable($timedata)->withStop($stopData)->withTitle('Timetable')->withCached($cachedMessage);
	}
}