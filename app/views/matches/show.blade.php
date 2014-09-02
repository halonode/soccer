<!-- app/views/teams/show.blade.php -->

<!DOCTYPE html>
<html>
<head>
	<title>useinsider league</title>
	<link rel="stylesheet" href="//netdna.bootstrapcdn.com/bootstrap/3.0.0/css/bootstrap.min.css">
</head>
<body>
<div class="container">

<nav class="navbar navbar-inverse">
	<div class="navbar-header">
		<a class="navbar-brand" href="{{ URL::to('teams') }}">Teams</a>
	</div>
	<ul class="nav navbar-nav">
		<li><a href="{{ URL::to('teams') }}">View All Teams</a></li>
		<li><a href="{{ URL::to('teams/create') }}">Create a Team</a>
	</ul>
</nav>

<h1>Showing {{ $team->name }}</h1>

	<div class="jumbotron text-center">
		<h2>{{ $team->name }}</h2>
		<p>
			<strong>Team Power:</strong> {{ $team->tp }}
		</p>
	</div>

</div>
</body>
</html>