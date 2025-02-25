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
        title: 'Api tokens',
        href: '/settings/api-tokens',
    },
];
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbItems">
        <Head title="API tokens" />

        <SettingsLayout>
            <div class="space-y-6">
                <HeadingSmall
                    title="Manage API tokens"
                    description="API tokens allow third-party services to authenticate with our application on your behalf."
                />
            </div>
        </SettingsLayout>
    </AppLayout>
</template>
