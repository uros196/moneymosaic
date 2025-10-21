import { createContext, useContext, useMemo, useState, lazy, Suspense } from 'react'

const LazyConfirmDelete = lazy(() => import('@/components/ui/confirm-delete'))

const ConfirmDeleteContext = createContext({
  openConfirmDelete: () => {},
})

export function ConfirmDeleteProvider({ children }) {
  const [state, setState] = useState({ open: false, opts: {} })

  const api = useMemo(() => ({
    openConfirmDelete: (opts) => setState({ open: true, opts: opts || {} }),
  }), [])

  const { open, opts } = state

  return (
    <ConfirmDeleteContext.Provider value={api}>
      {children}
      {open && (
        <Suspense fallback={null}>
          <LazyConfirmDelete
            open={open}
            onOpenChange={(v) => setState((s) => ({ ...s, open: Boolean(v) }))}
            title={opts.title}
            description={opts.description}
            confirmText={opts.confirmText}
            cancelText={opts.cancelText}
            requirePassword={Boolean(opts.requirePassword)}
            verifyPasswordUrl={opts.verifyPasswordUrl}
            onConfirm={opts.onConfirm}
          />
        </Suspense>
      )}
    </ConfirmDeleteContext.Provider>
  )
}

export function useConfirmDelete() {
  return useContext(ConfirmDeleteContext)
}
