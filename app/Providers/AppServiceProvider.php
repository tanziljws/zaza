<?php

namespace App\Providers;

use App\Models\Post;
use Livewire\Livewire;
use App\Models\Service;
use App\Models\UiConfigGroup;
use Illuminate\Support\ServiceProvider;
use Illuminate\Auth\Notifications\ResetPassword;

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
    protected $policies = [
        // 'App\Models\Model' => 'App\Policies\ModelPolicy',
    ];

    public function boot(): void
    {
        // $this->registerPolicies();

        // Removed custom Livewire routes to avoid route name conflicts
        // Livewire will use default routes: /livewire/update and /livewire/livewire.js

        ResetPassword::createUrlUsing(function ($notifiable, string $token) {
        

            // Jika user dari tabel users (admin)
            if ($notifiable instanceof \App\Models\User) {
                return route('admin.password.reset', [
                    'token' => $token,
                    'email' => $notifiable->getEmailForPasswordReset(),
                ]);
            }

            // fallback (jaga-jaga)
            return url('/reset-password/' . $token . '?email=' . urlencode($notifiable->getEmailForPasswordReset()));
        });
    }

}
