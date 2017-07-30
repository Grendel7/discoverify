<?php


namespace App\Parsers;


use Spatie\Regex\Regex;

class Fanlink extends DownloadGateParser
{
    protected function getDomain()
    {
        return 'fanlink.to';
    }
}