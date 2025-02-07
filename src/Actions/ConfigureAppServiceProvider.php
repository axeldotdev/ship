<?php

namespace Axeldotdev\Ship\Actions;

class ConfigureAppServiceProvider extends Action
{
    public function handle(): void
    {
        $this->executeTask(
            task: fn () => copy(
                __DIR__.'/../../stubs/commons/AppServiceProvider.php',
                app_path('Providers/AppServiceProvider.php'),
            ),
            success: 'AppServiceProvider copied successfully',
            failure: 'Could not copy the AppServiceProvider stub',
        );
    }
}
