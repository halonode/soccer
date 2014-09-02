<!DOCTYPE html>
<html>
<head>
	<title>useinsider league</title>
	<link rel="stylesheet" href="//netdna.bootstrapcdn.com/bootstrap/3.0.0/css/bootstrap.min.css">

	<script src="//cdnjs.cloudflare.com/ajax/libs/jquery/2.1.1/jquery.min.js"></script>

	<script src="//cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/3.2.0/js/tooltip.min.js"></script>
	<script src="//cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/3.2.0/js/popover.min.js"></script>

	<link href="/soccer/public/css/xeditable.css" rel="stylesheet">
	


	<script src="//cdnjs.cloudflare.com/ajax/libs/angular.js/1.2.20/angular.js"></script>
	<script src="//cdnjs.cloudflare.com/ajax/libs/angular.js/1.2.20/angular-loader.js"></script>
	
	<script src="//cdnjs.cloudflare.com/ajax/libs/angular.js/1.2.20/angular-route.js"></script>
	<script src="//cdnjs.cloudflare.com/ajax/libs/angular.js/1.2.20/angular-resource.js"></script>
	<script src="//cdnjs.cloudflare.com/ajax/libs/angular.js/1.2.20/angular-sanitize.js"></script>
	<script src="//cdnjs.cloudflare.com/ajax/libs/underscore.js/1.6.0/underscore.js"></script>
	<script src="/soccer/public/js/xeditable.js"></script>
  <script>
    angular.module("app",[]).constant("CSRF_TOKEN", '<?php echo csrf_token(); ?>');
  </script>
  <style>
  .displayFix,.displayWinnerFix{display: none}
  .fixButtons{margin-top:80px;}
  </style>

</head>
<body>
<div class="container" ng-app="App">

<nav class="navbar navbar-inverse">
	<div class="navbar-header">
		<a class="navbar-brand" href="{{ URL::to('lig') }}">League</a>
	</div>
	<ul class="nav navbar-nav">
		<li><a href="{{ URL::to('teams') }}">View All Teams</a></li>
		<li><a href="{{ URL::to('teams/create') }}">Create a Team</a>
		<li><a href="{{ URL::to('matches') }}">View All Matches</a></li>
		<li><a href="{{ URL::to('matches/create') }}">(re)Generate Matches</a>
		<li><a href="{{ URL::to('lig') }}">Show League</a>
	</ul>
</nav>



<!-- will be used to show any messages -->
@if (Session::has('message'))
	<div class="alert alert-info">{{ Session::get('message') }}</div>
@endif



<div class="col-md-6" ng-controller="LeagueController">
<h1>League Status <img id="spinner" ng-src="/soccer/public/images/spinner.gif" style="display:none;"></h1>
<table class="table table-striped table-bordered ">
	<thead>
		<tr>
			<td class="col-md-3">Team</td>
			<td class="col-md-1">Point</td>
			<td class="col-md-1">Played</td>
			<td class="col-md-1">Win</td>
			<td class="col-md-1">Draw</td>
			<td class="col-md-1">Lost</td>
			<td class="col-md-1">Goals for</td>
			<td class="col-md-1">Goals against</td>
			<td class="col-md-1">Goal diff</td>
		</tr>
	</thead>
	<tbody>
		<tr ng-repeat="data in tempTeams">
			<td>@{{ data.name }}</td>
			<td>@{{ data.totalPoints }}</td>
			<td>@{{ data.totalPlayed }}</td>
			<td>@{{ data.totalWins }}</td>
			<td>@{{ data.totalDraws }}</td>
			<td>@{{ data.totalLosts }}</td>
			<td>@{{ data.totalGoals }}</td>
			<td>@{{ data.totalGoalAgainsts }}</td>
			<td>@{{ data.totalGoalDifferences }}</td>
		</tr>
	</tbody>
</table>


</div>


<div class="col-md-6" ng-controller="ScoresController">
<h1 class="displayFix">Week @{{ week }} Match Scores</h1>
<table class="table table-striped table-bordered displayFix">
	<thead>
		<tr>
			<td class="col-md-3">Home Team</td>
			<td class="col-md-1">Score</td>
			<td class="col-md-1">Away Team</td>
	</thead>
	<tbody>
		<tr ng-repeat="data in tempScores">
			<td>@{{ data.homeTeamName }}</td>
			<td><a href="#" editable-text="data.homeScore" onbeforesave="updateHomeMatch($data)">@{{ data.homeScore }}</a> - <a  href="#" editable-text="data.awayScore" onbeforesave="updateAwayMatch($data)">@{{ data.awayScore }}</a></td>
			<td>@{{ data.awayTeamName }}</td>
		</tr>
	</tbody>
</table>

<table class="table table-striped table-bordered displayWinnerFix">
	<thead>
		<tr>
			<td class="col-md-3">Team</td>
			<td class="col-md-1">Win Probability</td>
	</thead>
	<tbody>
		<tr ng-repeat="data in tempWinners">
			<td>@{{ data.teamName }}</td>
			<td>@{{ data.teamChance }}</td>
		</tr>
	</tbody>
</table>

	<div class="fixButtons">
	<a ng-click="playNext(week)" class="btn btn-small btn-success playNext" href="#">Play Next Matches</a>
	<a ng-click="playAll()" class="btn btn-small btn-info playAll" href="#">Play All Matches</a>
	</div>
</div>




</div>

<script type="text/javascript">
var app = angular.module('App',['ngResource', 'ngRoute','xeditable']);

app.run(function(editableOptions) {
  editableOptions.theme = 'bs3'; 
});

app.config(function($routeProvider,$httpProvider) {

	  $routeProvider.when('/soccer/public/lig/:id', {
            templateUrl: function(urlattr){
                return '/soccer/public/lig/' + urlattr.id;
            },
            controller: 'LeagueController'
        });

	  $httpProvider.responseInterceptors.push('myHttpInterceptor');
	  var spinnerFunction = function spinnerFunction(data, headersGetter) {
	    $("#spinner").show();
	    return data;
	  };
	  $httpProvider.defaults.transformRequest.push(spinnerFunction);

});

app.factory('myHttpInterceptor', function ($q, $window) {
  return function (promise) {
    return promise.then(function (response) {
      $("#spinner").hide();
      return response;
    }, function (response) {
      $("#spinner").hide();
      return $q.reject(response);
    });
  };
});


app.controller('LeagueController', function($scope, $http) {
  // Function to get the data
  $scope.loading = true;
  $scope.getData = function(){

 				$http.get('/soccer/public/updateLig')
			    .success(function (d) {
				  $scope.tempTeams=d;
				  $scope.loading = false;
			    });
  
  };
  // Refresh league data every second
  setInterval($scope.getData, 2000);
});

app.controller('ScoresController', function($scope, $http) {


$scope.updateHomeMatch = function(data) {
	this.$parent.data.homeScore=data;
    return $http.post('/soccer/public/updateMatch', this.$parent.data);
};

$scope.updateAwayMatch = function(data) {
	this.$parent.data.awayScore=data;
    return $http.post('/soccer/public/updateMatch', this.$parent.data);
};

	$http.get('/soccer/public/getCurrentWeek')
			    .success(function (d) {
				  $scope.week=d;
			    });

	$scope.playNext = function(id) {

		$('.displayFix').css("display","block");
		$('.fixButtons').css("margin-top","0px");
			$scope.loading = true; 				 
			$http.get('/soccer/public/getCurrentWeek')
			    .success(function (d) {
				  $scope.week=d;
				  if(d=={{Team::calcLeagueLength()}}){
				  	$('a.playNext').addClass("disabled");
				  	$('a.playAll').addClass("disabled");
				  }
				  if(d>3){
				  	$('.displayWinnerFix').css("display","block");
				  	$http.get('/soccer/public/updateWinners')
				    .success(function (ddd) {
					  $scope.tempWinners=ddd;
				    });
				  }
					  $http.get('/soccer/public/updateScores', {
				        params: {
				            week: d,
				        }
				     })
				    .success(function (dd) {
					  $scope.tempScores=dd;
				    });
			    });

		};

		$scope.playAll = function() {

		$('.displayFix').css("display","block");
		$('.fixButtons').css("margin-top","0px");
		$('.displayWinnerFix').css("display","block");

		$http.get('/soccer/public/playAllMatches')
			.success(function (data) {
			$scope.tempScores=data;

			$http.get('/soccer/public/getCurrentWeek')
			    .success(function (d) {
				$scope.week=d;
				});

			$http.get('/soccer/public/updateWinners')
				.success(function (ddd) {
				$scope.tempWinners=ddd;
				});

			});

		};

});

</script>

</body>
</html>