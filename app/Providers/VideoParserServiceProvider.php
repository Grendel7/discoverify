<?php

namespace App\Providers;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\TransferStats;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;

class VideoParserServiceProvider extends ServiceProvider
{
    const BLACKLISTED_URLS = ['youtube.com', 'twitter.com', 'facebook.com', 'twitter.com', 'instagram.com',
        'soundcloud.com'];

    protected $defer = true;
    /**
     * @var Client
     */
    protected $guzzle;

    public function register()
    {
        $this->app->singleton('video_parser', function () {
            $this->guzzle = new Client();
            return $this;
        });
    }

    public function provides()
    {
        return ['video_parser'];
    }

    public function mapTrackToSpotifyId(\SimpleXMLElement $entry)
    {
        $name = $entry->title;
        $description = $entry->children('media', true)->group->description;

        $spotifyId = $this->getTrackFromDescription($description);

        if ($spotifyId) {
            return $spotifyId;
        }

        $spotifyId = $this->getTrackFromSearch($name);

        if ($spotifyId) {
            return $spotifyId;
        }
    }

    public function getTrackFromDescription($description)
    {
        $matches = [];
        preg_match_all('/https?\:\/\/[^\",\s]+/i', $description, $matches);

        $urls = array_filter($matches[0], function ($url) {
            return !str_contains($url, self::BLACKLISTED_URLS);
        });

        foreach ($urls as $url) {
            try {
                $page = (string)$this->guzzle->get($url, [
                    'on_stats' => function (TransferStats $stats) use (&$realUrl) {
                        $realUrl = $stats->getEffectiveUri();
                    }
                ])->getBody();

                if (str_contains($realUrl, '.spotify.com')) {
                    if (preg_match('/https?\:\/\/open\.spotify\.com\/track\/(\w+)/', $realUrl, $matches)) {
                        $spotifyId = $matches[1];
                    } elseif (preg_match('/https?\:\/\/open\.spotify\.com\/album\/(\w+)/', $page, $matches)) {
                        $spotifyId = $this->getTrackFromAlbum($matches[1]);
                    } else {
                        $spotifyId = null;
                    }
                } elseif (!str_contains($realUrl, self::BLACKLISTED_URLS)) {
                    $spotifyId = $this->getSpotifyIdFromPage($page);
                } else {
                    $spotifyId = null;
                }

                if ($spotifyId) {
                    return $spotifyId;
                }
            } catch (RequestException $e) {
                Log::warning('Could not get download gate from `' . $url . '`: ' . $e->getMessage());
            }
        }

        return null;
    }

    protected function getSpotifyIdFromPage($page)
    {
        $matches = [];
        if (preg_match('/spotify\:track:(\w+)/', $page, $matches)) {
            $trackId = $matches[1];
        } elseif (preg_match('/https?\:\/\/open\.spotify\.com\/track\/(\w+)/', $page, $matches)) {
            $trackId = $matches[1];
        } elseif (preg_match('/spotify\:album:(\w+)/', $page, $matches)) {
            $albumId = $matches[1];
        } elseif (preg_match('/https?\:\/\/open\.spotify\.com\/album\/(\w+)/', $page, $matches)) {
            $albumId = $matches[1];
        } else {
            return null;
        }

        if (isset($trackId)) {
            return $trackId;
        } elseif (isset($albumId)) {
            return $this->getTrackFromAlbum($albumId);
        } else {
            throw new \ErrorException('No parse exception was thrown but no album ID and track ID were available either.');
        }
    }

    protected function getTrackFromAlbum($albumId)
    {
        $album = app('spotify')->getAlbum($albumId);

        if ($album->album_type == 'single') {
            return array_first($album->tracks->items)->id;
        }
    }

    public function getTrackFromSearch($name)
    {
        $title = $this->normalizeTitle($name);

        $results = app('spotify')->search($title, 'track');

        if ($track = array_first($results->tracks->items)) {
            return $track->id;
        }
    }

    protected function normalizeTitle($name)
    {
        // Remove stuff like [NCS Release].
        $name = preg_replace('/\[.+\]/', '', $name);

        // Lowercase everything.
        $name = strtolower($name);

        // Convert dashes to spaces.
        $name = str_replace('-', ' ', $name);

        // Remove special chars.
        $name = preg_replace('/[^a-z0-9\s!]/', '', Str::ascii($name));

        // Remove excess whitespace.
        $name = preg_replace('/ {2,}/', ' ', trim($name));

        // Remove meaningless words.
        $name = implode(' ', array_filter(explode(' ', $name), function ($token) {
            return $token && !in_array($token, [
                    'ft',
                    'feat',
                    'remix',
                    'release',
                    'mix',
                    'lyric',
                    'video',
                    'exclusive',
                    'free',
                    'premiere',
                    'edit',
                    'ep',
                    'album',
                    'full',
                    'x'
                ]);
        }));

        return $name;
    }
}