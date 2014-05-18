@extends('master')

@section('header')
		<ol class="breadcrumb">
			<li>{{link_to('/', 'Home')}}</li>
			<li>{{link_to(URL::previous(), 'Stops')}}</li>
			<li class="active">Timetable</li>
		</ol>
		<h4>Timetable</h4>
		<p class="text-muted">for {{date('d/m/y')}} at {{date('H:i')}}</p>
@stop

@section('content')
<div class="row">
	<div class="col-md-6">
@if(isset($timetable))
		<table class="table">
			<thead>
				<tr>
					<th>Service</th>
					<th>Destination</th>
					<th>Time</th>
				</tr>
			</thead>
			<tbody>
		@foreach($timetable->MonitoredStopVisit as $time)
				<tr>
					<td>{{{$time->MonitoredVehicleJourney->PublishedLineName}}}</td>
					<td>{{{$time->MonitoredVehicleJourney->DirectionName}}}</td>
					<td>
					@if (isset($time->MonitoredVehicleJourney->MonitoredCall->ExpectedDepartureTime))
					{{{date('H:i', strtotime($time->MonitoredVehicleJourney->MonitoredCall->ExpectedDepartureTime))}}}
					@else
					{{{date('H:i', strtotime($time->MonitoredVehicleJourney->MonitoredCall->AimedDepartureTime))}}}
					@endif
					</td>
				</tr>
		@endforeach
			</tbody>
		</table>
@elseif(isset($error))
		<div class="alert alert-warning">
			{{$error}}
		</div>
@endif
	</div>
@if(isset($stop))
	<div class="col-md-6">
		<img class="img-responsive" src="https://maps.googleapis.com/maps/api/staticmap?sensor=false&amp;key=AIzaSyDPoTi3VIEkmiFhyMoprykJOIIn4w6lBgE&amp;size=500x500&amp;scale=2&amp;markers=color:red|{{$stop[0]->location['coordinates'][1]}},{{$stop[0]->location['coordinates'][0]}}" alt="Map">
		<p class="text-muted">{{$cached}} Public sector information from Traveline licensed under the Open Government Licence v2.0.</p>
		<div class="well">
			<input class="form-control" type="text" value="http://bust.ml/{{{$stop[0]->id}}}" readonly>
		</div>
	</div>
@endif
</div>
@stop
