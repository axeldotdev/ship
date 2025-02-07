<?php

namespace Axeldotdev\Ship\Actions;

class InstallLarastan extends Action
{
    public function handle(): void
    {
        if (! $this->command->option('larastan')) {
            return;
        }

        $this->executeTask(
            task: fn () => $this->command->requireComposerDevPackages('larastan/larastan'),
            success: 'larastan/larastan installed successfully',
            failure: 'Could not install the larastan/larastan package',
        );

        $this->executeTask(
            task: fn () => copy(
                __DIR__.'/../../stubs/commons/phpstan.neon',
                base_path('phpstan.neon'),
            ),
            success: 'phpstan.neon file successfully',
            failure: 'Could not copy the phpstan.neon stub',
        );

        $this->command->runCommands(['./vendor/bin/phpstan', 'analyse']);
    }
}
