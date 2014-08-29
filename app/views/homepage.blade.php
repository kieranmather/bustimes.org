@extends('master')


@section('htmlhead')
	{{HTML::script('assets/bustimes.org/geo.js')}}
	<script type="text/javascript" src="https://maps.googleapis.com/maps/api/js?key=AIzaSyDPoTi3VIEkmiFhyMoprykJOIIn4w6lBgE&amp;sensor=false"></script>
@stop


@section('header')
		<h1 class="text-center">Bustimes.org</h1>
		<h3 class="text-center">Bus times for any stop in Great Britain</h3>
@stop

@section('content')
@if(Session::has('message'))
<div class="alert alert-info">
	{{Session::get('message')}}
</div>
@endif
		<div class="row">
			<div class="col-md-6 col-md-offset-3">
				{{Form::open(['role' => 'form', 'id' => 'searchForm'] )}}
				
				<div class="input-group">
					{{Form::text('query', '', ['id' => 'searchBox', 'placeholder' => 'Place name, postcode or landmark', 'class' => 'form-control'])}}
					<span class="input-group-btn">
						{{Form::button('Go', ['class' => 'btn btn-default', 'onClick' => 'geoCode()'])}}
					</span>
				</div>
				{{Form::close()}}
			</div>
		</div>
		<div class="row">
			<div class="col-md-6 col-md-offset-3" style="padding-top:10px;">
				<p class="text-center"><button onClick="geoLocate();" class="btn btn-primary btn-lg"><span id="geolocateButton">Use my location</span> <span id="geolocateIcon" class="glyphicon glyphicon-map-marker"></span> </button></p>
			</div>
		</div>
@stop

@section('footer')
<script src="//cdnjs.cloudflare.com/ajax/libs/jquery/2.1.0/jquery.min.js"></script>
<script type="text/javascript">
	$("#searchForm").submit(function(event){

      event.preventDefault(); 
      geoCode();
  	});
</script>
@stop
