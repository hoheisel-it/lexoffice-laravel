<?php

namespace HoheiselIT\Lexoffice\Tests;

use Orchestra\Testbench\TestCase as Orchestra;
use HoheiselIT\Lexoffice\LexofficeServiceProvider;

class TestCase extends Orchestra
{
    protected function getPackageProviders($app): array
    {
        return [LexofficeServiceProvider::class];
    }

    protected function defineEnvironment($app): void
    {
        $app['config']->set('lexoffice.api_key', 'test-api-key');
        $app['config']->set('queue.default', 'sync');
    }
}
