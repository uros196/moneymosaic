import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select'
import { useI18n } from '@/i18n'
import PaginateLinks from '@/components/ui/paginate-links.jsx';
import { usePage } from '@inertiajs/react';
import { setCurrentParam } from '@/lib/url-query'

/**
 * DataTable - a reusable, easily configurable table component.
 *
 * Props:
 * - columns: Array<{
 *     id: string;
 *     header: React.ReactNode;
 *     className?: string;
 *     accessor?: (row: any) => React.ReactNode; // used if no custom cell renderer
 *     cell?: (row: any) => React.ReactNode;     // custom cell renderer
 *   }>
 * - data: any[]
 * - loading: boolean
 * - emptyText: string
 * - perPage?: { value: number; options: number[]; onChange: (value: number) => void }
 */
export default function DataTable({
  columns,
  data,
  emptyText,
  perPage,
  perPageLabel,
}) {
  const { __ } = useI18n()
    const pageProps = usePage().props || {};

  const rows = data?.data ?? []
  const hasRows = Array.isArray(rows) && rows.length > 0
  const getRowKey = (row) => row?.id ?? crypto.randomUUID?.() ?? Math.random()

  const tPerPage = perPageLabel ?? __('common.pagination.per_page')

    // Define 'per page' data
    const perPageValue = perPage?.value ?? pageProps.paging.perPage ?? '';
    const perPageOptions = perPage?.options ?? pageProps.paging.options ?? [];

    // Default handler for perPage change: preserve existing query, append perPage, reload with preserveScroll
    const handlePerPageChange = (v) => {
      const next = Number(v);
      if (perPage?.onChange) {
        perPage.onChange(next);
      } else {
        setCurrentParam('perPage', next, { preserveScroll: true });
      }
    };

  const meta = data?.meta ?? {}
  const total = Number(meta?.total ?? (hasRows ? rows.length : 0))
  const currentPage = Number(meta?.current_page ?? 1)
  const perPageCount = Number(meta?.per_page ?? perPage?.value ?? rows.length ?? 0)
  const from = Number(meta?.from ?? (total > 0 ? (currentPage - 1) * perPageCount + 1 : 0))
  const to = Number(meta?.to ?? (total > 0 ? Math.min(from + perPageCount - 1, total) : 0))

  function renderCell(col, row) {
    if (typeof col.cell === 'function') {
      return col.cell(row)
    }
    if (typeof col.accessor === 'function') {
      return col.accessor(row)
    }
    // default: try property by id
    return row?.[col.id]
  }

  const hasLinks = Array.isArray(data?.meta?.links) && data.meta.links.length > 0
  const showFooter = Boolean(perPage) || hasLinks

  return (
    <div className="space-y-3">
      <div className="overflow-x-auto rounded-lg border">
        <table className="w-full text-sm">
          <thead className="bg-muted">
            <tr className="text-left">
              {columns.map((col) => (
                <th key={col.id} className={`px-3 py-2 ${col.className ?? ''}`}>
                  {col.header}
                </th>
              ))}
            </tr>
          </thead>
          <tbody>
            {!hasRows && (
              <tr>
                <td className="px-3 py-8 text-center text-muted-foreground" colSpan={columns.length}>
                  {emptyText ?? __('common.table.empty')}
                </td>
              </tr>
            )}

            {hasRows &&
              rows.map((row, idx) => (
                <tr key={getRowKey(row, idx)} className="border-t">
                  {columns.map((col) => (
                    <td key={col.id} className={`px-3 py-2 ${col.className ?? ''}`}>
                      {renderCell(col, row)}
                    </td>
                  ))}
                </tr>
              ))}
          </tbody>
        </table>
      </div>

      {showFooter && (
        <div className="flex flex-col gap-2 py-3 sm:flex-row sm:items-center sm:justify-between">
          <div className="flex items-center gap-2 text-sm">
            <Select
              value={String(perPageValue)}
              onValueChange={handlePerPageChange}
            >
              <SelectTrigger id="per_page" area-label={tPerPage}>
                  <span className="text-muted-foreground">{tPerPage}:</span>
                  <span className="font-medium">&nbsp;<SelectValue /></span>
              </SelectTrigger>
              <SelectContent>
                {perPageOptions.map((opt) => (
                  <SelectItem value={String(opt)} key={opt}>
                    {opt}
                  </SelectItem>
                ))}
              </SelectContent>
            </Select>
          </div>

          <div className="flex items-center justify-end gap-3">
            {total > 0 && (
              <div className="text-sm text-muted-foreground">
                {__('common.pagination.showing', { from, to, total })}
              </div>
            )}
            <PaginateLinks links={data?.meta?.links ?? []} />
          </div>
        </div>
      )}
    </div>
  )
}
