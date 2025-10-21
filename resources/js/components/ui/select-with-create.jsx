import { useEffect, useMemo, useRef, useState } from 'react'
import { Select, SelectContent, SelectItem, SelectSeparator, SelectTrigger, SelectValue } from '@/components/ui/select'
import { Dialog, DialogClose, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle } from '@/components/ui/dialog'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import { Button } from '@/components/ui/button'
import InputError from '@/components/input-error'
import { Plus } from 'lucide-react'
import { useI18n } from '@/i18n'

/**
 * Reusable select component with an inline "Add new" action that opens a modal to create an item.
 *
 * Props:
 * - items: Array<any> - list of items to display
 * - defaultValue?: string | number | undefined - initial selected value
 * - onChange?: (value: string, created?: any) => void - called when selection changes
 * - name?: string - optional name for hidden input to submit with outer form
 * - createAction: string - URL to POST when creating a new item
 * - itemValueKey?: string - key used for the value (default: 'id')
 * - itemLabelKey?: string - key used for the label (default: 'name')
 * - labels?: {
 *     addOption?: string,
 *     modalTitle?: string,
 *     modalDescription?: string,
 *     inputLabel?: string,
 *     inputPlaceholder?: string,
 *     cancel?: string,
 *     save?: string,
 *   }
 * - selectProps?: any - forwarded to SelectTrigger
 */
export default function SelectWithCreate({
  items = [],
  defaultValue,
  onChange,
  name,
  createAction,
  itemValueKey = 'id',
  itemLabelKey = 'name',
  labels = {},
  selectProps = {},
}) {
  // Local state: options list, selected value, UI flags (select/modal), and request/validation state
  const [options, setOptions] = useState(items)
  const [selected, setSelected] = useState(() => (defaultValue != null ? String(defaultValue) : ''))
  const [selectOpen, setSelectOpen] = useState(false)
  const [openCreate, setOpenCreate] = useState(false)
  const [creating, setCreating] = useState(false)
  const [errors, setErrors] = useState({})
  const inputRef = useRef(null)
    const pendingSelectRef = useRef(null)
  const { __ } = useI18n()

  // Default labels come from i18n 'common' and can be overridden via the `labels` prop
  const text = useMemo(() => ({
    addOption: __('common.add_new'),
    modalTitle: __('common.add_new_item_title'),
    modalDescription: __('common.add_new_item_desc'),
    inputLabel: __('common.name'),
    inputPlaceholder: __('common.name_placeholder'),
    cancel: __('common.cancel'),
    save: __('common.save'),
    ...labels,
  }), [labels, __])

    // Update options when the `items` prop changes
  useEffect(() => { setOptions(items); }, [items])

    // Apply queued selection once the new option exists
    useEffect(() => {
        const pending = pendingSelectRef.current
        if (!pending) return

        // Trigger onChange event or automatically set the selected value
        triggerChange(pending)

        // clear refs after applying
        pendingSelectRef.current = null
    }, [options])

  /**
   * Create a new item via JSON POST, append it to the options, and select it.
   * Handles 422 validation errors and best-effort CSRF.
   */
  async function createItem(formData) {
    setCreating(true)
    setErrors({})

    try {
      const resp = await fetch(createAction, {
        method: 'POST',
        headers: {
          'Accept': 'application/json',
          'Content-Type': 'application/json',
          'X-Requested-With': 'XMLHttpRequest',
          // Try to add CSRF token if present in meta tag or cookie
          ...(getCsrfHeader()),
        },
        body: JSON.stringify({ name: formData.get('name') }),
        credentials: 'same-origin',
      })

      if (resp.status === 422) {
        const data = await resp.json()
        setErrors(data.errors ?? {})
        inputRef.current?.focus()
        return
      }

      if (!resp.ok) {
        // Fallback: close dialog but do not change selection
        setOpenCreate(false)
        return
      }

      const data = await resp.json()
      const created = data.data ?? data // Resource or plain JSON
      if (created && created[itemValueKey] != null) {
          // Set the pending value to select after options are updated
          pendingSelectRef.current = String(created[itemValueKey])

        // Update list and selection
          setOptions(prev => [...prev, created])
      }

      setOpenCreate(false)
    } finally {
      setCreating(false)
    }
  }

  /** Handle selection; open the "create" dialog for the special value. */
  function handleSelectChange(v) {
    if (v === '__add__') {
      setSelectOpen(false)
      setOpenCreate(true)
      // focus input on the next tick
      setTimeout(() => inputRef.current?.focus(), 50)
      return
    }
      triggerChange(v)
  }

  /** Update local selection and notify parent (if provided). */
  function triggerChange(value) {
      onChange ? onChange(value) : setSelected(value)
  }

  return (
    <>
      {name && <input type="hidden" name={name} value={selected} />}

      <Select value={selected} onValueChange={handleSelectChange} open={selectOpen} onOpenChange={setSelectOpen}>
        <SelectTrigger {...selectProps}>
          <SelectValue />
        </SelectTrigger>
        <SelectContent>
          {options.map((option) => (
            <SelectItem value={String(option[itemValueKey])} key={`opt-${option[itemValueKey]}`}>
                {option[itemLabelKey]}
            </SelectItem>
          ))}
          <SelectSeparator />
          <SelectItem value="__add__">
            <span className="inline-flex items-center gap-2">
              <Plus className="size-4" /> {text.addOption}
            </span>
          </SelectItem>
        </SelectContent>
      </Select>

      <Dialog open={openCreate} onOpenChange={setOpenCreate}>
        <DialogContent>
          <DialogHeader>
            <DialogTitle>{text.modalTitle}</DialogTitle>
            <DialogDescription>{text.modalDescription}</DialogDescription>
          </DialogHeader>
          <form
            onSubmit={(e) => {
              e.preventDefault()
              e.stopPropagation()
              const fd = new FormData(e.currentTarget)
              createItem(fd)
            }}
            className="space-y-6"
          >
            <div className="grid gap-2">
              <Label htmlFor="new_item_name">{text.inputLabel}</Label>
              <Input name="name" id="new_item_name" placeholder={text.inputPlaceholder} ref={inputRef} />
              <InputError message={errors.name} />
            </div>
            <DialogFooter className="gap-2">
              <DialogClose asChild>
                <Button type="button" variant="secondary" disabled={creating}>
                  {text.cancel}
                </Button>
              </DialogClose>
              <Button type="submit" isLoading={creating} disabled={creating}>
                {text.save}
              </Button>
            </DialogFooter>
          </form>
        </DialogContent>
      </Dialog>
    </>
  )
}

/**
 * Best-effort CSRF header detection for JSON POST requests.
 * Checks <meta name="csrf-token"> then falls back to the XSRF-TOKEN cookie.
 */
function getCsrfHeader() {
  // Prefer meta tag if present
  const meta = document.querySelector('meta[name="csrf-token"]')
  if (meta?.content) {
    return { 'X-CSRF-TOKEN': meta.content }
  }
  // Try cookie 'XSRF-TOKEN' for Sanctum-like setups
  const match = document.cookie.match(/XSRF-TOKEN=([^;]+)/)
  if (match) {
    try {
      return { 'X-XSRF-TOKEN': decodeURIComponent(match[1]) }
    } catch (_) {
      return { 'X-XSRF-TOKEN': match[1] }
    }
  }
  return {}
}
