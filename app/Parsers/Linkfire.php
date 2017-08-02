<?php

namespace App\Parsers;

class Linkfire extends DownloadGateParser
{
    protected function getDomain()
    {
        return 'lnk.to';
    }
}