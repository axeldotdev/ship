<?php

namespace Axeldotdev\Ship\Actions;

class ConfigureWorkOS extends Action
{
    public function handle(): void
    {
        if (! $this->command->option('workos')) {
            return;
        }

        $this->command->ensureDirectoryExists(base_path('config'));

        $this->executeTask(
            task: fn () => copy(
                __DIR__.'/../../stubs/commons/services.php',
                config_path('services.php'),
            ),
            success: 'services config copied successfully',
            failure: 'Could not copy the services config stub',
        );
    }
}
