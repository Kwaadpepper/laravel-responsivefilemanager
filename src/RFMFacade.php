<?php

namespace Kwaadpepper\ResponsiveFileManager;

use Illuminate\Support\Facades\Facade;

class RFMFacade extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'rfm-utils';
    }
}
