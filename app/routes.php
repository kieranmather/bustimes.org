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
Route::get('/regionblock/', function() {
	return 'This service is only accessible in the United Kingdom and Ireland (in case you\'re near the border and we get confused). You appear to be in ' . GeoIP::getLocation()['country'] . '. If you believe you are in the UK or Ireland, check that you have not travelled to a foreign country by mistake, your ISP is not involved in some dodgy tax <del>evasion</del> planning scheme and you\'re not roaming on foreign cell towers before e-mailing me at kieran.mather [at] gmail.com.';
});
Route::get('/', function() {
	return View::make('homepage', ['title' => 'Home']);
});
Route::get('/location/', function() {
	if (Input::has('lat') && Input::has('lon')) {
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
		'InUse' => [
				'$ne' => FALSE
			]
		]
        )->limit(10)->get();
	if (!$stops->isEmpty()){
		$colours = array("black", "brown", "green", "purple", "yellow", "blue", "gray", "orange", "red", "white");
		$letter = "A";
		foreach ($stops as &$stop) {
			$stop['colour'] = current($colours);
			$stop['letter'] = $letter;
			$letter++;
			next($colours);
		}
		return View::make('stoplist')->withStops($stops)->withTitle('Stops');
	} else {
		return Redirect::to('/')->withMessage('We couldn\'t find any stops within 3km of your location.');
	}
}))->where('lon', '[0-9.-]+')->where('lat', '[0-9.-]+');
Route::get('stop/{stop}/{force?}', 'TimetableController@produceTimetable');
