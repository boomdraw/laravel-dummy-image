<?php

namespace BoomDraw\DummyImage;

use BoomDraw\DummyImage\DummyImage;
use Illuminate\Support\ServiceProvider;

class DummyImageServiceProvider extends ServiceProvider
{
    public function boot()
    {
        if ($this->isLaravel() && $this->app->runningInConsole()) {
            $this->publishes([
                $this->getConfigFile() => config_path('dummyimage.php'),
            ], 'config');
        }
    }

    public function register()
    {
        $this->mergeConfigFrom(
            $this->getConfigFile(),
            'dummyimage'
        );

        $this->app->bind('dummy_image', DummyImage::class);

    }

    protected function isLaravel(): bool
    {
        return !preg_match('/lumen/i', app()->version());
    }

    protected function getConfigFile(): string
    {
        return __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'dummyimage.php';
    }
}
