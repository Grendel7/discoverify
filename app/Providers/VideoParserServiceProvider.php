<?php

namespace App\Providers;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\TransferStats;
use Illuminate\Contracts\Support\DeferrableProvider;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use SpotifyWebAPI\SpotifyWebAPIException;

class VideoParserServiceProvider extends ServiceProvider implements DeferrableProvider
{
    const BLACKLISTED_URLS = [
        'youtube.com', 'twitter.com', 'facebook.com', 'instagram.com', 'soundcloud.com', 'paypal.com',
    ];
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

    /**
     * Get a Spotify track object for the video.
     *
     * It first tries to grab the Spotify link from the video description (also checking fangates). If that fails, it
     * tries to use the video title to search on Spotify.
     *
     * @see https://developer.spotify.com/documentation/web-api/reference/object-model/#track-object-full
     * @param \SimpleXMLElement $entry
     * @return array|null
     * @throws SpotifyWebAPIException
     */
    public function getSpotifyTrackFromVideo(\SimpleXMLElement $entry)
    {
        $name = $entry->title;
        $description = $entry->children('media', true)->group->description;

        if ($track = $this->getTrackFromDescription($description)) {
            return $track;
        }

        return $this->getTrackFromSearch($name);
    }

    /**
     * Attempt to get a Spotify track object by parsing the description.
     *
     * It pulls all the URLs from the video description and polls all those URLs to find a Spotify web player URL.
     *
     * @param string $description
     * @return array|null
     * @throws SpotifyWebAPIException
     */
    public function getTrackFromDescription(string $description)
    {
        // Get all the URLs from the video description.
        $matches = [];
        preg_match_all('/https?:\/\/[^\",\s]+/i', $description, $matches);

        // Remove all the blacklisted domains from the URL set (hopefully prevents redirect loops).
        $urls = array_filter($matches[0], function ($url) {
            return !Str::contains($url, self::BLACKLISTED_URLS);
        });

        // Go through all the URLs to find a Spotify URL.
        foreach ($urls as $url) {
            try {
                $spotifyId = $this->getSpotifyIdFromUrl($url);

                if ($spotifyId) {
                    try {
                        return app('spotify')->getTrack($spotifyId);
                    } catch (SpotifyWebAPIException $e) {
                        if (Str::contains($e->getMessage(), 'non existing id')) {
                            Log::warning('Video description has invalid track ID.', [
                                'trackId' => $spotifyId,
                                'description' => $description,
                            ]);

                            return null;
                        } else {
                            throw $e;
                        }
                    }
                }
            } catch (RequestException $e) {
                Log::warning('Could not get download gate from `' . $url . '`: ' . $e->getMessage());
            }
        }

        return null;
    }

    /**
     * Take the URL from the video title and attempt to get a Spotify track ID from it.
     *
     * @param string $url
     * @return string|null
     */
    protected function getSpotifyIdFromUrl(string $url)
    {
        // Fetch the URL which was present in the description.
        // Also capture the real URL in case there are redirects.
        $page = (string)$this->guzzle->get($url, [
            'on_stats' => function (TransferStats $stats) use (&$realUrl) {
                $realUrl = $stats->getEffectiveUri();
            }
        ])->getBody();

        if (Str::contains($realUrl, '.spotify.com')) {
            // Check if we're already looking at a Spotify URL.
            if (preg_match('/https?:\/\/open\.spotify\.com\/track\/(\w+)/', $realUrl, $matches)) {
                return $matches[1];
            } elseif (preg_match('/https?:\/\/open\.spotify\.com\/album\/(\w+)/', $page, $matches)) {
                return $this->getTrackFromAlbum($matches[1]);
            } else {
                return null;
            }
        } elseif (Str::contains($realUrl, self::BLACKLISTED_URLS)) {
            // We were redirected to a blacklisted domain.
            return null;
        } else {
            // Check if we've not been forwarded to a blacklisted domain name. If not, check the page for Spotify URLs.
            return $this->getSpotifyIdFromPageContent($page);
        }
    }

    /**
     * Try to get a Spotify URL from the page content (e.g. a fan gate).
     *
     * @param string $page
     * @return string|null
     */
    protected function getSpotifyIdFromPageContent(string $page)
    {
        // Look for a track ID.
        $matches = [];
        if (preg_match('/spotify:track:(\w+)/', $page, $matches)) {
            // We have a Spotify desktop URL.
            $trackId = $matches[1];
        } elseif (preg_match('/https?:\/\/open\.spotify\.com\/track\/(\w+)/', $page, $matches)) {
            // We have Spotify web player URL.
            $trackId = $matches[1];
        }

        if (isset($trackId)) {
            return $trackId;
        }

        // Look for an album ID.
        if (preg_match('/spotify:album:(\w+)/', $page, $matches)) {
            $albumId = $matches[1];
        } elseif (preg_match('/https?:\/\/open\.spotify\.com\/album\/(\w+)/', $page, $matches)) {
            $albumId = $matches[1];
        }

        if (isset($albumId)) {
            return $this->getTrackFromAlbum($albumId);
        }

        return null;
    }

    /**
     * Try to get a track ID from an album. Currently only supports "single" type albums.
     *
     * @param string $albumId
     * @return string|null
     */
    protected function getTrackFromAlbum(string $albumId)
    {
        $album = app('spotify')->getAlbum($albumId);

        if ($album->album_type == 'single') {
            return Arr::first($album->tracks->items)->id;
        }
    }

    /**
     * Attempt to find the track by searching Spotify for the video title.
     *
     * @param string $title
     * @return array|null
     */
    public function getTrackFromSearch($title)
    {
        $normalizedTitle = $this->normalizeTitle($title);

        $results = app('spotify')->search($normalizedTitle, 'track');

        return Arr::first($results->tracks->items);
    }

    /**
     * Normalize the video title so the Spotify search engine can process it effectively.
     *
     * @param string $name
     * @return string
     */
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
                    'lyrics',
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
