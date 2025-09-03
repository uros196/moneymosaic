import * as React from "react"
import { cn } from "@/lib/utils"

/**
 * TagInput component
 *
 * Props:
 * - value: string[]
 * - onChange: (tags: string[]) => void
 * - suggestions?: string[] (existing tags to suggest)
 * - placeholder?: string
 * - allowNew?: boolean (default: true)
 * - className?: string
 */
export function TagInput({ value = [], onChange, suggestions = [], placeholder = "", allowNew = true, className }) {
  const [input, setInput] = React.useState("")
  const [open, setOpen] = React.useState(false)
  const [highlight, setHighlight] = React.useState(0)
  const containerRef = React.useRef(null)
  const inputRef = React.useRef(null)

  const selected = Array.isArray(value) ? value : []

  const filtered = React.useMemo(() => {
    const q = (input || "").trim().toLowerCase()
    const pool = Array.isArray(suggestions) ? suggestions : []
    const seen = new Set(selected.map((t) => String(t).toLowerCase()))
    const list = pool
      .filter((s) => !seen.has(String(s).toLowerCase()))
      .filter((s) => (q ? String(s).toLowerCase().includes(q) : true))
    return list.slice(0, 12)
  }, [input, suggestions, selected])

  React.useEffect(() => {
    setOpen(Boolean(input) && filtered.length > 0)
    setHighlight(0)
  }, [input, filtered.length])

  function addTag(tag) {
    const t = String(tag || "").trim()
    if (!t) return
    const exists = selected.some((x) => String(x).toLowerCase() === t.toLowerCase())
    if (exists) {
      setInput("")
      return
    }
    onChange?.([...selected, t])
    setInput("")
  }

  function removeTag(tag) {
    onChange?.(selected.filter((t) => t !== tag))
  }

  function handleKeyDown(e) {
    if (e.key === "Backspace" && !input) {
      // Remove last tag when input empty
      if (selected.length > 0) {
        e.preventDefault()
        onChange?.(selected.slice(0, -1))
      }
      return
    }

    if (e.key === "ArrowDown") {
      e.preventDefault()
      if (!open) {
        setOpen(filtered.length > 0)
        return
      }
      setHighlight((h) => (h + 1) % Math.max(filtered.length, 1))
      return
    }
    if (e.key === "ArrowUp") {
      e.preventDefault()
      if (!open) {
        setOpen(filtered.length > 0)
        return
      }
      setHighlight((h) => (h - 1 + Math.max(filtered.length, 1)) % Math.max(filtered.length, 1))
      return
    }

    if (e.key === "Enter" || e.key === " ") {
      // Space or Enter creates/chooses tag
      e.preventDefault()
      if (open && filtered.length > 0) {
        const chosen = filtered[highlight] ?? filtered[0]
        if (chosen) {
          addTag(chosen)
          return
        }
      }
      if (allowNew) {
        addTag(input)
      }
    }
  }

  function handleBlur(e) {
    // Delay closing suggestions to allow click
    setTimeout(() => setOpen(false), 100)
  }

  return (
    <div ref={containerRef} className={cn("relative", className)}>
      <div
        className={cn(
          "flex flex-wrap items-center gap-2 border rounded-md px-2 py-2 bg-background",
          "focus-within:border-ring focus-within:ring-ring/50 focus-within:ring-[3px] transition-[color,box-shadow]"
        )}
        onClick={() => inputRef.current?.focus()}
      >
        {selected.length > 0 ? (
          selected.map((tag) => (
            <span key={tag} className="inline-flex items-center gap-1 rounded-full bg-muted px-2 py-0.5 text-xs">
              {tag}
              <button
                type="button"
                className="text-muted-foreground hover:text-foreground"
                onClick={() => removeTag(tag)}
                aria-label={`Remove ${tag}`}
              >
                ×
              </button>
            </span>
          ))
        ) : (
          <span className="text-xs text-muted-foreground">—</span>
        )}
        <input
          ref={inputRef}
          value={input}
          onChange={(e) => setInput(e.target.value)}
          onKeyDown={handleKeyDown}
          onBlur={handleBlur}
          placeholder={placeholder}
          className="min-w-[8rem] flex-1 bg-transparent outline-none text-sm"
        />
      </div>

      {open && filtered.length > 0 ? (
        <div className="absolute z-50 mt-1 max-h-56 w-full overflow-auto rounded-md border bg-popover p-1 text-sm shadow-md">
          {filtered.map((s, idx) => (
            <button
              key={s}
              type="button"
              className={cn(
                "block w-full cursor-pointer rounded-sm px-2 py-1 text-left hover:bg-accent hover:text-accent-foreground",
                idx === highlight && "bg-accent text-accent-foreground"
              )}
              onMouseEnter={() => setHighlight(idx)}
              onMouseDown={(e) => e.preventDefault()}
              onClick={() => addTag(s)}
            >
              {s}
            </button>
          ))}
        </div>
      ) : null}
    </div>
  )
}
