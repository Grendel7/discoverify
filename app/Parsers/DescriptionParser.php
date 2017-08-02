<?php

namespace App\Parsers;

class DescriptionParser extends AbstractParser
{
    public function canParse($content)
    {
        return str_contains($content, 'open.spotify.com');
    }

    public function getSpotifyId($content)
    {
        return $this->getSpotifyIdFromPage($content);
    }
}