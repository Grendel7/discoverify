<?php


namespace App\Parsers;


class NoCopyrightSounds extends DownloadGateParser
{

    protected function getDomain()
    {
        return 'ncs.io';
    }
}