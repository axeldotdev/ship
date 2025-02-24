<?php

namespace Axeldotdev\Ship\Actions;

use Illuminate\Support\Str;

class ConfigureSessionCookie extends Action
{
    public function handle(): void
    {
        $uuid = Str::uuid();

        $this->replaceInFile(
            file: base_path('.env'),
            replacements: [
                'SESSION_DOMAIN=null' => "SESSION_DOMAIN=null\nSESSION_COOKIE={$uuid}",
            ],
            failure: 'Could not update the env SESSION_COOKIE variable',
        );

        $this->replaceInFile(
            file: base_path('.env.example'),
            replacements: [
                'SESSION_DOMAIN=null' => "SESSION_DOMAIN=null\nSESSION_COOKIE={$uuid}",
            ],
            success: 'SESSION_COOKIE variable updated successfully',
            failure: 'Could not update the env SESSION_COOKIE variable',
        );
    }
}
