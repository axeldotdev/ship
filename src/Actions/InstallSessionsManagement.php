<?php

namespace Axeldotdev\Ship\Actions;

class InstallSessionsManagement extends Action
{
    public function handle(): void
    {
        if (! $this->command->option('sessions')) {
            return;
        }

        $this->publishAgent();
        $this->publishModel();
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

    protected function publishAgent(): void
    {
        $this->command->ensureDirectoryExists(app_path('Support'));

        $this->executeTask(
            task: fn () => copy(
                __DIR__.'/../../stubs/commons/Agent.php',
                app_path('Support/Agent.php'),
            ),
            success: 'Agent class copied successfully',
            failure: 'Could not copy the Agent class stub',
        );

        $this->executeTask(
            task: fn () => $this->command->requireComposerPackages('mobiledetect/mobiledetectlib'),
            success: 'mobiledetect/mobiledetectlib installed successfully',
            failure: 'Could not install the mobiledetect/mobiledetectlib package',
        );
    }

    protected function publishModel(): void
    {
        $this->executeTask(
            task: fn () => copy(
                __DIR__.'/../../stubs/commons/Session.php',
                app_path('Models/Session.php'),
            ),
            success: 'Session model copied successfully',
            failure: 'Could not copy the Session model stub',
        );
    }

    protected function publishTrait(): void
    {
        $this->command->ensureDirectoryExists(app_path('Concerns'));

        $this->executeTask(
            task: fn () => copy(
                __DIR__.'/../../stubs/commons/HasSession.php',
                app_path('Concerns/HasSession.php'),
            ),
            success: 'HasSession trait copied successfully',
            failure: 'Could not copy the Session trait stub',
        );

        $this->replaceInFile(
            file: app_path('Models/User.php'),
            replacements: [
                'use Notifiable;' => 'use HasSession;
    use Notifiable;',
                'namespace App\Models;' => 'namespace App\Models;

use App\Concerns\HasSession;',
            ],
            success: 'User model updated successfully',
            failure: 'Could not update the User model traits',
        );
    }

    protected function publishLivewireViews(): void
    {
        $this->executeTask(
            task: fn () => copy(
                __DIR__.'/../../stubs/livewire/Sessions.php',
                app_path('Livewire/Settings/Sessions.php'),
            ),
            success: 'settings session class copied successfully',
            failure: 'Could not copy the settings session class',
        );

        $this->executeTask(
            task: fn () => copy(
                __DIR__.'/../../stubs/livewire/sessions.blade.php',
                resource_path('views/livewire/settings/sessions.blade.php'),
            ),
            success: 'settings sessions view copied successfully',
            failure: 'Could not copy the settings sessions view',
        );

        $this->replaceInFile(
            file: base_path('routes/web.php'),
            replacements: [
                "Route::get('settings/profile', Profile::class)->name('settings.profile');" => "Route::get('settings/profile', Profile::class)->name('settings.profile');
    Route::get('settings/sessions', Sessions::class)->name('settings.sessions');",
                "use App\Livewire\Settings\Profile;" => "use App\Livewire\Settings\Profile;
use App\Livewire\Settings\Sessions;",
            ],
            success: 'settings sessions route added successfully',
            failure: 'Could not add the settings sessions route',
        );

        $this->replaceInFile(
            file: resource_path('views/components/settings/layout.blade.php'),
            replacements: [
                '<flux:navlist.item :href="route(\'settings.profile\')" wire:navigate>{{ __(\'Profile\') }}</flux:navlist.item>' => '<flux:navlist.item :href="route(\'settings.profile\')" wire:navigate>{{ __(\'Profile\') }}</flux:navlist.item>
            <flux:navlist.item href="{{ route(\'settings.sessions\') }}" wire:navigate>{{ __(\'Sessions\') }}</flux:navlist.item>',
            ],
            success: 'settings layout updated successfully',
            failure: 'Could not update the settings layout',
        );
    }

    protected function publishReactViews(): void
    {
        $this->executeTask(
            task: fn () => copy(
                __DIR__.'/../../stubs/react/SessionController.php',
                app_path('Http/Controllers/Settings/SessionController.php'),
            ),
            success: 'settings sessions controller copied successfully',
            failure: 'Could not copy the settings sessions controller',
        );

        $this->executeTask(
            task: fn () => copy(
                __DIR__.'/../../stubs/react/sessions.tsx',
                resource_path('js/pages/settings/sessions.tsx'),
            ),
            success: 'settings sessions view copied successfully',
            failure: 'Could not copy the settings sessions view',
        );

        $this->replaceInFile(
            file: base_path('routes/settings.php'),
            replacements: [
                "Route::delete('settings/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');" => "Route::delete('settings/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::get('settings/sessions', [SessionController::class, 'index'])->name('sessions.index');
    Route::delete('settings/sessions', [SessionController::class, 'destroy'])->name('sessions.destroy');",
                "use App\Http\Controllers\Settings\ProfileController;" => "use App\Http\Controllers\Settings\ProfileController;
use App\Http\Controllers\Settings\SessionController;",
            ],
            success: 'settings sessions route added successfully',
            failure: 'Could not add the settings sessions route',
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
        title: 'Sessions',
        url: '/settings/sessions',
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
                __DIR__.'/../../stubs/volt/sessions.blade.php',
                resource_path('views/livewire/settings/sessions.blade.php'),
            ),
            success: 'settings sessions view copied successfully',
            failure: 'Could not copy the settings sessions view',
        );

        $this->replaceInFile(
            file: base_path('routes/web.php'),
            replacements: [
                "Volt::route('settings/profile', 'settings.profile')->name('settings.profile');" => "Volt::route('settings/profile', 'settings.profile')->name('settings.profile');
    Volt::route('settings/sessions', 'settings.sessions')->name('settings.sessions');",
            ],
            success: 'settings sessions route added successfully',
            failure: 'Could not add the settings sessions route',
        );

        $this->replaceInFile(
            file: resource_path('views/components/settings/layout.blade.php'),
            replacements: [
                '<flux:navlist.item href="{{ route(\'settings.password\') }}" wire:navigate>{{ __(\'Password\') }}</flux:navlist.item>' => '<flux:navlist.item href="{{ route(\'settings.password\') }}" wire:navigate>{{ __(\'Password\') }}</flux:navlist.item>
            <flux:navlist.item href="{{ route(\'settings.sessions\') }}" wire:navigate>{{ __(\'Sessions\') }}</flux:navlist.item>',
            ],
            success: 'settings layout updated successfully',
            failure: 'Could not update the settings layout',
        );
    }

    protected function publishVueViews(): void
    {
        $this->executeTask(
            task: fn () => copy(
                __DIR__.'/../../stubs/vue/SessionController.php',
                app_path('Http/Controllers/Settings/SessionController.php'),
            ),
            success: 'settings sessions controller copied successfully',
            failure: 'Could not copy the settings sessions controller',
        );

        $this->executeTask(
            task: fn () => copy(
                __DIR__.'/../../stubs/vue/Sessions.vue',
                resource_path('js/pages/settings/Sessions.vue'),
            ),
            success: 'settings sessions view copied successfully',
            failure: 'Could not copy the settings sessions view',
        );

        $this->replaceInFile(
            file: base_path('routes/settings.php'),
            replacements: [
                "Route::delete('settings/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');" => "Route::delete('settings/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::get('settings/sessions', [SessionController::class, 'index'])->name('sessions.index');
    Route::delete('settings/sessions', [SessionController::class, 'destroy'])->name('sessions.destroy');",
                "use App\Http\Controllers\Settings\ProfileController;" => "use App\Http\Controllers\Settings\ProfileController;
use App\Http\Controllers\Settings\SessionController;",
            ],
            success: 'settings sessions route added successfully',
            failure: 'Could not add the settings sessions route',
        );

        $this->replaceInFile(
            file: resource_path('js/layouts/settings/Layout.vue'),
            replacements: [
                "href: '/settings/password',
    }," => "href: '/settings/password',
    },
    {
        title: 'Sessions',
        href: '/settings/sessions',
    },",
            ],
            success: 'settings layout updated successfully',
            failure: 'Could not update the settings layout',
        );
    }
}
