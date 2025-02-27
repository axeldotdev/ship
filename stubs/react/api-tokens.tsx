import { Transition } from '@headlessui/react';
import { Head, useForm, usePage } from '@inertiajs/react';
import { FormEventHandler, Fragment, useState } from 'react';

import HeadingSmall from '@/components/heading-small';
import InputError from '@/components/input-error';
import { Button } from '@/components/ui/button';
import { Dialog, DialogClose, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AppLayout from '@/layouts/app-layout';
import SettingsLayout from '@/layouts/settings/layout';
import { type BreadcrumbItem, type SharedData } from '@/types';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Api tokens',
        href: '/settings/api-tokens',
    },
];

export default function ApiTokens({status, tokens}: {status?: string; tokens: Array<{id: number; name: string; last_used_ago: string;}>}) {
    const page = usePage<SharedData>();
    const plainTextToken = (page.props.flash as { token?: string })?.token;

    let [displayingToken, setDisplayingToken] = useState<boolean>(false);
    let [deletingToken, setDeletingToken] = useState<boolean>(false);
    let [apiTokenBeingDeleted, setApiTokenBeingDeleted] = useState<number | null>(null);

    const createForm = useForm({
        name: '',
    });

    const deleteForm = useForm({
        password: '',
    });

    const createApiToken: FormEventHandler = (e) => {
        e.preventDefault();

        createForm.post(route('api-tokens.store'), {
            preserveScroll: true,
            onSuccess: () => {
                setDisplayingToken(true);
                createForm.reset();
            },
        });
    };

    const confirmApiTokenDeletion = (tokenId: number) => {
        setApiTokenBeingDeleted(tokenId);
        setDeletingToken(true);
    };

    const deleteApiToken: FormEventHandler = (e) => {
        e.preventDefault();

        deleteForm.delete(route('api-tokens.destroy', { token: apiTokenBeingDeleted }), {
            preserveScroll: true,
            onSuccess: () => closeModal(),
            onFinish: () => deleteForm.reset(),
        });
    };

    const closeModal = () => {
        setDeletingToken(false);
        deleteForm.clearErrors();
        deleteForm.reset();
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="API tokens"/>

            <SettingsLayout>
                <div className="space-y-6">
                    <HeadingSmall title="Create API tokens" description="API tokens allow third-party services to authenticate with our application on your behalf."/>

                    <form onSubmit={createApiToken} className="mt-6 space-y-6">
                        <div className="grid gap-2">
                            <Label htmlFor="name">
                                Token name
                            </Label>
                            <Input id="name" className="mt-1 block w-full" value={createForm.data.name} onChange={(e) => createForm.setData('name', e.target.value)} required/>
                            <InputError className="mt-2" message={createForm.errors.name}/>
                        </div>

                        <div className="flex items-center gap-4">
                            <Button disabled={createForm.processing} type="submit">
                                Create
                            </Button>

                            <Transition show={createForm.recentlySuccessful} enter="transition ease-in-out" enter-from="opacity-0" leave="transition ease-in-out" leave-to="opacity-0">
                                <p className="text-sm text-neutral-600">
                                    Created.
                                </p>
                            </Transition>
                        </div>
                    </form>

                    <Dialog open={displayingToken} onOpenChange={setOpen => setDisplayingToken(setOpen)}>
                        <DialogContent>
                            <div className="space-y-6">
                                <DialogHeader className="space-y-3">
                                    <DialogTitle>
                                        API token
                                    </DialogTitle>

                                    <DialogDescription>
                                        Please copy your new API token. For your security, it won't be shown again.
                                    </DialogDescription>
                                </DialogHeader>

                                <div className="grid gap-2">
                                    <Label htmlFor="api_token_plaintext_token" className="sr-only">
                                        API token
                                    </Label>
                                    <Input value={plainTextToken} id="api_token_plaintext_token" name="plaintext_token" readOnly/>
                                </div>
                            </div>
                        </DialogContent>
                    </Dialog>

                    {tokens.length > 0 && (
                        <section className="mt-10 space-y-6">
                            <HeadingSmall title="Manage API Tokens" description="You may delete any of your existing tokens if they are no longer needed."/>

                            <div className="space-y-6">
                                {tokens.map(token => (
                                    <Fragment key={token.id}>
                                        <div className="flex items-center justify-between">
                                            <div className="break-all dark:text-white">
                                                {token.name}
                                            </div>

                                            <div className="ms-2 flex items-center">
                                                {token.last_used_ago && (
                                                    <div className="text-sm text-zinc-400">
                                                        Last used {token.last_used_ago}
                                                    </div>
                                                )}

                                                <Button onClick={() => confirmApiTokenDeletion(token.id)} variant="destructive" size="sm" type="button">
                                                    Delete
                                                </Button>
                                            </div>
                                        </div>
                                    </Fragment>
                                ))}
                            </div>

                            <Dialog open={deletingToken} onOpenChange={setOpen => !setOpen && closeModal()}>
                                <DialogContent>
                                    <form className="space-y-6" onSubmit={deleteApiToken}>
                                        <DialogHeader className="space-y-3">
                                            <DialogTitle>
                                                Are you sure you want to delete this API token?
                                            </DialogTitle>

                                            <DialogDescription>
                                                Please enter your password to confirm you would like to delete this API token.
                                            </DialogDescription>
                                        </DialogHeader>

                                        <div className="grid gap-2">
                                            <Label htmlFor="password" className="sr-only">
                                                Password
                                            </Label>
                                            <Input value={deleteForm.data.password} onChange={(e) => deleteForm.setData('password', e.target.value)} id="password" type="password" name="password" placeholder="Password" required/>
                                            <InputError message={deleteForm.errors.password}/>
                                        </div>

                                        <DialogFooter>
                                            <DialogClose as-child>
                                                <Button onClick={closeModal} variant="secondary" type="button">
                                                    Cancel
                                                </Button>
                                            </DialogClose>

                                            <Button type="submit" variant="destructive" disabled={deleteForm.processing}>
                                                Delete
                                            </Button>
                                        </DialogFooter>
                                    </form>
                                </DialogContent>
                            </Dialog>
                        </section>
                    )}
                </div>
            </SettingsLayout>
        </AppLayout>
    );
}
