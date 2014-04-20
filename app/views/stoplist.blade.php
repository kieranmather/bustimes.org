@extends('master')

@section('header')
	<ol class="breadcrumb">
	  <li>{{link_to('/', 'Home')}}</li>
	  <li class="active">Stops</li>
	</ol>
	<h4>Stops</h4>
@stop

@section('content')
<div class="row">
	<div class="col-md-6">
		<table class="table">
			<thead>
				<tr>
					<th>Stop</th>
					<th>Map pin<th>
				</tr>
			</thead>
			<tbody>
@foreach($stops as $stop)
			<tr><td>{{link_to('/stop/' . $stop->id, $stop->name . ' (' . $stop->indicator . ')')}}</td><td>{{$stop->letter}} ({{$stop->colour}})</td></tr>
@endforeach
			</tbody>
		</table>
	</div>
	<div class="col-md-6">
		<img class="img-responsive" alt="Map" src="https://maps.googleapis.com/maps/api/staticmap?sensor=false&amp;key=AIzaSyDPoTi3VIEkmiFhyMoprykJOIIn4w6lBgE&amp;size=500x500&amp;scale=2 @foreach($stops as $stop)&amp;markers=label:{{{$stop->letter}}}|color:{{{$stop->colour}}}|{{{$stop->location['coordinates'][1]}}},{{{$stop->location['coordinates'][0]}}}@endforeach ">
		<p class="text-muted>Public sector information from the Department for Transport licensed under the Open Government Licence v2.0.</p>
	</div>
</div>
@stop
