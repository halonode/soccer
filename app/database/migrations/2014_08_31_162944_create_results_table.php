<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateResultsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('results', function(Blueprint $table)
		{
			$table->increments('id');
			$table->integer('match_id')->unsigned();  
			$table->integer('team_id')->unsigned();  
			$table->integer('week')->unsigned();    
            $table->integer('win');
            $table->integer('lose');
            $table->integer('draw');
            $table->integer('goal_for');
            $table->integer('goal_against');
            $table->integer('goal_difference');
			$table->timestamps();
		});
	}



	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('results');
	}

}
