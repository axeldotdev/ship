<?php

namespace Axeldotdev\Ship\Actions;

class DeleteConfigFiles extends Action
{
    public function handle(): void
    {
        if (! $this->command->option('delete-configs')) {
            return;
        }

        $this->executeTask(
            task: fn () => $this->command->filesystem()->deleteDirectory(base_path('config')),
            success: 'Config files deleted successfully',
            failure: 'Could not delete the config files',
        );
    }
}
