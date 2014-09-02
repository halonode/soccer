<?php

class LigController extends \BaseController {

	/**
	 * Display a listing of the resource.
	 *
	 * @return Response
	 */
	public function index()
	{

		$matches = Match::all();
		$teams = Team::all();
		$results = Result::all();
		$tempTeams = array();
		$id=0;
		
		foreach ($teams as $team) {
			$teamArray=$team->toArray();
			$teamArray["totalPoints"] = $team->totalPoints();
			$teamArray["totalWins"] = $team->totalWins();
			$teamArray["totalDraws"] = $team->totalDraws();	
			$teamArray["totalLosts"] = $team->totalLosts();
			$teamArray["totalGoals"] = $team->totalGoals();	
			$teamArray["totalGoalAgainsts"] = $team->totalGoalAgainsts();	
			$teamArray["totalGoalDifferences"] = $team->totalGoalDifferences();				
			$tempTeams[]=$teamArray;
		}
		
		$temp = array();
		foreach ($tempTeams as $key => $row)
		{
		    $temp[$key] = $row['totalPoints'];
		}
		array_multisort($temp, SORT_DESC, $tempTeams);

		//
		//$this->playWeek(1);
		//$this->playAllWeeks();
		// load the view and pass the data
		return View::make('lig.index', compact('matches','teams','results','tempTeams','id'));
	}

	public function comparePoints($a, $b)
	{
		    if ($a['points'] == $b['points']) 
		    {
		        if ($a['goalsdiff'] == $b['goalsdiff']) return 0;
		        return ($a['goalsdiff'] < $b['goalsdiff']) ? 1 : -1 ;
		    }       
		    return ($a['points'] < $b['points']) ? 1 : -1 ;
	}

	public function updateMatch(){
		$data=Input::get();
		$this->replayMatch($data["id"],$data["homeScore"],$data["awayScore"]);
	}

	public function replayMatch($matchId,$homeGoals,$awayGoals){
		$match=Match::find($matchId);
		$home=Team::find($match->home_id);
		$away=Team::find($match->away_id);
		//clear old results
		Result::where('match_id', '=', $matchId)->delete();

		if($homeGoals==$awayGoals){
			$goalDifference =	0;
			$draw			=	1;
			$win			=	0;
			$lose			=	0;
			$this->createResult($match->id,$match->home_id,$match->week,
				$win,$lose,$draw,$homeGoals,$awayGoals,$goalDifference);
			$this->createResult($match->id,$match->away_id,$match->week,
				$win,$lose,$draw,$awayGoals,$homeGoals,$goalDifference);			
		}else if ($homeGoals>$awayGoals){
			$homeDraw		=	0;
			$homeWin		=	1;
			$homeLose		=	0;	
			$awayDraw		=	0;
			$awayWin		=	0;
			$awayLose		=	1;
			$goalDifference = $homeGoals-$awayGoals;

			$this->createResult($match->id,$match->home_id,$match->week,
				$homeWin,$homeLose,$homeDraw,$homeGoals,$awayGoals,$goalDifference);
			$this->createResult($match->id,$match->away_id,$match->week,
				$awayWin,$awayLose,$awayDraw,$awayGoals,$homeGoals,-$goalDifference);	
		}else{
			$homeDraw		=	0;
			$homeWin		=	0;
			$homeLose		=	1;	
			$awayDraw		=	0;
			$awayWin		=	1;
			$awayLose		=	0;
			$goalDifference = $homeGoals-$awayGoals;

			$this->createResult($match->id,$match->home_id,$match->week,
				$homeWin,$homeLose,$homeDraw,$homeGoals,$awayGoals,$goalDifference);
			$this->createResult($match->id,$match->away_id,$match->week,
				$awayWin,$awayLose,$awayDraw,$awayGoals,$homeGoals,-$goalDifference);		
		}
		

	}

	public function playWeek($week){
		$matchesOfWeek=Match::where('week', '=', $week)->get();
		foreach ($matchesOfWeek as $match) {
			if(!$match->played){
				$this->playMatch($match->id);
				$match->played=true;
				$match->save();
			}			
		}
	}

	public function playAllWeeks(){
		$matches=Match::all();
		$results=Result::all();
		if(count($results)<=0){
			foreach ($matches as $match) {
				$this->playMatch($match->id);
				$match->played=true;
				$match->save();
			}
		}else{
			Result::truncate();
			Match::truncate();
			Team::createLeague();
			$matches=Match::all();
			foreach ($matches as $match) {
				$this->playMatch($match->id);
				$match->played=true;
				$match->save();
			}
		}
		
	}

	public function playMatch($matchId){
		$match=Match::find($matchId);
		$home=Team::find($match->home_id);
		$away=Team::find($match->away_id);

		//Teampower based scores
		$homePower = $home->tp;
		$awayPower = $away->tp;
		//Fix for bad team power to min power
		if($homePower == 0 ){$homePower=1;}
		if($awayPower == 0 ){$awayPower=1;}

		$homeGoals = intval(abs($homePower/$awayPower) + RAND()  / 10000);
		$awayGoals = intval(abs($awayPower/$homePower) + RAND()  / 10000);

		if($homeGoals==$awayGoals){
			$goalDifference =	0;
			$draw			=	1;
			$win			=	0;
			$lose			=	0;
			$this->createResult($match->id,$match->home_id,$match->week,
				$win,$lose,$draw,$homeGoals,$awayGoals,$goalDifference);
			$this->createResult($match->id,$match->away_id,$match->week,
				$win,$lose,$draw,$awayGoals,$homeGoals,$goalDifference);			
		}else if ($homeGoals>$awayGoals){
			$homeDraw		=	0;
			$homeWin		=	1;
			$homeLose		=	0;	
			$awayDraw		=	0;
			$awayWin		=	0;
			$awayLose		=	1;
			$goalDifference = $homeGoals-$awayGoals;

			$this->createResult($match->id,$match->home_id,$match->week,
				$homeWin,$homeLose,$homeDraw,$homeGoals,$awayGoals,$goalDifference);
			$this->createResult($match->id,$match->away_id,$match->week,
				$awayWin,$awayLose,$awayDraw,$awayGoals,$homeGoals,-$goalDifference);	
		}else{
			$homeDraw		=	0;
			$homeWin		=	0;
			$homeLose		=	1;	
			$awayDraw		=	0;
			$awayWin		=	1;
			$awayLose		=	0;
			$goalDifference = $homeGoals-$awayGoals;

			$this->createResult($match->id,$match->home_id,$match->week,
				$homeWin,$homeLose,$homeDraw,$homeGoals,$awayGoals,$goalDifference);
			$this->createResult($match->id,$match->away_id,$match->week,
				$awayWin,$awayLose,$awayDraw,$awayGoals,$homeGoals,-$goalDifference);		
		}
		

	}

	public function createResult($match_id,$team_id,$week,$win,$lose,$draw,$homeGoals,$awayGoals,$goalDifference){
		$result = new Result;
		$result->match_id       	= $match_id;
		$result->team_id 			= $team_id;
		$result->week 				= $week;
		$result->win 				= $win;
        $result->lose 				= $lose;
        $result->draw 				= $draw;
        $result->goal_for 			= $homeGoals;
        $result->goal_against 		= $awayGoals;
        $result->goal_difference	= $goalDifference;
		$result->save();
	}


	public function updateLig() {
		$teams = Team::all();
		$tempTeams = array();
		
		foreach ($teams as $team) {
			$teamArray=$team->toArray();
			$teamArray["totalPoints"] = $team->totalPoints();
			$teamArray["totalPlayed"] = $team->totalPlayed();
			$teamArray["totalWins"] = $team->totalWins();
			$teamArray["totalDraws"] = $team->totalDraws();	
			$teamArray["totalLosts"] = $team->totalLosts();
			$teamArray["totalGoals"] = $team->totalGoals();	
			$teamArray["totalGoalAgainsts"] = $team->totalGoalAgainsts();	
			$teamArray["totalGoalDifferences"] = $team->totalGoalDifferences();				
			$tempTeams[]=$teamArray;
		}
		
		$temp = array();
		foreach ($tempTeams as $key => $row)
		{	
		    $temp[$key] = $row['totalPoints'];
		}
		array_multisort($temp, SORT_DESC, $tempTeams);

		if($tempTeams[0]['totalPoints'] == $tempTeams[1]['totalPoints']){
			foreach ($tempTeams as $key => $row)
			{	
			    $temp[$key] = $row['totalGoalDifferences'];
			}
			array_multisort($temp, SORT_DESC, $tempTeams);
		}
	
	    return Response::json($tempTeams);
	}

	public function playAllMatches() {
		$this->playAllWeeks();
		$matches = Match::all();
			$tempResults=array();
			foreach ($matches as $match) {
				$tempScores=$match->toArray();
				$tempScores["matchId"] = $match->id ;
				$tempScores["homeId"] = $match->home_id;
				$tempScores["homeTeamName"] = Team::find($match->home_id)->name;
				$tempScores["homeScore"] = Result::where('match_id','=',$match->id)->first()->goal_for;
				$tempScores["awayId"] = $match->away_id;
				$tempScores["awayTeamName"] = Team::find($match->away_id)->name;
				$tempScores["awayScore"] = Result::where('match_id','=',$match->id)->first()->goal_against;
				$tempResults[]=$tempScores;
			}
			return Response::json($tempResults);
	}

	public function updateScores() {
		$week=Input::get('week');
		if($week!=0){

			$this->playWeek($week);

			$matches = Match::where('week','=',$week)->get();
			$tempResults=array();

			foreach ($matches as $match) {
				$tempScores=$match->toArray();
				$tempScores["matchId"] = $match->id ;
				$tempScores["homeId"] = $match->home_id;
				$tempScores["homeTeamName"] = Team::find($match->home_id)->name;
				$tempScores["homeScore"] = Result::where('match_id','=',$match->id)->first()->goal_for;
				$tempScores["awayId"] = $match->away_id;
				$tempScores["awayTeamName"] = Team::find($match->away_id)->name;
				$tempScores["awayScore"] = Result::where('match_id','=',$match->id)->first()->goal_against;
				$tempResults[]=$tempScores;
			}


			return Response::json($tempResults);
		}else{
			return Response::json(0);
		}
		
	}

	public function updateWinners() {
			$teams = Team::all();
			$tempChances=array();
			$tempWinner=array();
			$chanceW=0;
			foreach ($teams as $team) {
				$chance = ($team->totalWins() + $team->totalDraws()/3) / ($team->totalWins() + $team->totalLosts() * 3 + $team->totalDraws()/3);	
				$chanceR =round($chance * 100);
				$chanceW = $chanceW + $chanceR;
				$tempChances[$team->name]=$chanceR;
			}
			foreach ($tempChances as $key=>$val) {
				$tempWinner[] = array("teamName"=>$key,"teamChance"=>abs(round($val * 100 / $chanceW)));
			}

			$temp = array();
			foreach ($tempWinner as $key => $row)
			{
			    $temp[$key] = $row['teamChance'];
			}
			array_multisort($temp, SORT_DESC, $tempWinner);

			if($this->getCurrentWeek() >= Team::calcLeagueLength()){
				$tempWinner[0]["teamChance"] ="Winner!"; 
				for ($i=1; $i < count($tempWinner); $i++) { 
					$tempWinner[$i]["teamChance"] ="No way!";
				}
			}

			return Response::json($tempWinner);
	}

	public function getCurrentWeek(){
		$week=DB::table('matches')->where('played', '=', 1)->groupBy('week')->get();
		if(count($week)<Team::calcLeagueLength()){
			$week=count($week)+1;
		}else{
			$week=Team::calcLeagueLength();
		}
		return $week;
	}


	/**
	 * Show the form for creating a new resource.
	 *
	 * @return Response
	 */
	public function create()
	{
		//
	}

	/**
	 * Store a newly created resource in storage.
	 *
	 * @return Response
	 */
	public function store()
	{
		//
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
		return View::make('lig.index', compact('id'));
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
	}

}