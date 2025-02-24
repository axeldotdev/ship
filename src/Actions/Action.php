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

    protected function replaceInFile(
        string $file,
        array $replacements,
        ?string $success = null,
        string $failure = 'Failed',
    ): void {
        $content = file_get_contents($file);

        foreach ($replacements as $search => $replace) {
            $content = str_replace($search, $replace, $content);
        }

        $this->executeTask(
            task: fn () => file_put_contents($file, $content),
            success: $success,
            failure: $failure,
        );
    }
}
