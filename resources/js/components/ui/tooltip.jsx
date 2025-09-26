import * as React from "react"
import * as TooltipPrimitive from "@radix-ui/react-tooltip"

import { cn } from "@/lib/utils"

function TooltipProvider(props) {
  return <TooltipPrimitive.Provider delayDuration={150} skipDelayDuration={100} {...props} />
}

function Tooltip(props) {
  return <TooltipPrimitive.Root {...props} />
}

function TooltipTrigger(props) {
  return <TooltipPrimitive.Trigger {...props} />
}

function TooltipContent({ className, sideOffset = 6, ...props }) {
  return (
    <TooltipPrimitive.Portal>
      <TooltipPrimitive.Content
        className={cn(
            "bg-primary text-primary-foreground animate-in fade-in-0 zoom-in-95 z-50 max-w-sm rounded-md px-3 py-1.5 text-xs",
          "data-[state=closed]:animate-out data-[state=closed]:fade-out-0",
          "data-[state=closed]:zoom-out-95 data-[side=bottom]:slide-in-from-top-2",
          "data-[side=left]:slide-in-from-right-2 data-[side=right]:slide-in-from-left-2",
          "data-[side=top]:slide-in-from-bottom-2",
          className
        )}
        sideOffset={sideOffset}
        {...props}
      />
    </TooltipPrimitive.Portal>
  )
}

export { TooltipProvider, Tooltip, TooltipTrigger, TooltipContent }
