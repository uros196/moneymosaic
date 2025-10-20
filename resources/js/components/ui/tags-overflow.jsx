import { memo, useMemo } from 'react'
import { Badge } from '@/components/ui/badge'
import { Tooltip, TooltipContent, TooltipTrigger } from '@/components/ui/tooltip'
import { cn } from '@/lib/utils'

/**
 * TagsOverflow — performant tags renderer for tables and compact UIs.
 * Renders up to `maxVisible` tags inline and collapses the rest into a "+N" badge
 * with a tooltip showing hidden tags. Avoids any DOM measurements or ResizeObservers.
 *
 * Props:
 * - tags: Array<string | { id?: number|string; name: string }>
 * - className?: string
 * - badgeVariant?: 'secondary' | 'default' | 'outline' | 'destructive'
 * - maxVisible?: number (default 3)
 */
function TagsOverflow({ tags = [], className, badgeVariant = 'secondary', maxVisible = 3 }) {
  const list = useMemo(() => {
    const src = Array.isArray(tags) ? tags : []
    return src
      .map((t) => (typeof t === 'string' ? t : (t?.name ?? '')))
      .filter((t) => typeof t === 'string' && t.trim().length > 0)
  }, [tags])

  if (list.length === 0) {
    return <span className="text-xs text-muted-foreground">—</span>
  }

  const visibleCount = Math.min(list.length, Math.max(0, Number.isFinite(maxVisible) ? maxVisible : 3))
  const hidden = list.length - visibleCount

  return (
    <div className={cn('flex items-center gap-1 overflow-hidden', className)}>
      {list.slice(0, visibleCount).map((t, idx) => (
        <Badge key={`${t}-${idx}`} variant={badgeVariant} title={t}>{t}</Badge>
      ))}

      {hidden > 0 && (
        <Tooltip>
          <TooltipTrigger asChild>
            <Badge variant="outline">+{hidden}</Badge>
          </TooltipTrigger>
          <TooltipContent>
            <div className="max-w-xs">
              {list.slice(visibleCount).join(', ')}
            </div>
          </TooltipContent>
        </Tooltip>
      )}
    </div>
  )
}

export default memo(TagsOverflow)
