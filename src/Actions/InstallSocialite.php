<?php

namespace Axeldotdev\Ship\Actions;

class InstallSocialite extends Action
{
    public function handle(): void
    {
        if (! $this->command->option('socialite')) {
            return;
        }

        $this->executeTask(
            task: fn () => $this->command->requireComposerPackages('laravel/socialite'),
            success: 'laravel/socialite installed successfully',
            failure: 'Could not install the laravel/socialite package',
        );

        $this->executeTask(
            task: fn () => copy(
                __DIR__.'/../../stubs/commons/SocialiteController.php',
                app_path('Http/Controllers/Auth/SocialiteController.php'),
            ),
            success: 'SocialiteController copied successfully',
            failure: 'Could not copy the SocialiteController',
        );

        $this->replaceInFile(
            file: base_path('.env'),
            replacements: [
                'VITE_APP_NAME="${APP_NAME}"' => 'VITE_APP_NAME="${APP_NAME}"

GOOGLE_CLIENT_ID=
GOOGLE_CLIENT_SECRET=
GOOGLE_REDIRECT="${APP_URL}"/login/google/callback',
            ],
            success: 'Google OAuth credentials added in .env successfully',
            failure: 'Could not add Google OAuth credentials in .env',
        );

        $this->replaceInFile(
            file: base_path('.env.example'),
            replacements: [
                'VITE_APP_NAME="${APP_NAME}"' => 'VITE_APP_NAME="${APP_NAME}"

GOOGLE_CLIENT_ID=
GOOGLE_CLIENT_SECRET=
GOOGLE_REDIRECT="${APP_URL}"/login/google/callback',
            ],
            success: 'Google OAuth credentials added in .env.example successfully',
            failure: 'Could not add Google OAuth credentials in .env.example',
        );

        // TODO: Fill out /config/services.php

        match ($this->command->argument('stack')) {
            'livewire' => match ($this->command->option('volt')) {
                true => $this->configureForVolt(),
                false => $this->configureForLivewire(),
            },
            'react' => $this->configureForReact(),
            'vue' => $this->configureForVue(),
            'no-starter' => $this->configureForNoStarter(),
        };
    }

    protected function configureForLivewire(): void
    {
        // TODO: add routes
        // TODO: add views
    }

    protected function configureForNoStarter(): void
    {
        // TODO: add routes
    }

    protected function configureForReact(): void
    {
        // TODO: add routes
        // TODO: add views
    }

    protected function configureForVolt(): void
    {
        $this->replaceInFile(
            file: base_path('routes/auth.php'),
            replacements: [
                "Volt::route('login', 'auth.login')
        ->name('login');" => "Volt::route('login', 'auth.login')
        ->name('login');

    Route::get('login/{provider}/redirect', [SocialiteController::class, 'redirect'])
        ->name('login.redirect');

    Route::get('login/{provider}/callback', [SocialiteController::class, 'callback'])
        ->name('login.callback');",
                'use Livewire\Volt\Volt;' => 'use Livewire\Volt\Volt;
use App\Http\Controllers\Auth\SocialiteController;',
            ],
            success: 'Socialite routes added successfully',
            failure: 'Could not add Socialite routes',
        );

        // TODO: add views
    }

    protected function configureForVue(): void
    {
        // TODO: add routes
        // TODO: add views
    }
}
