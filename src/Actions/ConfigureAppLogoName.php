<?php

namespace Axeldotdev\Ship\Actions;

class ConfigureAppLogoName extends Action
{
    public function handle(): void
    {
        if ($this->command->argument('stack') === 'no-starter') {
            return;
        }

        match ($this->command->argument('stack')) {
            'livewire' => $this->configureLivewireComponent(),
            'react' => $this->configureReactComponent(),
            'vue' => $this->configureVueComponent(),
        };
    }

    protected function configureLivewireComponent(): void
    {
        $this->replaceInFile(
            file: resource_path('views/components/app-logo.blade.php'),
            replacements: [
                'Laravel Starter Kit' => str(basename(base_path()))->replace('-', ' ')->title(),
            ],
            success: 'App logo updated successfully',
            failure: 'Could not update the app logo',
        );
    }

    protected function configureReactComponent(): void {}

    protected function configureVueComponent(): void
    {
        $this->replaceInFile(
            file: resource_path('js/components/AppLogo.vue'),
            replacements: [
                'Laravel Starter Kit' => str(basename(base_path()))->replace('-', ' ')->title(),
            ],
            success: 'App logo updated successfully',
            failure: 'Could not update the app logo',
        );
    }
}
