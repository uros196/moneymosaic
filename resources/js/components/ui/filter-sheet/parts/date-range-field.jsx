import { DateInput } from '@/components/ui/date-input'
import InputError from '@/components/input-error'

export default function DateRangeField({ field, form }) {
  const label = field?.label
  const fromKey = field?.fromKey
  const toKey = field?.toKey
  const fromPlaceholder = field?.fromPlaceholder
  const toPlaceholder = field?.toPlaceholder
  const errorKeys = Array.isArray(field?.errorKeys) ? field.errorKeys : [fromKey, toKey]

  const fromValue = form?.data?.[fromKey] ?? ''
  const toValue = form?.data?.[toKey] ?? ''

  const hasError = errorKeys.some((k) => Boolean(form?.errors?.[k]))

  function handleChange(key) {
    return (e) => {
      const value = e.target.value
      if (!value) {
        form.setData(key, null)
      } else {
        form.setData(key, value)
      }
    }
  }

  return (
    <div className="flex flex-col gap-2">
      <span className="text-sm text-muted-foreground">{label}</span>
      <div className="grid grid-cols-1 sm:grid-cols-2 gap-2">
        <DateInput
          id={fromKey}
          value={fromValue}
          onChange={handleChange(fromKey)}
          placeholder={fromPlaceholder}
          aria-invalid={hasError}
        />
        <DateInput
          id={toKey}
          value={toValue}
          onChange={handleChange(toKey)}
          placeholder={toPlaceholder}
          aria-invalid={hasError}
        />
      </div>
      <InputError errors={form?.errors} fields={errorKeys} />
    </div>
  )
}
