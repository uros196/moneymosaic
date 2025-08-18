import { Form, Head } from '@inertiajs/react'

import InputError from '@/components/input-error'
import TextLink from '@/components/text-link'
import { Button } from '@/components/ui/button'
import { PasswordInput } from '@/components/ui/password-input'
import { Label } from '@/components/ui/label'
import AuthLayout from '@/layouts/auth-layout'

export default function ConfirmPassword({ logout }) {
  return (
    <AuthLayout
      title="Confirm your password"
      description="For security, please confirm your password to continue."
    >
      <Head title="Confirm password" />

      <Form method="post" action={route('password.confirm')} onSubmitComplete={(form) => form.reset('password')} className="flex flex-col gap-6">
        {({ processing, errors }) => (
          <>
            <div className="grid gap-6">
              <div className="grid gap-2">
                <Label htmlFor="password">Password</Label>
                <PasswordInput id="password" name="password" required autoFocus autoComplete="current-password" placeholder="Password" aria-invalid={!!errors.password} resetKey={JSON.stringify(errors.password ?? '')} />
                <InputError message={errors.password} />
              </div>

              <Button type="submit" className="mt-2 w-full" disabled={processing} isLoading={processing}>
                Confirm
              </Button>
            </div>

            <div className="text-center text-sm text-muted-foreground">
              <TextLink href={route('password.request')}>Forgot your password?</TextLink>
            </div>

            <div className="text-center text-sm">
              <TextLink href={logout} method="post">Log out</TextLink>
            </div>
          </>
        )}
      </Form>
    </AuthLayout>
  )
}
