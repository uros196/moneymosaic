import { Head, Link, usePage } from '@inertiajs/react'
import AppLayout from '@/layouts/app-layout'
import HeadingSmall from '@/components/heading-small'
import { useI18n } from '@/i18n'

function formatAmount(minor, currency) {
  try {
    return new Intl.NumberFormat(undefined, { style: 'currency', currency }).format((minor || 0) / 100)
  } catch (_) {
    return `${((minor || 0) / 100).toFixed(2)} ${currency}`
  }
}

export default function IncomeShow() {
  const { __ } = useI18n()
  const { props } = usePage()
  const id = props.id ?? null

  // Since backend isn't wired yet, present a placeholder item for the UI
  const item = {
    id,
    occurred_on: '2025-08-01',
    description: 'Monthly salary',
    income_type_key: 'salary',
    amount_minor: 250000,
    currency_code: 'EUR',
  }

  const breadcrumbs = [
    { title: __('incomes.title'), href: route('incomes.index') },
    { title: __('incomes.details') ?? 'Details', href: route('incomes.show', id) },
  ]

  return (
    <AppLayout breadcrumbs={breadcrumbs}>
      <Head title={`${__('incomes.title')} · #${id}`} />

      <div className="space-y-6">
        <HeadingSmall title={__('incomes.details_title') ?? __('incomes.title')} description={__('incomes.description')} />

        <div className="rounded-lg border">
          <div className="grid gap-0 divide-y">
            <div className="flex items-center justify-between px-4 py-3">
              <span className="text-muted-foreground text-sm">{__('incomes.table.date')}</span>
              <span className="text-sm font-medium">{item.occurred_on}</span>
            </div>
            <div className="flex items-center justify-between px-4 py-3">
              <span className="text-muted-foreground text-sm">{__('incomes.table.type')}</span>
              <span className="text-sm font-medium">{__(`incomes.types.${item.income_type_key}`)}</span>
            </div>
            <div className="flex items-center justify-between px-4 py-3">
              <span className="text-muted-foreground text-sm">{__('incomes.table.amount')}</span>
              <span className="text-sm font-medium">{formatAmount(item.amount_minor, item.currency_code)}</span>
            </div>
            <div className="flex items-center justify-between px-4 py-3">
              <span className="text-muted-foreground text-sm">{__('incomes.table.currency')}</span>
              <span className="text-sm font-medium">{item.currency_code}</span>
            </div>
            <div className="px-4 py-3">
              <div className="text-muted-foreground text-sm mb-1">{__('incomes.table.description')}</div>
              <div className="text-sm whitespace-pre-line">{item.description || '—'}</div>
            </div>
          </div>
        </div>

        <div className="flex items-center gap-3">
          <Link href={route('incomes.index')} className="inline-flex items-center rounded-md border px-3 py-2 hover:bg-accent">
            {__('common.back') ?? 'Back'}
          </Link>
          <Link href={route('incomes.index')} className="inline-flex items-center rounded-md bg-primary text-primary-foreground px-3 py-2 hover:bg-primary/90">
            {__('incomes.actions.edit')}
          </Link>
        </div>
      </div>
    </AppLayout>
  )
}
