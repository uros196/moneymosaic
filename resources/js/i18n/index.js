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

  function __(key, params = {}) {
    if (!key) {
      return ''
    }
    const parts = String(key).split('.')
    if (parts.length === 1) {
      // allow direct group key access e.g. __('common')
      const val = translations[parts[0]]
      return typeof val === 'string' ? format(val, params) : val ?? key
    }
    const ns = parts.shift()
    const val = get(translations[ns] || {}, parts)
    if (val === undefined) {
      return key
    }
    return typeof val === 'string' ? format(val, params) : val
  }

  return { __, t: __, locale }
}
