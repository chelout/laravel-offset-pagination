<?php

namespace Chelout\OffsetPagination\Tests;

use Chelout\OffsetPagination\OffsetPaginationServiceProvider;
use Orchestra\Testbench\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    /**
     * @param \Illuminate\Foundation\Application $application
     *
     * @return array
     */
    protected function getPackageProviders($application)
    {
        return [OffsetPaginationServiceProvider::class];
    }
}
