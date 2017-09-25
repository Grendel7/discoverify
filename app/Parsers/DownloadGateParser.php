<?php

namespace App\Parsers;

use GuzzleHttp\Exception\RequestException;
use Illuminate\Support\Facades\Log;

abstract class DownloadGateParser extends AbstractParser
{
    public function canParse($content)
    {
        return str_contains($content, $this->getDomain());
    }

    public function getSpotifyId($content)
    {
        $url = $this->getUrlFromDescription($content);

        try {
            $page = (string) $this->guzzle->get($url)->getBody();
        } catch (RequestException $e) {
            Log::warning('Could not get download gate from `'.$url.'`: '.$e->getMessage());

            throw new ParseException('Download URL returned an error.');
        }

        return $this->getSpotifyIdFromPage($page);
    }

    private function getUrlFromDescription($description)
    {
        $domain = str_replace('.', '\.', $this->getDomain());

        $matches = [];
        preg_match("/(https?:\/\/[\w-\.]*{$domain}\/\S+)/", $description, $matches);

        return $matches[1];
    }

    protected abstract function getDomain();
}