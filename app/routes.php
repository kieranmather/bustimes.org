<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It's a breeze. Simply tell Laravel the URIs it should respond to
| and give it the Closure to execute when that URI is requested.
|
*/

Route::get('/', function() {
	return View::make('homepage', ['title' => 'Home']);
});
Route::get('/location/', function() {
	if (Input::has('lat') AND Input::has('lon')) {
		return Redirect::to('/location/' . Input::get('lat') . '/' . Input::get('lon'));
	} else {
		return Redirect::to('/')->withMessage('An error occurred.');
	}
});
Route::get('/location/{lat}/{lon}', array('as' => 'location', function($lat, $lon) {
	$stops = Stop::whereRaw([
		'location' => [
			'$near' => [
				'$geometry' => [
					"type" => "Point",
					"coordinates" => [floatval($lon), floatval($lat)]
					],
				'$maxDistance' => 3000
				]
			],
		'InUse' => TRUE,
		]
        )->limit(10)->get();
	$colours = array("black", "brown", "green", "purple", "yellow", "blue", "gray", "orange", "red", "white");
	$letter = "A";
	foreach ($stops as &$stop) {
		$stop['colour'] = current($colours);
		$stop['letter'] = $letter;
		$letter++;
		next($colours);
	}
	return View::make('stoplist')->withStops($stops)->withTitle('Stops');
}))->where('lon', '[0-9.-]+')->where('lat', '[0-9.-]+');
Route::get('/stop/{stop}', function($stop){
	$stopData = Stop::where('id', '=', $stop)->get();
	$url = 'http://nextbus.mxdata.co.uk/nextbuses/1.0/1';
	$date =  date("Y-m-d\TH:i:s\Z");
	$id = rand(100000, 999999);
	$request = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
				<Siri version="1.0" xmlns="http://www.siri.org.uk/">
				<ServiceRequest>
				<RequestTimestamp>' . $date . '</RequestTimestamp>
				<RequestorRef>TravelineAPI180</RequestorRef>
				<StopMonitoringRequest version="1.0">
				<RequestTimestamp>' . $date . '</RequestTimestamp>
				<MessageIdentifier>' . $id . '</MessageIdentifier>
				<MonitoringRef>' . $stop . '</MonitoringRef>
				</StopMonitoringRequest>
				</ServiceRequest>
				</Siri>';
	$response = Httpful::post($url)->body($request)->authenticateWith(Config::get('traveline.username'), Config::get('traveline.password'))->sendsXml()->expectsXml()->send();
	$timetable = $response->body->ServiceDelivery->StopMonitoringDelivery;
	return View::make('timetable')->withTimetable($timetable)->withStop($stopData)->withTitle('Timetable');
});