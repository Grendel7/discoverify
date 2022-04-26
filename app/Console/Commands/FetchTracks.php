<?php

namespace App\Console\Commands;

use App\Models\Channel;
use App\Models\Track;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Arr;

class FetchTracks extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fetch_tracks';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check all feeds for new tracks.';

    /**
     * Create a new command instance.
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     * @throws \Throwable
     */
    public function handle()
    {
        $channels = Channel::all();

        $user = User::inRandomOrder()->first();
        $user->assertValidAccessToken();
        app('spotify')->setAccessToken($user->access_token);

        foreach ($channels as $channel) {
            $xml = simplexml_load_string(file_get_contents($channel->getFeedUrl()));

            foreach ($xml->entry as $entry) {
                $youTubeId = $entry->children('yt', true)->videoId;

                if (!Track::where('channel_id', $channel->id)->where('youtube_id', $youTubeId)->exists()) {
                    $track = $this->searchTrackForChannel($channel, $entry);

                    if ($track->spotify_id) {
                        $this->addTrackToPlaylist($channel, $track);
                        $this->info('Added '.$track->spotify_name.' to a playlist');
                    } else {
                        $this->warn('Could not find track for '.$track->name);
                    }

                    $track->saveOrFail();
                }
            }
        }
    }

    protected function searchTrackForChannel(Channel $channel, \SimpleXMLElement $entry)
    {
        $track = new Track();
        $track->youtube_id = $entry->children('yt', true)->videoId;
        $track->name = $entry->title;
        $track->channel_id = $channel->id;

        $spotifyTrack = app('video_parser')->getSpotifyTrackFromVideo($entry);

        if ($spotifyTrack) {
            $track->spotify_id = $spotifyTrack->id;

            $artists = implode(', ', array_map(function ($artist) {
                return $artist->name;
            }, $spotifyTrack->artists));

            $track->spotify_name = $artists.' - '.$spotifyTrack->name;
        } else {
            $track->error = 'Could not find the track on Spotify.';
        }

        return $track;
    }

    protected function addTrackToPlaylist(Channel $channel, Track $track)
    {
        foreach ($channel->userChannels()->with('user', 'playlists')->get() as $userChannel) {
            $userChannel->user->assertValidAccessToken();
            app('spotify')->setAccessToken($userChannel->user->access_token);

            foreach ($userChannel->playlists as $playlist) {
                $hasTrack = false;
                $offset = 0;

                while (!$hasTrack) {
                    $tracks = app('spotify')->getPlaylistTracks($playlist->spotify_id, [
                        'limit' => 100,
                        'offset' => $offset,
                    ]);

                    $hasTrack = Arr::first($tracks->items, function($item) use ($track) {
                        return $track->spotify_id == $item->track->id;
                    });

                    if (($offset += 100) > $tracks->total) {
                        break;
                    }
                }

                if (!$hasTrack) {
                    app('spotify')->addPlaylistTracks($playlist->spotify_id, $track->spotify_id);
                }
            }
        }
    }
}
