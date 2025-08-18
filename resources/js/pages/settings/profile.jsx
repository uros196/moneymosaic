import { Form, Head, Link, usePage } from '@inertiajs/react';

import DeleteUser from '@/components/delete-user';
import HeadingSmall from '@/components/heading-small';
import InputError from '@/components/input-error';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AppLayout from '@/layouts/app-layout';
import SettingsLayout from '@/layouts/settings/layout';
import { useState } from 'react';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select.jsx';

const breadcrumbs = [
    {
        title: 'Profile settings',
        href: '/settings/profile',
    },
];

export default function Profile({ mustVerifyEmail, status, twoFactorSetupInProgress }) {
    const { auth } = usePage().props;
    const enabled = Boolean(auth.user.two_factor_enabled);
    const tfType = auth.user.two_factor_type;
    const [locale, setLocale] = useState(auth.user.locale ?? 'en');
    const [passwordConfirmMinutes, setPasswordConfirmMinutes] = useState(String(auth.user.password_confirm_minutes ?? '0'));

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Profile settings" />

            <SettingsLayout>
                <div className="space-y-6">
                    <HeadingSmall title="Profile information" description="Update your name and email address" />

                    <Form
                        method="patch"
                        action={route('profile.update')}
                        options={{
                            preserveScroll: true,
                        }}
                        className="space-y-6"
                    >
                        {({ processing, errors }) => (
                            <>
                                <div className="grid gap-2">
                                    <Label htmlFor="name">Name</Label>

                                    <Input
                                        id="name"
                                        defaultValue={auth.user.name}
                                        name="name"
                                        required
                                        autoComplete="name"
                                        placeholder="Full name"
                                        aria-invalid={!!errors.name}
                                    />

                                    <InputError className="mt-2" message={errors.name} />
                                </div>

                                <div className="grid gap-2">
                                    <Label htmlFor="email">Email address</Label>

                                    <Input
                                        id="email"
                                        type="email"
                                        defaultValue={auth.user.email}
                                        name="email"
                                        required
                                        autoComplete="username"
                                        placeholder="Email address"
                                        aria-invalid={!!errors.email}
                                    />

                                    <InputError className="mt-2" message={errors.email} />
                                </div>

                                <div className="grid gap-2">
                                    <Label htmlFor="locale">Language</Label>

                                    <input type="hidden" name="locale" value={locale} />
                                    <Select value={locale} onValueChange={setLocale}>
                                        <SelectTrigger id="locale" aria-invalid={!!errors.locale}>
                                            <SelectValue />
                                        </SelectTrigger>
                                        <SelectContent>
                                            <SelectItem value="en">English</SelectItem>
                                            <SelectItem value="sr">Srpski</SelectItem>
                                        </SelectContent>
                                    </Select>

                                    <InputError className="mt-2" message={errors.locale} />
                                </div>

                                <div className="grid gap-2">
                                    <Label htmlFor="password_confirm_minutes">Require password after inactivity</Label>
                                    <input type="hidden" name="password_confirm_minutes" value={passwordConfirmMinutes} />
                                    <Select value={passwordConfirmMinutes} onValueChange={setPasswordConfirmMinutes}>
                                        <SelectTrigger id="password_confirm_minutes" aria-invalid={!!errors.password_confirm_minutes}>
                                            <SelectValue />
                                        </SelectTrigger>
                                        <SelectContent>
                                            <SelectItem value="0">Off</SelectItem>
                                            <SelectItem value="30">After 30 minutes</SelectItem>
                                            <SelectItem value="60">After 1 hour</SelectItem>
                                            <SelectItem value="240">After 4 hours</SelectItem>
                                            <SelectItem value="600">After 10 hours</SelectItem>
                                        </SelectContent>
                                    </Select>
                                    <p className="text-xs text-muted-foreground">You will be asked to confirm your password after being inactive for the selected time.</p>
                                    <InputError className="mt-2" message={errors.password_confirm_minutes} />
                                </div>

                                {mustVerifyEmail && auth.user.email_verified_at === null && (
                                    <div>
                                        <p className="-mt-4 text-sm text-muted-foreground">
                                            Your email address is unverified.{' '}
                                            <Link
                                                href={route('verification.send')}
                                                method="post"
                                                as="button"
                                                className="text-foreground underline decoration-neutral-300 underline-offset-4 transition-colors duration-300 ease-out hover:decoration-current! dark:decoration-neutral-500"
                                            >
                                                Click here to resend the verification email.
                                            </Link>
                                        </p>

                                        {status === 'verification-link-sent' && (
                                            <div className="mt-2 text-sm font-medium text-green-600">
                                                A new verification link has been sent to your email address.
                                            </div>
                                        )}
                                    </div>
                                )}

                                <div className="flex items-center gap-4">
                                    <Button disabled={processing} isLoading={processing}>Save</Button>
                                </div>
                            </>
                        )}
                    </Form>
                </div>

                <div className="rounded-lg border p-4">
                    <div className="flex items-center justify-between gap-4">
                        <div>
                            <p className="font-medium">Two-factor authentication</p>
                            <p className="text-sm text-muted-foreground">Add an extra layer of security to your account.</p>
                            <div className="mt-1">
                                {enabled ? (
                                    <span className="inline-flex items-center gap-1 rounded-full bg-emerald-500/10 px-2 py-0.5 text-xs font-medium text-emerald-700 dark:text-emerald-400">
                                        <span className="h-1.5 w-1.5 rounded-full bg-emerald-500" aria-hidden="true"></span>
                                        Enabled{tfType ? ` (${String(tfType).toUpperCase()})` : ''}
                                    </span>
                                ) : twoFactorSetupInProgress ? (
                                    <span className="inline-flex items-center gap-1 rounded-full bg-amber-500/10 px-2 py-0.5 text-xs font-medium text-amber-700 dark:text-amber-400">
                                        <span className="h-1.5 w-1.5 rounded-full bg-amber-500" aria-hidden="true"></span>
                                        Setting up{tfType ? ` (${String(tfType).toUpperCase()})` : ''}
                                    </span>
                                ) : (
                                    <span className="inline-flex items-center gap-1 rounded-full bg-neutral-500/10 px-2 py-0.5 text-xs font-medium text-neutral-600 dark:text-neutral-400">
                                        <span className="h-1.5 w-1.5 rounded-full bg-neutral-400" aria-hidden="true"></span>
                                        Disabled
                                    </span>
                                )}
                            </div>
                        </div>
                        <Link href={route('settings.security')} className="text-foreground underline decoration-neutral-300 underline-offset-4 transition-colors duration-300 ease-out hover:decoration-current! dark:decoration-neutral-500">
                            Manage
                        </Link>
                    </div>
                </div>

                <div className="rounded-lg border p-4">
                    <div className="flex items-center justify-between gap-4">
                        <div>
                            <p className="font-medium">Active sessions</p>
                            <p className="text-sm text-muted-foreground">Review and log out devices signed in to your account.</p>
                        </div>
                        <Link href={route('settings.sessions')} className="text-foreground underline decoration-neutral-300 underline-offset-4 transition-colors duration-300 ease-out hover:decoration-current! dark:decoration-neutral-500">
                            Manage
                        </Link>
                    </div>
                </div>

                <DeleteUser />
            </SettingsLayout>
        </AppLayout>
    );
}
