<?php

namespace App\Parsers;

class SpinninRecords extends DownloadGateParser
{
    protected function getDomain()
    {
        return 'release.spinninrecords.com';
    }
}
