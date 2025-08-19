import InputError from '@/components/input-error';
import TextLink from '@/components/text-link';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { PasswordInput } from '@/components/ui/password-input';
import { Label } from '@/components/ui/label';
import AuthLayout from '@/layouts/auth-layout';
import { Form, Head } from '@inertiajs/react';
import { useState } from 'react';
import { useI18n } from '@/i18n';

export default function Login({ status, canResetPassword }) {
    const [errorNonce, setErrorNonce] = useState(0);
    const { __ } = useI18n();
    return (
        <AuthLayout title={__('auth.login_title')} description={__('auth.login_description')}>
            <Head title={__('auth.login_head')} />

            <Form method="post" action={route('login')} onSubmitComplete={(form) => form.reset('password')} onError={() => setErrorNonce(n => n + 1)} className="flex flex-col gap-6">
                {({ processing, errors }) => (
                    <>
                        <div className="grid gap-6">
                            <div className="grid gap-2">
                                <Label htmlFor="email">{__('auth.email')}</Label>
                                <Input
                                    id="email"
                                    type="email"
                                    name="email"
                                    required
                                    autoFocus
                                    tabIndex={1}
                                    autoComplete="email"
                                    placeholder={__('auth.email')}
                                    aria-invalid={!!errors.email}
                                />
                                <InputError message={errors.email} />
                            </div>

                            <div className="grid gap-2">
                                <div className="flex items-center">
                                    <Label htmlFor="password">{__('auth.password')}</Label>
                                    {canResetPassword && (
                                        <TextLink href={route('password.request')} className="ml-auto text-sm" tabIndex={5}>
                                            {__('auth.forgot_password')}
                                        </TextLink>
                                    )}
                                </div>
                                <PasswordInput
                                    id="password"
                                    name="password"
                                    required
                                    tabIndex={2}
                                    autoComplete="current-password"
                                    placeholder={__('auth.password')}
                                    aria-invalid={!!errors.password}
                                    resetKey={errorNonce}
                                />
                                <InputError message={errors.password} />
                            </div>

                            <div className="flex items-center space-x-3">
                                <Checkbox id="remember" name="remember" tabIndex={3} />
                                <Label htmlFor="remember">{__('auth.remember_me')}</Label>
                            </div>

                            <Button type="submit" className="mt-4 w-full" tabIndex={4} disabled={processing} isLoading={processing}>
                                {__('auth.log_in')}
                            </Button>
                        </div>

                        <div className="text-center text-sm text-muted-foreground">
                            {__('auth.dont_have_account')}{' '}
                            <TextLink href={route('register')} tabIndex={5}>
                                {__('auth.sign_up')}
                            </TextLink>
                        </div>
                    </>
                )}
            </Form>

            {status && <div className="mb-4 text-center text-sm font-medium text-green-600">{status}</div>}
        </AuthLayout>
    );
}
