import { useMemo, useRef, useState, useEffect } from 'react'
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select'
import { Input } from '@/components/ui/input'
import InputError from '@/components/input-error'
import { useI18n } from '@/i18n'

export default function SearchSelectField({ field, form }) {
  const { __ } = useI18n()
  const label = field?.label
  const name = field?.key
  const options = field?.options ?? []
  const allLabel = field?.allLabel
  const searchPlaceholder = field?.searchPlaceholder
  const errorKeys = Array.isArray(field?.errorKeys) ? field.errorKeys : [name]

  const value = form?.data?.[name] != null ? String(form.data[name]) : 'all'
  const [searchText, setSearchText] = useState('')
  const searchRef = useRef(null)
  const hasError = errorKeys.some((k) => Boolean(form?.errors?.[k]))

  const filtered = useMemo(() => {
    const q = searchText.toLowerCase()
    if (!q) return options
    return options.filter((opt) => String(opt.label ?? opt.value ?? '').toLowerCase().includes(q))
  }, [options, searchText])

  useEffect(() => {
    // keep focus inside search input when typing
    if (!searchRef.current) { return }
    const el = searchRef.current
    if (document.activeElement !== el) {
      el.focus({ preventScroll: true })
      try { const len = el.value.length; el.setSelectionRange?.(len, len) } catch {}
    }
  }, [searchText])

  function handleChange(v) {
    if (!v || v === 'all') {
      form.setData(name, null)
    } else {
      form.setData(name, v)
    }
  }

  return (
    <div className="flex flex-col gap-1">
      <span className="text-sm text-muted-foreground">{label}</span>
      <Select value={value} onValueChange={handleChange}>
        <SelectTrigger id={name} aria-invalid={hasError}>
          <SelectValue />
        </SelectTrigger>
        <SelectContent>
          <div className="p-2 border-b">
            <Input
              ref={searchRef}
              autoFocus
              placeholder={searchPlaceholder ?? __('common.search')}
              value={searchText}
              onChange={(e) => setSearchText(e.target.value)}
              onKeyDown={(e) => e.stopPropagation()}
            />
          </div>
          <SelectItem value="all">{allLabel ?? __('common.all')}</SelectItem>
          {filtered.map((opt) => (
            <SelectItem key={String(opt.value)} value={String(opt.value)}>
              {opt.label ?? opt.value}
            </SelectItem>
          ))}
        </SelectContent>
      </Select>
      <InputError errors={form?.errors} fields={errorKeys} />
    </div>
  )
}
