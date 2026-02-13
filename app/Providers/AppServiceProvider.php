<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        \Illuminate\Pagination\Paginator::useBootstrapFive();
        
        // Set pagination to show max 5 links on each side
        \Illuminate\Pagination\Paginator::defaultSimpleView('pagination::bootstrap-5');
        
        // Register Microsoft Azure Socialite Provider using Custom Provider
        \Illuminate\Support\Facades\Event::listen(function (\SocialiteProviders\Manager\SocialiteWasCalled $event) {
            $event->extendSocialite('azure', \App\Services\CustomAzureProvider::class);
        });
        
        // Configure rate limiting
        $this->configureRateLimiting();
    }
    
    /**
     * Configure rate limiting for the application
     */
    protected function configureRateLimiting(): void
    {
        \Illuminate\Support\Facades\RateLimiter::for('web', function ($request) {
            return \Illuminate\Cache\RateLimiting\Limit::perMinute(100)
                ->by($request->user()?->id ?: $request->ip())
                ->response(function ($request, $headers) {
                    return response()->view('errors.429', [], 429)->withHeaders($headers);
                });
        });
        
        \Illuminate\Support\Facades\RateLimiter::for('api', function ($request) {
            return \Illuminate\Cache\RateLimiting\Limit::perMinute(60)
                ->by($request->user()?->id ?: $request->ip());
        });
    }
}
