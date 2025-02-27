<?php

use Illuminate\Console\Command;

it('ask for argument and options if none are passed', function (string $stack, bool $deleteConfigFiles, array $options) {
    $this->artisan('ship:install')
        ->expectsQuestion('Which stack would you like to install?', $stack)
        ->expectsQuestion('Would you like to delete the default Laravel config files?', $deleteConfigFiles)
        ->expectsQuestion('Would you like any optional features?', $options)
        ->assertExitCode(Command::SUCCESS);
})->with('datasets');

it('do not ask for options when argument is passed', function (string $stack, bool $deleteConfigFiles, array $options) {
    $this->artisan('ship:install', ['stack' => $stack])
        ->assertExitCode(Command::SUCCESS);
})->with('datasets');

dataset('datasets', [
    [
        'stack' => 'no-starter',
        'deleteConfigFiles' => true,
        'options' => [],
    ],
    [
        'stack' => 'livewire',
        'deleteConfigFiles' => true,
        'options' => [],
    ],
    [
        'stack' => 'react',
        'deleteConfigFiles' => true,
        'options' => [],
    ],
    [
        'stack' => 'vue',
        'deleteConfigFiles' => true,
        'options' => [],
    ],
]);
