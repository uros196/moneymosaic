import { Form, Head, Link, usePage } from '@inertiajs/react'
import { Dialog, Transition } from '@headlessui/react'
import { useState, useEffect, useRef, Fragment } from 'react'

import HeadingSmall from '@/components/heading-small'
import InputError from '@/components/input-error'
import TextLink from '@/components/text-link'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import AppLayout from '@/layouts/app-layout'
import SettingsLayout from '@/layouts/settings/layout'
import { useI18n } from '@/i18n'
import { Copy } from 'lucide-react'
import { toast } from 'react-hot-toast'
import { Tooltip, TooltipTrigger, TooltipContent } from '@/components/ui/tooltip'

export default function Security({ otpAuthUrl, qrUrl, recoveryCodes, setupJustBegan, emailPending }) {
  const { auth } = usePage().props
  const tfType = auth.user.two_factor_type
  const enabled = Boolean(auth.user.two_factor_enabled)
  const { __ } = useI18n()

  const breadcrumbs = [
    { title: __('security.title'), href: route('settings.security') },
  ]

  const [open, setOpen] = useState(Boolean(setupJustBegan || qrUrl))
  const [emailOpen, setEmailOpen] = useState(Boolean(emailPending))

  const emailInputRef = useRef(null)
  const totpInputRef = useRef(null)

  useEffect(() => {
    setEmailOpen(Boolean(emailPending))
  }, [emailPending])

  // Automatically open the TOTP setup modal when a QR becomes available
  useEffect(() => {
    if (qrUrl) {
      setOpen(true)
    }
  }, [qrUrl])

  const typeUpper = String(tfType || '').toUpperCase()

  const handleCopyRecoveryCodes = async () => {
    const text = Array.isArray(recoveryCodes) ? recoveryCodes.join('\n') : ''
    if (!text) {
      return
    }
    try {
      if (navigator?.clipboard?.writeText) {
        await navigator.clipboard.writeText(text)
      } else {
        const ta = document.createElement('textarea')
        ta.value = text
        ta.setAttribute('readonly', '')
        ta.style.position = 'absolute'
        ta.style.left = '-9999px'
        document.body.appendChild(ta)
        ta.select()
        document.execCommand('copy')
        document.body.removeChild(ta)
      }
      toast.success(__('security.copied_recovery_codes'))
    } catch (e) {
      console.error(e)
      toast.error(__('security.copy_failed'))
    }
  }

  return (
    <AppLayout breadcrumbs={breadcrumbs}>
      <Head title={__('security.title')} />

      <SettingsLayout>
        <div className="space-y-8">
          <div className="flex items-start justify-between gap-4">
            <HeadingSmall title={__('security.two_factor')} description={__('security.two_factor_desc')} />
            <TextLink href={route('profile.edit')} className="mt-1 text-sm">{__('common.back_to_profile')}</TextLink>
          </div>

          <div className="space-y-4">
            {enabled ? (
              <div className="rounded-lg border p-4">
                <p className="mb-2 text-sm text-muted-foreground">{__('security.enabled_using', { type: typeUpper })}</p>
                <Form method="post" action={route('settings.security.disable')}>
                  {({ processing }) => (
                    <Button type="submit" variant="secondary" disabled={processing} isLoading={processing}>{__('common.disable_2fa')}</Button>
                  )}
                </Form>
              </div>
            ) : (
              <div className="grid gap-4 md:grid-cols-2">
                <div className="rounded-lg border p-4">
                  <h3 className="mb-2 font-medium">{__('security.email_code_title')}</h3>
                  <p className="mb-4 text-sm text-muted-foreground">{__('security.email_code_desc')}</p>
                  <Form method="post" action={route('settings.security.email.enable')}>
                    {({ processing }) => (
                      <Button type="submit" disabled={processing} isLoading={processing}>{__('common.enable_email_2fa')}</Button>
                    )}
                  </Form>
                </div>
                <div className="rounded-lg border p-4">
                  <h3 className="mb-2 font-medium">{__('security.totp_title')}</h3>
                  <p className="mb-4 text-sm text-muted-foreground">{__('security.totp_desc')}</p>
                  {qrUrl ? (
                    <div className="flex items-center gap-3">
                      <Button type="button" onClick={() => setOpen(true)}>{__('common.open_setup')}</Button>
                    </div>
                  ) : (
                    <Form method="post" action={route('settings.security.totp.begin')}>
                      {({ processing }) => (
                        <Button type="submit" disabled={processing} isLoading={processing}>{__('common.begin_setup')}</Button>
                      )}
                    </Form>
                  )}
                </div>
              </div>
            )}
          </div>

          {recoveryCodes && recoveryCodes.length > 0 && (
            <div className="rounded-lg border p-4">
              <div className="mb-2 flex items-center justify-between">
                <h3 className="font-medium">{__('security.recovery_codes_title')}</h3>
                <Tooltip>
                  <TooltipTrigger asChild>
                    <Button type="button" variant="ghost" size="icon" onClick={handleCopyRecoveryCodes} aria-label={__('security.copy_codes')}>
                      <Copy className="size-4" />
                    </Button>
                  </TooltipTrigger>
                  <TooltipContent>{__('security.copy_codes')}</TooltipContent>
                </Tooltip>
              </div>
              <p className="mb-3 text-sm text-muted-foreground">{__('security.recovery_codes_desc')}</p>
              <div className="grid grid-cols-2 gap-2 sm:grid-cols-3">
                {recoveryCodes.map((code, i) => (
                  <code key={i} className="rounded bg-muted px-2 py-1 font-mono text-sm">{code}</code>
                ))}
              </div>
            </div>
          )}

          <div className="text-sm text-muted-foreground">
            {__('security.tip_lost_access')}
          </div>
        </div>
      </SettingsLayout>

      <Transition appear show={emailOpen} as={Fragment}>
        <Dialog as="div" className="relative z-50" onClose={setEmailOpen} initialFocus={emailInputRef}>
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
                  <Dialog.Title className="text-lg font-medium">{__('security.email_confirm_title')}</Dialog.Title>
                  <div className="mt-3 space-y-3">
                    <p className="text-sm text-muted-foreground">{__('security.email_confirm_desc')}</p>
                    <Form method="post" action={route('settings.security.email.confirm')} className="space-y-3">
                      {({ processing, errors }) => (
                        <>
                          <div className="grid gap-2">
                            <Label htmlFor="email_code">{__('security.authentication_code')}</Label>
                            <Input id="email_code" name="code" inputMode="numeric" pattern="[0-9]*" maxLength={6} aria-invalid={!!errors.code} ref={emailInputRef} autoFocus />
                            <InputError message={errors.code} />
                          </div>
                          <div className="flex items-center gap-3">
                            <Button type="submit" disabled={processing} isLoading={processing}>{__('common.confirm_and_enable')}</Button>
                          </div>
                        </>
                      )}
                    </Form>
                    <div className="flex items-center gap-3">
                      <Link as="button" href={route('settings.security.email.resend')} method="post" className="text-sm underline">
                        {__('common.resend_code')}
                      </Link>
                      <Form method="post" action={route('settings.security.disable')}>
                        {({ processing: disabling }) => (
                          <Button type="submit" variant="secondary" disabled={disabling} isLoading={disabling}>{__('common.cancel')}</Button>
                        )}
                      </Form>
                    </div>
                  </div>
                </Dialog.Panel>
              </Transition.Child>
            </div>
          </div>
        </Dialog>
      </Transition>

      <Transition appear show={open && Boolean(qrUrl)} as={Fragment}>
        <Dialog as="div" className="relative z-50" onClose={setOpen} initialFocus={totpInputRef}>
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
                  <Dialog.Title className="text-lg font-medium">{__('security.totp_setup_title')}</Dialog.Title>
                  <div className="mt-3 space-y-3">
                    {qrUrl ? (
                      <div className="space-y-3">
                        <img src={qrUrl} alt={__('security.totp_scan_alt')} className="h-40 w-40" />
                        <p className="text-xs text-muted-foreground break-all">{otpAuthUrl}</p>
                        <Form method="post" action={route('settings.security.totp.confirm')} className="space-y-3">
                          {({ processing, errors }) => (
                            <>
                              <div className="grid gap-2">
                                <Label htmlFor="code">{__('security.enter_code_to_confirm')}</Label>
                                <Input id="code" name="code" inputMode="numeric" pattern="[0-9]*" maxLength={6} aria-invalid={!!errors.code} ref={totpInputRef} autoFocus />
                                <InputError message={errors.code} />
                              </div>
                              <div className="flex items-center gap-3">
                                <Button type="submit" disabled={processing} isLoading={processing}>{__('common.confirm_and_enable')}</Button>
                              </div>
                            </>
                          )}
                        </Form>
                        <div className="flex items-center gap-3">
                          <Link as="button" href={route('settings.security.totp.begin')} method="post" className="text-sm underline">
                            {__('common.regenerate_qr')}
                          </Link>
                          <Form method="post" action={route('settings.security.disable')}>
                            {({ processing: disabling }) => (
                              <Button type="submit" variant="secondary" disabled={disabling} isLoading={disabling}>{__('common.cancel')}</Button>
                            )}
                          </Form>
                        </div>
                      </div>
                    ) : (
                      <div className="space-y-3">
                        <p className="text-sm text-muted-foreground">{__('security.begin_setup_desc')}</p>
                        <Form method="post" action={route('settings.security.totp.begin')}>
                          {({ processing }) => (
                            <Button type="submit" disabled={processing} isLoading={processing}>{__('common.begin_setup')}</Button>
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
