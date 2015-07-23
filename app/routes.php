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
	if (Input::has('lat') && Input::has('lon')) {
		return Redirect::to('/location/' . Input::get('lat') . '/' . Input::get('lon'));
	} else {
		return Redirect::to('/')->withMessage('An error occurred.');
	}
});
Route::get('/location/{lat}/{lon}', array('as' => 'location', function($lat, $lon) {
	/*$stops = Stop::whereRaw([
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
        )->limit(10)->get();*/
	$stops = Stop::select(
		DB::raw('*, ST_AsText(location) AS location'))
		->whereRaw('ST_DWithin(location, ST_GeographyFromText(\'SRID=4326;POINT(' . floatval($lon) . ' ' . floatval($lat) . ')\'), 1000)')
		->orderByRaw('ST_Distance(location, ST_GeographyFromText(\'SRID=4326;POINT(' . floatval($lon) . ' ' . floatval($lat) . ')\'))')
		->limit(10)
		->get();
	if (!$stops->isEmpty()){
		$colours = array("black", "brown", "green", "purple", "yellow", "blue", "gray", "orange", "red", "white");
		$letter = "A";
		foreach ($stops as &$stop) {
			$stop['colour'] = current($colours);
			$stop['letter'] = $letter;
			$letter++;
			next($colours);
			$coords = explode(' ', $stop['location']);
			$stop['lon'] = filter_var($coords[0], FILTER_SANITIZE_NUMBER_FLOAT, ['flags' => FILTER_FLAG_ALLOW_FRACTION]);
			$stop['lat'] = filter_var($coords[1], FILTER_SANITIZE_NUMBER_FLOAT, ['flags' => FILTER_FLAG_ALLOW_FRACTION]);
		}
		return View::make('stoplist')->withStops($stops)->withTitle('Stops');
	} else {
		return Redirect::to('/')->withMessage('We couldn\'t find any stops within 3km of your location.');
	}
}))->where('lon', '[0-9.-]+')->where('lat', '[0-9.-]+');
Route::get('stop/{stop}/{force?}', 'TimetableController@produceTimetable');
