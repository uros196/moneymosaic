import { Form, Head, Link } from '@inertiajs/react'
import { useEffect, useMemo, useRef, useState } from 'react'

import InputError from '@/components/input-error'
import TextLink from '@/components/text-link'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import AuthLayout from '@/layouts/auth-layout'

export default function TwoFactorChallenge({ method, status }) {
  const [values, setValues] = useState(Array(6).fill(''))
  const [showRecovery, setShowRecovery] = useState(false)
  const [recovery, setRecovery] = useState('')
  const inputsRef = useRef([])

  const code = useMemo(() => values.join(''), [values])

  useEffect(() => {
    if (!showRecovery) {
      inputsRef.current[0]?.focus()
    }
  }, [showRecovery])

  function handleChange(index, val) {
    const cleaned = val.replace(/\D+/g, '')
    if (cleaned.length === 0) {
      setValues((prev) => {
        const next = [...prev]
        next[index] = ''
        return next
      })
      return
    }

    // If user pasted multiple digits, spread across fields
    if (cleaned.length > 1) {
      const digits = cleaned.slice(0, 6).split('')
      setValues((prev) => {
        const next = [...prev]
        for (let i = 0; i < digits.length; i++) {
          if (index + i < 6) next[index + i] = digits[i]
        }
        return next
      })
      const nextIndex = Math.min(index + cleaned.length, 5)
      requestAnimationFrame(() => inputsRef.current[nextIndex]?.focus())
    } else {
      setValues((prev) => {
        const next = [...prev]
        next[index] = cleaned
        return next
      })
      if (index < 5) {
        inputsRef.current[index + 1]?.focus()
      }
    }
  }

  function handleKeyDown(index, e) {
    if (e.key === 'Backspace') {
      if (values[index] === '' && index > 0) {
        inputsRef.current[index - 1]?.focus()
        setValues((prev) => {
          const next = [...prev]
          next[index - 1] = ''
          return next
        })
      } else {
        setValues((prev) => {
          const next = [...prev]
          next[index] = ''
          return next
        })
      }
    }
    if (e.key === 'ArrowLeft' && index > 0) inputsRef.current[index - 1]?.focus()
    if (e.key === 'ArrowRight' && index < 5) inputsRef.current[index + 1]?.focus()
  }

  function handlePaste(e, startIndex) {
    e.preventDefault()
    const text = e.clipboardData.getData('text') || ''
    const digits = text.replace(/\D+/g, '').slice(0, 6)
    if (!digits) return

    setValues((prev) => {
      const next = [...prev]
      for (let i = 0; i < digits.length && startIndex + i < 6; i++) {
        next[startIndex + i] = digits[i]
      }
      return next
    })

    const focusIndex = Math.min(startIndex + Math.max(digits.length - 1, 0), 5)
    requestAnimationFrame(() => inputsRef.current[focusIndex]?.focus())
  }

  return (
    <AuthLayout title="Two-factor authentication" description={method === 'email' ? 'Enter the 6-digit code sent to your email.' : 'Enter the 6-digit code from your authenticator app.'}>
      <Head title="Two-factor challenge" />

      {status && (
        <div className="mb-4 text-center text-sm font-medium text-green-600">{status}</div>
      )}

      <Form method="post" action={route('twofactor.store')} className="flex flex-col gap-6">
        {({ processing, errors }) => (
          <>
            {showRecovery ? (
              <>
                <div className="grid gap-2">
                  <Label htmlFor="recovery_code">Recovery code</Label>
                  <Input
                    id="recovery_code"
                    name="recovery_code"
                    value={recovery}
                    onChange={(e) => setRecovery(e.target.value)}
                    placeholder="XXXX-XXXX or paste your recovery code"
                    aria-invalid={!!errors.recovery_code}
                  />
                  <InputError message={errors.recovery_code} />
                  <button type="button" className="text-sm underline" onClick={() => setShowRecovery(false)}>
                    Use authenticator code instead
                  </button>
                </div>
              </>
            ) : (
              <>
                <input type="hidden" name="code" value={code} />
                <div className="grid gap-2">
                  <Label>Authentication code</Label>
                  <div className="flex justify-between gap-2">
                    {values.map((v, idx) => (
                      <Input
                        key={idx}
                        inputMode="numeric"
                        pattern="[0-9]*"
                        maxLength={1}
                        value={v}
                        onChange={(e) => handleChange(idx, e.target.value)}
                        onKeyDown={(e) => handleKeyDown(idx, e)}
                        onPaste={(e) => handlePaste(e, idx)}
                        ref={(el) => (inputsRef.current[idx] = el)}
                        className="h-14 w-10 text-center text-xl"
                        aria-label={`Digit ${idx + 1}`}
                        aria-invalid={!!errors.code}
                      />
                    ))}
                  </div>
                  <InputError message={errors.code} />
                  {method === 'totp' && (
                    <button type="button" className="text-sm underline" onClick={() => setShowRecovery(true)}>
                      Use recovery code
                    </button>
                  )}
                </div>
              </>
            )}

            <div className="flex items-center gap-3">
              <Button type="submit" disabled={processing} className="min-w-28" isLoading={processing}>
                Verify
              </Button>
              {method === 'email' && (
                <Link as="button" href={route('twofactor.resend')} method="post" className="text-sm text-foreground underline">
                  Resend code
                </Link>
              )}
              <TextLink href={route('logout')} method="post" className="ml-auto text-sm">
                Log out
              </TextLink>
            </div>
          </>
        )}
      </Form>
    </AuthLayout>
  )
}
