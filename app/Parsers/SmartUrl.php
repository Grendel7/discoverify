<?php

namespace App\Parsers;

class SmartUrl extends DownloadGateParser
{
    protected function getDomain()
    {
        return 'smarturl.it';
    }
}