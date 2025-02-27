import { Transition } from '@headlessui/react';
import { Head, useForm } from '@inertiajs/react';
import { Monitor, Smartphone } from 'lucide-react';
import { Fragment, FormEventHandler, useState } from 'react';

import HeadingSmall from '@/components/heading-small';
import InputError from '@/components/input-error';
import { Button } from '@/components/ui/button';
import { Dialog, DialogClose, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AppLayout from '@/layouts/app-layout';
import SettingsLayout from '@/layouts/settings/layout';
import { type BreadcrumbItem } from '@/types';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Sessions',
        href: '/settings/sessions',
    },
];

export default function Sessions({status, sessions}: {status?: string; sessions: Array<{id: number; agent: {platform: string; browser: string; is_desktop: boolean;}; ip_address: string; last_active: string; is_current_device: boolean;}>;}) {
    const [sessionsDeletion, setSessionsDeletion] = useState<boolean>(false);

    const form = useForm({
        password: '',
    });

    const confirmSessionsDeletion = () => {
        setSessionsDeletion(true);
    };

    const logoutSessions: FormEventHandler = () => {
        form.delete(route('sessions.destroy'), {
            preserveScroll: true,
            onSuccess: () => closeModal(),
            onFinish: () => form.reset(),
        });
    };

    const closeModal = () => {
        setSessionsDeletion(false);
        form.clearErrors();
        form.reset();
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Sessions"/>

            <SettingsLayout>
                <div className="space-y-6">
                    <HeadingSmall title="Manage sessions" description="Manage and log out your active sessions on other browsers and devices."/>

                    {sessions.length > 0 && (
                        <div className="mt-5 space-y-6">
                            {sessions.map(session => (
                                <Fragment key={session.id}>
                                    <div className="flex items-center">
                                        <div>
                                            {session.agent.is_desktop ? (
                                                <Monitor className="size-8 text-zinc-500"/>
                                            ) : (
                                                <Smartphone className="size-8 text-zinc-500"/>
                                            )}
                                        </div>

                                        <div className="ms-3">
                                            <div className="text-sm text-zinc-600 dark:text-zinc-400">
                                                {session.agent.platform ? session.agent.platform : 'Unknown'} -
                                                {session.agent.browser ? session.agent.browser : 'Unknown'}
                                            </div>

                                            <div>
                                                <div className="text-xs text-zinc-500">
                                                    {session.ip_address},

                                                    {session.is_current_device ? (
                                                        <span className="font-semibold text-green-500">
                                                            This device
                                                        </span>
                                                    ) : (
                                                        <span>
                                                            Last active {session.last_active}
                                                        </span>
                                                    )}
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </Fragment>
                            ))}
                        </div>
                    )}

                    <div className="flex items-center gap-4">
                        <Button onClick={confirmSessionsDeletion} type="button">
                            Log Out Other Browser Sessions
                        </Button>

                        <Transition show={form.recentlySuccessful} enter="transition ease-in-out" enter-from="opacity-0" leave="transition ease-in-out" leave-to="opacity-0">
                            <p className="text-sm text-neutral-600">
                                Done.
                            </p>
                        </Transition>
                    </div>

                    <Dialog open={sessionsDeletion} onOpenChange={setOpen => !setOpen && closeModal()}>
                        <DialogContent>
                            <form onSubmit={logoutSessions} className="space-y-6">
                                <DialogHeader className="space-y-3">
                                    <DialogTitle>
                                        Are you sure you want to logout of other sessions?
                                    </DialogTitle>

                                    <DialogDescription>
                                        Please enter your password to confirm you would like to log out of your other browser sessions.
                                    </DialogDescription>
                                </DialogHeader>

                                <div className="grid gap-2">
                                    <Label htmlFor="password" className="sr-only">
                                        Password
                                    </Label>
                                    <Input value={form.data.password} onChange={(e) => form.setData('password', e.target.value)} id="password" type="password" name="password" placeholder="Password" required/>
                                    <InputError message={form.errors.password}/>
                                </div>

                                <DialogFooter>
                                    <DialogClose as-child>
                                        <Button onClick={closeModal} variant="secondary" type="button">
                                            Cancel
                                        </Button>
                                    </DialogClose>

                                    <Button type="submit" variant="destructive" disabled={form.processing}>
                                        Logout
                                    </Button>
                                </DialogFooter>
                            </form>
                        </DialogContent>
                    </Dialog>
                </div>
            </SettingsLayout>
        </AppLayout>
    );
}
