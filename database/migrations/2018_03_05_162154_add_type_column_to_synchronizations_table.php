<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddTypeColumnToSynchronizationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Workaround for "Cannot add a NOT NULL column with default value NULL" when using SQLite
        if(config('database.default') == 'sqlite'){
            Schema::table('synchronizations', function (Blueprint $table) {
                $table->string('type')->nullable();
            });
            Schema::table('synchronizations', function (Blueprint $table) {
                $table->string('type')->nullable(false)->change();
            });
        } else {
            Schema::table('synchronizations', function (Blueprint $table) {
                $table->string('type');
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('synchronizations', function (Blueprint $table) {
            $table->dropColumn('type');
        });
    }
}
