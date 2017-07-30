<?php

namespace App\Parsers;

use Spatie\Regex\Regex;

class Revealed extends DownloadGateParser
{
    protected function getDomain()
    {
        return 'rvld.dj';
    }
}