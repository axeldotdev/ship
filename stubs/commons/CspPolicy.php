<?php

namespace App\Support;

use Spatie\Csp\Directive;
use Spatie\Csp\Policies\Policy;

class CspPolicy extends Policy
{
    private string $appUrl;

    public function configure(): void
    {
        $this->appUrl = (string) config('app.url');

        $this->addGeneralDirectives();
        $this->addStyleDirectives();
        $this->addScriptDirectives();
    }

    public function addGeneralDirectives(): Policy
    {
        return $this
            ->addDirective(Directive::BASE, 'self')
            ->addDirective(Directive::FORM_ACTION, [
                $this->appUrl,
                $this->appUrl.':5173',
            ])
            ->addDirective(Directive::IMG, [
                '*',
                'unsafe-inline',
                'data:',
            ])
            ->addDirective(Directive::OBJECT, 'none');
    }

    public function addStyleDirectives(): Policy
    {
        return $this->addDirective(Directive::STYLE, [
            $this->appUrl,
            $this->appUrl.':5173',
            'fonts.bunny.net',
            'fonts.googleapis.com',
            'unsafe-inline',
        ]);
    }

    public function addScriptDirectives(): Policy
    {
        return $this
            ->addNonceForDirective(Directive::SCRIPT)
            ->addDirective(Directive::SCRIPT, [
                $this->appUrl,
                $this->appUrl.':5173',
                'unsafe-eval',
                'unsafe-inline',
            ]);
    }

    public function addFontDirectives(): Policy
    {
        return $this->addDirective(Directive::FONT, [
            $this->appUrl,
            $this->appUrl.':5173',
            'unsafe-inline',
            'data:',
        ]);
    }
}
