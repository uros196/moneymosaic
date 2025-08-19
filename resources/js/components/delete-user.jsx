import HeadingSmall from '@/components/heading-small';
import InputError from '@/components/input-error';
import { Button } from '@/components/ui/button';
import { Dialog, DialogClose, DialogContent, DialogDescription, DialogFooter, DialogTitle, DialogTrigger } from '@/components/ui/dialog';
import { PasswordInput } from '@/components/ui/password-input';
import { Label } from '@/components/ui/label';
import { Form } from '@inertiajs/react';
import { useRef } from 'react';
import { useI18n } from '@/i18n';

export default function DeleteUser() {
    const passwordInput = useRef(null);
    const { __ } = useI18n();

    return (
        <div className="space-y-6">
            <HeadingSmall title={__('profile.delete_account')} description={__('profile.delete_account_desc')} />
            <div className="space-y-4 rounded-lg border border-red-100 bg-red-50 p-4 dark:border-red-200/10 dark:bg-red-700/10">
                <div className="relative space-y-0.5 text-red-600 dark:text-red-100">
                    <p className="font-medium">{__('profile.delete_warning')}</p>
                    <p className="text-sm">{__('profile.delete_warning_desc')}</p>
                </div>

                <Dialog>
                    <DialogTrigger asChild>
                        <Button variant="destructive">{__('profile.delete_account')}</Button>
                    </DialogTrigger>
                    <DialogContent>
                        <DialogTitle>{__('profile.delete_confirm_title')}</DialogTitle>
                        <DialogDescription>
                            {__('profile.delete_confirm_desc')}
                        </DialogDescription>

                        <Form
                            method="delete"
                            action={route('profile.destroy')}
                            options={{
                                preserveScroll: true,
                            }}
                            onError={() => passwordInput.current?.focus()}
                            onSubmitComplete={(form) => form.reset()}
                            className="space-y-6"
                        >
                            {({ resetAndClearErrors, processing, errors }) => (
                                <>
                                    <div className="grid gap-2">
                                        <Label htmlFor="password" className="sr-only">
                                            {__('auth.password')}
                                        </Label>

                                        <PasswordInput
                                            id="password"
                                            name="password"
                                            ref={passwordInput}
                                            placeholder={__('auth.password')}
                                            autoComplete="current-password"
                                            resetKey={JSON.stringify(errors.password ?? '')}
                                        />

                                        <InputError message={errors.password} />
                                    </div>

                                    <DialogFooter className="gap-2">
                                        <DialogClose asChild>
                                            <Button variant="secondary" onClick={() => resetAndClearErrors()}>
                                                {__('common.cancel')}
                                            </Button>
                                        </DialogClose>

                                        <Button variant="destructive" disabled={processing} isLoading={processing} type="submit">
                                            {__('profile.delete_account')}
                                        </Button>
                                    </DialogFooter>
                                </>
                            )}
                        </Form>
                    </DialogContent>
                </Dialog>
            </div>
        </div>
    );
}
