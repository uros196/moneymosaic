import { Form, Head } from '@inertiajs/react'

import InputError from '@/components/input-error'
import TextLink from '@/components/text-link'
import { Button } from '@/components/ui/button'
import { PasswordInput } from '@/components/ui/password-input'
import { Label } from '@/components/ui/label'
import AuthLayout from '@/layouts/auth-layout'
import { useI18n } from '@/i18n'

export default function ConfirmPassword() {
  const { __ } = useI18n()
  return (
    <AuthLayout
      title={__('auth.confirm_title')}
      description={__('auth.confirm_description')}
    >
      <Head title={__('auth.confirm_head')} />

      <Form method="post" action={route('password.confirm')} onSubmitComplete={(form) => form.reset('password')} className="flex flex-col gap-6">
        {({ processing, errors }) => (
          <>
            <div className="grid gap-6">
              <div className="grid gap-2">
                <Label htmlFor="password">{__('auth.password')}</Label>
                <PasswordInput id="password" name="password" required autoFocus autoComplete="current-password" placeholder={__('auth.password')} aria-invalid={!!errors.password} resetKey={JSON.stringify(errors.password ?? '')} />
                <InputError message={errors.password} />
              </div>

              <Button type="submit" className="mt-2 w-full" disabled={processing} isLoading={processing}>
                {__('auth.confirm_action')}
              </Button>
            </div>

            <div className="text-center text-sm text-muted-foreground">
              <TextLink href={route('password.request')}>{__('auth.forgot_your_password')}</TextLink>
            </div>

            <div className="text-center text-sm">
              <TextLink href={route('logout')} method="post">{__('common.log_out')}</TextLink>
            </div>
          </>
        )}
      </Form>
    </AuthLayout>
  )
}
