import { useEffect, useLayoutEffect, useMemo, useRef, useState } from 'react'
import { Badge } from '@/components/ui/badge'
import { Tooltip, TooltipContent, TooltipTrigger } from '@/components/ui/tooltip'
import { cn } from '@/lib/utils'

/**
 * TagsOverflow — renders a single-line list of tags that truncates to fit the available width.
 * When not all tags fit, shows a "+N" badge with a tooltip listing the hidden tags.
 *
 * Props:
 * - tags: Array<string | { id?: number|string; name: string }>
 * - className?: string
 * - badgeVariant?: 'secondary' | 'default' | 'outline' | 'destructive'
 */
export default function TagsOverflow({ tags = [], className, badgeVariant = 'secondary' }) {
  const containerRef = useRef(null)
  const measureRef = useRef(null)
  const [visibleCount, setVisibleCount] = useState(0)

  const list = useMemo(() => {
    const src = Array.isArray(tags) ? tags : []
    return src
      .map((t) => (typeof t === 'string' ? t : (t?.name ?? '')))
      .filter((t) => typeof t === 'string' && t.trim().length > 0)
  }, [tags])

  // Early return for empty state
  if (!list || list.length === 0) {
    return <span className="text-xs text-muted-foreground">—</span>
  }

  // Recalculate on resize and when tags change
  useEffect(() => {
    const ro = new ResizeObserver(() => {
      calculate()
    })
    if (containerRef.current) {
      ro.observe(containerRef.current)
    }
    // also recalculate once mounted
    calculate()
    return () => ro.disconnect()
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [list.join('|')])

  function calculate() {
    const container = containerRef.current
    const measurer = measureRef.current
    if (!container || !measurer) return

    const containerWidth = container.clientWidth
    if (containerWidth <= 0) {
      setVisibleCount(0)
      return
    }

    // Prepare measurement badges
    measurer.innerHTML = ''
    const tagEls = list.map((text) => {
      const el = document.createElement('span')
      el.className = badgeClass()
      el.textContent = text
      el.dataset.measureTag = '1'
      measurer.appendChild(el)
      return el
    })

    const gap = 4 // Tailwind gap-1 -> 0.25rem ≈ 4px

    // Helper to measure "+N" badge width dynamically
    const measureMore = (n) => {
      const el = document.createElement('span')
      el.className = badgeClass('outline')
      el.textContent = `+${n}`
      measurer.appendChild(el)
      const w = el.offsetWidth
      measurer.removeChild(el)
      return w
    }

    let used = 0
    let count = 0

    for (let i = 0; i < tagEls.length; i++) {
      const remaining = tagEls.length - (i + 1)
      const tagWidth = tagEls[i].offsetWidth
      // account for gap before this tag, except the first
      const withGap = used > 0 ? used + gap : used

      // if there will be hidden tags, we must reserve space for +N (with its own preceding gap)
      const reserveMore = remaining > 0 ? (gap + measureMore(remaining)) : 0

      const needed = withGap + tagWidth + reserveMore
      if (needed <= containerWidth) {
        used = withGap + tagWidth
        count = i + 1
        continue
      }

      // If no tag fits, show 0 so that only +N is shown
      break
    }

    setVisibleCount(count)
  }

  function badgeClass(variant = badgeVariant) {
    // replicate Badge with variant styling via component usage when rendering real tags
    // For measurer, we need raw classes similar to Badge output to get correct widths
    const base = 'inline-flex items-center justify-center rounded-md border px-2 py-0.5 text-xs font-medium w-fit whitespace-nowrap shrink-0 gap-1'
    const variants = {
      default: 'border-transparent bg-primary text-primary-foreground',
      secondary: 'border-transparent bg-secondary text-secondary-foreground',
      destructive: 'border-transparent bg-destructive text-white',
      outline: 'text-foreground',
    }
    return `${base} ${variants[variant] ?? variants.secondary}`
  }

  const hidden = list.length - visibleCount

  return (
    <div ref={containerRef} className={cn('flex items-center gap-1 overflow-hidden', className)}>
      {/* Visible tags */}
      {list.slice(0, visibleCount).map((t, idx) => (
        <Badge key={`${t}-${idx}`} variant={badgeVariant} title={t}>{t}</Badge>
      ))}

      {/* +N for hidden tags */}
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

      {/* Hidden measurer */}
      <div ref={measureRef} aria-hidden className="fixed -left-[9999px] -top-[9999px] opacity-0 pointer-events-none" />
    </div>
  )
}
