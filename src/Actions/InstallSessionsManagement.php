<?php

namespace Axeldotdev\Ship\Actions;

class InstallSessionsManagement extends Action
{
    public function handle(): void
    {
        if (! $this->command->option('sessions')) {
            return;
        }

        $this->executeTask(
            task: fn () => copy(
                __DIR__.'/../../stubs/commons/Session.php',
                app_path('Models/Session.php'),
            ),
            success: 'Session model copied successfully',
            failure: 'Could not copy the Session model stub',
        );

        $this->command->ensureDirectoryExists(app_path('Concerns'));

        $this->executeTask(
            task: fn () => copy(
                __DIR__.'/../../stubs/commons/HasSession.php',
                app_path('Concerns/HasSession.php'),
            ),
            success: 'HasSession trait copied successfully',
            failure: 'Could not copy the Session trait stub',
        );

        $content = file_get_contents(app_path('Models/User.php'));
        $content = str_replace(
            'use Notifiable;',
            'use HasSession;
    use Notifiable;',
            $content,
        );
        $content = str_replace(
            "namespace App\Models;\n\n",
            "namespace App\Models;\n\nuse App\Concerns\HasSession;\n",
            $content,
        );

        $this->executeTask(
            task: fn () => file_put_contents(app_path('Models/User.php'), $content),
            success: 'User model updated successfully',
            failure: 'Could not update the User model traits',
        );

        $this->command->ensureDirectoryExists(app_path('Support'));

        $this->executeTask(
            task: fn () => copy(
                __DIR__.'/../../stubs/commons/Agent.php',
                app_path('Support/Agent.php'),
            ),
            success: 'Agent class copied successfully',
            failure: 'Could not copy the Agent class stub',
        );

        $this->executeTask(
            task: fn () => $this->command->requireComposerPackages('mobiledetect/mobiledetectlib'),
            success: 'mobiledetect/mobiledetectlib installed successfully',
            failure: 'Could not install the mobiledetect/mobiledetectlib package',
        );

        // TODO: Add views stubs for the selected stack
    }
}
