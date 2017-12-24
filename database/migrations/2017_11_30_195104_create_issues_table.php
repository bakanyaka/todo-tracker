<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateIssuesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('issues', function (Blueprint $table) {
            $table->unsignedInteger('id')->unique();
            $table->string('subject',255);
            $table->string('department')->nullable();
            $table->string('assigned_to')->nullable();
            $table->dateTime('created_on');
            $table->boolean('control')->default(false);
            $table->integer('service_id')->unsigned()->nullable();
            $table->integer('priority_id')->unsigned()->default(4);
            $table->dateTime('closed_on')->nullable();
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
        Schema::dropIfExists('issues');
    }
}
