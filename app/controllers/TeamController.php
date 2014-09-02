<?php

class TeamController extends \BaseController {

	/**
	 * Display a listing of the resource.
	 *
	 * @return Response
	 */
	public function index()
	{
		// get all the teams
		$teams = Team::all();

		// load the view and pass the teams
		return View::make('teams.index')
			->with('teams', $teams);
	}

	/**
	 * Show the form for creating a new resource.
	 *
	 * @return Response
	 */
	public function create()
	{
		//
		return View::make('teams.create');
	}

	/**
	 * Store a newly created resource in storage.
	 *
	 * @return Response
	 */
	public function store()
	{
		//
		$rules = array(
			'name'       => 'required',
			'tp' => 'required|numeric'
		);
		$validator = Validator::make(Input::all(), $rules);

		// process the login
		if ($validator->fails()) {
			return Redirect::to('teams/create')
				->withErrors($validator)
				->withInput(Input::except('password'));
		} else {
			// store
			$team = new Team;
			$team->name       	= Input::get('name');
			$team->tp 			= Input::get('tp');
			$team->save();

			// redirect
			Session::flash('message', 'Successfully created team!');
			return Redirect::to('teams');
		}
	}

	/**
	 * Display the specified resource.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function show($id)
	{
		//
		// get the team
		$team = Team::find($id);

		// show the view and pass the team to it
		return View::make('teams.show')
			->with('team', $team);
	}

	/**
	 * Show the form for editing the specified resource.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function edit($id)
	{
		//
		// get the team
		$team = Team::find($id);

		// show the edit form and pass the team
		return View::make('teams.edit')
			->with('team', $team);
	}

	/**
	 * Update the specified resource in storage.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function update($id)
	{
		//
		$rules = array(
			'name'       => 'required',
			'tp' => 'required|numeric'
		);
		$validator = Validator::make(Input::all(), $rules);

		// process the login
		if ($validator->fails()) {
			return Redirect::to('teams/' . $id . '/edit')
				->withErrors($validator)
				->withInput(Input::except('password'));
		} else {
			// store
			$team = Team::find($id);
			$team->name       = Input::get('name');
			$team->tp = Input::get('tp');
			$team->save();

			// redirect
			Session::flash('message', 'Successfully updated team!');
			return Redirect::to('teams');
		}
	}

	/**
	 * Remove the specified resource from storage.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function destroy($id)
	{
		//
		// delete
		$team = Team::find($id);
		$team->delete();

		// redirect
		Session::flash('message', 'Successfully deleted the team!');
		return Redirect::to('teams');
	}

	

}
