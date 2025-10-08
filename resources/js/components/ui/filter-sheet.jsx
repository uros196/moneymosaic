import { useEffect, useMemo, useRef, useState } from 'react'
import { Sheet, SheetContent, SheetHeader, SheetTitle, SheetFooter } from '@/components/ui/sheet'
import { Button } from '@/components/ui/button'
import { Tooltip, TooltipContent, TooltipTrigger } from '@/components/ui/tooltip'
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select'
import { Badge } from '@/components/ui/badge'
import { ListFilter } from 'lucide-react'
import { usePage } from '@inertiajs/react'
import { cn } from '@/lib/utils'
import { useI18n } from '@/i18n'
import { getQueryObject, visitCurrentPath, removeParams, mergeQuery } from '@/lib/url-query'
import { Input } from '@/components/ui/input'
import { DateInput } from '@/components/ui/date-input'
import { TagInput } from '@/components/ui/tag-input'

/**
 * FilterSheet
 *
 * Reusable, mobile-friendly filter UI that opens in a Sheet (drawer) with configurable fields.
 * - Initializes from current URL query string.
 * - Calls onApply with only selected values.
 * - Provides Clear All.
 * - Renders icon-only trigger with tooltip.
 *
 * Props:
 * - id: string — unique id for remembering state (optional)
 * - title: string — sheet title
 * - tooltip: string — tooltip shown on trigger
 * - fields: Array<
 *     | { key: string, label: string, type?: 'select', options?: Array<{ value: string|number, label: string }>, allLabel?: string }
 *     | { key: string, label: string, type: 'search-select', options: Array<{ value: string|number, label: string }>, allLabel?: string, searchPlaceholder?: string }
 *     | { key: string, label: string, type: 'input', placeholder?: string }
 *     | { key: string, label: string, type: 'date-range', fromKey?: string, toKey?: string, fromPlaceholder?: string, toPlaceholder?: string }
 *     | { key: string, label: string, type: 'min-max', minKey?: string, maxKey?: string, minPlaceholder?: string, maxPlaceholder?: string }
 *   >
 * - onApply: (selected: Record<string, string|number>) => void
 * - onClearAll?: () => void
 * - className?: string
 * - triggerClassName?: string
 */
export default function FilterSheet({
  id = 'filters',
  title,
  tooltip,
  fields = [],
  onApply,
  onClearAll,
  className,
  triggerClassName,
  onlyKeys,
  preserveScroll = true,
  preserveState = true,
}) {
  const { url } = usePage()
  const { __ } = useI18n()
  const [open, setOpen] = useState(false)

  // Use the provided title/tooltip or fallback to translations
  const effTitle = title ?? __('common.filters')
  const effTooltip = tooltip ?? __('common.filters')

  // Extract current URL query params and calculate available filter fields
  const query = useMemo(() => getQueryObject(), [url])
  const availableFields = useMemo(() => {
    const keys = []
    for (const f of fields ?? []) {
      if (f?.type === 'date-range') {
        const fromKey = f.fromKey ?? `${f.key}_from`
        const toKey = f.toKey ?? `${f.key}_to`
        keys.push(fromKey, toKey)
      } else if (f?.type === 'min-max') {
        const minKey = f.minKey ?? `${f.key}_min`
        const maxKey = f.maxKey ?? `${f.key}_max`
        keys.push(minKey, maxKey)
      } else {
        keys.push(f.key)
      }
    }
    return keys
  }, [fields])


  // Internal state mirrors selected values (strings)
  const [selected, setSelected] = useState(() => initialSelected(availableFields, query))
  const [searchText, setSearchText] = useState({})
  const searchInputRefs = useRef({})

  // Keep selected in sync with URL changes
  useEffect(() => {
    setSelected(initialSelected(availableFields, query))
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [query])

  /**
   * Applies the selected filters by:
   * 1. Building a compact object of non-empty values
   * 2. Converting arrays to comma-separated strings
   * 3. Calling onApply callback or updating URL
   */
  function apply() {
    // Build a compact object, including arrays (e.g., tags) serialized as comma-separated strings
    const compact = {}
    for (const [key, value] of Object.entries(selected)) {
      if (Array.isArray(value)) {
        const list = value.map((v) => String(v).trim()).filter(Boolean)
        if (list.length > 0) {
          compact[key] = list.join(',')
        }
        continue
      }
      if (value != null && value !== '' && value !== '-' && value !== 'all') {
        compact[key] = value
      }
    }

    if (typeof onApply === 'function') {
      onApply(compact)
    } else {
      // Always reset filter-only query params, then apply the filled filter values
      const cleared = removeParams(getQueryObject(), availableFields)
      visitWithQuery(mergeQuery(cleared, compact))
    }
    setOpen(false)
  }

  function clearAll() {
    setSelected({})
    if (typeof onClearAll === 'function') {
      onClearAll()
    } else {
      defaultClearAll()
    }
    setOpen(false)
  }

  function changeValue(key, val) {
    setSelected((prev) => ({ ...prev, [key]: val }))
  }

  function visitWithQuery(next) {
    visitCurrentPath(next, buildRouterOptions())
  }

  function defaultClearAll() {
    visitCurrentPath(
        removeParams(getQueryObject(), availableFields),
        buildRouterOptions()
    )
  }

  function buildRouterOptions() {
      const options = { preserveScroll, preserveState }
      if (Array.isArray(onlyKeys) && onlyKeys.length > 0) {
          options.only = onlyKeys
      }

      return options;
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
        <SheetContent side="right">
          <SheetHeader>
            <SheetTitle>{effTitle}</SheetTitle>
          </SheetHeader>

          <div className="flex flex-col gap-4 p-4">
            {/* Render different filter types based on field configuration */}
            {fields.map((field) => {
              const type = field.type || 'select' // Default to select type

              // Date range filter with from/to inputs
              if (type === 'date-range') {
                const fromKey = field.fromKey ?? `${field.key}_from`
                const toKey = field.toKey ?? `${field.key}_to`
                return (
                  <div className="flex flex-col gap-2" key={field.key}>
                    <span className="text-sm text-muted-foreground">{field.label}</span>
                    <div className="grid grid-cols-1 sm:grid-cols-2 gap-2">
                      <DateInput
                        id={`${id}_${fromKey}`}
                        value={selected[fromKey] ?? ''}
                        onChange={(e) => changeValue(fromKey, e.target.value)}
                        placeholder={field.fromPlaceholder ?? __('common.from')}
                      />
                      <DateInput
                        id={`${id}_${toKey}`}
                        value={selected[toKey] ?? ''}
                        onChange={(e) => changeValue(toKey, e.target.value)}
                        placeholder={field.toPlaceholder ?? __('common.to')}
                      />
                    </div>
                  </div>
                )
              }

              if (type === 'min-max') {
                const minKey = field.minKey ?? `${field.key}_min`
                const maxKey = field.maxKey ?? `${field.key}_max`
                return (
                  <div className="flex flex-col gap-2" key={field.key}>
                    <span className="text-sm text-muted-foreground">{field.label}</span>
                    <div className="grid grid-cols-1 sm:grid-cols-2 gap-2">
                      <Input
                        id={`${id}_${minKey}`}
                        type="number"
                        inputMode="decimal"
                        step="any"
                        value={selected[minKey] ?? ''}
                        onChange={(e) => changeValue(minKey, e.target.value)}
                        placeholder={field.minPlaceholder ?? __('common.min')}
                      />
                      <Input
                        id={`${id}_${maxKey}`}
                        type="number"
                        inputMode="decimal"
                        step="any"
                        value={selected[maxKey] ?? ''}
                        onChange={(e) => changeValue(maxKey, e.target.value)}
                        placeholder={field.maxPlaceholder ?? __('common.max')}
                      />
                    </div>
                  </div>
                )
              }

              if (type === 'input') {
                return (
                  <div className="flex flex-col gap-1" key={field.key}>
                    <span className="text-sm text-muted-foreground">{field.label}</span>
                    <Input
                      id={`${id}_${field.key}`}
                      value={selected[field.key] ?? ''}
                      onChange={(e) => changeValue(field.key, e.target.value)}
                      placeholder={field.placeholder ?? ''}
                    />
                  </div>
                )
              }

              if (type === 'tags') {
                const raw = selected[field.key]
                const initial = Array.isArray(raw)
                  ? raw
                  : (typeof raw === 'string' ? raw.split(',').map((s) => s.trim()).filter(Boolean) : [])
                return (
                  <div className="flex flex-col gap-1" key={field.key}>
                    <span className="text-sm text-muted-foreground">{field.label}</span>
                    <TagInput
                      defaultValue={initial}
                      suggestions={field.suggestions ?? []}
                      placeholder={field.placeholder ?? __('common.form.tag_input_placeholder')}
                      onChange={(vals) => changeValue(field.key, Array.isArray(vals) ? vals : [])}
                    />
                  </div>
                )
              }

              if (type === 'search-select') {
                const q = (searchText[field.key] ?? '').toLowerCase()
                const options = (field.options ?? []).filter((opt) => {
                  if (!q) return true
                  const label = String(opt.label ?? opt.value ?? '').toLowerCase()
                  return label.includes(q)
                })
                return (
                  <div className="flex flex-col gap-1" key={field.key}>
                    <span className="text-sm text-muted-foreground">{field.label}</span>
                    <Select value={selected[field.key] ?? 'all'} onValueChange={(v) => changeValue(field.key, v)}>
                      <SelectTrigger id={`${id}_${field.key}`}>
                        <SelectValue />
                      </SelectTrigger>
                      <SelectContent>
                        <div className="p-2 border-b">
                          <Input
                            ref={(el) => {
                              if (el) { searchInputRefs.current[field.key] = el }
                            }}
                            autoFocus
                            placeholder={field.searchPlaceholder ?? __('common.search')}
                            value={searchText[field.key] ?? ''}
                            onChange={(e) => {
                              const val = e.target.value
                              setSearchText((prev) => ({ ...prev, [field.key]: val }))
                              // Keep focus in the search input even when options update
                              requestAnimationFrame(() => {
                                const el = searchInputRefs.current?.[field.key]
                                if (el && document.activeElement !== el) {
                                  el.focus({ preventScroll: true })
                                  try {
                                    const len = el.value.length
                                    el.setSelectionRange?.(len, len)
                                  } catch { /* ignore */ }
                                }
                              })
                            }}
                            onKeyDown={(e) => {
                              // Prevent the parent Select from capturing typing/navigation keys
                              e.stopPropagation()
                            }}
                          />
                        </div>
                        <SelectItem value="all">{field.allLabel ?? __('common.all')}</SelectItem>
                        {options.map((opt) => (
                          <SelectItem key={String(opt.value)} value={String(opt.value)}>
                            {opt.label ?? opt.value}
                          </SelectItem>
                        ))}
                      </SelectContent>
                    </Select>
                  </div>
                )
              }

              // default: simple select
              return (
                <div className="flex flex-col gap-1" key={field.key}>
                  <span className="text-sm text-muted-foreground">{field.label}</span>
                  <Select value={selected[field.key] ?? 'all'} onValueChange={(v) => changeValue(field.key, v)}>
                    <SelectTrigger id={`${id}_${field.key}`}>
                      <SelectValue />
                    </SelectTrigger>
                    <SelectContent>
                      <SelectItem value="all">{field.allLabel ?? __('common.all')}</SelectItem>
                      {field.options?.map((opt) => (
                        <SelectItem key={String(opt.value)} value={String(opt.value)}>
                          {opt.label ?? opt.value}
                        </SelectItem>
                      ))}
                    </SelectContent>
                  </Select>
                </div>
              )
            })}
          </div>

          <SheetFooter>
            <div className="flex items-center justify-between gap-2">
              <Button type="button" variant="ghost" onClick={clearAll}>
                {__('common.clear_all')}
              </Button>
              <div className="flex items-center gap-2">
                <Button type="button" variant="secondary" onClick={() => setOpen(false)}>
                  {__('common.cancel')}
                </Button>
                <Button type="button" onClick={apply}>
                  {__('common.apply')}
                </Button>
              </div>
            </div>
          </SheetFooter>
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
      const next = removeParams(getQueryObject(), allKeys)
      visitWithQuery(next)
    }
  }

  function removeMany(keys) {
    visitCurrentPath(
        removeParams(getQueryObject(), keys),
        buildRouterOptions()
    )
  }

    function buildRouterOptions() {
        const options = { preserveScroll, preserveState }
        if (Array.isArray(onlyKeys) && onlyKeys.length > 0) {
            options.only = onlyKeys
        }

        return options;
    }

  if (chips.length === 0) {
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

// Attach subcomponent for ergonomic API
FilterSheet.Chips = FilterChips

function initialSelected(fields, query) {
  const output = {}
  for (const field of fields) {
    const value = query[field]
    if (value != null && value !== '') {
      output[field] = String(value)
    }
  }
  return output
}

