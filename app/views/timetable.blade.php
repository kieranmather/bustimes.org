@extends('master')

@section('header')
		<ol class="breadcrumb">
			<li>{{link_to('/', 'Home')}}</li>
			<li>{{link_to(URL::previous(), 'Stops')}}</li>
			<li class="active">Timetable</li>
		</ol>
		<h4>Timetable</h4>
		<p class="text-muted">for {{date('d/m/y')}} at {{date('H:i')}}. @if(isset($scheduled) && $scheduled === TRUE) {{link_to('/stop/' . $stop[0]->id . '/live', 'Retry with live data?')}} @endif</p>
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
		@foreach($timetable as $time)
				<tr>
					<td>{{{$time['BusName']}}}</td>
					<td>{{{$time['BusHeading']}}}</td>
					<td>
					{{{date('H:i', $time['ArrivalTime'])}}}
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
@if(!isset($error))
	<div class="col-md-6">
		<img class="img-responsive" src="https://maps.googleapis.com/maps/api/staticmap?sensor=false&amp;key=AIzaSyDPoTi3VIEkmiFhyMoprykJOIIn4w6lBgE&amp;size=500x500&amp;scale=2&amp;markers=color:red|{{{$stop[0]->lat}}},{{{$stop[0]->lon}}}" alt="Map">
		<p class="text-muted">{{$credit}}</p>
	</div>
@endif
</div>
@stop
