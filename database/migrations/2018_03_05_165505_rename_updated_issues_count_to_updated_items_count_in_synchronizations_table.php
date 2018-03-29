<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class RenameUpdatedIssuesCountToUpdatedItemsCountInSynchronizationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('synchronizations', function (Blueprint $table) {
            $table->renameColumn('updated_issues_count', 'updated_items_count');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('synchronizations', function (Blueprint $table) {
            $table->renameColumn('updated_items_count', 'updated_issues_count');
        });
    }
}
