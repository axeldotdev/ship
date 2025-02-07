<?php

namespace Axeldotdev\Ship\Actions;

use Illuminate\Console\Command;

abstract class Action
{
    public function __construct(public Command $command) {}

    abstract public function handle(): void;

    protected function executeTask(
        callable $task,
        ?string $success = null,
        string $failure = 'Failed',
    ): void {
        if (! $task()) {
            $this->command->error($failure);
        }

        if ($success) {
            $this->command->info($success);
        }
    }
}
