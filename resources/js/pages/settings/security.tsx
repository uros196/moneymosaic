import { Form, Head, Link, usePage } from '@inertiajs/react'
import { Dialog, Transition } from '@headlessui/react'
import { useState, Fragment } from 'react'

import HeadingSmall from '@/components/heading-small'
import InputError from '@/components/input-error'
import TextLink from '@/components/text-link'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import AppLayout from '@/layouts/app-layout'
import SettingsLayout from '@/layouts/settings/layout'
import { type BreadcrumbItem, type SharedData } from '@/types'

const breadcrumbs: BreadcrumbItem[] = [
  { title: 'Security settings', href: '/settings/security' },
]

interface Props {
  otpAuthUrl?: string | null
  qrUrl?: string | null
  recoveryCodes?: string[] | null
  setupJustBegan?: boolean
}

export default function Security({ otpAuthUrl, qrUrl, recoveryCodes, setupJustBegan }: Props) {
  const { auth } = usePage<SharedData>().props
  const tfType = auth.user.two_factor_type as 'email' | 'totp' | null
  const enabled = Boolean(auth.user.two_factor_enabled)

  const [open, setOpen] = useState(Boolean(setupJustBegan))

  return (
    <AppLayout breadcrumbs={breadcrumbs}>
      <Head title="Security settings" />

      <SettingsLayout>
        <div className="space-y-8">
          <div className="flex items-start justify-between gap-4">
            <HeadingSmall title="Two-factor authentication" description="Add an extra layer of security to your account." />
            <TextLink href={route('profile.edit')} className="mt-1 text-sm">Back to profile</TextLink>
          </div>

          <div className="space-y-4">
            {enabled ? (
              <div className="rounded-lg border p-4">
                <p className="mb-2 text-sm text-muted-foreground">2FA is enabled using <span className="font-medium">{tfType?.toUpperCase()}</span>.</p>
                <Form method="post" action={route('settings.security.disable')}>
                  {({ processing }) => (
                    <Button type="submit" variant="secondary" disabled={processing}>Disable 2FA</Button>
                  )}
                </Form>
              </div>
            ) : (
              <div className="grid gap-4 md:grid-cols-2">
                <div className="rounded-lg border p-4">
                  <h3 className="mb-2 font-medium">Email code</h3>
                  <p className="mb-4 text-sm text-muted-foreground">Receive a 6-digit code to your email address when signing in.</p>
                  <Form method="post" action={route('settings.security.email.enable')}>
                    {({ processing }) => (
                      <Button type="submit" disabled={processing}>Enable Email 2FA</Button>
                    )}
                  </Form>
                </div>
                <div className="rounded-lg border p-4">
                  <h3 className="mb-2 font-medium">Authenticator app (TOTP)</h3>
                  <p className="mb-4 text-sm text-muted-foreground">Use Google Authenticator or a compatible app to generate 6-digit codes.</p>
                  {qrUrl ? (
                    <div className="flex items-center gap-3">
                      <Button type="button" onClick={() => setOpen(true)}>Open setup</Button>
                    </div>
                  ) : (
                    <Form method="post" action={route('settings.security.totp.begin')}>
                      {({ processing }) => (
                        <Button type="submit" disabled={processing}>Begin setup</Button>
                      )}
                    </Form>
                  )}
                </div>
              </div>
            )}
          </div>

          {recoveryCodes && recoveryCodes.length > 0 && (
            <div className="rounded-lg border p-4">
              <h3 className="mb-2 font-medium">Save these recovery codes</h3>
              <p className="mb-3 text-sm text-muted-foreground">Store these one-time codes in a safe place. Each code can be used once if you lose access to your authenticator app.</p>
              <div className="grid grid-cols-2 gap-2 sm:grid-cols-3">
                {recoveryCodes.map((code, i) => (
                  <code key={i} className="rounded bg-muted px-2 py-1 font-mono text-sm">{code}</code>
                ))}
              </div>
            </div>
          )}

          <div className="text-sm text-muted-foreground">
            Tip: If you enable 2FA and lose access, you will need to contact support or use email 2FA to regain access.
          </div>
        </div>
      </SettingsLayout>

      <Transition appear show={open && Boolean(qrUrl)} as={Fragment}>
        <Dialog as="div" className="relative z-50" onClose={setOpen}>
          <Transition.Child
            as={Fragment}
            enter="ease-out duration-200"
            enterFrom="opacity-0"
            enterTo="opacity-100"
            leave="ease-in duration-150"
            leaveFrom="opacity-100"
            leaveTo="opacity-0"
          >
            <div className="fixed inset-0 bg-black/40" />
          </Transition.Child>

          <div className="fixed inset-0 overflow-y-auto">
            <div className="flex min-h-full items-center justify-center p-4">
              <Transition.Child
                as={Fragment}
                enter="ease-out duration-200"
                enterFrom="opacity-0 scale-95"
                enterTo="opacity-100 scale-100"
                leave="ease-in duration-150"
                leaveFrom="opacity-100 scale-100"
                leaveTo="opacity-0 scale-95"
              >
                <Dialog.Panel className="w-full max-w-md transform rounded-xl bg-background p-6 text-left align-middle shadow-lg">
                  <Dialog.Title className="text-lg font-medium">Set up your authenticator app</Dialog.Title>
                  <div className="mt-3 space-y-3">
                    {qrUrl ? (
                      <div className="space-y-3">
                        <img src={qrUrl} alt="Scan this QR with your authenticator app" className="h-40 w-40" />
                        <p className="text-xs text-muted-foreground break-all">{otpAuthUrl}</p>
                        <Form method="post" action={route('settings.security.totp.confirm')} className="space-y-3">
                          {({ processing, errors }) => (
                            <>
                              <div className="grid gap-2">
                                <Label htmlFor="code">Enter code to confirm</Label>
                                <Input id="code" name="code" inputMode="numeric" pattern="[0-9]*" maxLength={6} aria-invalid={!!errors.code} />
                                <InputError message={errors.code} />
                              </div>
                              <div className="flex items-center gap-3">
                                <Button type="submit" disabled={processing}>Confirm and enable</Button>
                                <Link as="button" href={route('settings.security.totp.begin')} method="post" className="text-sm underline">
                                  Regenerate QR
                                </Link>
                                <Button type="button" variant="secondary" onClick={() => setOpen(false)}>Close</Button>
                              </div>
                            </>
                          )}
                        </Form>
                      </div>
                    ) : (
                      <div className="space-y-3">
                        <p className="text-sm text-muted-foreground">Click the button below to begin setup and generate your QR code.</p>
                        <Form method="post" action={route('settings.security.totp.begin')}>
                          {({ processing }) => (
                            <Button type="submit" disabled={processing}>Begin setup</Button>
                          )}
                        </Form>
                      </div>
                    )}
                  </div>
                </Dialog.Panel>
              </Transition.Child>
            </div>
          </div>
        </Dialog>
      </Transition>
    </AppLayout>
  )
}
