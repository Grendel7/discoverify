<?php

namespace App\Parsers;

use Spatie\Regex\Regex;

class SpotiFi extends AbstractParser
{
    public function canParse($content)
    {
        if (str_contains($content, 'spoti.fi')) {
            $url = $this->getRealUrl($content);

            return str_contains($url, ['open.spotify.com/track/', 'open.spotify.com/album/']);
        } else {
            return false;
        }
    }

    public function getSpotifyId($content)
    {
        $url = $this->getRealUrl($content);
        $parts = explode('/', $url);
        $id = array_pop($parts);
        $type = array_pop($parts);

        if ($type == 'track') {
            return $id;
        } else {
            return $this->getTrackFromAlbum($id);
        }
    }

    private function getRealUrl($content)
    {
        $shortenedUrl = Regex::match('/(spoti\.fi\/\w+)/', $content)->result();

        $response = $this->guzzle->get('https://'.$shortenedUrl, [
            'allow_redirects' => false,
        ]);

        return array_first($response->getHeader('Location'));
    }
}