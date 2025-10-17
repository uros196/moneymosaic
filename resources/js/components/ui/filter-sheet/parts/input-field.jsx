import { Input } from '@/components/ui/input'
import InputError from '@/components/input-error'

export default function InputField({ field, form }) {
  const label = field?.label
  const name = field?.key
  const placeholder = field?.placeholder
  const value = form?.data?.[name] ?? ''
  const errorKeys = Array.isArray(field?.errorKeys) ? field.errorKeys : [name]
  const hasError = errorKeys.some((k) => Boolean(form?.errors?.[k]))

  function handleChange(e) {
    const value = e.target.value
    if (String(value).trim() === '') {
      form.setData(name, null)
    } else {
      form.setData(name, value)
    }
  }

  return (
    <div className="flex flex-col gap-1">
      <span className="text-sm text-muted-foreground">{label}</span>
      <Input id={name} value={value} onChange={handleChange} placeholder={placeholder ?? ''} aria-invalid={hasError} />
      <InputError errors={form?.errors} fields={errorKeys} />
    </div>
  )
}
