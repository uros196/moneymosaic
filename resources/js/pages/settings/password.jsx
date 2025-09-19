import InputError from '@/components/input-error';
import AppLayout from '@/layouts/app-layout';
import SettingsLayout from '@/layouts/settings/layout';
import { Transition } from '@headlessui/react';
import { Head, useForm } from '@inertiajs/react';
import { useRef } from 'react';

import HeadingSmall from '@/components/heading-small';
import { Button } from '@/components/ui/button';
import { PasswordInput } from '@/components/ui/password-input';
import { Label } from '@/components/ui/label';
import { useI18n } from '@/i18n';

export default function Password() {
    const { __ } = useI18n();

    const breadcrumbs = [
        {
            title: __('settings.password.title'),
            href: route('password.edit'),
        },
    ];
    const passwordInput = useRef(null);
    const currentPasswordInput = useRef(null);

    const { data, setData, errors, put, reset, processing, recentlySuccessful } = useForm({
        current_password: '',
        password: '',
        password_confirmation: '',
    });

    const updatePassword = (e) => {
        e.preventDefault();

        put(route('password.update'), {
            preserveScroll: true,
            onSuccess: () => reset(),
            onError: (errors) => {
                if (errors.password) {
                    reset('password', 'password_confirmation');
                    passwordInput.current?.focus();
                }

                if (errors.current_password) {
                    reset('current_password');
                    currentPasswordInput.current?.focus();
                }
            },
        });
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title={__('settings.password.title')} />

            <SettingsLayout>
                <div className="space-y-6">
                    <HeadingSmall title={__('settings.password.update_password')} description={__('settings.password.update_password_desc')} />

                    <form method="POST" onSubmit={updatePassword} className="space-y-6">
                        <div className="grid gap-2">
                            <Label htmlFor="current_password">{__('settings.password.current_password')}</Label>

                            <PasswordInput
                                id="current_password"
                                ref={currentPasswordInput}
                                value={data.current_password}
                                onChange={(e) => setData('current_password', e.target.value)}
                                autoComplete="current-password"
                                placeholder={__('settings.password.placeholder_current')}
                                required
                                aria-invalid={!!errors.current_password}
                                resetKey={JSON.stringify(errors)}
                            />

                            <InputError message={errors.current_password} />
                        </div>

                        <div className="grid gap-2">
                            <Label htmlFor="password">{__('settings.password.new_password')}</Label>

                            <PasswordInput
                                id="password"
                                ref={passwordInput}
                                value={data.password}
                                onChange={(e) => setData('password', e.target.value)}
                                autoComplete="new-password"
                                placeholder={__('settings.password.placeholder_new')}
                                required
                                aria-invalid={!!errors.password}
                                resetKey={JSON.stringify(errors)}
                            />

                            <InputError message={errors.password} />
                        </div>

                        <div className="grid gap-2">
                            <Label htmlFor="password_confirmation">{__('settings.password.confirm_password')}</Label>

                            <PasswordInput
                                id="password_confirmation"
                                value={data.password_confirmation}
                                onChange={(e) => setData('password_confirmation', e.target.value)}
                                autoComplete="new-password"
                                placeholder={__('settings.password.placeholder_confirm')}
                                required
                                aria-invalid={!!errors.password_confirmation}
                                resetKey={JSON.stringify(errors)}
                            />

                            <InputError message={errors.password_confirmation} />
                        </div>

                        <div className="flex items-center gap-4">
                            <Button disabled={processing} isLoading={processing}>{__('settings.password.save_password')}</Button>

                            <Transition
                                show={recentlySuccessful}
                                enter="transition ease-in-out"
                                enterFrom="opacity-0"
                                leave="transition ease-in-out"
                                leaveTo="opacity-0"
                            >
                                <p className="text-sm text-neutral-600">{__('common.saved')}</p>
                            </Transition>
                        </div>
                    </form>
                </div>
            </SettingsLayout>
        </AppLayout>
    );
}
