<?php

namespace Axeldotdev\Ship\Actions;

class ConfigureEnvExampleForProduction extends Action
{
    public function handle(): void
    {
        $content = file_get_contents(base_path('.env.example'));
        $content = str_replace('APP_ENV=local', 'APP_ENV=prod', $content);
        $content = str_replace('APP_DEBUG=true', 'APP_DEBUG=false', $content);
        $content = str_replace('LOG_STACK=single', 'LOG_STACK=daily', $content);

        $this->executeTask(
            task: fn () => file_put_contents(base_path('.env.example'), $content),
            success: 'env.example updated successfully for production',
            failure: 'Could not update the env.example to get it ready for production',
        );
    }
}
