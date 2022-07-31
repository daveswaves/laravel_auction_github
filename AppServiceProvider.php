<?php
// app/Providers/AppServiceProvider.php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

use Illuminate\Contracts\Foundation\Application; // Added to lag SQL queries
use Psr\Log\LoggerInterface; // Added to lag SQL queries
use App\Logging\QueryLogger; // Added to lag SQL queries

use Illuminate\Support\Facades\DB; // Added to lag SQL queries
use Illuminate\Database\Events\QueryExecuted; // Added to lag SQL queries

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        // Added to lag SQL queries
        $this->app->singleton(QueryLogger::class, function (Application $app) {
            return new QueryLogger($app->make(LoggerInterface::class));
        });
        
        // Added to lag SQL queries
        DB::listen(function(QueryExecuted $query) {
            /** @var \App\Logging\QueryLogger $logger */
            $logger = app()->make(\App\Logging\QueryLogger::class);
            $logger->debug($query->sql, [
                'execute_time_milliseconds' => $query->time,
                'params' => $query->bindings,
            ]);
        });
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }
}
