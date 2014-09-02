<?php
header('Access-Control-Allow-Origin: *');
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

Route::get('/', function()
{
	return View::make('hello');
});

Route::resource('teams', 'TeamController');
Route::resource('matches', 'MatchController');
Route::resource('lig', 'LigController');


Route::get('lig/(:any)', array('as' => 'lig', 'uses' => 'LigController@show'));

Route::post('updateLig', 'LigController@updateLig');
Route::get('updateLig', 'LigController@updateLig');

Route::post('updateScores', 'LigController@updateScores');
Route::get('updateScores', 'LigController@updateScores');

Route::get('getCurrentWeek', 'LigController@getCurrentWeek');
Route::get('updateWinners', 'LigController@updateWinners');

Route::get('playAllMatches', 'LigController@playAllMatches');

Route::post('updateMatch', 'LigController@updateMatch');
