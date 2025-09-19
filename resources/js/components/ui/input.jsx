import * as React from "react"

import { cn } from "@/lib/utils"

/**
 * Input — styled input component used across the app.
 *
 * - Forwards the ref to the underlying <input /> element.
 * - Accepts all native input props via ...props.
 * - Applies consistent focus, invalid, and disabled styles.
 *
 * Props:
 * - className?: string — extra classes merged with defaults.
 * - type?: string — input type (defaults to browser default if omitted).
 */
const Input = React.forwardRef(function Input(
  { className, type, ...props },
  ref
) {
  return (
    <input
      ref={ref}
      type={type}
      data-slot="input"
      className={cn(
        "border-input file:text-foreground placeholder:text-muted-foreground selection:bg-primary selection:text-primary-foreground flex h-9 w-full min-w-0 rounded-md border bg-transparent px-3 py-1 text-base shadow-xs transition-[color,box-shadow] outline-none file:inline-flex file:h-7 file:border-0 file:bg-transparent file:text-sm file:font-medium disabled:pointer-events-none disabled:cursor-not-allowed disabled:opacity-50 md:text-sm",
        "focus-visible:border-ring focus-visible:ring-ring/50 focus-visible:ring-[3px]",
        "aria-invalid:ring-destructive/20 dark:aria-invalid:ring-destructive/40 aria-invalid:border-destructive",
        className
      )}
      {...props}
    />
  )
})

export { Input }
