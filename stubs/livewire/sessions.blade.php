<?php

use Livewire\Volt\Component;

new class extends Component {}; ?>

<section class="w-full">
    @include('partials.settings-heading')

    <x-settings.layout heading="Manage sessions" subheading="Manage and log out your active sessions">
    </x-settings.layout>
</section>
