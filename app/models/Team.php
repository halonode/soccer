<?php

class Team extends Eloquent
{
	public function totalPoints(){
		$resultWinPoints = Result::where("team_id","=",$this->id)->sum("win");
		$resultDrawPoints = Result::where("team_id","=",$this->id)->sum("draw");
		return ($resultWinPoints * 3) + $resultDrawPoints;
	}

	public function totalPlayed(){
		$resultWins = Match::where("played","=",1)->get();
		return count($resultWins)/2;
	}

	public function totalWins(){
		$resultWins = Result::where("team_id","=",$this->id)->sum("win");
		return $resultWins;
	}

	public function totalDraws(){
		$resultDraws = Result::where("team_id","=",$this->id)->sum("draw");
		return $resultDraws;
	}

	public function totalLosts(){
		$resultLosts = Result::where("team_id","=",$this->id)->sum("lose");
		return  $resultLosts;
	}
	public function totalGoals(){
		$resultGoals = Result::where("team_id","=",$this->id)->sum("goal_for");
		return  $resultGoals;
	}
	public function totalGoalAgainsts(){
		$resultGoalAgainsts = Result::where("team_id","=",$this->id)->sum("goal_against");
		return  $resultGoalAgainsts;
	}

	public function totalGoalDifferences(){
		$resultGoalDifferences = Result::where("team_id","=",$this->id)->sum("goal_difference");
		return  $resultGoalDifferences;
	}

	public static function createLeague(){
		$teams=Team::all();

		$rounds_count = Team::calcLeagueLength();
		$matches_per_round = count($teams) / 2;
		$teams_count = count($teams);
		$awayMatches = array();
		$homeMatches = array();
		$crossMatches = array();

		//Round Robin Tournament Algorithm for Schedule
		for( $round = 0; $round < $rounds_count-$matches_per_round; $round++)
		{
		    for( $match = 0; $match < $matches_per_round; $match++)
		    {
		         $home = ($round + $match) % ($teams_count  );
		         $away = ($teams_count - 1 - $match + $round) % ($teams_count);
		      		 // Fix for Round Robin Tournament Algorithm.
		       		 $crossAway=(($round + $match) % ($teams_count ));
		       		 $crossHome=($teams_count + $matches_per_round - $match - $round) % ($teams_count - $matches_per_round);
		       		 if($crossAway == $crossHome){$crossHome= $crossHome+$matches_per_round;}
		       		 $crossMatches[$round] = $crossAway .":" . $crossHome;
		       
		        $homeMatches[$round] = $home . ":" . $away;
		        $awayMatches[$round] = $away . ":" . $home;
		        $finalFixture = array_merge_recursive($homeMatches,$awayMatches,$crossMatches);
		    }
		}

		//Creating match rounds based on finalFixture
		$matchRounds = array();
		$matchForRound = 2;
		for ($i=0; $i < $rounds_count; $i++) { 
			if($i < 4){
				if($i<$matchForRound){
				$matchRounds[$i][0]=$finalFixture[$i];
				$matchRounds[$i][1]=$finalFixture[$i+$matchForRound];
				}
				else{
					$matchRounds[$i][0]=$finalFixture[$i+$matchForRound];
					$matchRounds[$i][1]=$finalFixture[$i+$matchForRound+2];
				}
			}
			else{
				$matchRounds[$i][0]=$finalFixture[$i*$matchForRound];
				$matchRounds[$i][1]=$finalFixture[$i*$matchForRound+1];				
			}
		}

		//Store matches to database
		shuffle($matchRounds); // shuffle rounds for different fixtures.
		$weekStart=1;
		foreach ($matchRounds as $matchRound) {
			foreach ($matchRound as $round) {				
				$round=explode(":", $round);
				$matchCount = Match::count();
				if($matchCount < Team::calcLeagueLength()*2){
					$match = new Match;
					$match->home_id       	= $round[0]+1;
					$match->away_id 		= $round[1]+1;
					$match->week 			= $weekStart;
					$match->save();
				}				
			}
			$weekStart++;
		}
	}

	// Calculation of how many matches will playing
	public static function calcLeagueLength(){
		$teams = Team::count();
		$leaugeLength = $teams * 2 - 2 ;

		return $leaugeLength;
	}
	
}