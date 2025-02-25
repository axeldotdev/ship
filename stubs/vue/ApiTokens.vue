<script setup lang="ts">
import { TransitionRoot } from '@headlessui/vue';
import { Head, useForm, usePage } from '@inertiajs/vue3';
import { ref } from 'vue';

import HeadingSmall from '@/components/HeadingSmall.vue';
import InputError from '@/components/InputError.vue';
import Button from '@/components/ui/button/Button.vue';
import { Dialog, DialogClose, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AppLayout from '@/layouts/AppLayout.vue';
import SettingsLayout from '@/layouts/settings/Layout.vue';
import { type BreadcrumbItem, type SharedData } from '@/types';

interface Props {
    className?: string;
    status?: string;
    tokens: Array<{
        id: number;
        name: string;
        last_used_ago: string;
    }>;
}

defineProps<Props>();

const breadcrumbItems: BreadcrumbItem[] = [
    {
        title: 'Api tokens',
        href: '/settings/api-tokens',
    },
];

const passwordInput = ref<HTMLInputElement | null>(null);
const displayingToken = ref(false);
const deletingToken = ref(false);
const apiTokenBeingDeleted = ref<number | null>(null);

const page = usePage<SharedData>();

const createForm = useForm({
    name: '',
});

const deleteForm = useForm({
    password: '',
});

const createApiToken = () => {
    createForm.post(route('api-tokens.store'), {
        preserveScroll: true,
        onSuccess: () => {
            displayingToken.value = true;
            createForm.reset();
        },
    });
};

const confirmApiTokenDeletion = (tokenId: number) => {
    apiTokenBeingDeleted.value = tokenId;
    deletingToken.value = true;
};

const deleteApiToken = () => {
    deleteForm.delete(route('api-tokens.destroy', { token: apiTokenBeingDeleted.value }), {
        preserveScroll: true,
        onSuccess: () => closeModal(),
        onFinish: () => deleteForm.reset(),
    });
};

const closeModal = () => {
    deletingToken.value = false;
    deleteForm.clearErrors();
    deleteForm.reset();
};
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbItems">
        <Head title="API tokens"/>

        <SettingsLayout>
            <div class="space-y-6">
                <HeadingSmall title="Create API tokens" description="API tokens allow third-party services to authenticate with our application on your behalf."/>

                <form @submit.prevent="createApiToken" class="mt-6 space-y-6">
                    <div class="grid gap-2">
                        <Label for="name">
                            Token name
                        </Label>
                        <Input id="name" class="mt-1 block w-full" v-model="createForm.name" required/>
                        <InputError class="mt-2" :message="createForm.errors.name"/>
                    </div>

                    <div class="flex items-center gap-4">
                        <Button :disabled="createForm.processing">
                            Create
                        </Button>

                        <TransitionRoot :show="createForm.recentlySuccessful" enter="transition ease-in-out" enter-from="opacity-0" leave="transition ease-in-out" leave-to="opacity-0">
                            <p class="text-sm text-neutral-600">
                                Created.
                            </p>
                        </TransitionRoot>
                    </div>
                </form>

                <Dialog v-model:open="displayingToken">
                    <DialogContent>
                        <div class="space-y-6">
                            <DialogHeader class="space-y-3">
                                <DialogTitle>
                                    API token
                                </DialogTitle>

                                <DialogDescription>
                                    Please copy your new API token. For your security, it won't be shown again.
                                </DialogDescription>
                            </DialogHeader>

                            <div class="grid gap-2">
                                <Label for="password" class="sr-only">
                                    API token
                                </Label>
                                <Input :defaultValue="page.props.flash?.token" id="api_token_plaintext_token" name="plaintext_token" readonly autocomplete="off" autocorrect="off" autocapitalize="off" spellcheck="false"/>
                            </div>
                        </div>
                    </DialogContent>
                </Dialog>

                <section v-if="tokens.length > 0" class="mt-10 space-y-6">
                    <HeadingSmall title="Manage API Tokens" description="You may delete any of your existing tokens if they are no longer needed."/>

                    <div class="space-y-6">
                        <div v-for="token in tokens" :key="token.id" class="flex items-center justify-between">
                            <div class="break-all dark:text-white">
                                {{ token.name }}
                            </div>

                            <div class="ms-2 flex items-center">
                                <div v-if="token.last_used_ago" class="text-sm text-zinc-400">
                                    Last used {{ token.last_used_ago }}
                                </div>

                                <Button @click="confirmApiTokenDeletion(token.id)" variant="destructive" size="sm">
                                    Delete
                                </Button>
                            </div>
                        </div>
                    </div>

                    <Dialog v-model:open="deletingToken">
                        <DialogContent>
                            <form class="space-y-6" @submit.prevent="deleteApiToken">
                                <DialogHeader class="space-y-3">
                                    <DialogTitle>
                                        Are you sure you want to delete this API token?
                                    </DialogTitle>

                                    <DialogDescription>
                                        Please enter your password to confirm you would like to delete this API token.
                                    </DialogDescription>
                                </DialogHeader>

                                <div class="grid gap-2">
                                    <Label for="password" class="sr-only">
                                        Password
                                    </Label>
                                    <Input v-model="deleteForm.password" id="password" type="password" name="password" ref="passwordInput" placeholder="Password" required/>
                                    <InputError :message="deleteForm.errors.password"/>
                                </div>

                                <DialogFooter>
                                    <DialogClose as-child>
                                        <Button variant="secondary" @click="closeModal"> Cancel </Button>
                                    </DialogClose>

                                    <Button type="submit" variant="destructive" :disabled="deleteForm.processing">
                                        Delete
                                    </Button>
                                </DialogFooter>
                            </form>
                        </DialogContent>
                    </Dialog>
                </section>
            </div>
        </SettingsLayout>
    </AppLayout>
</template>
