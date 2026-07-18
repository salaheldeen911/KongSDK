<?php

namespace PDFKong;

use Illuminate\Support\ServiceProvider;
use PDFKong\Contracts\PDFKongClientInterface;

class PDFKongServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Merge the package configuration with the application's published copy.
        $this->mergeConfigFrom(
            __DIR__.'/../config/pdfkong.php', 'pdfkong'
        );

        // Bind the interface to the implementation
        $this->app->bind(PDFKongClientInterface::class, function ($app) {
            return new PDFKongClient();
        });

        // Bind the facade name
        $this->app->bind('pdfkong', function ($app) {
            return $app->make(PDFKongClientInterface::class);
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../config/pdfkong.php' => config_path('pdfkong.php'),
            ], 'pdfkong-config');
        }
    }
}
