import { router } from '@inertiajs/react';

/**
 * URL Query Utilities
 *
 * All helpers are safe for SSR and catch URL parsing errors.
 */

// ---- Read helpers ----

/**
 * Returns current URL's query parameters as a plain object.
 */
export function getQueryObject(url) {
    try {
        const href = url ?? (typeof window !== 'undefined' ? window.location.href : '');
        if (!href) return {};
        return Object.fromEntries(new URL(href).searchParams.entries());
    } catch {
        return {};
    }
}

/**
 * Get a single query parameter value.
 */
export function getParam(key, fallback = undefined, url) {
    const query = getQueryObject(url);
    return query[key] ?? fallback;
}

/**
 * Get a subset of params by keys.
 */
export function getParams(keys, url) {
    const query = getQueryObject(url);
    const output = {};
    for (const key of keys ?? []) {
        if (key in query) output[key] = query[key];
    }
    return output;
}

// ---- Build / mutate helpers ----

/**
 * Returns a new object with next applied over base.
 * If a value in next is null/undefined/'' remove that key from a result.
 */
export function mergeQuery(base, next) {
    const out = { ...(base || {}) };
    for (const [key, value] of Object.entries(next || {})) {
        if (value === null || value === undefined || value === '') {
            delete out[key];
        } else {
            out[key] = value;
        }
    }
    return out;
}

/**
 * Remove one or more keys from the provided query object.
 */
export function removeParams(queryObj, keys) {
    const out = { ...(queryObj || {}) };
    for (const key of keys ?? []) {
        delete out[key];
    }
    return out;
}

/**
 * Build a query string from an object.
 */
export function toQueryString(obj) {
    return new URLSearchParams(obj || {}).toString();
}

/**
 * Build an href from a pathname and query object.
 */
export function buildUrl(pathname, queryObj) {
    const query_string = toQueryString(queryObj);
    return query_string ? `${pathname}?${query_string}` : pathname;
}

// ---- Inertia integration ----

/**
 * Visit the current path with the provided query object.
 */
export function visitCurrentPath(nextQuery, options = {}) {
    try {
        const urlObj = new URL(window.location.href);
        const href = buildUrl(urlObj.pathname, nextQuery);
        router.visit(href, options);
    } catch {
        // ignore
    }
}

/**
 * Visit a named route with the provided query object.
 */
export function visitRoute(routeName, nextQuery, options = {}) {
    try {
        const routeFn = typeof globalThis !== 'undefined' && globalThis.route ? globalThis.route : undefined;
        if (typeof routeFn === 'function') {
            router.visit(routeFn(routeName, nextQuery), options);
        } else {
            // Fallback: visit the current path with merged query
            visitCurrentPath(mergeQuery(getQueryObject(), nextQuery), options);
        }
    } catch {
        // ignore
    }
}

/**
 * Convenience to set a single param on the current URL and visit.
 */
export function setCurrentParam(key, value, options = {}) {
    const cur = getQueryObject();
    const merged = mergeQuery(cur, { [key]: value });
    visitCurrentPath(merged, options);
}

/**
 * Convenience to replace a set of params (remove then set) on the current URL and visit.
 */
export function replaceCurrentParams(removeKeys = [], set = {}, options = {}) {
    const cur = getQueryObject();
    const cleared = removeParams(cur, removeKeys);
    const merged = mergeQuery(cleared, set);
    visitCurrentPath(merged, options);
}

// ---- Append / Prepend value helpers ----

/**
 * Normalize an input value to an array of strings.
 * - strings are split by delimiter
 * - arrays are stringified item-wise
 * - null/undefined/empty values yield []
 */
function valueToList(value, delimiter = ',') {
    if (value === null || value === undefined) {
        return [];
    }
    if (Array.isArray(value)) {
        return value.map(v => String(v).trim()).filter(Boolean);
    }
    const str = String(value);
    if (!str) {
        return [];
    }
    // If value already contains delimiter, split it
    return str
        .split(delimiter)
        .map(s => s.trim())
        .filter(Boolean);
}

/**
 * Update a delimited list string by appending or prepending values.
 * Returns an empty string when result is empty (so mergeQuery removes the key).
 */
function updateDelimitedParam(currentValue, valuesToAdd, { delimiter = ',', mode = 'append', unique = true } = {}) {
    const current = valueToList(currentValue, delimiter);
    const incoming = valueToList(valuesToAdd, delimiter);

    let combined = mode === 'prepend' ? [...incoming, ...current] : [...current, ...incoming];

    if (unique) {
        const seen = new Set();
        combined = combined.filter(v => {
            const k = v;
            if (seen.has(k)) {
                return false;
            }
            seen.add(k);
            return true;
        });
    }

    return combined.join(delimiter);
}

/**
 * Append a value (or values) to a delimited query param on the current URL and visit.
 * - value can be a string ("a" or "a,b") or an array (["a","b"])
 * - by default ensures uniqueness and uses comma as delimiter
 */
export function appendCurrentParam(key, value, options = {}, delimiter = ',', unique = true) {
    const cur = getQueryObject();
    const next = updateDelimitedParam(cur[key], value, { delimiter, mode: 'append', unique });
    const merged = mergeQuery(cur, { [key]: next });
    visitCurrentPath(merged, options);
}

/**
 * Prepend a value (or values) to a delimited query param on the current URL and visit.
 * - value can be a string ("a" or "a,b") or an array (["a","b"])
 * - by default ensures uniqueness and uses comma as delimiter
 */
export function prependCurrentParam(key, value, options = {}, delimiter = ',', unique = true) {
    const cur = getQueryObject();
    const next = updateDelimitedParam(cur[key], value, { delimiter, mode: 'prepend', unique });
    const merged = mergeQuery(cur, { [key]: next });
    visitCurrentPath(merged, options);
}


/**
 * Insert one or more params at the very beginning of the current URL's query string and visit it.
 * - params: object of key -> value
 * - null/undefined/'' values will remove the key from the final URL
 * - the provided params appear first, followed by any remaining existing params in their original order
 */
export function setCurrentParamsFirst(params = {}, options = {}) {
    try {
        const urlObj = new URL(window.location.href);

        // Existing entries in their current order
        const existingEntries = Array.from(urlObj.searchParams.entries());

        // Filter out removals and coerce values to strings
        const incomingEntries = Object.entries(params || {})
            .filter(([, v]) => v !== null && v !== undefined && v !== '')
            .map(([k, v]) => [k, String(v)]);

        const incomingKeys = new Set(incomingEntries.map(([k]) => k));

        // Compose: incoming first (in the order provided), then remaining existing
        const finalEntries = [
            ...incomingEntries,
            ...existingEntries.filter(([k]) => !incomingKeys.has(k)),
        ];

        // Build ordered query string
        const sp = new URLSearchParams();
        for (const [k, v] of finalEntries) {
            // If the key exists in both, we want the incoming value (already placed first)
            // and we skipped existing duplicates above.
            sp.append(k, v);
        }

        const href = `${urlObj.pathname}${sp.toString() ? `?${sp}` : ''}`;
        router.visit(href, options);
    } catch {
        // ignore
    }
}

/**
 * Alias with a more intention-revealing name.
 * Places provided params at the start of the query string on the current URL and visits it.
 */
export const prependCurrentParams = setCurrentParamsFirst;
