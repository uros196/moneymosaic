import * as React from "react"
import * as DialogPrimitive from "@radix-ui/react-dialog"
import { XIcon } from "lucide-react"

import { cn } from "@/lib/utils"

function Drawer(props) {
  return <DialogPrimitive.Root data-slot="drawer" {...props} />
}

function DrawerTrigger(props) {
  return <DialogPrimitive.Trigger data-slot="drawer-trigger" {...props} />
}

function DrawerPortal(props) {
  return <DialogPrimitive.Portal data-slot="drawer-portal" {...props} />
}

function DrawerClose(props) {
  return <DialogPrimitive.Close data-slot="drawer-close" {...props} />
}

function DrawerOverlay({ className, ...props }) {
  return (
    <DialogPrimitive.Overlay
      data-slot="drawer-overlay"
      className={cn(
        "fixed inset-0 z-50 bg-black/60 backdrop-blur-sm data-[state=open]:animate-in data-[state=closed]:animate-out data-[state=closed]:fade-out-0 data-[state=open]:fade-in-0",
        className
      )}
      {...props}
    />
  )
}

function DrawerContent({ className, children, showClose = true, preventClose = false, ...props }) {
  return (
    <DrawerPortal>
      <DrawerOverlay />
      <DialogPrimitive.Content
        data-slot="drawer-content"
        // Right side slide-over panel
        className={cn(
          "fixed inset-y-0 right-0 z-50 grid h-full w-full overflow-y-auto bg-background shadow-xl outline-none",
          // Responsive width: full on mobile, a bit wider on desktop
          "sm:w-full md:w-3/5 lg:w-2/5 xl:w-2/5",
          // Slide in/out animations using translate-x
          "data-[state=open]:animate-in data-[state=closed]:animate-out",
          "data-[state=closed]:slide-out-to-right data-[state=open]:slide-in-from-right",
          // Fallback if custom keyframes aren't present
          "transition-transform duration-200 will-change-transform",
          className
        )}
        onEscapeKeyDown={(e) => { if (preventClose) { e.preventDefault() } }}
        onPointerDownOutside={(e) => { if (preventClose) { e.preventDefault() } }}
        onInteractOutside={(e) => { if (preventClose) { e.preventDefault() } }}
        {...props}
      >
        {children}
        {showClose ? (
          <DialogPrimitive.Close className="absolute right-4 top-4 rounded-xs opacity-70 transition-opacity hover:opacity-100 focus:outline-hidden focus:ring-2 focus:ring-ring focus:ring-offset-2 ring-offset-background [&_svg]:pointer-events-none [&_svg]:shrink-0 [&_svg:not([class*='size-'])]:size-4">
            <XIcon />
            <span className="sr-only">Close</span>
          </DialogPrimitive.Close>
        ) : null}
      </DialogPrimitive.Content>
    </DrawerPortal>
  )
}

function DrawerHeader({ className, ...props }) {
  return (
    <div
      data-slot="drawer-header"
      className={cn("flex flex-col gap-1 border-b px-6 py-4", className)}
      {...props}
    />
  )
}

function DrawerFooter({ className, ...props }) {
  return (
    <div
      data-slot="drawer-footer"
      className={cn("border-t px-6 py-4", className)}
      {...props}
    />
  )
}

function DrawerTitle({ className, ...props }) {
  return (
    <DialogPrimitive.Title
      data-slot="drawer-title"
      className={cn("text-base font-semibold", className)}
      {...props}
    />
  )
}

function DrawerDescription({ className, ...props }) {
  return (
    <DialogPrimitive.Description
      data-slot="drawer-description"
      className={cn("text-sm text-muted-foreground", className)}
      {...props}
    />
  )
}

export {
  Drawer,
  DrawerTrigger,
  DrawerPortal,
  DrawerOverlay,
  DrawerContent,
  DrawerHeader,
  DrawerFooter,
  DrawerTitle,
  DrawerDescription,
  DrawerClose,
}
