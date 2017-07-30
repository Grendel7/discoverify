<?php

namespace App\Parsers;

use GuzzleHttp\Client;

abstract class AbstractParser
{
    /**
     * @var Client
     */
    protected $guzzle;

    public function __construct()
    {
        $this->guzzle = new Client();
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
            throw new ParseException('No Spotify URL on download page');
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
        } else {
            throw new ParseException('Spotify URL pointed to an album with multiple tracks.');
        }
    }

    public abstract function canParse($content);

    public abstract function getSpotifyId($content);
}