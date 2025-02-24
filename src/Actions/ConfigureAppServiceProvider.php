<?php

namespace Axeldotdev\Ship\Actions;

class ConfigureAppServiceProvider extends Action
{
    public function handle(): void
    {
        $this->executeTask(
            task: fn () => copy(
                __DIR__.'/../../stubs/commons/AppServiceProvider.php',
                app_path('Providers/AppServiceProvider.php'),
            ),
            success: 'AppServiceProvider copied successfully',
            failure: 'Could not copy the AppServiceProvider stub',
        );

        $content = file_get_contents(app_path('Models/User.php'));
        $content = str_replace(
            '/** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        \'name\',
        \'email\',
        \'password\',
    ];',
            '/** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory;

    use Notifiable;',
            $content,
        );

        $this->executeTask(
            task: fn () => file_put_contents(app_path('Models/User.php'), $content),
            success: 'User model updated successfully',
            failure: 'Could not update the User model traits',
        );
    }
}
