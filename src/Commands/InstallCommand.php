<?php

namespace Axeldotdev\Ship\Commands;

use Illuminate\Console\Command;
use Illuminate\Contracts\Console\PromptsForMissingInput;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Process\PhpExecutableFinder;
use Illuminate\Support\Str;
use RuntimeException;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Process;

use function Laravel\Prompts\confirm;
use function Laravel\Prompts\multiselect;
use function Laravel\Prompts\select;
use function Laravel\Prompts\text;

#[AsCommand(name: 'ship:install')]
class InstallCommand extends Command implements PromptsForMissingInput
{
    /** @var string */
    public $signature = 'ship:install {stack : The development stack that should be installed (livewire,react,vue)}
                                      {--tenant : Indicates if you want to install the tenant model}
                                      {--tenantModel= : The name of the tenant model}
                                      {--csp : Install the Content Security Policy package from Spatie}
                                      {--larastan : Install Larastan for static analysis}
                                      {--pest : Indicates if Pest should be installed}
                                      {--rector : Install Rector for code refactoring}
                                      {--socialite : Install Laravel Socialite for OAuth}
                                      {--sessions : Install the way to manage sessions for users}';

    /** @var string */
    public $description = 'Install the things you need for your application.';

    protected array $steps = [
        'configureAppServiceProvider',
        'configureSessionCookie',
        'installContentSecurityPolicy',
        'installLarastan',
        'installRector',
        'installSessionsManagement',
        'installSocialite',
        'installTenantModel',
    ];

    public function handle(): int
    {
        if (! in_array($this->argument('stack'), ['livewire', 'react', 'vue'])) {
            $this->components->error('Invalid stack. Supported stacks are [livewire], [react] and [vue].');

            return Command::FAILURE;
        }

        foreach ($this->steps as $step) {
            if (! $this->{$step}()) {
                return Command::FAILURE;
            }
        }

        $this->runCommands(['./vendor/bin/pint']);

        $this->runDatabaseMigrations();

        return Command::SUCCESS;
    }

    protected function afterPromptingForMissingArguments(InputInterface $input, OutputInterface $output): void
    {
        collect(multiselect(
            label: 'Would you like any optional features?',
            options: [
                'csp' => 'Content Security Policy (with Spatie)',
                'larastan' => 'Larastan',
                'rector' => 'Rector',
                'sessions' => 'Sessions management',
                'tenant' => 'Tenant model',
            ],
        ))->each(fn ($option) => $input->setOption($option, true));

        if ($this->option('tenant')) {
            $input->setOption('tenantModel', text(
                label: 'What is the name of the tenant model?',
                default: 'Tenant',
                validate: ['required', 'string'],
            ));
        }

        $input->setOption('pest', select(
            label: 'Which testing framework do you prefer?',
            options: ['Pest', 'PHPUnit'],
            default: 'Pest',
        ) === 'Pest');
    }

    protected function configureAppServiceProvider(): bool
    {
        if (! copy(__DIR__.'/../../stubs/commons/AppServiceProvider.php', app_path('Providers/AppServiceProvider.php'))) {
            $this->error('Could not copy the AppServiceProvider stub');

            return false;
        }

        $this->info('AppServiceProvider copied successfully');

        return true;
    }

    protected function configureSessionCookie(): bool
    {
        $uuid = Str::uuid();

        $envContent = file_get_contents(base_path('.env'));
        $envContent = str_replace('SESSION_DOMAIN=null', "SESSION_DOMAIN=null\nSESSION_COOKIE={$uuid}", $envContent);

        if (! file_put_contents(base_path('.env'), $envContent)) {
            $this->error('Could not update the env SESSION_COOKIE variable');

            return false;
        }

        $envContent = file_get_contents(base_path('.env.example'));
        $envContent = str_replace('SESSION_DOMAIN=null', "SESSION_DOMAIN=null\nSESSION_COOKIE={$uuid}", $envContent);

        if (! file_put_contents(base_path('.env.example'), $envContent)) {
            $this->error('Could not update the env SESSION_COOKIE variable');

            return false;
        }

        $this->info('SESSION_COOKIE env variable updated successfully');

        return true;
    }

    protected function installContentSecurityPolicy(): bool
    {
        if (! $this->option('csp')) {
            return true;
        }

        if (! $this->requireComposerPackages('spatie/laravel-csp')) {
            $this->error('Could not install the spatie/laravel-csp package');

            return false;
        }

        $this->info('spatie/laravel-csp installed successfully');

        if (! copy(__DIR__.'/../../stubs/commons/csp.php', config_path('csp.php'))) {
            $this->error('Could not copy the CSP config stub');

            return false;
        }

        $this->info('CSP config copied successfully');

        (new Filesystem)->ensureDirectoryExists(app_path('Support'));

        if (! copy(__DIR__.'/../../stubs/commons/LaravelViteNonceGenerator.php', app_path('Support/LaravelViteNonceGenerator.php'))) {
            $this->error('Could not copy the LaravelViteNonceGenerator class stub');

            return false;
        }

        $this->info('LaravelViteNonceGenerator class copied successfully');

        if (! copy(__DIR__.'/../../stubs/commons/CspPolicy.php', app_path('Support/CspPolicy.php'))) {
            $this->error('Could not copy the CspPolicy class stub');

            return false;
        }

        $this->info('CspPolicy class copied successfully');
        $this->info('You can now configure your CSP policy in this file');

        return true;
    }

    protected function installLarastan(): bool
    {
        if (! $this->option('larastan')) {
            return true;
        }

        if (! $this->requireComposerDevPackages('larastan/larastan')) {
            $this->error('Could not install the larastan/larastan package');

            return false;
        }

        $this->info('larastan/larastan installed successfully');

        if (! copy(__DIR__.'/../../stubs/commons/phpstan.neon', base_path('phpstan.neon'))) {
            $this->error('Could not copy the phpstan.neon stub');

            return false;
        }

        $this->info('phpstan.neon file successfully');

        $this->runCommands(['./vendor/bin/phpstan', 'analyse']);

        return true;
    }

    protected function installRector(): bool
    {
        if (! $this->option('rector')) {
            return true;
        }

        if (! $this->requireComposerDevPackages('rector/rector')) {
            $this->error('Could not install the rector/rector package');

            return false;
        }

        $this->info('rector/rector installed successfully');

        if (! copy(__DIR__.'/../../stubs/commons/rector.php', base_path('rector.php'))) {
            $this->error('Could not copy the rector.php stub');

            return false;
        }

        $this->info('rector.php file successfully');

        $this->runCommands(['./vendor/bin/rector', 'process']);

        return true;
    }

    protected function installSessionsManagement(): bool
    {
        if (! $this->option('sessions')) {
            return true;
        }

        if (! copy(__DIR__.'/../../stubs/commons/Session.php', app_path('Models/Session.php'))) {
            $this->error('Could not copy the Session model stub');

            return false;
        }

        $this->info('Session model copied successfully');

        (new Filesystem)->ensureDirectoryExists(app_path('Concerns'));

        if (! copy(__DIR__.'/../../stubs/commons/HasSession.php', app_path('Concerns/HasSession.php'))) {
            $this->error('Could not copy the Session trait stub');

            return false;
        }

        $this->info('HasSession trait copied successfully');

        $userModelContent = file_get_contents(app_path('Models/User.php'));
        $userModelContent = str_replace("namespace App\Models;\n\n", "namespace App\Models;\n\nuse App\Concerns\HasSession;\n", $userModelContent);
        $userModelContent = str_replace('HasFactory, Notifiable', 'HasSession, HasFactory, Notifiable', $userModelContent);

        if (! file_put_contents(app_path('Models/User.php'), $userModelContent)) {
            $this->error('Could not update the User model traits');

            return false;
        }

        $this->info('User model updated successfully');

        (new Filesystem)->ensureDirectoryExists(app_path('Support'));

        if (! copy(__DIR__.'/../../stubs/commons/Agent.php', app_path('Support/Agent.php'))) {
            $this->error('Could not copy the Agent class stub');

            return false;
        }

        $this->info('Agent class copied successfully');

        if (! $this->requireComposerPackages('mobiledetect/mobiledetectlib')) {
            $this->error('Could not install the mobiledetect/mobiledetectlib package');

            return false;
        }

        $this->info('mobiledetect/mobiledetectlib installed successfully');

        // TODO: Add views stubs for the selected stack

        return true;
    }

    protected function installSocialite(): bool
    {
        if (! $this->option('socialite')) {
            return true;
        }

        if (! $this->requireComposerPackages('laravel/socialite')) {
            $this->error('Could not install the laravel/socialite package');

            return false;
        }

        // TODO: configure socialite (routes, controller, etc.) for the selected stack

        return true;
    }

    protected function installTenantModel(): bool
    {
        if (! $this->option('tenant')) {
            return true;
        }

        $modelName = str($this->option('tenantModel') ?? 'Tenant');
        $variableName = $modelName->lower();
        $relationName = $variableName->plural();

        if (! copy(__DIR__.'/../../stubs/commons/Tenant.php', app_path("Models/{$modelName}.php"))) {
            $this->error('Could not copy the Tenant model stub');

            return false;
        }

        $modelContent = file_get_contents(app_path("Models/{$modelName}.php"));
        $modelContent = str_replace('Tenant', $modelName, $modelContent);

        if (! file_put_contents(app_path("Models/{$modelName}.php"), $modelContent)) {
            $this->error('Could not replace the Tenant model name');

            return false;
        }

        $this->info('Tenant model copied successfully');

        (new Filesystem)->ensureDirectoryExists(app_path('Concerns'));

        if (! copy(__DIR__.'/../../stubs/commons/HasTenant.php', app_path("Concerns/Has{$modelName}.php"))) {
            $this->error('Could not copy the HasTenant trait stub');

            return false;
        }

        $modelContent = file_get_contents(app_path("Concerns/Has{$modelName}.php"));
        $modelContent = str_replace('Tenant', $modelName, $modelContent);
        $modelContent = str_replace('tenant', $variableName, $modelContent);
        $modelContent = str_replace('tenants', $relationName, $modelContent);

        if (! file_put_contents(app_path("Concerns/Has{$modelName}.php"), $modelContent)) {
            $this->error('Could not replace the Tenant model name');

            return false;
        }

        $this->info('HasTenant trait copied successfully');

        $userModelContent = file_get_contents(app_path('Models/User.php'));
        $userModelContent = str_replace("namespace App\Models;\n\n", "namespace App\Models;\n\nuse App\Concerns\Has{$modelName};\n", $userModelContent);
        $userModelContent = str_replace('HasFactory, Notifiable', "Has{$modelName}, HasFactory, Notifiable", $userModelContent);

        if (! file_put_contents(app_path('Models/User.php'), $userModelContent)) {
            $this->error('Could not update the User model traits');

            return false;
        }

        $this->info('User model updated successfully');

        if (! copy(__DIR__.'/../../stubs/commons/0001_01_01_000003_create_tenants_table.php', database_path("migrations/0001_01_01_000003_create_{$relationName}_table.php"))) {
            $this->error('Could not copy the Tenant migration stub');

            return false;
        }

        $migrationContent = file_get_contents(database_path("migrations/0001_01_01_000003_create_{$relationName}_table.php"));
        $migrationContent = str_replace('tenant', $variableName, $migrationContent);
        $migrationContent = str_replace('tenants', $relationName, $migrationContent);

        if (! file_put_contents(database_path("migrations/0001_01_01_000003_create_{$relationName}_table.php"), $migrationContent)) {
            $this->error('Could not replace the Tenant model name');

            return false;
        }

        $this->info('Tenant migration copied successfully');

        $userMigrationContent = file_get_contents(database_path('migrations/0001_01_01_000000_create_users_table.php'));
        $userMigrationContent = str_replace('$table->rememberToken();', "\$table->rememberToken();\n            \$table->foreignId('current_{$variableName}_id')->nullable();", $userMigrationContent);

        if (! file_put_contents(database_path('migrations/0001_01_01_000000_create_users_table.php'), $userMigrationContent)) {
            $this->error('Could not update the User migration');

            return false;
        }

        $this->info('User migration updated successfully');

        // TODO: Add views stubs for the selected stack

        return true;
    }

    protected function isUsingPest(): bool
    {
        return class_exists(\Pest\TestSuite::class);
    }

    protected function phpBinary(): string
    {
        if (function_exists('Illuminate\Support\php_binary')) {
            return \Illuminate\Support\php_binary();
        }

        return (new PhpExecutableFinder)->find(false) ?: 'php';
    }

    protected function promptForMissingArgumentsUsing(): array
    {
        return [
            'stack' => fn () => select(
                label: 'Which stack would you like to install?',
                options: [
                    'livewire' => 'Livewire',
                    'react' => 'React',
                    'vue' => 'Vue',
                ],
            ),
        ];
    }

    protected function requireComposerDevPackages(mixed $packages): bool
    {
        $command = array_merge(
            ['composer', 'require', '--dev'],
            is_array($packages) ? $packages : func_get_args()
        );

        return ! (new Process($command, base_path(), ['COMPOSER_MEMORY_LIMIT' => '-1']))
            ->setTimeout(null)
            ->run(function ($type, $output) {
                $this->output->write($output);
            });
    }

    protected function requireComposerPackages(mixed $packages): bool
    {
        $command = array_merge(
            ['composer', 'require'],
            is_array($packages) ? $packages : func_get_args()
        );

        return ! (new Process($command, base_path(), ['COMPOSER_MEMORY_LIMIT' => '-1']))
            ->setTimeout(null)
            ->run(function ($type, $output) {
                $this->output->write($output);
            });
    }

    protected function runCommands(array $commands): void
    {
        $process = Process::fromShellCommandline(implode(' && ', $commands), null, null, null, null);

        if ('\\' !== DIRECTORY_SEPARATOR && file_exists('/dev/tty') && is_readable('/dev/tty')) {
            try {
                $process->setTty(true);
            } catch (RuntimeException $e) {
                $this->output->writeln('  <bg=yellow;fg=black> WARN </> '.$e->getMessage().PHP_EOL);
            }
        }

        $process->run(function ($type, $line) {
            $this->output->write('    '.$line);
        });
    }

    protected function runDatabaseMigrations(): void
    {
        if (confirm('New database migrations were added. Would you like to re-run your migrations?', true)) {
            (new Process([$this->phpBinary(), 'artisan', 'migrate:fresh', '--force'], base_path()))
                ->setTimeout(null)
                ->run(function ($type, $output) {
                    $this->output->write($output);
                });
        }
    }
}
