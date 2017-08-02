<?php

namespace App\Parsers;

class Fanlink extends DownloadGateParser
{
    protected function getDomain()
    {
        return 'fanlink.to';
    }
}