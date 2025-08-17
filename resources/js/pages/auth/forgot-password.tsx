import { Form, Head } from '@inertiajs/react'
import { LoaderCircle } from 'lucide-react'

import InputError from '@/components/input-error'
import TextLink from '@/components/text-link'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import AuthLayout from '@/layouts/auth-layout'

interface ForgotPasswordProps {
  status?: string
}

export default function ForgotPassword({ status }: ForgotPasswordProps) {
  return (
    <AuthLayout title="Forgot password" description="Enter your email and we will send you a password reset link.">
      <Head title="Forgot password" />

      {status && (
        <div className="mb-4 text-center text-sm font-medium text-green-600">{status}</div>
      )}

      <Form method="post" action={route('password.email')} className="flex flex-col gap-6">
        {({ processing, errors }) => (
          <>
            <div className="grid gap-6">
              <div className="grid gap-2">
                <Label htmlFor="email">Email address</Label>
                <Input id="email" type="email" name="email" required autoFocus autoComplete="email" placeholder="email@example.com" aria-invalid={!!errors.email} />
                <InputError message={errors.email} />
              </div>

              <Button type="submit" className="mt-2 w-full" disabled={processing}>
                {processing && <LoaderCircle className="h-4 w-4 animate-spin" />}
                Send reset link
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
