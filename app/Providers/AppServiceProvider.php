<?php

namespace App\Providers;

use Illuminate\Database\Events\MigrationsEnded;
use Illuminate\Database\Events\MigrationsStarted;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        Schema::defaultStringLength(191);
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        // https://github.com/laravel/framework/issues/33238#issuecomment-897063577
        Event::listen(MigrationsStarted::class, function () {
            DB::statement('SET SESSION sql_require_primary_key=0');
        });
        Event::listen(MigrationStarted::class, function () {
            DB::statement('SET SESSION sql_require_primary_key=0');
        });
        Event::listen(MigrationsEnded::class, function () {
            DB::statement('SET SESSION sql_require_primary_key=1');
        });
        Event::listen(MigrationEnded::class, function () {
            DB::statement('SET SESSION sql_require_primary_key=1');
        });
    }
}
