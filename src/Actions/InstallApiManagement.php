<?php

namespace Axeldotdev\Ship\Actions;

class InstallApiManagement extends Action
{
    public function handle(): void
    {
        if (! $this->command->option('api')) {
            return;
        }

        $this->command->runArtisanCommand(['install:api']);

        $this->command->filesystem()->delete(config_path('sanctum.php'));

        $this->publishCommand();
        $this->publishTrait();

        if ($this->command->argument('stack') === 'no-starter') {
            return;
        }

        match ($this->command->argument('stack')) {
            'livewire' => match ($this->command->option('volt')) {
                true => $this->publishVoltViews(),
                false => $this->publishLivewireViews(),
            },
            'react' => $this->publishReactViews(),
            'vue' => $this->publishVueViews(),
        };
    }

    protected function publishCommand(): void
    {
        $this->replaceInFile(
            file: base_path('routes/console.php'),
            replacements: [
                "Schedule::command('model:prune')->daily();" => "Schedule::command('model:prune')->daily();
Schedule::command('sanctum:prune-expired')->daily();",
            ],
            success: 'Sanctum prune command added successfully',
            failure: 'Could not add the Sanctum prune command',
        );
    }

    protected function publishLivewireViews(): void
    {
        $this->executeTask(
            task: fn () => copy(
                __DIR__.'/../../stubs/livewire/ApiTokens.php',
                app_path('Livewire/Settings/ApiTokens.php'),
            ),
            success: 'settings api tokens class copied successfully',
            failure: 'Could not copy the settings api tokens class',
        );

        $this->executeTask(
            task: fn () => copy(
                __DIR__.'/../../stubs/livewire/api-tokens.blade.php',
                resource_path('views/livewire/settings/api-tokens.blade.php'),
            ),
            success: 'settings api tokens view copied successfully',
            failure: 'Could not copy the settings api tokens view',
        );

        $this->replaceInFile(
            file: base_path('routes/web.php'),
            replacements: [
                "Route::get('settings/profile', Profile::class)->name('settings.profile');" => "Route::get('settings/profile', Profile::class)->name('settings.profile');
    Route::get('settings/api-tokens', ApiTokens::class)->name('settings.api-tokens');",
                "use App\Livewire\Settings\Profile;" => "use App\Livewire\Settings\Profile;
use App\Livewire\Settings\ApiTokens;",
            ],
            success: 'settings api tokens route added successfully',
            failure: 'Could not add the settings api tokens route',
        );

        $this->replaceInFile(
            file: resource_path('views/components/settings/layout.blade.php'),
            replacements: [
                '<flux:navlist.item :href="route(\'settings.profile\')" wire:navigate>{{ __(\'Profile\') }}</flux:navlist.item>' => '<flux:navlist.item :href="route(\'settings.profile\')" wire:navigate>{{ __(\'Profile\') }}</flux:navlist.item>
            <flux:navlist.item href="{{ route(\'settings.api-tokens\') }}" wire:navigate>{{ __(\'API tokens\') }}</flux:navlist.item>',
            ],
            success: 'settings layout updated successfully',
            failure: 'Could not update the settings layout',
        );
    }

    protected function publishReactViews(): void
    {
        $this->executeTask(
            task: fn () => copy(
                __DIR__.'/../../stubs/react/ApiTokenController.php',
                app_path('Http/Controllers/Settings/ApiTokenController.php'),
            ),
            success: 'settings api tokens controller copied successfully',
            failure: 'Could not copy the settings api tokens controller',
        );

        $this->executeTask(
            task: fn () => copy(
                __DIR__.'/../../stubs/react/api-tokens.tsx',
                resource_path('js/pages/settings/api-tokens.tsx'),
            ),
            success: 'settings api tokens view copied successfully',
            failure: 'Could not copy the settings api tokens view',
        );

        $this->replaceInFile(
            file: base_path('routes/settings.php'),
            replacements: [
                "Route::delete('settings/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');" => "Route::delete('settings/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::get('settings/api-tokens', [ApiTokenController::class, 'index'])->name('api-tokens.index');
    Route::post('settings/api-tokens', [ApiTokenController::class, 'store'])->name('api-tokens.store');
    Route::delete('settings/api-tokens/{token}', [ApiTokenController::class, 'destroy'])->name('api-tokens.destroy');",
                "use App\Http\Controllers\Settings\ProfileController;" => "use App\Http\Controllers\Settings\ProfileController;
use App\Http\Controllers\Settings\ApiTokenController;",
            ],
            success: 'settings api tokens route added successfully',
            failure: 'Could not add the settings api tokens route',
        );

        $this->replaceInFile(
            file: resource_path('js/layouts/settings/layout.tsx'),
            replacements: [
                "url: '/settings/password',
        icon: null,
    }," => "url: '/settings/password',
        icon: null,
    },
    {
        title: 'API tokens',
        url: '/settings/api-tokens',
        icon: null,
    },",
            ],
            success: 'settings layout updated successfully',
            failure: 'Could not update the settings layout',
        );
    }

    protected function publishVoltViews(): void
    {
        $this->executeTask(
            task: fn () => copy(
                __DIR__.'/../../stubs/volt/api-tokens.blade.php',
                resource_path('views/livewire/settings/api-tokens.blade.php'),
            ),
            success: 'settings api tokens view copied successfully',
            failure: 'Could not copy the settings api tokens view',
        );

        $this->replaceInFile(
            file: base_path('routes/web.php'),
            replacements: [
                "Volt::route('settings/profile', 'settings.profile')->name('settings.profile');" => "Volt::route('settings/profile', 'settings.profile')->name('settings.profile');
    Volt::route('settings/api-tokens', 'settings.api-tokens')->name('settings.api-tokens');",
            ],
            success: 'settings api tokens route added successfully',
            failure: 'Could not add the settings api tokens route',
        );

        $this->replaceInFile(
            file: resource_path('views/components/settings/layout.blade.php'),
            replacements: [
                '<flux:navlist.item href="{{ route(\'settings.password\') }}" wire:navigate>{{ __(\'Password\') }}</flux:navlist.item>' => '<flux:navlist.item href="{{ route(\'settings.password\') }}" wire:navigate>{{ __(\'Password\') }}</flux:navlist.item>
            <flux:navlist.item href="{{ route(\'settings.api-tokens\') }}" wire:navigate>{{ __(\'API tokens\') }}</flux:navlist.item>',
            ],
            success: 'settings layout updated successfully',
            failure: 'Could not update the settings layout',
        );
    }

    protected function publishVueViews(): void
    {
        $this->executeTask(
            task: fn () => copy(
                __DIR__.'/../../stubs/vue/ApiTokenController.php',
                app_path('Http/Controllers/Settings/ApiTokenController.php'),
            ),
            success: 'settings api tokens controller copied successfully',
            failure: 'Could not copy the settings api tokens controller',
        );

        $this->executeTask(
            task: fn () => copy(
                __DIR__.'/../../stubs/vue/ApiTokens.vue',
                resource_path('js/pages/settings/ApiTokens.vue'),
            ),
            success: 'settings api tokens view copied successfully',
            failure: 'Could not copy the settings api tokens view',
        );

        $this->replaceInFile(
            file: base_path('routes/settings.php'),
            replacements: [
                "Route::delete('settings/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');" => "Route::delete('settings/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::get('settings/api-tokens', [ApiTokenController::class, 'index'])->name('api-tokens.index');
    Route::post('settings/api-tokens', [ApiTokenController::class, 'store'])->name('api-tokens.store');
    Route::delete('settings/api-tokens/{token}', [ApiTokenController::class, 'destroy'])->name('api-tokens.destroy');",
                "use App\Http\Controllers\Settings\ProfileController;" => "use App\Http\Controllers\Settings\ProfileController;
use App\Http\Controllers\Settings\ApiTokenController;",
            ],
            success: 'settings api tokens route added successfully',
            failure: 'Could not add the settings api tokens route',
        );

        $this->replaceInFile(
            file: resource_path('js/layouts/settings/Layout.vue'),
            replacements: [
                "href: '/settings/password',
    }," => "href: '/settings/password',
    },
    {
        title: 'API tokens',
        href: '/settings/api-tokens',
    },",
            ],
            success: 'settings layout updated successfully',
            failure: 'Could not update the settings layout',
        );
    }

    protected function publishTrait(): void
    {
        $this->replaceInFile(
            file: app_path('Models/User.php'),
            replacements: [
                'use Notifiable;' => 'use HasApiTokens;
    use Notifiable;',
                'namespace App\Models;' => 'namespace App\Models;

use Laravel\Sanctum\HasApiTokens;',
            ],
            success: 'User model updated successfully',
            failure: 'Could not update the User model traits',
        );
    }
}
