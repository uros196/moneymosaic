import { Form, Head, Link } from '@inertiajs/react'
import AppLayout from '@/layouts/app-layout'
import SettingsLayout from '@/layouts/settings/layout'
import HeadingSmall from '@/components/heading-small'
import { Button } from '@/components/ui/button'
import { type BreadcrumbItem } from '@/types'
import { Laptop, Smartphone, Tablet, type LucideIcon } from 'lucide-react'

interface SessionItem {
  id: string
  ip_address: string | null
  user_agent: string | null
  last_activity: number
  is_current: boolean
}

function getDeviceInfo(uaRaw: string | null): { Icon: LucideIcon; label: string } {
  const ua = (uaRaw ?? '').toLowerCase()
  const isAndroid = /android/.test(ua)
  const isTablet = /ipad|tablet/.test(ua) || (isAndroid && !/mobile/.test(ua))
  const isPhone = /iphone|ipod/.test(ua) || (isAndroid && /mobile/.test(ua))

  if (isPhone) {
    return { Icon: Smartphone, label: 'Phone' }
  }
  if (isTablet) {
    return { Icon: Tablet, label: 'Tablet' }
  }
  return { Icon: Laptop, label: 'Computer' }
}

export default function Sessions({ sessions }: { sessions: SessionItem[] }) {
  const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Active sessions', href: '/settings/sessions' },
  ]

  const formatDateTime = (epochSeconds: number): string => {
    try {
      const d = new Date(epochSeconds * 1000)
      return d.toLocaleString()
    } catch {
      return String(epochSeconds)
    }
  }

  return (
    <AppLayout breadcrumbs={breadcrumbs}>
      <Head title="Active sessions" />

      <SettingsLayout>
        <div className="space-y-6">
          <div className="flex items-start justify-between gap-4">
            <HeadingSmall title="Active sessions" description="Review devices currently signed in to your account." />
            <Link href={route('profile.edit')} className="mt-1 text-sm underline">Back to profile</Link>
          </div>

          <div className="rounded-lg border">
            <div className="divide-y">
              {sessions.length === 0 ? (
                <div className="p-4 text-sm text-muted-foreground">No active sessions.</div>
              ) : (
                sessions.map((s) => {
                  const d = getDeviceInfo(s.user_agent)
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
                            <span className="font-medium">{s.ip_address ?? 'Unknown IP'}</span>
                            {s.is_current && (
                              <span className="rounded-full bg-emerald-100 px-2 py-0.5 text-xs text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-300">Current</span>
                            )}
                          </div>
                          <div className="mt-1 truncate text-xs text-muted-foreground">
                            {s.user_agent ?? `${d.label} device`}
                          </div>
                          <div className="mt-1 text-xs text-muted-foreground">Last active: {formatDateTime(s.last_activity)}</div>
                        </div>
                      </div>
                      <div className="shrink-0">
                        <Form method="delete" action={route('settings.sessions.destroy', s.id)}>
                          {({ processing }) => (
                            <Button type="submit" variant="secondary" disabled={processing || s.is_current}>Log out</Button>
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
                <Button type="submit" variant="secondary" disabled={processing}>Log out other sessions</Button>
              )}
            </Form>
            <Form method="post" action={route('settings.sessions.all')}>
              {({ processing }) => (
                <Button type="submit" variant="destructive" disabled={processing}>Log out all sessions</Button>
              )}
            </Form>
          </div>
        </div>
      </SettingsLayout>
    </AppLayout>
  )
}
