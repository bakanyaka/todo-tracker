<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::table('issues', function (Blueprint $table) {
            $table->unsignedInteger('parent_id')->nullable();
            $table->date('start_date')->nullable();
            $table->date('due_date')->nullable();

            $table->foreign('parent_id')->references('id')->on('issues')->cascadeOnDelete();
        });
    }

};
