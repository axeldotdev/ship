<?php

namespace Axeldotdev\Ship\Actions;

use Illuminate\Support\Str;

class ConfigureSessionCookie extends Action
{
    public function handle(): void
    {
        $uuid = Str::uuid();

        $content = file_get_contents(base_path('.env'));

        if (str_contains($content, 'SESSION_COOKIE')) {
            $this->command->info('SESSION_COOKIE variable already set');

            return;
        }

        $content = str_replace(
            'SESSION_DOMAIN=null',
            "SESSION_DOMAIN=null\nSESSION_COOKIE={$uuid}",
            $content,
        );

        $this->executeTask(
            task: fn () => file_put_contents(base_path('.env'), $content),
            failure: 'Could not update the env SESSION_COOKIE variable',
        );

        $content = file_get_contents(base_path('.env.example'));
        $content = str_replace(
            'SESSION_DOMAIN=null',
            "SESSION_DOMAIN=null\nSESSION_COOKIE={$uuid}",
            $content,
        );

        $this->executeTask(
            task: fn () => file_put_contents(base_path('.env.example'), $content),
            success: 'SESSION_COOKIE variable updated successfully',
            failure: 'Could not update the env SESSION_COOKIE variable',
        );
    }
}
