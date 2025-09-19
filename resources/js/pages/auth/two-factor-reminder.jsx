import { Form, Head, Link, router } from '@inertiajs/react'
import AuthLayout from '@/layouts/auth-layout'
import { Button } from '@/components/ui/button'
import { useI18n } from '@/i18n'

export default function TwoFactorReminder({ snoozeDays }) {
  const { __ } = useI18n()

  return (
    <AuthLayout
      title={__('auth.twofactor_title')}
      description={__('auth.twofactor_desc')}
    >
      <Head title={__('auth.twofactor_head')} />

      <div className="flex flex-col gap-6">
        <div className="rounded-lg border p-4">
          <p className="text-sm text-muted-foreground">
            {__('auth.twofactor_title')}: {__('auth.twofactor_desc')}
          </p>
        </div>

        <div className="grid gap-3 sm:grid-cols-2">
          <Button type="button" onClick={() => router.visit(route('settings.security'))}>
            {__('common.manage')}
          </Button>

          <Form method="post" action={route('twofactor.reminder.skip')}>
            {({ processing }) => (
              <Button type="submit" variant="secondary" disabled={processing} isLoading={processing} className="w-full">
                {__('auth.skip_for_now')}
              </Button>
            )}
          </Form>
        </div>

        <Form method="post" action={route('twofactor.reminder.snooze')}>
          {({ processing }) => (
            <div className="flex items-center justify-between gap-4 rounded-lg border p-4">
              <div className="text-sm text-muted-foreground">
                {__('auth.remind_again_in_days', { days: snoozeDays ?? 30 })}
              </div>
              <Button type="submit" variant="ghost" disabled={processing} isLoading={processing}>
                {__('auth.remind_me_later')}
              </Button>
            </div>
          )}
        </Form>

      </div>
    </AuthLayout>
  )
}
