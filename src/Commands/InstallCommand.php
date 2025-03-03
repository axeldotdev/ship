<?php

namespace Axeldotdev\Ship\Commands;

use Axeldotdev\Ship\Actions;
use Illuminate\Console\Command;
use Illuminate\Contracts\Console\PromptsForMissingInput;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Process\PhpExecutableFinder;
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
    public $signature = 'ship:install {stack : The development stack that should be installed (no-starter,livewire,react,vue)}
                                      {--workos : Indicates if you use WorkOS}
                                      {--volt : Indicates if you want to install the Volt}
                                      {--tenant : Indicates if you want to install the tenant model}
                                      {--tenantModel= : The name of the tenant model}
                                      {--delete-configs : Indicates if the default Laravel config files should be deleted}
                                      {--api : Install the API management resources and Sanctum}
                                      {--csp : Install the Content Security Policy package from Spatie}
                                      {--larastan : Install Larastan for static analysis}
                                      {--pest : Indicates if Pest should be installed}
                                      {--rector : Install Rector for code refactoring}
                                      {--socialite : Install Laravel Socialite for OAuth}
                                      {--sessions : Install the way to manage sessions for users}';

    /** @var string */
    public $description = 'Install the things you need for your application.';

    public Filesystem $filesystem;

    protected array $actions = [
        Actions\ConfigureAppServiceProvider::class,
        Actions\ConfigureEnvExampleForProduction::class,
        Actions\ConfigureAppLogoName::class,
        Actions\ConfigureSessionCookie::class,
        Actions\DeleteConfigFiles::class,
        Actions\ConfigureWorkOS::class,
        Actions\InstallInertiaMiddlewareFlash::class,
        Actions\InstallApiManagement::class,
        Actions\InstallContentSecurityPolicy::class,
        Actions\InstallLarastan::class,
        Actions\InstallRector::class,
        Actions\InstallSessionsManagement::class,
        Actions\InstallSocialite::class,
        Actions\InstallTenantManagement::class,
    ];

    public function handle(): int
    {
        if (! in_array($this->argument('stack'), ['no-starter', 'livewire', 'react', 'vue'])) {
            $this->error('Invalid stack. Supported stacks are [no-starter], [livewire], [react] and [vue].');

            return Command::FAILURE;
        }

        foreach ($this->actions as $action) {
            (new $action($this))->handle();
        }

        $this->runCommands(['./vendor/bin/pint']);
        $this->runDatabaseMigrations();

        return Command::SUCCESS;
    }

    protected function afterPromptingForMissingArguments(
        InputInterface $input,
        OutputInterface $output,
    ): void {
        if ($this->argument('stack') === 'livewire') {
            $input->setOption('volt', confirm(
                label: 'Do you use Volt?',
                default: true,
            ));
        }

        $input->setOption('workos', confirm(
            label: 'Do you use WorkOS Authkit?',
            default: false,
        ));

        $input->setOption('delete-configs', confirm(
            label: 'Would you like to delete the default Laravel config files?',
            default: true,
        ));

        collect(multiselect(
            label: 'Would you like any optional features?',
            options: [
                'api' => 'API management',
                'csp' => 'Content Security Policy (with Spatie)',
                'larastan' => 'Larastan',
                'rector' => 'Rector',
                'sessions' => 'Sessions management',
                'socialite' => 'Socialite (Don\'t install it if you use WorkOS)',
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

    public function ensureDirectoryExists(string $directory): void
    {
        $this->filesystem()->ensureDirectoryExists($directory);
    }

    public function filesystem(): Filesystem
    {
        return $this->filesystem ??= new Filesystem;
    }

    public function isUsingPest(): bool
    {
        return class_exists(\Pest\TestSuite::class);
    }

    public function phpBinary(): string
    {
        if (function_exists('Illuminate\Support\php_binary')) {
            return \Illuminate\Support\php_binary();
        }

        return (new PhpExecutableFinder)->find(false) ?: 'php';
    }

    public function promptForMissingArgumentsUsing(): array
    {
        return [
            'stack' => fn () => select(
                label: 'Which stack would you like to install?',
                options: [
                    'no-starter' => 'No starter kit',
                    'livewire' => 'Livewire',
                    'react' => 'React',
                    'vue' => 'Vue',
                ],
            ),
        ];
    }

    public function requireComposerDevPackages(mixed $packages): bool
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

    public function requireComposerPackages(mixed $packages): bool
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

    public function runArtisanCommand(array $command): void
    {
        if (confirm('New database migrations were added. Would you like to re-run your migrations?', true)) {
            (new Process([$this->phpBinary(), 'artisan', ...$command], base_path()))
                ->setTimeout(null)
                ->run(function ($type, $output) {
                    $this->output->write($output);
                });
        }
    }

    public function runCommands(array $commands): void
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

    public function runDatabaseMigrations(): void
    {
        if (! $this->option('tenant')) {
            return;
        }

        if (confirm('New database migrations were added. Would you like to re-run your migrations?', true)) {
            (new Process([$this->phpBinary(), 'artisan', 'migrate:fresh', '--force'], base_path()))
                ->setTimeout(null)
                ->run(function ($type, $output) {
                    $this->output->write($output);
                });
        }
    }
}
