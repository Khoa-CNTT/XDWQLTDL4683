<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tbl_history', function (Blueprint $table) {
            $table->foreign(['userId'], 'fk_history_user')->references(['userId'])->on('tbl_users');
            $table->foreign(['tourId'], 'fk_history_tour')->references(['tourId'])->on('tbl_tours');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('tbl_history', function (Blueprint $table) {
            $table->dropForeign('fk_history_user');
            $table->dropForeign('fk_history_tour');
        });
    }
};
