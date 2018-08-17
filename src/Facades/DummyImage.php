<?php

namespace BoomDraw\DummyImage\Facades;

use Illuminate\Support\Facades\Facade;

class DummyImage extends Facade
{
    protected static function getFacadeAccessor() { return 'dummy_image'; }
}
