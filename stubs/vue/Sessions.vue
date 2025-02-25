<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import SettingsLayout from '@/layouts/settings/Layout.vue';

import HeadingSmall from '@/components/HeadingSmall.vue';
import { type BreadcrumbItem } from '@/types';

interface Props {
    className?: string;
}

defineProps<Props>();

const breadcrumbItems: BreadcrumbItem[] = [
    {
        title: 'Sessions',
        href: '/settings/sessions',
    },
];
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbItems">
        <Head title="Sessions" />

        <SettingsLayout>
            <div class="space-y-6">
                <HeadingSmall title="Manage sessions" description="Manage and log out your active sessions on other browsers and devices." />
            </div>
        </SettingsLayout>
    </AppLayout>
</template>
