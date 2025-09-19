import { Form, Head } from '@inertiajs/react';
import { useState } from 'react';

import InputError from '@/components/input-error';
import TextLink from '@/components/text-link';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { PasswordInput } from '@/components/ui/password-input';
import { Label } from '@/components/ui/label';
import AuthLayout from '@/layouts/auth-layout';
import { useI18n } from '@/i18n';

export default function Register() {
    const [errorNonce, setErrorNonce] = useState(0);
    const { __ } = useI18n();
    return (
        <AuthLayout title={__('auth.register_title')} description={__('auth.register_description')}>
            <Head title={__('auth.register')} />
            <Form
                method="post"
                action={route('register')}
                onSubmitComplete={(form) => form.reset('password', 'password_confirmation')}
                onError={() => setErrorNonce((n) => n + 1)}
                disableWhileProcessing
                className="flex flex-col gap-6"
            >
                {({ processing, errors }) => (
                    <>
                        <div className="grid gap-6">
                            <div className="grid gap-2">
                                <Label htmlFor="name">{__('auth.name')}</Label>
                                <Input
                                    id="name"
                                    type="text"
                                    required
                                    autoFocus
                                    tabIndex={1}
                                    autoComplete="name"
                                    name="name"
                                    placeholder={__('auth.full_name')}
                                    aria-invalid={!!errors.name}
                                />
                                <InputError message={errors.name} className="mt-2" />
                            </div>

                            <div className="grid gap-2">
                                <Label htmlFor="email">{__('auth.email')}</Label>
                                <Input
                                    id="email"
                                    type="email"
                                    required
                                    tabIndex={2}
                                    autoComplete="email"
                                    name="email"
                                    placeholder={__('auth.email')}
                                    aria-invalid={!!errors.email}
                                />
                                <InputError message={errors.email} />
                            </div>

                            <div className="grid gap-2">
                                <Label htmlFor="password">{__('auth.password')}</Label>
                                <PasswordInput
                                    id="password"
                                    required
                                    tabIndex={3}
                                    autoComplete="new-password"
                                    name="password"
                                    placeholder={__('auth.password')}
                                    aria-invalid={!!errors.password}
                                    resetKey={errorNonce}
                                />
                                <InputError message={errors.password} />
                            </div>

                            <div className="grid gap-2">
                                <Label htmlFor="password_confirmation">{__('auth.confirm_password')}</Label>
                                <PasswordInput
                                    id="password_confirmation"
                                    required
                                    tabIndex={4}
                                    autoComplete="new-password"
                                    name="password_confirmation"
                                    placeholder={__('auth.confirm_password')}
                                    aria-invalid={!!errors.password_confirmation}
                                    resetKey={errorNonce}
                                />
                                <InputError message={errors.password_confirmation} />
                            </div>

                            <Button type="submit" className="mt-2 w-full" tabIndex={5} disabled={processing} isLoading={processing}>
                                {__('auth.create_account')}
                            </Button>
                        </div>

                        <div className="text-center text-sm text-muted-foreground">
                            {__('auth.already_have_account')}{' '}
                            <TextLink href={route('login')} tabIndex={6}>
                                {__('auth.log_in')}
                            </TextLink>
                        </div>
                    </>
                )}
            </Form>
        </AuthLayout>
    );
}
