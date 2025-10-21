/**
 * TableSkeleton - renders a full table skeleton (wrapper, thead, tbody).
 *
 * Props:
 * - columns: Array<{ id: string; header?: React.ReactNode; className?: string }>
 * - rows: number (default: 8)
 */
export default function TableSkeleton({ columns = [], rows = 8 }) {
    return (
        <div className="overflow-x-auto rounded-lg border">
            <table className="w-full text-sm">
                <thead className="bg-muted/60">
                    <tr className="text-left">
                        {columns.map((col) => (
                            <th key={col.id} className={`px-3 py-2 ${col.className ?? ''}`}>
                                {/* Keep headers visible so widths align; could also render skeleton bars here if desired */}
                                {col.header ?? ''}
                            </th>
                        ))}
                    </tr>
                </thead>
                <tbody>
                    {Array.from({ length: rows }).map((_, index) => (
                        <tr key={`skeleton-${index}`} className="animate-pulse border-t">
                            {columns.map((col, cIdx) => (
                                <td key={`sk-${index}-${cIdx}`} className={`px-3 py-3 ${col.className ?? ''}`}>
                                    {String(col.id).toLowerCase() === 'actions' ? (
                                        <div className="flex items-center gap-2">
                                            <div className="h-7 w-7 rounded bg-muted" />
                                            <div className="h-7 w-7 rounded bg-muted" />
                                            <div className="h-7 w-7 rounded bg-muted" />
                                        </div>
                                    ) : (
                                        <div className="h-4 rounded bg-muted" style={{ width: cIdx === columns.length - 1 ? '6rem' : '7rem' }} />
                                    )}
                                </td>
                            ))}
                        </tr>
                    ))}
                </tbody>
            </table>
        </div>
    );
}
