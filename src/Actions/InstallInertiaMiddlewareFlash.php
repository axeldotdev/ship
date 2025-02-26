<?php

namespace Axeldotdev\Ship\Actions;

class InstallInertiaMiddlewareFlash extends Action
{
    public function handle(): void
    {
        if (in_array($this->command->argument('stack'), ['no-starter', 'livewire'])) {
            return;
        }

        $this->replaceInFile(
            file: app_path('Http/Middleware/HandleInertiaRequests.php'),
            replacements: [
                "'auth' => [
                'user' => \$request->user(),
            ]," => "'auth' => [
                'user' => \$request->user(),
            ],
            'flash' => \$request->session()->get('flash', []),",
            ],
            success: 'Inertia middleware flash installed successfully',
            failure: 'Could not install the Inertia middleware flash',
        );
    }
}
