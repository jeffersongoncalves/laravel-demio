<?php

namespace JeffersonGoncalves\Demio\Tests;

use JeffersonGoncalves\Demio\DemioServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;

class TestCase extends Orchestra
{
    protected function getPackageProviders($app): array
    {
        return [
            DemioServiceProvider::class,
        ];
    }

    protected function defineEnvironment($app): void
    {
        $app['config']->set('demio.api_key', 'fake-key');
        $app['config']->set('demio.api_secret', 'fake-secret');
        $app['config']->set('demio.timeout', 5);
    }
}
