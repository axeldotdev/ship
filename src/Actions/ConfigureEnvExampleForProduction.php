<?php

namespace Axeldotdev\Ship\Actions;

class ConfigureEnvExampleForProduction extends Action
{
    public function handle(): void
    {
        $appName = str(basename(base_path()))->replace('-', ' ')->title();

        $this->replaceInFile(
            file: base_path('.env'),
            replacements: [
                'APP_NAME=Laravel' => "APP_NAME={$appName}",
                'LOG_STACK=single' => 'LOG_STACK=daily',
            ],
            success: '.env updated successfully',
            failure: 'Could not update the .env',
        );

        $this->replaceInFile(
            file: base_path('.env.example'),
            replacements: [
                'APP_NAME=Laravel' => "APP_NAME={$appName}",
                'APP_ENV=local' => 'APP_ENV=prod',
                'APP_DEBUG=true' => 'APP_DEBUG=false',
                'LOG_STACK=single' => 'LOG_STACK=daily',
            ],
            success: '.env.example updated successfully for production',
            failure: 'Could not update the .env.example to get it ready for production',
        );
    }
}
