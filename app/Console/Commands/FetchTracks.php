<?php

namespace App\Console\Commands;

use App\Channel;
use App\Parsers\AbstractParser;
use App\Parsers\BigAndDirtyRecords;
use App\Parsers\DescriptionParser;
use App\Parsers\Fanlink;
use App\Parsers\Linkfire;
use App\Parsers\NoCopyrightSounds;
use App\Parsers\ParseException;
use App\Parsers\Revealed;
use App\Parsers\SmartUrl;
use App\Parsers\SpinninRecords;
use App\Parsers\SpotiFi;
use App\Track;
use App\User;
use Illuminate\Console\Command;

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

    protected $parsers = [];

    /**
     * Create a new command instance.
     */
    public function __construct()
    {
        parent::__construct();

        $this->parsers = [
            new DescriptionParser(),
            new SpotiFi(),
            new Linkfire(),
            new SmartUrl(),
            new Fanlink(),
            new Revealed(),
            new BigAndDirtyRecords(),
            new NoCopyrightSounds(),
            new SpinninRecords(),
        ];
    }

    /**
     * Execute the console command.
     *
     * @return mixed
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

        $description = $entry->children('media', true)->group->description;

        $parsers = array_filter($this->parsers, function (AbstractParser $parser) use ($description) {
            return $parser->canParse($description);
        });

        if ($parsers) {
            foreach ($parsers as $parser) {
                try {
                    $spotifyId = $parser->getSpotifyId($description);
                    break;
                } catch (ParseException $e) {
                    // do nothing for now
                }
            }

            if (isset($spotifyId) && $spotifyId) {
                $spotifyTrack = app('spotify')->getTrack($spotifyId);
                $track->spotify_id = $spotifyId;

                $artists = implode(', ', array_map(function ($artist) {
                    return $artist->name;
                }, $spotifyTrack->artists));

                $track->spotify_name = $artists.' - '.$spotifyTrack->name;
            } else {
                $track->error = isset($e) ? $e->getMessage() : 'None of the links contain references to Spotify.';
            }
        } else {
            $track->error = 'No usable URL found.';
        }

        return $track;
    }

    protected function addTrackToPlaylist(Channel $channel, Track $track)
    {
        foreach ($channel->userChannels()->with('user', 'playlists')->get() as $userChannel) {
            $userChannel->user->assertValidAccessToken();
            app('spotify')->setAccessToken($userChannel->user->access_token);

            foreach ($userChannel->playlists as $playlist) {
                $tracks = app('spotify')->getuserPlaylistTracks($userChannel->user->remote_id, $playlist->spotify_id);

                $hasTrack = array_first($tracks->items, function($item) use ($track) {
                    return $track->spotify_id == $item->track->id;
                });

                if ($hasTrack) {
                    return;
                }

                app('spotify')->addUserPlaylistTracks($userChannel->user->remote_id, $playlist->spotify_id, $track->spotify_id);
            }
        }
    }
}
