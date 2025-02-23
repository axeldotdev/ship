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

        $this->installTenantModel();
        $this->installHasTenantTrait();
        $this->updateUserModel();
        $this->installTenantMigration();
        $this->updateUserMigration();

        // TODO: Add views stubs for the selected stack
    }

    protected function installHasTenantTrait(): void
    {
        $this->command->ensureDirectoryExists(app_path('Concerns'));

        $this->executeTask(
            task: fn () => copy(
                __DIR__.'/../../stubs/commons/HasTenant.php',
                app_path("Concerns/Has{$this->modelName}.php"),
            ),
            failure: 'Could not copy the HasTenant trait stub',
        );

        $content = file_get_contents(app_path("Concerns/Has{$this->modelName}.php"));
        $content = str_replace('Tenant', $this->modelName, $content);
        $content = str_replace('tenant', $this->variableName, $content);
        $content = str_replace('tenants', $this->relationName, $content);

        $this->executeTask(
            task: fn () => file_put_contents(app_path("Concerns/Has{$this->modelName}.php"), $content),
            success: 'HasTenant trait copied successfully',
            failure: 'Could not replace the Tenant model name',
        );
    }

    protected function installTenantMigration(): void
    {
        $this->executeTask(
            task: fn () => copy(
                __DIR__.'/../../stubs/commons/0001_01_01_000003_create_tenants_table.php',
                database_path("migrations/0001_01_01_000003_create_{$this->relationName}_table.php"),
            ),
            failure: 'Could not copy the Tenant migration stub',
        );

        $content = file_get_contents(database_path("migrations/0001_01_01_000003_create_{$this->relationName}_table.php"));
        $content = str_replace('tenant', $this->variableName, $content);
        $content = str_replace('tenants', $this->relationName, $content);

        $this->executeTask(
            task: fn () => file_put_contents(
                database_path("migrations/0001_01_01_000003_create_{$this->relationName}_table.php"),
                $content,
            ),
            success: 'Tenant migration copied successfully',
            failure: 'Could not replace the Tenant model name',
        );
    }

    protected function installTenantModel(): void
    {
        $this->executeTask(
            task: fn () => copy(
                __DIR__.'/../../stubs/commons/Tenant.php',
                app_path("Models/{$this->modelName}.php"),
            ),
            failure: 'Could not copy the Tenant model stub',
        );

        $content = file_get_contents(app_path("Models/{$this->modelName}.php"));
        $content = str_replace('Tenant', $this->modelName, $content);

        $this->executeTask(
            task: fn () => file_put_contents(app_path("Models/{$this->modelName}.php"), $content),
            success: 'Tenant model copied successfully',
            failure: 'Could not replace the Tenant model name',
        );
    }

    protected function updateUserMigration(): void
    {
        $content = file_get_contents(database_path('migrations/0001_01_01_000000_create_users_table.php'));
        $content = str_replace(
            '$table->rememberToken();',
            "\$table->rememberToken();\n            \$table->foreignId('current_{$this->variableName}_id')->nullable();",
            $content,
        );

        $this->executeTask(
            task: fn () => file_put_contents(
                database_path('migrations/0001_01_01_000000_create_users_table.php'),
                $content,
            ),
            success: 'User migration updated successfully',
            failure: 'Could not update the User migration',
        );
    }

    protected function updateUserModel(): void
    {
        $content = file_get_contents(app_path('Models/User.php'));
        $content = str_replace(
            "namespace App\Models;\n\n",
            "namespace App\Models;\n\nuse App\Concerns\Has{$this->modelName};\n",
            $content,
        );
        $content = str_replace(
            'HasFactory, Notifiable',
            "Has{$this->modelName}, HasFactory, Notifiable",
            $content,
        );

        $this->executeTask(
            task: fn () => file_put_contents(app_path('Models/User.php'), $content),
            success: 'User model updated successfully',
            failure: 'Could not update the User model traits',
        );
    }
}
