<?php

namespace App\Parsers;

abstract class DownloadGateParser extends AbstractParser
{
    public function canParse($content)
    {
        return str_contains($content, $this->getDomain());
    }

    public function getSpotifyId($content)
    {
        $url = $this->getUrlFromDescription($content);

        $page = (string) $this->guzzle->get($url)->getBody();

        return $this->getSpotifyIdFromPage($page);
    }

    private function getUrlFromDescription($description)
    {
        $domain = str_replace('.', '\.', $this->getDomain());

        $matches = [];
        preg_match("/(https?:\/\/[\w-\.]*{$domain}\/[\w-\/_]+)/", $description, $matches);

        return $matches[1];
    }

    protected abstract function getDomain();
}