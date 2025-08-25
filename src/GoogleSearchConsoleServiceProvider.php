<?php

namespace MichaelCrowcroft\GoogleSearchConsole;

use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class GoogleSearchConsoleServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package
            ->name('google-search-console-laravel')
            ->hasConfigFile();
    }

    public function packageBooted(): void
    {
        $this->publishes([
            __DIR__.'/../config/google-search-console.php' => config_path('google-search-console.php'),
        ], 'google-search-console-config');
    }

    public function packageRegistered(): void
    {
        $this->app->singleton(GoogleSearchConsole::class, function ($app) {
            return new GoogleSearchConsole();
        });
    }
}
