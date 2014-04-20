
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
			&copy; Kieran Mather 2014 <span class="text-muted">Built with <a href="http://www.laravel.com">Laravel</a></span> <a href="http://www.bigv.io"><img src="{{asset('assets/bustimes.org/bigv.png')}}" alt="Powered by BigV"></a>
		</footer>
	</div>
@yield('footer')
</body>
</html>
