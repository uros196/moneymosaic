import * as React from "react"
import * as TogglePrimitive from "@radix-ui/react-toggle"

import { cn } from "@/lib/utils"

function Toggle({ className, ...props }) {
  return (
    <TogglePrimitive.Root
      data-slot="toggle"
      className={cn(
        "border-input data-[state=on]:bg-primary data-[state=on]:text-primary-foreground data-[state=on]:border-primary inline-flex h-8 items-center justify-center rounded-md border bg-transparent px-3 text-sm shadow-xs transition-[color,box-shadow] outline-none",
        "focus-visible:border-ring focus-visible:ring-ring/50 focus-visible:ring-[3px]",
        "aria-invalid:ring-destructive/20 dark:aria-invalid:ring-destructive/40 aria-invalid:border-destructive",
        "disabled:cursor-not-allowed disabled:opacity-50",
        className
      )}
      {...props}
    />
  )
}

export { Toggle }
