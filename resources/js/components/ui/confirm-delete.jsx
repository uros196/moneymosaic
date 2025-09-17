import { useState, useRef } from 'react'
import { useI18n } from '@/i18n'
import { Dialog, DialogContent, DialogHeader, DialogTitle, DialogDescription, DialogFooter } from '@/components/ui/dialog'
import { Button } from '@/components/ui/button'
import { PasswordInput } from '@/components/ui/password-input'
import InputError from '@/components/input-error'
import { router } from '@inertiajs/react'

export default function ConfirmDelete({
  open,
  onOpenChange,
  title,
  description,
  confirmText,
  cancelText,
  requirePassword = false,
  verifyPasswordUrl,
  onConfirm,
}) {
  const { __ } = useI18n()
  const [password, setPassword] = useState('')
  const [errors, setErrors] = useState({})
  const [processing, setProcessing] = useState(false)
  const inputRef = useRef(null)

  const defaultTitle = __('common.confirm_delete')
  const defaultDesc = requirePassword ? __('common.verify_password_prompt') : ''
  const defaultConfirm = __('common.delete')
  const defaultCancel = __('common.cancel')

  function reset() {
    setPassword('')
    setErrors({})
  }

  async function handleConfirm() {
    if (processing) return
    setProcessing(true)

    const runMain = async () => {
      try {
        await Promise.resolve(onConfirm?.())
      } finally {
        setProcessing(false)
        onOpenChange?.(false)
        reset()
      }
    }

    if (!requirePassword) {
      await runMain()
      return
    }

    setErrors({})

    router.post(
      verifyPasswordUrl || route('auth.password.verify'),
      { password },
      {
        preserveScroll: true,
        onError: (err) => {
          setProcessing(false)
          setErrors(err || {})
          inputRef.current?.focus()
        },
        onSuccess: async () => {
          await runMain()
        },
      }
    )
  }

  return (
    <Dialog open={open} onOpenChange={(v) => { if (!v) reset(); onOpenChange?.(v) }}>
      <DialogContent>
        <DialogHeader>
          <DialogTitle>{title || defaultTitle}</DialogTitle>
          {(description || defaultDesc) ? (
            <DialogDescription>{description || defaultDesc}</DialogDescription>
          ) : null}
        </DialogHeader>

        {requirePassword && (
          <div className="grid gap-2 py-2">
            <label htmlFor="password" className="sr-only">{__('auth.password')}</label>
            <PasswordInput
              id="password"
              value={password}
              onChange={(e) => setPassword(e.target.value)}
              ref={inputRef}
              placeholder={__('auth.password')}
              autoComplete="current-password"
            />
            <InputError message={errors?.password} />
          </div>
        )}

        <DialogFooter>
          <div className="flex items-center justify-end gap-2">
            <Button type="button" variant="secondary" onClick={() => onOpenChange?.(false)} disabled={processing}>
              {cancelText || defaultCancel}
            </Button>
            <Button type="button" variant="destructive" onClick={handleConfirm} disabled={processing} isLoading={processing}>
              {confirmText || defaultConfirm}
            </Button>
          </div>
        </DialogFooter>
      </DialogContent>
    </Dialog>
  )
}
