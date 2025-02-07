<?php

namespace Axeldotdev\Ship\Actions;

class InstallRector extends Action
{
    public function handle(): void
    {
        if (! $this->command->option('rector')) {
            return;
        }

        $this->executeTask(
            task: fn () => $this->command->requireComposerDevPackages('rector/rector'),
            success: 'rector/rector installed successfully',
            failure: 'Could not install the rector/rector package',
        );

        $this->executeTask(
            task: fn () => copy(
                __DIR__.'/../../stubs/commons/rector.php',
                base_path('rector.php'),
            ),
            success: 'rector.php file successfully',
            failure: 'Could not copy the rector.php stub',
        );

        $this->command->runCommands(['./vendor/bin/rector', 'process']);
    }
}
