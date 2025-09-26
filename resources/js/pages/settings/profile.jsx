import { Form, Head, Link } from '@inertiajs/react';

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
import { useI18n } from '@/i18n';
import { useLocaleRefreshOnly } from '@/components/language-switcher.jsx';

export default function Profile({ user, mustVerifyEmail, status, twoFactorSetupInProgress, currencies }) {
    const { __ } = useI18n();
    const breadcrumbs = [
        {
            title: __('settings.profile.title'),
            href: route('profile.edit'),
        },
    ];

    const enabled = Boolean(user.data.two_factor_enabled);
    const tfType = user.data.two_factor_type;
    const [locale, setLocale] = useState(user.data.locale);
    const [passwordConfirmMinutes, setPasswordConfirmMinutes] = useState(String(user.data.password_confirm_minutes ?? '0'));
    const [defaultCurrencyCode, setDefaultCurrencyCode] = useState(user.data.default_currency_code);

    // Refresh the user data after the language is changed using a LanguageSwitcher component
    useLocaleRefreshOnly(['user']);

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title={__('settings.profile.title')} />

            <SettingsLayout>
                <div className="space-y-6">
                    <HeadingSmall title={__('settings.profile.profile_information')} description={__('settings.profile.profile_information_desc')} />

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
                                    <Label htmlFor="name">{__('settings.profile.name')}</Label>

                                    <Input
                                        id="name"
                                        defaultValue={user.data.name}
                                        name="name"
                                        required
                                        autoComplete="name"
                                        placeholder={__('settings.profile.full_name')}
                                        aria-invalid={!!errors.name}
                                    />

                                    <InputError className="mt-2" message={errors.name} />
                                </div>

                                <div className="grid gap-2">
                                    <Label htmlFor="email">{__('settings.profile.email')}</Label>

                                    <Input
                                        id="email"
                                        type="email"
                                        defaultValue={user.data.email}
                                        name="email"
                                        required
                                        autoComplete="username"
                                        placeholder={__('settings.profile.email')}
                                        aria-invalid={!!errors.email}
                                    />

                                    <InputError className="mt-2" message={errors.email} />
                                </div>

                                <div className="grid gap-2">
                                    <Label htmlFor="locale">{__('settings.profile.language')}</Label>

                                    <input type="hidden" name="locale" value={locale} />
                                    <Select value={locale} onValueChange={setLocale}>
                                        <SelectTrigger id="locale" aria-invalid={!!errors.locale}>
                                            <SelectValue />
                                        </SelectTrigger>
                                        <SelectContent>
                                            <SelectItem value="en">{__('settings.profile.english')}</SelectItem>
                                            <SelectItem value="sr">{__('settings.profile.serbian')}</SelectItem>
                                        </SelectContent>
                                    </Select>

                                    <InputError className="mt-2" message={errors.locale} />
                                </div>

                                <div className="grid gap-2">
                                    <Label htmlFor="default_currency_code">{__('settings.profile.default_currency')}</Label>

                                    <input type="hidden" name="default_currency_code" value={defaultCurrencyCode} />
                                    <Select value={defaultCurrencyCode} onValueChange={setDefaultCurrencyCode}>
                                        <SelectTrigger id="default_currency_code" aria-invalid={!!errors.default_currency_code}>
                                            {defaultCurrencyCode}
                                        </SelectTrigger>
                                        <SelectContent>
                                            {currencies.data.map((currency) => (
                                                <SelectItem key={currency.value} value={currency.value}>
                                                    {currency.display_name}
                                                </SelectItem>
                                            ))}
                                        </SelectContent>
                                    </Select>

                                    <InputError className="mt-2" message={errors.default_currency_code} />
                                </div>

                                <div className="grid gap-2">
                                    <Label htmlFor="password_confirm_minutes">{__('settings.profile.require_password_after_inactivity')}</Label>
                                    <input type="hidden" name="password_confirm_minutes" value={passwordConfirmMinutes} />
                                    <Select value={passwordConfirmMinutes} onValueChange={setPasswordConfirmMinutes}>
                                        <SelectTrigger id="password_confirm_minutes" aria-invalid={!!errors.password_confirm_minutes}>
                                            <SelectValue />
                                        </SelectTrigger>
                                        <SelectContent>
                                            <SelectItem value="0">{__('settings.profile.inactivity_off')}</SelectItem>
                                            <SelectItem value="30">{__('settings.profile.inactivity_30')}</SelectItem>
                                            <SelectItem value="60">{__('settings.profile.inactivity_60')}</SelectItem>
                                            <SelectItem value="240">{__('settings.profile.inactivity_240')}</SelectItem>
                                            <SelectItem value="600">{__('settings.profile.inactivity_600')}</SelectItem>
                                        </SelectContent>
                                    </Select>
                                    <p className="text-xs text-muted-foreground">{__('settings.profile.require_password_hint')}</p>
                                    <InputError className="mt-2" message={errors.password_confirm_minutes} />
                                </div>

                                {mustVerifyEmail && user.data.email_verified_at === null && (
                                    <div>
                                        <p className="-mt-4 text-sm text-muted-foreground">
                                            {__('settings.profile.email_unverified')}{' '}
                                            <Link
                                                href={route('verification.send')}
                                                method="post"
                                                as="button"
                                                className="text-foreground underline decoration-neutral-300 underline-offset-4 transition-colors duration-300 ease-out hover:decoration-current! dark:decoration-neutral-500"
                                            >
                                                {__('settings.profile.resend_verification')}
                                            </Link>
                                        </p>

                                        {status === 'verification-link-sent' && (
                                            <div className="mt-2 text-sm font-medium text-green-600">
                                                {__('settings.profile.verification_link_sent')}
                                            </div>
                                        )}
                                    </div>
                                )}

                                <div className="flex items-center gap-4">
                                    <Button disabled={processing} isLoading={processing}>{__('common.save')}</Button>
                                </div>
                            </>
                        )}
                    </Form>
                </div>

                <div className="rounded-lg border p-4">
                    <div className="flex items-center justify-between gap-4">
                        <div>
                            <p className="font-medium">{__('settings.profile.two_factor')}</p>
                            <p className="text-sm text-muted-foreground">{__('settings.profile.two_factor_desc')}</p>
                            <div className="mt-1">
                                {enabled ? (
                                    <span className="inline-flex items-center gap-1 rounded-full bg-emerald-500/10 px-2 py-0.5 text-xs font-medium text-emerald-700 dark:text-emerald-400">
                                        <span className="h-1.5 w-1.5 rounded-full bg-emerald-500" aria-hidden="true"></span>
                                        {__('settings.profile.status_enabled')}{tfType ? ` (${String(tfType).toUpperCase()})` : ''}
                                    </span>
                                ) : twoFactorSetupInProgress ? (
                                    <span className="inline-flex items-center gap-1 rounded-full bg-amber-500/10 px-2 py-0.5 text-xs font-medium text-amber-700 dark:text-amber-400">
                                        <span className="h-1.5 w-1.5 rounded-full bg-amber-500" aria-hidden="true"></span>
                                        {__('settings.profile.status_setting_up')}{tfType ? ` (${String(tfType).toUpperCase()})` : ''}
                                    </span>
                                ) : (
                                    <span className="inline-flex items-center gap-1 rounded-full bg-neutral-500/10 px-2 py-0.5 text-xs font-medium text-neutral-600 dark:text-neutral-400">
                                        <span className="h-1.5 w-1.5 rounded-full bg-neutral-400" aria-hidden="true"></span>
                                        {__('settings.profile.status_disabled')}
                                    </span>
                                )}
                            </div>
                        </div>
                        <Link href={route('settings.security')} className="text-foreground underline decoration-neutral-300 underline-offset-4 transition-colors duration-300 ease-out hover:decoration-current! dark:decoration-neutral-500">
                            {__('common.manage')}
                        </Link>
                    </div>
                </div>

                <div className="rounded-lg border p-4">
                    <div className="flex items-center justify-between gap-4">
                        <div>
                            <p className="font-medium">{__('settings.profile.sessions_card_title')}</p>
                            <p className="text-sm text-muted-foreground">{__('settings.profile.sessions_card_desc')}</p>
                        </div>
                        <Link href={route('settings.sessions')} className="text-foreground underline decoration-neutral-300 underline-offset-4 transition-colors duration-300 ease-out hover:decoration-current! dark:decoration-neutral-500">
                            {__('common.manage')}
                        </Link>
                    </div>
                </div>

                <DeleteUser />
            </SettingsLayout>
        </AppLayout>
    );
}
