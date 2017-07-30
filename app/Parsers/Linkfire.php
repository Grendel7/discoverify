<?php


namespace App\Parsers;


use Spatie\Regex\Regex;

class Linkfire extends DownloadGateParser
{
    protected function getDomain()
    {
        return 'lnk.to';
    }
}