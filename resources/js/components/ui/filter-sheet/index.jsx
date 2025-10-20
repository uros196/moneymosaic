import { useMemo, useState, useEffect } from 'react'
import { Sheet, SheetContent, SheetHeader, SheetTitle, SheetFooter } from '@/components/ui/sheet'
import { Button } from '@/components/ui/button'
import { Tooltip, TooltipContent, TooltipTrigger } from '@/components/ui/tooltip'
import { Badge } from '@/components/ui/badge'
import { ListFilter } from 'lucide-react'
import { useForm, usePage } from '@inertiajs/react'
import { cn } from '@/lib/utils'
import { useI18n } from '@/i18n'
import { getQueryObject, removeParams, visitCurrentPath } from '@/lib/url-query'

import DateRangeField from './parts/date-range-field.jsx'
import MinMaxField from './parts/min-max-field.jsx'
import InputField from './parts/input-field.jsx'
import TagsField from './parts/tags-field.jsx'
import SelectField from './parts/select-field.jsx'
import SearchSelectField from './parts/search-select-field.jsx'

export default function FilterSheet({
  title,
  tooltip,
  filters,
  className,
  triggerClassName,
  onlyKeys,
  preserveScroll = true,
  preserveState = true,
}) {
  const { url } = usePage()
  const { __ } = useI18n()
  const [open, setOpen] = useState(false)

  const effTitle = title ?? __('common.filters')
  const effTooltip = tooltip ?? __('common.filters')

  const query = useMemo(() => getQueryObject(), [url])
  const availableFields = useMemo(() => Array.isArray(filters?.meta?.keys) ? filters.meta.keys : [], [filters])

  // Build initial values for inputs from current query
  const initial = useMemo(() => {
      return Object.fromEntries(Object.entries(query).filter(([key]) => availableFields.includes(key)));
  }, [query])

    // Build data from the query that are not part of the filters
    const restQueryData = useMemo(() => {
        return Object.fromEntries(Object.entries(query).filter(([key]) => !availableFields.includes(key)));
    }, [query])

  // Compute action URL (current path without query string)
  const action = useMemo(() => url.split('?')[0] || url, [url])

  // Helper to build Inertia options
  const inertiaOptions = useMemo(() => {
    const opts = { preserveScroll, preserveState }
    if (Array.isArray(onlyKeys) && onlyKeys.length > 0) { opts.only = onlyKeys }
    return opts
  }, [preserveScroll, preserveState, onlyKeys])

  // useForm to filter data based on the current query
  const form = useForm(initial)

    // Set initial values for form fields, track changes
    useEffect(() => form.setData(initial), [initial])

  // Prune null values from form.data whenever it changes
  useEffect(() => {
    const entries = Object.entries(form.data || {})
    const pruned = Object.fromEntries(entries.filter(([, value]) => value !== null))
    if (entries.length !== Object.keys(pruned).length) {
      form.setData(() => pruned)
    }
  }, [form.data])

    // Transform form data to include current query values
    form.transform((data) => ({...restQueryData, ...data}))

    // Handle form submission
  function handleApply(e) {
    e?.preventDefault?.()
    form.get(action, {
        ...inertiaOptions,
        onSuccess: () => setOpen(false),
        onError: () => setOpen(true)
    })
  }

  // Clear all filters
    function clearAll() {
      // Close the sheet before leaving the page
      setOpen(false)
      visitCurrentPath(restQueryData)
    }

  return (
    <div className={cn('flex flex-col gap-2', className)}>
      <Tooltip>
        <TooltipTrigger asChild>
          <Button
            type="button"
            variant="secondary"
            size="icon"
            className={cn('size-8', triggerClassName)}
            aria-label={effTooltip}
            title={effTooltip}
            onClick={() => setOpen(true)}
          >
            <ListFilter className="size-4" />
          </Button>
        </TooltipTrigger>
        <TooltipContent>{effTooltip}</TooltipContent>
      </Tooltip>

      <Sheet open={open} onOpenChange={setOpen}>
        <SheetContent side="right" className="p-0">
          <div className="flex h-full flex-col">
            <SheetHeader className="px-4 py-4">
              <SheetTitle>{effTitle}</SheetTitle>
            </SheetHeader>
            <div className="flex-1 overflow-y-auto px-4 pb-24">
              <div className="flex flex-col gap-4">
                {(filters?.fields ?? []).map((field) => {
                  const type = field.type || 'select'

                  if (type === 'date-range') {
                    return (
                      <DateRangeField key={field.key} field={field} form={form} />
                    )
                  }

                  if (type === 'min-max') {
                    return (
                      <MinMaxField key={field.key} field={field} form={form} />
                    )
                  }

                  if (type === 'input') {
                    return (
                      <InputField key={field.key} field={field} form={form} />
                    )
                  }

                  if (type === 'tags') {
                    return (
                      <TagsField key={field.key} field={field} form={form} />
                    )
                  }

                  if (type === 'search-select') {
                    return (
                      <SearchSelectField key={field.key} field={field} form={form} />
                    )
                  }

                  // default select
                  return (
                    <SelectField key={field.key} field={field} form={form} />
                  )
                })}
              </div>
            </div>

            <SheetFooter className="sticky bottom-0 bg-background px-4 py-4 border-t">
              <div className="flex items-center justify-between gap-2">
                <Button
                  type="button"
                  variant="ghost"
                  onClick={() => { clearAll() }}
                >
                  {__('common.clear_all')}
                </Button>
                <div className="flex items-center gap-2">
                  <Button type="button" variant="secondary" onClick={() => setOpen(false)}>
                    {__('common.cancel')}
                  </Button>
                  <Button type="button" onClick={handleApply} disabled={form.processing} isLoading={form.processing}>
                    {__('common.apply')}
                  </Button>
                </div>
              </div>
            </SheetFooter>
          </div>
        </SheetContent>
      </Sheet>
    </div>
  )
}

export function FilterChips({
  chips,
  className,
  onClearAll,
  onlyKeys,
  preserveScroll = true,
  preserveState = true,
}) {
  const { __ } = useI18n()
  const { url } = usePage()

  function buildRouterOptions() {
    const options = { preserveScroll, preserveState }
    if (Array.isArray(onlyKeys) && onlyKeys.length > 0) {
      options.only = onlyKeys
    }
    return options
  }

  const query = useMemo(() => getQueryObject(), [url])

  function visitWithQuery(next) {
    visitCurrentPath(next, buildRouterOptions())
  }

  function clearAll() {
    if (typeof onClearAll === 'function') {
      onClearAll()
      return
    }
    const allKeys = Array.from(new Set((Array.isArray(chips) ? chips : []).flatMap((c) => c.removeKeys || [])))
    if (allKeys.length > 0) {
      const next = removeParams({ ...query }, allKeys)
      visitWithQuery(next)
    }
  }

  function removeMany(keys) {
    const next = removeParams({ ...query }, keys)
    visitWithQuery(next)
  }

  if (!chips || chips.length === 0) {
    return null
  }

  return (
    <div className={cn('flex flex-wrap items-center gap-2', className)}>
      {chips.map((chip, index) => (
        <Badge key={`chip-${index}`} variant="secondary" className="gap-2 py-1">
          <span className="text-muted-foreground">{chip.label}:</span>
          <span className="font-medium">{chip.valueLabel}</span>
          <button
            type="button"
            className="ml-1 text-muted-foreground hover:text-foreground"
            aria-label={__('common.remove_filter')}
            onClick={() => removeMany(chip.removeKeys)}
          >
            ×
          </button>
        </Badge>
      ))}
      <Button type="button" variant="ghost" size="sm" onClick={clearAll}>
        {__('common.clear_all')}
      </Button>
    </div>
  )
}

// ergonomic API
FilterSheet.Chips = FilterChips
