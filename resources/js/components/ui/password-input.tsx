import * as React from 'react'
import { Eye, EyeOff } from 'lucide-react'

import { cn } from '@/lib/utils'
import { Input } from '@/components/ui/input'

export interface PasswordInputProps extends React.ComponentProps<'input'> {
  /**
   * When this value changes, the input will remount to clear any entered value.
   * Pass something like JSON.stringify(errors) from Inertia forms to clear after failed validation.
   */
  resetKey?: string | number | boolean
}

const PasswordInput = React.forwardRef<HTMLInputElement, PasswordInputProps>(
  ({ className, resetKey, ...props }, ref) => {
    const [visible, setVisible] = React.useState(false)

    function toggle() {
      setVisible((v) => !v)
    }

    // Compose a key for the inner input to force remounting only when external resetKey changes (e.g., on validation errors)
    const composedKey = String(resetKey ?? '')

    return (
      <div className="relative">
        <Input
          key={composedKey}
          ref={ref}
          type={visible ? 'text' : 'password'}
          className={cn('pr-10', className)}
          {...props}
        />
        <button
          type="button"
          onClick={toggle}
          className={cn(
            'absolute inset-y-0 right-0 flex items-center px-2 text-muted-foreground hover:text-foreground transition-colors',
            'focus:outline-none focus-visible:ring-[3px] focus-visible:ring-ring/50 rounded-md'
          )}
          aria-label={visible ? 'Hide password' : 'Show password'}
        >
          {visible ? <EyeOff className="size-4" /> : <Eye className="size-4" />}
        </button>
      </div>
    )
  }
)

PasswordInput.displayName = 'PasswordInput'

export { PasswordInput }
