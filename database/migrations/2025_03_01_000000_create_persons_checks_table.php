<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePersonsChecksTable extends Migration
{
    public function up()
    {
        Schema::create('persons_checks', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('person_id')->nullable();
            $table->datetime('moment_enter')->nullable();
            $table->datetime('moment_exit')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('persons_checks');
    }
}
