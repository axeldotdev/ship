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

        if ($this->command->argument('stack') === 'no-starter') {
            return;
        }

        // TODO: configure socialite (routes, controller, etc.) for the selected stack
    }
}
