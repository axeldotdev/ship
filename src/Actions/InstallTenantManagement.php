<?php

namespace Axeldotdev\Ship\Actions;

class InstallTenantManagement extends Action
{
    protected string $modelName;

    protected string $variableName;

    protected string $relationName;

    public function handle(): void
    {
        if (! $this->command->option('tenant')) {
            return;
        }

        $this->modelName = (string) str($this->command->option('tenantModel') ?? 'Tenant');
        $this->variableName = (string) str($this->modelName)->lower();
        $this->relationName = (string) str($this->variableName)->plural();

        $this->publishFactory();
        $this->publishModel();
        $this->publishMigration();
        $this->publishTrait();
        $this->updateUserMigration();
        $this->updateUserModel();

        // TODO: Add views stubs for the selected stack
    }

    protected function publishFactory(): void
    {
        $this->executeTask(
            task: fn () => copy(
                __DIR__.'/../../stubs/commons/TenantFactory.php',
                database_path("factories/{$this->modelName}Factory.php"),
            ),
            failure: 'Could not copy the Tenant factory stub',
        );

        $this->replaceInFile(
            file: database_path("factories/{$this->modelName}Factory.php"),
            replacements: [
                'Tenant' => $this->modelName,
            ],
            success: 'Tenant factory copied successfully',
            failure: 'Could not replace the Tenant factory name',
        );
    }

    protected function publishMigration(): void
    {
        $this->executeTask(
            task: fn () => copy(
                __DIR__.'/../../stubs/commons/0001_01_01_000003_create_tenants_table.php',
                database_path("migrations/0001_01_01_000003_create_{$this->relationName}_table.php"),
            ),
            failure: 'Could not copy the Tenant migration stub',
        );

        $this->replaceInFile(
            file: database_path("migrations/0001_01_01_000003_create_{$this->relationName}_table.php"),
            replacements: [
                'tenant' => $this->variableName,
                'tenants' => $this->relationName,
            ],
            success: 'Tenant migration copied successfully',
            failure: 'Could not replace the Tenant model name',
        );
    }

    protected function publishModel(): void
    {
        $this->executeTask(
            task: fn () => copy(
                __DIR__.'/../../stubs/commons/Tenant.php',
                app_path("Models/{$this->modelName}.php"),
            ),
            failure: 'Could not copy the Tenant model stub',
        );

        $this->replaceInFile(
            file: app_path("Models/{$this->modelName}.php"),
            replacements: [
                'Tenant' => $this->modelName,
            ],
            success: 'Tenant model copied successfully',
            failure: 'Could not replace the Tenant model name',
        );
    }

    protected function publishTrait(): void
    {
        $this->command->ensureDirectoryExists(app_path('Concerns'));

        $this->executeTask(
            task: fn () => copy(
                __DIR__.'/../../stubs/commons/HasTenant.php',
                app_path("Concerns/Has{$this->modelName}.php"),
            ),
            failure: 'Could not copy the HasTenant trait stub',
        );

        $this->replaceInFile(
            file: app_path("Concerns/Has{$this->modelName}.php"),
            replacements: [
                'Tenant' => $this->modelName,
                'tenant' => $this->variableName,
                'tenants' => $this->relationName,
            ],
            success: 'HasTenant trait copied successfully',
            failure: 'Could not replace the Tenant model name',
        );
    }

    protected function updateUserMigration(): void
    {
        $this->replaceInFile(
            file: database_path('migrations/0001_01_01_000000_create_users_table.php'),
            replacements: [
                '$table->rememberToken();' => "\$table->rememberToken();
            \$table->foreignId('current_{$this->variableName}_id')->nullable();",
            ],
            success: 'User migration updated successfully',
            failure: 'Could not update the User migration',
        );
    }

    protected function updateUserModel(): void
    {
        $this->replaceInFile(
            file: app_path('Models/User.php'),
            replacements: [
                'use Notifiable' => "use Has{$this->modelName};
    use Notifiable",
                'namespace App\Models;' => "namespace App\Models;

use App\Concerns\Has{$this->modelName};",
            ],
            success: 'User model updated successfully',
            failure: 'Could not update the User model traits',
        );
    }
}
