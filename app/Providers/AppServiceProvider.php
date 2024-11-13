<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Repositories\BackgroundJobRepositoryInterface;
use App\Repositories\BackgroundJobRepository;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(BackgroundJobRepositoryInterface::class, BackgroundJobRepository::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
