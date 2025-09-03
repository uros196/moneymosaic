import * as React from "react"
import { Input } from "@/components/ui/input"
import { Tooltip, TooltipTrigger, TooltipContent } from "@/components/ui/tooltip"
import { Calendar } from "lucide-react"
import { cn } from "@/lib/utils"
import { useI18n } from "@/i18n"

/**
 * DateInput: styled date picker with a calendar icon and tooltip.
 *
 * Props: same as Input plus className.
 */
export function DateInput({ className, ...props }) {
  const ref = React.useRef(null)
  const { __ } = useI18n()

  function openPicker() {
    const el = ref.current
    if (!el) return
    // Chrome supports showPicker, fallback to focus otherwise
    if (typeof el.showPicker === 'function') {
      try { el.showPicker() } catch (_) { el.focus() }
    } else {
      el.focus()
    }
  }

  return (
    <div className={cn("relative", className)}>
      <Input
        ref={ref}
        type="date"
        className={"pr-9"}
        {...props}
      />
      <div className="pointer-events-none absolute inset-y-0 right-2 flex items-center">
        <Tooltip>
          <TooltipTrigger asChild>
            <button
              type="button"
              className="pointer-events-auto inline-flex items-center justify-center rounded-xs p-1 text-muted-foreground hover:text-foreground"
              aria-label={__('common.open_calendar')}
              onClick={openPicker}
              tabIndex={-1}
            >
              <Calendar className="size-4" />
            </button>
          </TooltipTrigger>
          <TooltipContent>{__('common.open_calendar')}</TooltipContent>
        </Tooltip>
      </div>
    </div>
  )
}
