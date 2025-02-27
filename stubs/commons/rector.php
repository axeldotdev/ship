<?php

use Rector\Config\RectorConfig;

return RectorConfig::configure()
    ->withPaths([
        __DIR__.'/app',
        __DIR__.'/database',
        __DIR__.'/resources/views',
        __DIR__.'/routes',
    ])
    ->withPreparedSets(
        deadCode: true,
        codeQuality: true,
        codingStyle: true,
        typeDeclarations: true,
        earlyReturn: true,
        strictBooleans: true,
        privatization: true,
        instanceOf: true,
        carbon: true,
    )
    ->withPhpSets();
