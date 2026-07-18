<?php

namespace PDFKong\Tests;

use Orchestra\Testbench\TestCase as Orchestra;
use PDFKong\PDFKongServiceProvider;

abstract class TestCase extends Orchestra
{
    protected function getPackageProviders($app)
    {
        return [
            PDFKongServiceProvider::class,
        ];
    }

    protected function getEnvironmentSetUp($app)
    {
        // Setup default config values for testing
        $app['config']->set('pdfkong.api_key', 'test-api-key');
        $app['config']->set('pdfkong.base_url', 'https://pdfkong.online/api/v1');
    }
}
