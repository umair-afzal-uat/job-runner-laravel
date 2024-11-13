<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBackgroundJobsTable extends Migration
{
    public function up()
    {
        Schema::create('background_jobs', function (Blueprint $table) {
            $table->id();
            $table->string('class_name');
            $table->string('method');
            $table->json('params')->nullable();
            $table->integer('retry_attempts')->default(3);
            $table->integer('current_attempt')->default(0);
            $table->integer('retry_delay')->default(5);
            $table->integer('priority')->default(0);
            $table->timestamp('delay_until')->nullable();
            $table->enum('status', ['pending', 'running', 'completed', 'failed'])->default('pending');
            $table->text('error')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('background_jobs');
    }
}
