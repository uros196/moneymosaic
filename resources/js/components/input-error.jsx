import { cn } from '@/lib/utils';

/**
 * InputError — small, reusable error message component.
 *
 * Usage examples:
 * - <InputError message={errors.name} />
 * - <InputError errors={errors} field="name" />
 * - <InputError errors={errors} fields={["email", "password"]} />
 * - <InputError errors={errors} prefix="tags." /> // first error for keys starting with "tags."
 */
export default function InputError({
    message,
    errors,
    field,
    fields,
    prefix,
    className = '',
    ...props
}) {
    const text = pickMessage({ message, errors, field, fields, prefix });

    return text ? (
        <p {...props} className={cn('text-sm text-red-600 dark:text-red-400', className)} role="alert" aria-live="polite">
            {text}
        </p>
    ) : null;
}

/**
 * Normalize different inputs into a single error string (first available).
 */
function pickMessage({ message, errors, field, fields, prefix }) {
    // If a direct message is provided, normalize and return it.
    if (message != null) {
        if (Array.isArray(message)) {
            return firstNonEmpty(message);
        }
        if (isObject(message)) {
            return firstNonEmpty(Object.values(message).flat());
        }
        return String(message);
    }

    // If errors object provided, try fields or prefix selectors.
    if (isObject(errors)) {
        const hasField = typeof field === 'string' && Boolean(field);
        const hasFields = Array.isArray(fields) && fields.length > 0;
        const hasPrefix = typeof prefix === 'string' && Boolean(prefix);
        const hasSelectors = hasField || hasFields || hasPrefix;

        // Single field support
        if (hasField) {
            const msg = normalizeFieldError(errors[field]);
            if (msg) return msg;
        }
        // Multiple fields support
        if (hasFields) {
            for (const f of fields) {
                const msg = normalizeFieldError(errors[f]);
                if (msg) return msg;
            }
        }
        // Prefix support (e.g., 'tags.' -> picks first matching key)
        if (hasPrefix) {
            for (const [k, v] of Object.entries(errors)) {
                if (k.startsWith(prefix)) {
                    const msg = normalizeFieldError(v);
                    if (msg) return msg;
                }
            }
        }
        // If selectors were provided but nothing matched, do not show unrelated errors.
        if (hasSelectors) {
            return '';
        }
        // No selectors provided: fallback to the first error in the object
        return firstNonEmpty(Object.values(errors).flat());
    }

    return '';
}

function normalizeFieldError(value) {
    if (value == null) {
        return '';
    }
    if (Array.isArray(value)) {
        return firstNonEmpty(value);
    }
    return String(value);
}

function firstNonEmpty(arr) {
    for (const v of arr) {
        if (v == null) {
            continue;
        }
        const s = String(v).trim();
        if (s) {
            return s;
        }
    }
    return '';
}

function isObject(v) {
    return v != null && typeof v === 'object' && !Array.isArray(v);
}
