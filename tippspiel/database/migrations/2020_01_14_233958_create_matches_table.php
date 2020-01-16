<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMatchesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('matches', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('season_id');
            $table->unsignedBigInteger('team_home_id');
            $table->unsignedBigInteger('team_away_id');
            $table->unsignedSmallInteger('match_day'); // Alternatively create a matchday table (id, name, date?)
            $table->dateTime('match_start');
            $table->unsignedSmallInteger('score_home');
            $table->unsignedSmallInteger('score_away');
            $table->timestamps();

            $table->index(['season_id', 'match_day']);
            //$table->unique(['season_id', 'match_day', 'team_home_id', 'team_away_id'])
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('matches');
    }
}
