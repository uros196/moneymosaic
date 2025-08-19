import { Form, Head, Link } from '@inertiajs/react'
import AppLayout from '@/layouts/app-layout'
import SettingsLayout from '@/layouts/settings/layout'
import HeadingSmall from '@/components/heading-small'
import { Button } from '@/components/ui/button'
import { Laptop, Smartphone, Tablet } from 'lucide-react'
import { useI18n } from '@/i18n'

function getDeviceInfo(uaRaw, __) {
  const ua = (uaRaw ?? '').toLowerCase()
  const isAndroid = /android/.test(ua)
  const isTablet = /ipad|tablet/.test(ua) || (isAndroid && !/mobile/.test(ua))
  const isPhone = /iphone|ipod/.test(ua) || (isAndroid && /mobile/.test(ua))

  if (isPhone) {
    return { Icon: Smartphone, label: __('sessions.phone') }
  }
  if (isTablet) {
    return { Icon: Tablet, label: __('sessions.tablet') }
  }
  return { Icon: Laptop, label: __('sessions.computer') }
}

export default function Sessions({ sessions }) {
  const { __ } = useI18n()
  const breadcrumbs = [
    { title: __('sessions.title'), href: route('settings.sessions') },
  ]

  const formatDateTime = (epochSeconds) => {
    try {
      const d = new Date(epochSeconds * 1000)
      return d.toLocaleString()
    } catch {
      return String(epochSeconds)
    }
  }

  return (
    <AppLayout breadcrumbs={breadcrumbs}>
      <Head title={__('sessions.title')} />

      <SettingsLayout>
        <div className="space-y-6">
          <div className="flex items-start justify-between gap-4">
            <HeadingSmall title={__('sessions.title')} description={__('sessions.description')} />
            <Link href={route('profile.edit')} className="mt-1 text-sm underline">{__('common.back_to_profile')}</Link>
          </div>

          <div className="rounded-lg border">
            <div className="divide-y">
              {(!sessions || sessions.length === 0) ? (
                <div className="p-4 text-sm text-muted-foreground">{__('sessions.no_active')}</div>
              ) : (
                sessions.map((s) => {
                  const d = getDeviceInfo(s.user_agent, __)
                  const Icon = d.Icon

                  return (
                    <div key={s.id} className="flex flex-col gap-2 p-4 sm:flex-row sm:items-center sm:justify-between">
                      <div className="min-w-0 flex items-start gap-3">
                        <div className="flex h-10 w-10 items-center justify-center rounded-md border bg-muted text-muted-foreground">
                          <Icon className="size-5" aria-hidden="true" />
                          <span className="sr-only">{d.label}</span>
                        </div>
                        <div className="min-w-0">
                          <div className="flex items-center gap-2">
                            <span className="font-medium">{s.ip_address ?? __('sessions.unknown_ip')}</span>
                            {s.is_current && (
                              <span className="rounded-full bg-emerald-100 px-2 py-0.5 text-xs text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-300">{__('sessions.current')}</span>
                            )}
                          </div>
                          <div className="mt-1 truncate text-xs text-muted-foreground">
                            {s.user_agent ?? __('sessions.device_suffix', { label: d.label })}
                          </div>
                          <div className="mt-1 text-xs text-muted-foreground">{__('sessions.last_active', { time: formatDateTime(s.last_activity) })}</div>
                        </div>
                      </div>
                      <div className="shrink-0">
                        <Form method="delete" action={route('settings.sessions.destroy', s.id)}>
                          {({ processing }) => (
                            <Button type="submit" variant="secondary" disabled={processing || s.is_current} isLoading={processing}>{__('common.log_out')}</Button>
                          )}
                        </Form>
                      </div>
                    </div>
                  )
                })
              )}
            </div>
          </div>

          <div className="flex flex-wrap items-center gap-3">
            <Form method="post" action={route('settings.sessions.others')}>
              {({ processing }) => (
                <Button type="submit" variant="secondary" disabled={processing} isLoading={processing}>{__('common.log_out_other_sessions')}</Button>
              )}
            </Form>
            <Form method="post" action={route('settings.sessions.all')}>
              {({ processing }) => (
                <Button type="submit" variant="destructive" disabled={processing} isLoading={processing}>{__('common.log_out_all_sessions')}</Button>
              )}
            </Form>
          </div>
        </div>
      </SettingsLayout>
    </AppLayout>
  )
}
