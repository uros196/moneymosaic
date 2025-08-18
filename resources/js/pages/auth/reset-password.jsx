import { Form, Head } from '@inertiajs/react'

import InputError from '@/components/input-error'
import TextLink from '@/components/text-link'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { PasswordInput } from '@/components/ui/password-input'
import { Label } from '@/components/ui/label'
import AuthLayout from '@/layouts/auth-layout'

export default function ResetPassword({ email, token }) {
  return (
    <AuthLayout title="Reset password" description="Enter your new password below to reset your account password.">
      <Head title="Reset password" />

      <Form method="post" action={route('password.store')} onSubmitComplete={(form) => form.reset('password', 'password_confirmation')} className="flex flex-col gap-6">
        {({ processing, errors }) => (
          <>
            <input type="hidden" name="token" value={token} />

            <div className="grid gap-6">
              <div className="grid gap-2">
                <Label htmlFor="email">Email address</Label>
                <Input id="email" type="email" name="email" defaultValue={email} required autoComplete="email" aria-invalid={!!errors.email} />
                <InputError message={errors.email} />
              </div>

              <div className="grid gap-2">
                <Label htmlFor="password">New password</Label>
                <PasswordInput id="password" name="password" required autoComplete="new-password" aria-invalid={!!errors.password} resetKey={JSON.stringify({p: errors.password, pc: errors.password_confirmation})} />
                <InputError message={errors.password} />
              </div>

              <div className="grid gap-2">
                <Label htmlFor="password_confirmation">Confirm new password</Label>
                <PasswordInput id="password_confirmation" name="password_confirmation" required autoComplete="new-password" aria-invalid={!!errors.password_confirmation} resetKey={JSON.stringify({p: errors.password, pc: errors.password_confirmation})} />
                <InputError message={errors.password_confirmation} />
              </div>

              <Button type="submit" className="mt-2 w-full" disabled={processing} isLoading={processing}>
                Reset password
              </Button>
            </div>

            <div className="text-center text-sm text-muted-foreground">
              <TextLink href={route('login')}>Back to login</TextLink>
            </div>
          </>
        )}
      </Form>
    </AuthLayout>
  )
}
