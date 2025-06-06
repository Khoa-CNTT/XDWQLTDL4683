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
        Schema::table('tbl_wishlists', function (Blueprint $table) {
            $table->foreign(['tourId'], 'fk_wishlist_tours')->references(['tourId'])->on('tbl_tours');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('tbl_wishlists', function (Blueprint $table) {
            $table->dropForeign('fk_wishlist_tours');
        });
    }
};
