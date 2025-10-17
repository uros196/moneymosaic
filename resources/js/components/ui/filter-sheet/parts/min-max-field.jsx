import { Input } from '@/components/ui/input'
import InputError from '@/components/input-error'
import { useI18n } from '@/i18n'

export default function MinMaxField({ field, form }) {
  const { __ } = useI18n()
  const label = field?.label
  const minKey = field?.minKey
  const maxKey = field?.maxKey
  const minPlaceholder = field?.minPlaceholder
  const maxPlaceholder = field?.maxPlaceholder
  const errorKeys = Array.isArray(field?.errorKeys) ? field.errorKeys : [minKey, maxKey]

  const minValue = form?.data?.[minKey] ?? ''
  const maxValue = form?.data?.[maxKey] ?? ''

  const hasError = errorKeys.some((k) => Boolean(form?.errors?.[k]))

  function handleChange(key) {
    return (e) => {
      const value = e.target.value
      if (value === '') {
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
        <Input
          id={minKey}
          type="number"
          inputMode="decimal"
          step="any"
          value={minValue}
          onChange={handleChange(minKey)}
          placeholder={minPlaceholder ?? __('common.min')}
          aria-invalid={hasError}
        />
        <Input
          id={maxKey}
          type="number"
          inputMode="decimal"
          step="any"
          value={maxValue}
          onChange={handleChange(maxKey)}
          placeholder={maxPlaceholder ?? __('common.max')}
          aria-invalid={hasError}
        />
      </div>
      <InputError errors={form?.errors} fields={errorKeys} />
    </div>
  )
}
