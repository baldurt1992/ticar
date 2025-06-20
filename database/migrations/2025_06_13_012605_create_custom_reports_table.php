<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('custom_reports', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->index();
            $table->string('name')->nullable();
            $table->json('columns');
            $table->json('filters')->nullable();
            $table->json('emails');
            $table->enum('format', ['pdf', 'excel', 'both'])->default('pdf');
            $table->enum('schedule', ['daily', 'weekly', 'monthly', 'custom'])->default('monthly');
            $table->integer('custom_day')->nullable();
            $table->time('custom_time')->nullable();
            $table->string('cron')->nullable();
            $table->boolean('active')->default(true);
            $table->timestamp('last_sent_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('custom_reports');
    }
};
