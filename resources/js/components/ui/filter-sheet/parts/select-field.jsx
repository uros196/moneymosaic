import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select'
import InputError from '@/components/input-error'
import { useI18n } from '@/i18n'

export default function SelectField({ field, form }) {
  const { __ } = useI18n()
  const label = field?.label
  const name = field?.key
  const options = field?.options ?? []
  const allLabel = field?.allLabel
  const errorKeys = Array.isArray(field?.errorKeys) ? field.errorKeys : [name]

  const value = form?.data?.[name] != null ? String(form.data[name]) : 'all'
  const hasError = errorKeys.some((k) => Boolean(form?.errors?.[k]))

  function handleChange(value) {
    if (!value || value === 'all') {
      form.setData(name, null)
    } else {
      form.setData(name, value)
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
          <SelectItem value="all">{allLabel ?? __('common.all')}</SelectItem>
          {options.map((opt) => (
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
