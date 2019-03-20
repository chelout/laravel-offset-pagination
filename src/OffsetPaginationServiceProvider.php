<?php

namespace Chelout\OffsetPagination;

use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Support\ServiceProvider;

class OffsetPaginationServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application events.
     *
     * @return void
     */
    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../config/offset_pagination.php' => config_path('offset_pagination.php'),
            ], 'config');
        }

        $this->registerMacro();
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/offset_pagination.php', 'offset_pagination');
    }

    /**
     * Create Macros for the Builders.
     */
    public function registerMacro()
    {
        $macro = function ($perPage = null, $columns = ['*'], array $options = []) {
            if (! $perPage) {
                $perPage = request('limit') ?? config('offset_pagination.per_page', 15);
            }
            $perPage = $perPage < 0 ? 0 : ($perPage > config('offset_pagination.max_per_page') ? config('offset_pagination.max_per_page') : $perPage);

            // Limit results
            $this->skip(request('offset') ?? 0)
                ->limit($perPage);

            $total = $this->toBase()->getCountForPagination();

            return new OffsetPaginator($this->get($columns), $perPage, $total, $options);
        };

        // Register macros
        QueryBuilder::macro('offsetPaginate', $macro);
        EloquentBuilder::macro('offsetPaginate', $macro);
    }
}
