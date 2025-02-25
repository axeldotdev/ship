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

        $this->replaceInFile(
            file: base_path('routes/console.php'),
            replacements: [
                "Schedule::command('model:prune')->daily();" => "Schedule::command('model:prune')->daily();
Schedule::command('sanctum:prune-expired')->daily();",
            ],
            success: 'Sanctum prune command added successfully',
            failure: 'Could not add the Sanctum prune command',
        );

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

        match ($this->command->argument('stack')) {
            'livewire' => $this->publishLivewireViews(),
            'react' => $this->publishReactViews(),
            'vue' => $this->publishVueViews(),
        };
    }

    protected function publishLivewireViews(): void
    {
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
                "Volt::route('settings/password', 'settings.password')->name('settings.password');" => "Volt::route('settings/password', 'settings.password')->name('settings.password');
    Volt::route('settings/api-tokens', 'settings.api-tokens')->name('settings.api-tokens');",
            ],
            success: 'settings api tokens route added successfully',
            failure: 'Could not add the settings api tokens route',
        );

        $this->replaceInFile(
            file: resource_path('views/components/settings/layout.blade.php'),
            replacements: [
                '<flux:navlist.item href="{{ route(\'settings.password\') }}" wire:navigate>Password</flux:navlist.item>' => '<flux:navlist.item href="{{ route(\'settings.password\') }}" wire:navigate>Password</flux:navlist.item>
            <flux:navlist.item href="{{ route(\'settings.api-tokens\') }}" wire:navigate>API tokens</flux:navlist.item>',
            ],
            success: 'settings layout updated successfully',
            failure: 'Could not update the settings layout',
        );
    }

    protected function publishReactViews(): void {}

    protected function publishVueViews(): void {}
}
