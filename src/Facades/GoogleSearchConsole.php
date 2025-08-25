<?php

namespace MichaelCrowcroft\GoogleSearchConsole\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \MichaelCrowcroft\GoogleSearchConsole\GoogleSearchConsole
 */
class GoogleSearchConsole extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \MichaelCrowcroft\GoogleSearchConsole\GoogleSearchConsole::class;
    }
}
