<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class MigrateChannelsTable3 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('user_channels', function (Blueprint $table) {
            $table->dropForeign('user_channels_channel_id_foreign');
        });

        Schema::drop('channels_old');

        Schema::table('playlist_user_channel', function (Blueprint $table) {
            $table->foreign('playlist_id')->references('id')->on('playlists');
            $table->foreign('user_channel_id')->references('id')->on('user_channels');
        });

        Schema::table('tracks', function (Blueprint $table) {
            $table->foreign('channel_id')->references('id')->on('channels');
        });

        Schema::table('user_channels', function (Blueprint $table) {
            $table->foreign('channel_id')->references('id')->on('channels');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
