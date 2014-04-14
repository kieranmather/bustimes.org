
<!doctype html>
<html>
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, user-scalable=no">
	<title>Bustimes.org - {{$title}}</title>
	<link rel="stylesheet" href="{{asset('assets/bootstrap/css/bootstrap.min.css')}}">
	<style type="text/css">
		/* For navbar */
		body {padding-top: 40px;}
	</style>
@yield('htmlhead')
</head>
<body>
	<div class="container">
		@include('navbar', ['title' => $title])
		<div class="page-header">
@yield('header')
		</div>
@yield('content')

		<hr />
		<footer>
			&copy; Kieran Mather 2014 <span class="text-muted">Contains public sector information licensed under the <a href="http://www.nationalarchives.gov.uk/doc/open-government-licence/version/2/">Open Government Licence v2.0</a></span> Built with Laravel <span class="text-muted">Powered by BigV</span>
		</footer>
	</div>
@yield('footer')
</body>
</html>
