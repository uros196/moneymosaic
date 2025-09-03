import { Head, Link, router, usePage } from '@inertiajs/react'
import IncomeDrawer from './income-drawer'
import AppLayout from '@/layouts/app-layout'
import HeadingSmall from '@/components/heading-small'
import { useI18n } from '@/i18n'
import { useEffect, useMemo, useState } from 'react'
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select'
import { Tooltip, TooltipTrigger, TooltipContent } from '@/components/ui/tooltip'
import ConfirmDialog from '@/components/ui/confirm-dialog'
import { Eye, Pencil, PlusIcon, Trash2 } from 'lucide-react'
import { Button } from '@/components/ui/button'
import { Toggle } from '@/components/ui/toggle'

const TYPES = ['salary', 'bonus', 'other']

function formatAmount(minor, currency) {
  try {
    return new Intl.NumberFormat(undefined, { style: 'currency', currency }).format((minor || 0) / 100)
  } catch (_) {
    // Fallback: plain number + code
    return `${((minor || 0) / 100).toFixed(2)} ${currency}`
  }
}

function toISO(dateLike) {
  const d = new Date(dateLike)
  if (Number.isNaN(d.getTime())) return ''
  const m = `${d.getMonth() + 1}`.padStart(2, '0')
  const day = `${d.getDate()}`.padStart(2, '0')
  return `${d.getFullYear()}-${m}-${day}`
}

// Simple client-side conversion via EUR as a base (UI-only; mock rates)
const RATES_EUR = {
  EUR: 1,
  USD: 1.1, // 1 EUR = 1.10 USD (example)
  RSD: 117, // 1 EUR = 117.00 RSD (example)
}

function convertMinor(amountMinor, fromCode, toCode) {
  const from = String(fromCode || 'EUR').toUpperCase()
  const to = String(toCode || 'EUR').toUpperCase()
  if (from === to) return amountMinor || 0
  const rFrom = RATES_EUR[from]
  const rTo = RATES_EUR[to]
  if (!rFrom || !rTo) {
    return amountMinor || 0
  }
  // Convert minor->main to compute, then back to minor; round to the nearest cent
  const main = (amountMinor || 0) / 100
  const inEurMain = main / rFrom
  const inTargetMain = inEurMain * rTo
  return Math.round(inTargetMain * 100)
}

export default function IncomesIndex() {
  const { __ } = useI18n()

  // Mock data for the UI (client-only)
  const [items, setItems] = useState(() => [
    { id: 1, occurred_on: '2025-08-01', name: 'Salary August', description: 'Monthly salary', income_type_key: 'salary', amount_minor: 250000, currency_code: 'EUR', tags: ['job'] },
    { id: 2, occurred_on: '2025-07-15', name: 'Quarterly Bonus', description: 'Quarterly bonus', income_type_key: 'bonus', amount_minor: 75000, currency_code: 'USD', tags: ['bonus'] },
    { id: 3, occurred_on: '2025-06-02', name: 'Freelance', description: 'Freelance gig', income_type_key: 'other', amount_minor: 42000, currency_code: 'RSD', tags: ['side'] },
  ])

  const now = new Date()
  const [filters, setFilters] = useState({
    month: `${now.getMonth() + 1}`.padStart(2, '0'),
    year: `${now.getFullYear()}`,
    type: 'all',
    currency: 'all',
  })

  // Conversion UI state (display-only)
  const pageProps = usePage().props || {}
  const userDefault = pageProps?.auth?.user?.default_currency_code || 'EUR'
  const [convertEnabled, setConvertEnabled] = useState(false)
  const [convertCurrency, setConvertCurrency] = useState(userDefault)
    const hasModal = Boolean(pageProps.modal)

  const [filtersOpen, setFiltersOpen] = useState(false)
  const years = useMemo(() => {
    const ys = new Set(items.map((i) => i.occurred_on.slice(0, 4)))
    ys.add(`${now.getFullYear()}`)
    return Array.from(ys).sort((a, b) => Number(b) - Number(a))
  }, [items])

  const filtered = useMemo(() => {
    return items.filter((i) => {
      const [y, m] = i.occurred_on.split('-')
      if (filters.month !== 'all' && m !== filters.month) return false
      if (filters.year !== 'all' && y !== filters.year) return false
      if (filters.type !== 'all' && i.income_type_key !== filters.type) return false
      if (filters.currency !== 'all' && i.currency_code !== filters.currency) return false
      return true
    })
  }, [items, filters])

  // Income types: start with built-ins, allow adding custom ones dynamically (front-only)
  const BUILTIN_TYPES = TYPES
  const [types, setTypes] = useState([...TYPES])
  const [typeLabels, setTypeLabels] = useState({}) // map custom key -> label
  function isBuiltinType(key) {
    return BUILTIN_TYPES.includes(String(key))
  }
  function getTypeLabel(key) {
    return isBuiltinType(key) ? __(`incomes.types.${key}`) : (typeLabels[key] || String(key))
  }


  // Modal state
  const [open, setOpen] = useState(false)

  // Open the drawer based on modal props (URL-driven)
  useEffect(() => {
      hasModal ? setOpen(true) : setOpen(false)
  }, [pageProps.modal])

  function openCreate() {
    router.visit(route('incomes.create'), { preserveScroll: true })
  }

  function openEdit(item) {
    router.visit(route('incomes.edit', item.id), { preserveScroll: true })
  }

  const [confirmOpen, setConfirmOpen] = useState(false)
  const [toDeleteId, setToDeleteId] = useState(null)

  function askDelete(id) {
    setToDeleteId(id)
    setConfirmOpen(true)
  }
  function confirmDelete() {
    if (toDeleteId != null) {
      // TODO: call the server for this
    }
  }

  const breadcrumbs = [
    { title: __('incomes.title'), href: route('incomes.index') },
  ]

  return (
      <AppLayout breadcrumbs={breadcrumbs}>
          <Head title={__('incomes.title')} />

          <div className="space-y-6">
              <HeadingSmall title={__('incomes.title')} description={__('incomes.description')} />

              {/* Toolbar */}
              <div className="space-y-3">
                  <div className="flex items-center justify-between">
                      <div className="flex items-center gap-3">
                          <Button type="button" variant="secondary" size="sm" onClick={() => setFiltersOpen((v) => !v)} aria-expanded={filtersOpen}>
                              {__('incomes.filters.toggle')}
                          </Button>
                          <Toggle
                              pressed={convertEnabled}
                              onPressedChange={(v) => setConvertEnabled(Boolean(v))}
                              aria-label={__('incomes.filters.convert')}
                          >
                              {__('incomes.filters.convert')}
                          </Toggle>
                          {convertEnabled && (
                              <div className="flex items-center gap-2">
                                  <span className="text-sm">{__('incomes.filters.display_currency')}</span>
                                  <Select value={convertCurrency} onValueChange={setConvertCurrency}>
                                      <SelectTrigger id="display_currency">
                                          <SelectValue />
                                      </SelectTrigger>
                                      <SelectContent>
                                          {pageProps.currencies.data.map((c) => (
                                              <SelectItem value={c.value} key={c.value}>
                                                  {c.value}
                                              </SelectItem>
                                          ))}
                                      </SelectContent>
                                  </Select>
                              </div>
                          )}
                      </div>
                      <div className="flex justify-end">
                          <Button type="button" title={__('incomes.actions.add')} onClick={openCreate}>
                              <PlusIcon className="size-4" /> {__('incomes.actions.add')}
                          </Button>
                      </div>
                  </div>

                  {filtersOpen && (
                      <div className="grid grid-cols-2 gap-3 sm:grid-cols-4">
                          {/* Month */}
                          <label className="flex flex-col gap-1">
                              <span className="text-sm">{__('incomes.filters.month')}</span>
                              <Select value={filters.month} onValueChange={(v) => setFilters((f) => ({ ...f, month: v }))}>
                                  <SelectTrigger id="filter_month">
                                      <SelectValue />
                                  </SelectTrigger>
                                  <SelectContent>
                                      <SelectItem value="all">{__('incomes.filters.all')}</SelectItem>
                                      {Array.from({ length: 12 }).map((_, idx) => {
                                          const v = `${idx + 1}`.padStart(2, '0');
                                          return (
                                              <SelectItem value={v} key={v}>
                                                  {v}
                                              </SelectItem>
                                          );
                                      })}
                                  </SelectContent>
                              </Select>
                          </label>

                          {/* Year */}
                          <label className="flex flex-col gap-1">
                              <span className="text-sm">{__('incomes.filters.year')}</span>
                              <Select value={filters.year} onValueChange={(v) => setFilters((f) => ({ ...f, year: v }))}>
                                  <SelectTrigger id="filter_year">
                                      <SelectValue />
                                  </SelectTrigger>
                                  <SelectContent>
                                      <SelectItem value="all">{__('incomes.filters.all')}</SelectItem>
                                      {years.map((y) => (
                                          <SelectItem value={String(y)} key={y}>
                                              {y}
                                          </SelectItem>
                                      ))}
                                  </SelectContent>
                              </Select>
                          </label>

                          {/* Type */}
                          <label className="flex flex-col gap-1">
                              <span className="text-sm">{__('incomes.filters.type')}</span>
                              <Select value={filters.type} onValueChange={(v) => setFilters((f) => ({ ...f, type: v }))}>
                                  <SelectTrigger id="filter_type">
                                      <SelectValue />
                                  </SelectTrigger>
                                  <SelectContent>
                                      <SelectItem value="all">{__('incomes.filters.all')}</SelectItem>
                                      {pageProps.incomeTypes.data.map((type) => (
                                          <SelectItem value={type.id} key={type.id}>
                                              {type.name}
                                          </SelectItem>
                                      ))}
                                  </SelectContent>
                              </Select>
                          </label>

                          {/* Currency */}
                          <label className="flex flex-col gap-1">
                              <span className="text-sm">{__('incomes.filters.currency')}</span>
                              <Select value={filters.currency} onValueChange={(v) => setFilters((f) => ({ ...f, currency: v }))}>
                                  <SelectTrigger id="filter_currency">
                                      <SelectValue />
                                  </SelectTrigger>
                                  <SelectContent>
                                      <SelectItem value="all">{__('incomes.filters.all')}</SelectItem>
                                      {pageProps.currencies.data.map((c) => (
                                          <SelectItem value={c.value} key={c.value}>
                                              {c.value}
                                          </SelectItem>
                                      ))}
                                  </SelectContent>
                              </Select>
                          </label>
                      </div>
                  )}
              </div>

              {/* Table */}
              <div className="overflow-x-auto rounded-lg border">
                  <table className="w-full text-sm">
                      <thead className="bg-muted">
                          <tr className="text-left">
                              <th className="px-3 py-2">{__('incomes.table.date')}</th>
                              <th className="px-3 py-2">{__('incomes.table.description')}</th>
                              <th className="px-3 py-2">{__('incomes.table.type')}</th>
                              <th className="px-3 py-2 text-right">{__('incomes.table.amount')}</th>
                              <th className="px-3 py-2">{__('incomes.table.currency')}</th>
                              <th className="px-3 py-2">{__('incomes.table.actions')}</th>
                          </tr>
                      </thead>
                      <tbody>
                          {filtered.length === 0 && (
                              <tr>
                                  <td className="px-3 py-8 text-center text-muted-foreground" colSpan={6}>
                                      {__('incomes.table.empty')}
                                  </td>
                              </tr>
                          )}
                          {filtered.map((i) => (
                              <tr key={i.id} className="border-t">
                                  <td className="px-3 py-2 whitespace-nowrap">{i.occurred_on}</td>
                                  <td className="px-3 py-2">{i.description}</td>
                                  <td className="px-3 py-2">{getTypeLabel(i.income_type_key)}</td>
                                  <td className="px-3 py-2 text-right font-medium">
                                      {convertEnabled && i.currency_code !== convertCurrency ? (
                                          <Tooltip>
                                              <TooltipTrigger asChild>
                                                  <span className="cursor-help underline decoration-dotted">
                                                      {formatAmount(convertMinor(i.amount_minor, i.currency_code, convertCurrency), convertCurrency)}
                                                  </span>
                                              </TooltipTrigger>
                                              <TooltipContent>
                                                  {__('incomes.original_value', {
                                                      value: `${formatAmount(i.amount_minor, i.currency_code)} ${i.currency_code}`,
                                                  })}
                                              </TooltipContent>
                                          </Tooltip>
                                      ) : (
                                          formatAmount(i.amount_minor, i.currency_code)
                                      )}
                                  </td>
                                  <td className="px-3 py-2">
                                      {convertEnabled && i.currency_code !== convertCurrency ? (
                                          <Tooltip>
                                              <TooltipTrigger asChild>
                                                  <span className="cursor-help underline decoration-dotted">{convertCurrency}</span>
                                              </TooltipTrigger>
                                              <TooltipContent>
                                                  {__('incomes.original_value', { value: formatAmount(i.amount_minor, i.currency_code) })}
                                              </TooltipContent>
                                          </Tooltip>
                                      ) : (
                                          i.currency_code
                                      )}
                                  </td>
                                  <td className="px-3 py-2">
                                      <div className="flex items-center gap-2">
                                          <Tooltip>
                                              <TooltipTrigger asChild>
                                                  <Link
                                                      href={route('incomes.show', i.id)}
                                                      prefetch
                                                      className="inline-flex items-center rounded-md border px-2 py-1 hover:bg-accent"
                                                  >
                                                      <Eye className="size-4" />
                                                  </Link>
                                              </TooltipTrigger>
                                              <TooltipContent>{__('incomes.actions.view')}</TooltipContent>
                                          </Tooltip>
                                          <Tooltip>
                                              <TooltipTrigger asChild>
                                                  <button
                                                      className="inline-flex items-center rounded-md border px-2 py-1 hover:bg-accent"
                                                      onClick={() => openEdit(i)}
                                                  >
                                                      <Pencil className="size-4" />
                                                  </button>
                                              </TooltipTrigger>
                                              <TooltipContent>{__('incomes.actions.edit')}</TooltipContent>
                                          </Tooltip>
                                          <Tooltip>
                                              <TooltipTrigger asChild>
                                                  <button
                                                      className="inline-flex items-center rounded-md border px-2 py-1 hover:bg-accent"
                                                      onClick={() => askDelete(i.id)}
                                                  >
                                                      <Trash2 className="size-4 text-destructive" />
                                                  </button>
                                              </TooltipTrigger>
                                              <TooltipContent>{__('incomes.actions.delete')}</TooltipContent>
                                          </Tooltip>
                                      </div>
                                  </td>
                              </tr>
                          ))}
                      </tbody>
                  </table>
              </div>
          </div>

          {/* Drawer: render only when modal needs to be opened */}
          {hasModal && <IncomeDrawer open={open} setOpen={setOpen} />}

          {/* Confirm delete */}
          <ConfirmDialog
              open={confirmOpen}
              onOpenChange={setConfirmOpen}
              title={__('incomes.confirm.delete_title')}
              description={__('incomes.confirm.delete_description')}
              confirmText={__('incomes.actions.delete')}
              cancelText={__('incomes.actions.cancel')}
              onConfirm={confirmDelete}
          />
      </AppLayout>
  );
}
