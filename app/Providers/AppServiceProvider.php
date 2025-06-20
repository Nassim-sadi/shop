<?php

namespace App\Providers;

use App\Http\Resources\UserResource;
use Illuminate\Routing\UrlGenerator;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void {}

    /**
     * Bootstrap any application services.
     */
    public function boot(UrlGenerator $url): void
    {
        UserResource::withoutWrapping();

        Gate::before(function ($user, $ability) {
            return $user->hasRole('Super Admin') ? true : null;
        });
        // if (env('APP_ENV') == 'production') {
        //     $url->forceScheme('https');
        // }
        // TODO : Implement rate limiter method.
        // RateLimiter::for('api', function (Request $request) {
        //     return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
        // });
    }
}
