<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('persons_checks', function (Blueprint $table) {
            $table->dateTime('moment_enter')->change()->nullable();
            $table->dateTime('moment_exit')->change()->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('persons_checks', function (Blueprint $table) {
            $table->time('moment_enter')->change()->nullable();
            $table->time('moment_exit')->change()->nullable();
        });
    }
};
