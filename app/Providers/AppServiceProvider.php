<?php

namespace App\Providers;

use Illuminate\Support\Facades\Vite;
use Illuminate\Support\ServiceProvider;
use Inertia\Inertia;
use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Register Inertia
        $this->registerInertia();
        
        // Bind Ziggy routes for Inertia
        $this->registerZiggy();
    }

    /**
     * Register Inertia services.
     */
    protected function registerInertia(): void
    {
        // Set the Inertia version for cache busting
        Inertia::version(function () {
            return md5_file(public_path('build/manifest.json'));
        });

        // Share common data with all Inertia views
        Inertia::share([
            'app' => [
                'name' => config('app.name'),
                'env' => config('app.env'),
                'url' => config('app.url'),
            ],
            'auth' => function (Request $request) {
                return [
                    'user' => $request->user() ? [
                        'id' => $request->user()->id,
                        'name' => $request->user()->name,
                        'email' => $request->user()->email,
                        'roles' => $request->user()->roles->pluck('name') ?? [],
                    ] : null,
                ];
            },
        ]);
    }

    /**
     * Register Ziggy routes for Inertia.
     */
    protected function registerZiggy(): void
    {
        $this->app->singleton('ziggy', function () {
            return new \Tighten\Ziggy\Ziggy;
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        if ($this->app->environment('production')) {
            \URL::forceScheme('https');
            \URL::forceRootUrl(config('app.url'));
        }

        Vite::prefetch(concurrency: 3);
        
        Inertia::share([
            'flash' => function () {
                return [
                    'success' => session('success'),
                    'error' => session('error'),
                ];
            },
        ]);
    }
}
