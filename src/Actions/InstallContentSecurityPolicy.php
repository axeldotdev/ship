<?php

namespace Axeldotdev\Ship\Actions;

class InstallContentSecurityPolicy extends Action
{
    public function handle(): void
    {
        if (! $this->command->option('csp')) {
            return;
        }

        $this->configureMiddleware();
        $this->publishConfig();
        $this->publishPolicy();
        $this->installPackage();
    }

    protected function configureMiddleware(): void
    {
        $this->replaceInFile(
            file: base_path('bootstrap/app.php'),
            replacements: [
                '->withMiddleware(function (Middleware $middleware) {
        //
    })' => '->withMiddleware(function (Middleware $middleware) {
        $middleware->web([
            \Spatie\Csp\AddCspHeaders::class,
        ]);
    })',
            ],
            success: 'CSP headers middlweware added successfully',
            failure: 'Could not add the CSP headers middleware',
        );
    }

    protected function installPackage(): void
    {
        $this->executeTask(
            task: fn () => $this->command->requireComposerPackages('spatie/laravel-csp'),
            success: 'spatie/laravel-csp installed successfully',
            failure: 'Could not install the spatie/laravel-csp package',
        );
    }

    protected function publishConfig(): void
    {
        $this->command->ensureDirectoryExists(base_path('config'));

        $this->executeTask(
            task: fn () => copy(__DIR__.'/../../stubs/commons/csp.php', config_path('csp.php')),
            success: 'CSP config copied successfully',
            failure: 'Could not copy the CSP config stub',
        );
    }

    protected function publishPolicy(): void
    {
        $this->command->ensureDirectoryExists(app_path('Support'));

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
