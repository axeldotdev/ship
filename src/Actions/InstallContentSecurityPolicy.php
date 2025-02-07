<?php

namespace Axeldotdev\Ship\Actions;

use Illuminate\Filesystem\Filesystem;

class InstallContentSecurityPolicy extends Action
{
    public function handle(): void
    {
        if (! $this->command->option('csp')) {
            return;
        }

        $this->executeTask(
            task: fn () => $this->command->requireComposerPackages('spatie/laravel-csp'),
            success: 'spatie/laravel-csp installed successfully',
            failure: 'Could not install the spatie/laravel-csp package',
        );

        $this->executeTask(
            task: fn () => copy(__DIR__.'/../../stubs/commons/csp.php', config_path('csp.php')),
            success: 'CSP config copied successfully',
            failure: 'Could not copy the CSP config stub',
        );

        (new Filesystem)->ensureDirectoryExists(app_path('Support'));

        $this->executeTask(
            task: fn () => copy(
                __DIR__.'/../../stubs/commons/LaravelViteNonceGenerator.php',
                app_path('Support/LaravelViteNonceGenerator.php'),
            ),
            success: 'LaravelViteNonceGenerator class copied successfully',
            failure: 'Could not copy the LaravelViteNonceGenerator class stub',
        );

        $this->executeTask(
            task: fn () => copy(
                __DIR__.'/../../stubs/commons/CspPolicy.php',
                app_path('Support/CspPolicy.php'),
            ),
            success: 'CspPolicy class copied successfully. You can now configure your CSP policy in this file.',
            failure: 'Could not copy the CspPolicy class stub',
        );
    }
}
