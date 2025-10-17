import { useMemo } from 'react'
import { TagInput } from '@/components/ui/tag-input'
import InputError from '@/components/input-error'
import { useI18n } from '@/i18n'

export default function TagsField({ field, form }) {
  const { __ } = useI18n()
  const label = field?.label
  const name = field?.key
  const placeholder = field?.placeholder
  const suggestions = field?.suggestions ?? []
  const errorKeys = Array.isArray(field?.errorKeys) ? field.errorKeys : [name]

  const tags = useMemo(() => {
    const raw = form?.data?.[name]
    if (!raw) { return [] }
    return String(raw).split(',').map((s) => s.trim()).filter(Boolean)
  }, [form?.data?.[name]])

  const hasError = errorKeys.some((k) => Boolean(form?.errors?.[k]))

  function handleChange(vals) {
    const arr = Array.isArray(vals) ? vals : []
    if (arr.length === 0) {
      form.setData(name, null)
    } else {
      form.setData(name, arr.join(','))
    }
  }

  return (
    <div className="flex flex-col gap-1">
      <span className="text-sm text-muted-foreground">{label}</span>
      <TagInput
        defaultValue={tags}
        suggestions={suggestions}
        placeholder={placeholder ?? __('common.form.tag_input_placeholder')}
        onChange={handleChange}
        className={hasError ? 'ring-1 ring-red-500/50 border-red-500' : undefined}
      />
      <InputError errors={form?.errors} fields={errorKeys} />
    </div>
  )
}
