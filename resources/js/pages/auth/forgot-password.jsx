import { Form, Head } from '@inertiajs/react'

import InputError from '@/components/input-error'
import TextLink from '@/components/text-link'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import AuthLayout from '@/layouts/auth-layout'
import { useI18n } from '@/i18n'

export default function ForgotPassword({ status }) {
  const { __ } = useI18n()
  return (
    <AuthLayout title={__('auth.forgot_title')} description={__('auth.forgot_description')}>
      <Head title={__('auth.forgot_title')} />

      {status && (
        <div className="mb-4 text-center text-sm font-medium text-green-600">{status}</div>
      )}

      <Form method="post" action={route('password.email')} className="flex flex-col gap-6">
        {({ processing, errors }) => (
          <>
            <div className="grid gap-6">
              <div className="grid gap-2">
                <Label htmlFor="email">{__('auth.email')}</Label>
                <Input id="email" type="email" name="email" required autoFocus autoComplete="email" placeholder={__('auth.email')} aria-invalid={!!errors.email} />
                <InputError message={errors.email} />
              </div>

              <Button type="submit" className="mt-2 w-full" disabled={processing} isLoading={processing}>
                {__('auth.send_reset_link')}
              </Button>
            </div>

            <div className="text-center text-sm text-muted-foreground">
              <TextLink href={route('login')}>{__('auth.back_to_login')}</TextLink>
            </div>
          </>
        )}
      </Form>
    </AuthLayout>
  )
}
