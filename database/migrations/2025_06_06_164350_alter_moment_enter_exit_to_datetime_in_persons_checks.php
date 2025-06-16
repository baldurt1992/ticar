<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('persons_checks', function (Blueprint $table) {
            $table->dateTime('moment_enter')->nullable()->change();
            $table->dateTime('moment_exit')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('persons_checks', function (Blueprint $table) {
            $table->time('moment_enter')->nullable()->change();
            $table->time('moment_exit')->nullable()->change();
        });
    }
};

