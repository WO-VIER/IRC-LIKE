<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Inertia\Inertia;
use Laravel\Fortify\Fortify;
use Laravel\Fortify\Contracts\ConfirmPasswordViewResponse;
use Laravel\Fortify\Contracts\TwoFactorChallengeViewResponse;

class FortifyServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(ConfirmPasswordViewResponse::class, function () {
            return new class implements ConfirmPasswordViewResponse {
                public function toResponse($request)
                {
                    return Inertia::render('auth/ConfirmPassword');
                }
            };
        });

        $this->app->singleton(TwoFactorChallengeViewResponse::class, function () {
            return new class implements TwoFactorChallengeViewResponse {
                public function toResponse($request)
                {
                    return Inertia::render('auth/TwoFactorChallenge');
                }
            };
        });
    }

    public function boot(): void
    {
        Fortify::twoFactorChallengeView(fn () => Inertia::render('auth/TwoFactorChallenge'));
        Fortify::confirmPasswordView(fn () => Inertia::render('auth/ConfirmPassword'));

        RateLimiter::for('two-factor', function (Request $request) {
            return Limit::perMinute(5)->by($request->session()->get('login.id'));
        });
    }
}
