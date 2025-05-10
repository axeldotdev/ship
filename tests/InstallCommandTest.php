<?php

use Illuminate\Console\Command;

it('ask for argument and options if none are passed', function (string $stack, bool $deleteConfigFiles, array $options) {
    $command = $this->artisan('ship:install')
        ->expectsQuestion('Which stack would you like to install?', $stack)
        ->expectsQuestion('Would you like to delete the default Laravel config files?', $deleteConfigFiles)
        ->expectsQuestion('Would you like any optional features?', $options);

    if ($options['tenant']) {
        $command->expectsQuestion('What is the name of the tenant model?', 'Workspace');
    }

    $command->assertExitCode(Command::SUCCESS);
})->with('interactiveDataset');

it('do not ask for options when argument is passed', function (string $stack) {
    $this->artisan('ship:install', ['stack' => $stack])
        ->assertExitCode(Command::SUCCESS);
})->with('simpleDataset');

dataset('simpleDataset', ['no-starter', 'livewire', 'react', 'vue']);

dataset('interactiveDataset', [
    [
        'stack' => 'no-starter',
        'deleteConfigFiles' => true,
        'options' => [
            'api',
            'csp',
            'larastan',
            'rector',
            'sessions',
            'socialite',
            'tenant',
        ],
    ],
    [
        'stack' => 'no-starter',
        'deleteConfigFiles' => false,
        'options' => [],
    ],
    [
        'stack' => 'livewire',
        'deleteConfigFiles' => true,
        'options' => [
            'api',
            'csp',
            'larastan',
            'rector',
            'sessions',
            'socialite',
            'tenant',
        ],
    ],
    [
        'stack' => 'livewire',
        'deleteConfigFiles' => false,
        'options' => [],
    ],
    [
        'stack' => 'react',
        'deleteConfigFiles' => true,
        'options' => [
            'api',
            'csp',
            'larastan',
            'rector',
            'sessions',
            'socialite',
            'tenant',
        ],
    ],
    [
        'stack' => 'react',
        'deleteConfigFiles' => false,
        'options' => [],
    ],
    [
        'stack' => 'vue',
        'deleteConfigFiles' => true,
        'options' => [
            'api',
            'csp',
            'larastan',
            'rector',
            'sessions',
            'socialite',
            'tenant',
        ],
    ],
    [
        'stack' => 'vue',
        'deleteConfigFiles' => false,
        'options' => [],
    ],
]);
