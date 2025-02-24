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

        match ($this->command->argument('stack')) {
            'livewire' => $this->publishLivewireViews(),
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
                __DIR__.'/../../stubs/livewire/sessions.blade.php',
                resource_path('views/livewire/settings/sessions.blade.php'),
            ),
            success: 'settings sessions view copied successfully',
            failure: 'Could not copy the settings sessions view',
        );

        $this->replaceInFile(
            file: base_path('routes/web.php'),
            replacements: [
                "Volt::route('settings/password', 'settings.password')->name('settings.password');" => "Volt::route('settings/password', 'settings.password')->name('settings.password');
    Volt::route('settings/sessions', 'settings.sessions')->name('settings.sessions');",
            ],
            success: 'settings sessions route added successfully',
            failure: 'Could not add the settings sessions route',
        );

        $this->replaceInFile(
            file: resource_path('views/components/settings/layout.blade.php'),
            replacements: [
                '<flux:navlist.item href="{{ route(\'settings.password\') }}" wire:navigate>Password</flux:navlist.item>' => '<flux:navlist.item href="{{ route(\'settings.password\') }}" wire:navigate>Password</flux:navlist.item>
            <flux:navlist.item href="{{ route(\'settings.sessions\') }}" wire:navigate>Sessions</flux:navlist.item>',
            ],
            success: 'settings layout updated successfully',
            failure: 'Could not update the settings layout',
        );
    }

    protected function publishReactViews(): void {}

    protected function publishVueViews(): void {}
}
