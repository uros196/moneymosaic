import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select'
import { useI18n } from '@/i18n'
import PaginateLinks from '@/components/ui/paginate-links.jsx';

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
    const rows = data?.data ?? []
  const hasRows = Array.isArray(rows) && rows.length > 0
    const getRowKey = (row) => row?.id ?? crypto.randomUUID?.() ?? Math.random()

  const tPerPage = perPageLabel ?? __('data_table.per_page')

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

  return (
    <div className="space-y-3">
      {/* Toolbar (perPage selector on the left; optional actions on the right) */}
      <div className="flex items-center justify-between">
        <div className="hidden sm:flex items-center gap-2 text-sm">
          {perPage && (
            <label className="flex items-center gap-2">
              <span>{tPerPage}</span>
              <Select
                value={String(perPage.value)}
                onValueChange={(v) => perPage.onChange(Number(v))}
              >
                <SelectTrigger id="per_page">
                  <SelectValue />
                </SelectTrigger>
                <SelectContent>
                  {perPage.options.map((opt) => (
                    <SelectItem value={String(opt)} key={opt}>
                      {opt}
                    </SelectItem>
                  ))}
                </SelectContent>
              </Select>
            </label>
          )}
        </div>
      </div>

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
                    {emptyText ?? __('data_table.empty')}
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

      {Array.isArray(data?.meta?.links) && data.meta.links.length > 0 && (
        <div className="flex justify-end gap-2 py-3">
            <PaginateLinks links={data.meta.links} />
        </div>
      )}
    </div>
  )
}
