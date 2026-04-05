<?php

declare(strict_types=1);

namespace Pralhad\Zipcoder\Laravel\Tests;

use Orchestra\Testbench\TestCase as BaseTestCase;
use Pralhad\Zipcoder\Laravel\Facades\Zipcoder;
use Pralhad\Zipcoder\Laravel\ZipcoderServiceProvider;

abstract class TestCase extends BaseTestCase
{
    protected function getPackageProviders($app): array
    {
        return [
            ZipcoderServiceProvider::class,
        ];
    }

    protected function getPackageAliases($app): array
    {
        return [
            'Zipcoder' => Zipcoder::class,
        ];
    }
}
