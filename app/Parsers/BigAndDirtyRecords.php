<?php


namespace App\Parsers;


use Spatie\Regex\Regex;

class BigAndDirtyRecords extends DownloadGateParser
{
    protected function getDomain()
    {
        return 'listento.biganddirtyrecords.com';
    }
}