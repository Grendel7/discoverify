<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class MigrateChannelsTable2 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::table('tracks')->truncate();

        $channelIds = DB::table('channels_old')->select('channel_id')->distinct()->pluck('channel_id');

        foreach ($channelIds as $channelId) {
            $xml = $xml = simplexml_load_string(file_get_contents(
                'https://www.youtube.com/feeds/videos.xml?channel_id='.$channelId
            ));

            DB::table('channels')->insert([
                'name' => $xml->title,
                'youtube_id' => $channelId,
                'created_at' => \Carbon\Carbon::now(),
                'updated_at' => \Carbon\Carbon::now(),
            ]);
        }

        $oldChannels = DB::table('channels_old')->select('channel_id', 'user_id')->distinct()->get();

        foreach ($oldChannels as $channel) {
            $newChannelId = DB::table('channels')->select('id')->where('youtube_id', $channel->channel_id)->pluck('id')->first();

            DB::table('user_channels')->insert([
                'channel_id' => $newChannelId,
                'user_id' => $channel->user_id,
                'created_at' => \Carbon\Carbon::now(),
                'updated_at' => \Carbon\Carbon::now(),
            ]);
        }

        $oldChannels = DB::table('channels_old')->select('playlist_id', 'user_id')->distinct()->get();

        foreach ($oldChannels as $channel) {
            $user = \App\User::find($channel->user_id);
            $user->assertValidAccessToken();
            app('spotify')->setAccessToken($user->access_token);

            $playlist = app('spotify')->getUserPlaylist($user->remote_id, $channel->playlist_id);

            DB::table('playlists')->insert([
                'name' => $playlist->name,
                'spotify_id' => $channel->playlist_id,
                'user_id' => $user->id,
                'created_at' => \Carbon\Carbon::now(),
                'updated_at' => \Carbon\Carbon::now(),
            ]);
        }

        $oldChannels = DB::table('channels_old')->select('playlist_id', 'channel_id', 'user_id')->distinct()->get();

        foreach ($oldChannels as $channel) {
            $playlistId = DB::table('playlists')->select('id')->where('spotify_id', $channel->playlist_id)->first()->id;
            $channelId = DB::table('channels')->select('id')->where('youtube_id', $channel->channel_id)->first()->id;
            $userChannelId = DB::table('user_channels')->select('id')
                ->where('channel_id', $channelId)->where('user_id', $channel->user_id)->first()->id;

            DB::table('playlist_user_channel')->insert([
                'playlist_id' => $playlistId,
                'user_channel_id' => $userChannelId,
            ]);
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::table('playlist_user_channel')->truncate();
        DB::table('playlists')->truncate();
        DB::table('user_channels')->truncate();
        DB::table('channels')->truncate();
    }
}
