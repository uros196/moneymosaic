import * as React from "react"
import { cn } from "@/lib/utils"
import { useEffect, useRef, useState } from 'react';
import { Input } from '@headlessui/react';

/**
 * TagInput — simple tag input with suggestions.
 *
 * Props (short):
 * - name: string — hidden input name (required, submitted as `${name}[]`).
 * - defaultValue?: string[] — initial tags.
 * - onChange?: (tags: string[]) => void — callback when tags change.
 * - suggestions?: string[] — list of existing tags to suggest.
 * - placeholder?: string — input placeholder text.
 * - allowNew?: boolean — allow creating a new tag (default: true).
 * - className?: string — additional CSS classes for the wrapper.
 */
export function TagInput({ name, defaultValue = [], onChange, suggestions = [], placeholder = "", allowNew = true, className }) {
  // Internal state
  const [input, setInput] = useState("")
  const [open, setOpen] = useState(false)
  const [highlight, setHighlight] = useState(0)
  const [tags, setTags] = useState(Array.isArray(defaultValue) ? defaultValue : [])
  const containerRef = useRef(null)
  const inputRef = useRef(null)

  // Sync tags only when the contents of defaultValue actually change
  const defaultValueKey = React.useMemo(() => JSON.stringify(Array.isArray(defaultValue) ? defaultValue : []), [defaultValue])
  useEffect(() => {
    setTags(Array.isArray(defaultValue) ? defaultValue : [])
  }, [defaultValueKey])

  // Filtered suggestions (exclude selected, limit to 12 items)
  const filtered = React.useMemo(() => {
    const q = (input || "").trim().toLowerCase()
    const pool = Array.isArray(suggestions) ? suggestions : []
    const seen = new Set(tags.map((t) => String(t).toLowerCase()))
    const list = pool
      .filter((s) => !seen.has(String(s).toLowerCase()))
      .filter((s) => (q ? String(s).toLowerCase().includes(q) : true))
    return list.slice(0, 12)
  }, [input, suggestions, tags])

  // Open the suggestions list + reset the highlighted row
  React.useEffect(() => {
    setOpen(Boolean(input) && filtered.length > 0)
    setHighlight(0)
  }, [input, filtered.length])

  // Helper: set tags and call onChange
  function commit(next) {
    setTags(next)
    onChange?.(next)
  }

  // Add a new/selected tag
  function addTag(tag) {
    const t = String(tag || "").trim()
    if (!t) return
    const exists = tags.some((x) => String(x).toLowerCase() === t.toLowerCase())
    if (exists) {
      setInput("")
      return
    }
    commit([...tags, t])
    setInput("")
  }

  // Remove a tag
  function removeTag(tag) {
    commit(tags.filter((t) => t !== tag))
  }

  // Keyboard: backspace removes last, arrows navigate, enter/space confirm
  function handleKeyDown(e) {
    if (e.key === "Backspace" && !input) {
      // Delete the last tag when the input is empty
      if (tags.length > 0) {
        e.preventDefault()
        commit(tags.slice(0, -1))
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

    if (e.key === "Enter") {
      // Enter: confirm selection or create a new tag
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
      return
    }

    if (e.key === " ") {
      // Space: create a new tag from current input, do not select from suggestions
      if (allowNew) {
        e.preventDefault()
        addTag(input)
      }
      return
    }
  }

  // Short delay so a click on a suggestion is captured
  function handleBlur(e) {
    setTimeout(() => setOpen(false), 100)
  }

  return (
    <div ref={containerRef} className={cn("relative", className)}>
      {/* Hidden inputs for submitting to the server */}
      {Array.isArray(tags) && name ? tags.map((t, idx) => (
        <Input type="hidden" name={`${name}[]`} value={t} key={`hidden-${t}-${idx}`} />
      )) : null}

      <div
        className={cn(
          "flex flex-wrap items-center gap-2 border rounded-md px-2 py-2 bg-background",
          "focus-within:border-ring focus-within:ring-ring/50 focus-within:ring-[3px] transition-[color,box-shadow]"
        )}
        onClick={() => inputRef.current?.focus()}
      >
        {/* Selected tags list */}
        {tags.length > 0 ? (
          tags.map((tag) => (
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

        {/* Text input */}
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

      {/* Dropdown with suggestions */}
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
