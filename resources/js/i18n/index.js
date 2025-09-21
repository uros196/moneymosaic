import { usePage } from '@inertiajs/react'

/**
 * Safely traverses a nested object structure using an array of keys.
 * Returns undefined if any part of the path is invalid.
 *
 * @param {Object} obj - The object to traverse
 * @param {string[]} path - Array of keys representing the path to the desired value
 * @returns {*} The value at the specified path or undefined if not found
 */
function get(obj, path) {
  return path.reduce((acc, key) => (acc && acc[key] !== undefined ? acc[key] : undefined), obj)
}

/**
 * Replaces placeholders in a string with corresponding parameter values.
 * Placeholders are in the format :paramName
 *
 * @param {string} str - The template string containing placeholders
 * @param {Object} params - Object containing values to replace placeholders
 * @returns {string} Formatted string with replacements
 */
function format(str, params = {}) {
  if (str == null) return ''
  return String(str).replace(/:([A-Za-z0-9_]+)/g, (_, k) => (k in params ? String(params[k]) : `:${k}`))
}

/**
 * React hook that provides internationalization utilities.
 * Accesses translations from Inertia page props and provides methods to translate keys.
 *
 * @returns {{
 *   __: (key: string, params?: Object) => string,
 *   t: (key: string, params?: Object) => string,
 *   locale: string
 * }} Translation utilities and current locale
 */
export function useI18n() {
  const { translations = {}, locale = 'en' } = usePage().props || {}

  function choose(text, count) {
    if (typeof text !== 'string' || !text.includes('|')) {
      return text
    }

    const parts = text.split('|').map((s) => s.trim())

    // Try interval/exact syntax first: {n} text, [a,b] text, [a,*] text
    const matchers = parts.map((segment) => {
      const mExact = segment.match(/^\{\s*(-?\d+)\s*\}\s*(.*)$/)
      if (mExact) {
        return { type: 'exact', n: Number(mExact[1]), text: mExact[2] }
      }
      const mRange = segment.match(/^([\[\]])\s*(-?\d+|\*)\s*,\s*(-?\d+|\*)\s*([\[\]])\s*(.*)$/)
      if (mRange) {
        const startInc = mRange[1] === '['
        const start = mRange[2] === '*' ? -Infinity : Number(mRange[2])
        const end = mRange[3] === '*' ? Infinity : Number(mRange[3])
        const endInc = mRange[4] === ']'
        return { type: 'range', start, end, startInc, endInc, text: mRange[5] }
      }
      // Fallback segment without rule
      return { type: 'plain', text: segment }
    })

    // First, try to match exact rule
    for (const seg of matchers) {
      if (seg.type === 'exact' && count === seg.n) {
        return seg.text
      }
    }

    // Then, try to match range rule
    for (const seg of matchers) {
      if (seg.type === 'range') {
        const afterStart = seg.startInc ? count >= seg.start : count > seg.start
        const beforeEnd = seg.endInc ? count <= seg.end : count < seg.end
        if (afterStart && beforeEnd) {
          return seg.text
        }
      }
    }

    // Finally, fallback to two-choice simple plural: singular|plural
    if (parts.length === 2) {
      return count === 1 ? parts[0] : parts[1]
    }

    // Otherwise, return the last variant as a generic plural
    return parts[parts.length - 1]
  }

  function __(key, params = {}) {
    if (!key) {
      return ''
    }
    const parts = String(key).split('.')
    if (parts.length === 1) {
      // allow direct group key access e.g. __('common')
      const val = translations[parts[0]]
      if (typeof val === 'string') {
        const chosen = 'count' in params ? choose(val, Number(params.count)) : val
        return format(chosen, params)
      }
      return val ?? key
    }
    const ns = parts.shift()
    const val = get(translations[ns] || {}, parts)
    if (val === undefined) {
      return key
    }
    if (typeof val === 'string') {
      const chosen = 'count' in params ? choose(val, Number(params.count)) : val
      return format(chosen, params)
    }
    return val
  }

  return { __, t: __, locale }
}
