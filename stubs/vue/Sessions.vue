<script setup lang="ts">
import { TransitionRoot } from '@headlessui/vue';
import { Head, useForm } from '@inertiajs/vue3';
import { Monitor, Smartphone } from 'lucide-vue-next';
import { ref } from 'vue';

import HeadingSmall from '@/components/HeadingSmall.vue';
import InputError from '@/components/InputError.vue';
import Button from '@/components/ui/button/Button.vue';
import { Dialog, DialogClose, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AppLayout from '@/layouts/AppLayout.vue';
import SettingsLayout from '@/layouts/settings/Layout.vue';
import { type BreadcrumbItem } from '@/types';

interface Props {
    status?: string;
    sessions: Array<{
        id: number;
        agent: {
            platform: string;
            browser: string;
            is_desktop: boolean;
        };
        ip_address: string;
        last_active: string;
        is_current_device: boolean;
    }>;
}

defineProps<Props>();

const breadcrumbItems: BreadcrumbItem[] = [
    {
        title: 'Sessions',
        href: '/settings/sessions',
    },
];

const sessionsDeletion = ref(false);

const form = useForm({
    password: '',
});

const confirmSessionsDeletion = () => {
    sessionsDeletion.value = true;
};

const logoutSessions = () => {
    form.delete(route('sessions.destroy'), {
        preserveScroll: true,
        onSuccess: () => closeModal(),
        onFinish: () => form.reset(),
    });
};

const closeModal = () => {
    sessionsDeletion.value = false;
    form.clearErrors();
    form.reset();
};
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbItems">
        <Head title="Sessions"/>

        <SettingsLayout>
            <div class="space-y-6">
                <HeadingSmall title="Manage sessions" description="Manage and log out your active sessions on other browsers and devices."/>

                <div v-if="sessions.length > 0" class="mt-5 space-y-6">
                    <div v-for="session in sessions" :key="session.id" class="flex items-center">
                        <div>
                            <Monitor v-if="session.agent.is_desktop" class="size-8 text-zinc-500"/>

                            <Smartphone v-else class="size-8 text-zinc-500"/>
                        </div>

                        <div class="ms-3">
                            <div class="text-sm text-zinc-600 dark:text-zinc-400">
                                {{ session.agent.platform ? session.agent.platform : 'Unknown' }} -
                                {{ session.agent.browser ? session.agent.browser : 'Unknown' }}
                            </div>

                            <div>
                                <div class="text-xs text-zinc-500">
                                    {{ session.ip_address }},

                                    <span v-if="session.is_current_device" class="font-semibold text-green-500">
                                        This device
                                    </span>

                                    <span v-else>
                                        Last active {{ session.last_active }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="flex items-center gap-4">
                    <Button @click="confirmSessionsDeletion" type="button">
                        Log Out Other Browser Sessions
                    </Button>

                    <TransitionRoot :show="form.recentlySuccessful" enter="transition ease-in-out" enter-from="opacity-0" leave="transition ease-in-out" leave-to="opacity-0">
                        <p class="text-sm text-neutral-600">
                            Done.
                        </p>
                    </TransitionRoot>
                </div>

                <Dialog v-model:open="sessionsDeletion">
                    <DialogContent>
                        <form @submit.prevent="logoutSessions" class="space-y-6">
                            <DialogHeader class="space-y-3">
                                <DialogTitle>
                                    Are you sure you want to logout of other sessions?
                                </DialogTitle>

                                <DialogDescription>
                                    Please enter your password to confirm you would like to log out of your other browser sessions.
                                </DialogDescription>
                            </DialogHeader>

                            <div class="grid gap-2">
                                <Label for="password" class="sr-only">
                                    Password
                                </Label>
                                <Input v-model="form.password" id="password" type="password" name="password" placeholder="Password" required/>
                                <InputError :message="form.errors.password"/>
                            </div>

                            <DialogFooter>
                                <DialogClose as-child>
                                    <Button @click="closeModal" variant="secondary" type="button">
                                        Cancel
                                    </Button>
                                </DialogClose>

                                <Button type="submit" variant="destructive" :disabled="form.processing">
                                    Logout
                                </Button>
                            </DialogFooter>
                        </form>
                    </DialogContent>
                </Dialog>
            </div>
        </SettingsLayout>
    </AppLayout>
</template>
