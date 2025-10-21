import { router } from '@inertiajs/react'
import { Button } from '@/components/ui/button'

/**
 * PaginateLinks - Renders Laravel Resource paginator links.
 *
 * Expects the "links" array structure returned by Laravel paginator JSON:
 * [ { url: string|null, label: string, active: boolean }, ... ]
 *
 * Props:
 * - links: array
 * - align?: 'left' | 'center' | 'right' (default: 'right')
 */
export default function PaginateLinks({ links = [], align = 'right' }) {
  if (!Array.isArray(links) || links.length === 0) {
    return null
  }

  function visit(url) {
    if (!url) {
      return
    }
    router.visit(url, { preserveScroll: true, replace: true })
  }

  const justify = align === 'left' ? 'justify-start' : align === 'center' ? 'justify-center' : 'justify-end'

  return (
    <nav className={`flex ${justify} py-3`} role="navigation" aria-label="pagination">
      <ul className="flex items-center gap-2">
        {links.map((link, idx) => {
          const disabled = link.url == null
          const active = Boolean(link.active)

          return (
            <li key={`pg-${idx}`}>
              <Button
                type="button"
                variant={active ? 'default' : 'secondary'}
                size="sm"
                disabled={disabled}
                aria-current={active ? 'page' : undefined}
                onClick={() => visit(link.url)}
              >
                {/* Labels sometimes include HTML entities (e.g., «, ») */}
                <span dangerouslySetInnerHTML={{ __html: link.label }} />
              </Button>
            </li>
          )
        })}
      </ul>
    </nav>
  )
}
