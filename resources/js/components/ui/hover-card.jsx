import * as React from "react"
import { Tooltip as BaseTooltip, TooltipTrigger as BaseTrigger, TooltipContent as BaseContent } from "@/components/ui/tooltip"
import { cn } from "@/lib/utils"

// Lightweight HoverCard shim implemented using our existing Tooltip primitives.
// This avoids adding a new dependency (@radix-ui/react-hover-card) while keeping
// the same API surface used across the app (HoverCard, HoverCardTrigger, HoverCardContent).

function HoverCard(props) {
  // Accept the same props; Tooltip Root supports side/align via Content props.
  return <BaseTooltip {...props} />
}

function HoverCardTrigger(props) {
  return <BaseTrigger {...props} />
}

function HoverCardContent({ className, align = "center", sideOffset = 8, ...props }) {
  return (
    <BaseContent
      align={align}
      sideOffset={sideOffset}
      className={cn(
        "z-50 w-80 rounded-md border bg-popover p-3 text-popover-foreground shadow-md",
        "data-[state=closed]:animate-out data-[state=open]:animate-in",
        "data-[state=closed]:fade-out-0 data-[state=open]:fade-in-0",
        "data-[state=closed]:zoom-out-95 data-[state=open]:zoom-in-95",
        "data-[side=bottom]:slide-in-from-top-2 data-[side=left]:slide-in-from-right-2",
        "data-[side=right]:slide-in-from-left-2 data-[side=top]:slide-in-from-bottom-2",
        className
      )}
      {...props}
    />
  )
}

export { HoverCard, HoverCardTrigger, HoverCardContent }
