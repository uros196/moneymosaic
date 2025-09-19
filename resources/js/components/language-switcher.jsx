import { Select, SelectContent, SelectItem, SelectTrigger } from '@/components/ui/select';
import { router, usePage } from '@inertiajs/react';
import { useEffect } from 'react';

// Quick language switcher (dropdown) for the entire app

// Props that always refresh when the language changes
const DEFAULT_ONLY = ['locale', 'translations', 'availableLocales'];

// Registry of extra props a page can request to refresh
let extraOnly = [];

/**
 * Sets the list of additional prop keys to refresh when the language changes.
 */
export function setLocaleRefreshOnly(keys = []) {
    extraOnly = Array.from(new Set((Array.isArray(keys) ? keys : []).filter(Boolean)));
}

/**
 * Hook to register additional prop keys from a specific page.
 * Automatically clears the registration on unmount.
 */
export function useLocaleRefreshOnly(keys = []) {
    useEffect(() => {
        setLocaleRefreshOnly(keys);
        return () => setLocaleRefreshOnly([]);
    }, [JSON.stringify(keys)]);
}

/**
 * UI component for selecting the language.
 * Closed state shows only the locale code (EN/SR) without a border; the list shows full names.
 */
export default function LanguageSwitcher({ className = '', additionalOnly = [] }) {
    const { locale, availableLocales } = usePage().props;

    /**
     * Changes the language and performs an Inertia partial reload with the given "only" props.
     */
    function onChange(next) {
        if (!next || next === locale) return; // no change

        // Build the list: defaults + page-registered + component-provided
        const only = Array.from(
            new Set([
                ...DEFAULT_ONLY,
                ...(Array.isArray(extraOnly) ? extraOnly : []),
                ...(Array.isArray(additionalOnly) ? additionalOnly : [])
            ]),
        );

        // Partial reload without losing state/scroll
        router.post(
            route('locale.set'),
            { locale: next },
            {
                preserveState: true,
                preserveScroll: true,
                replace: true,
                only,
            },
        );
    }

    // Full language names in the dropdown list
    const labels = {
        en: 'English',
        sr: 'Srpski',
    };

    return (
        <Select value={locale} onValueChange={onChange}>
            {/* Trigger shows only the locale code + caret; no border or shadow */}
            <SelectTrigger
                className={`h-8 w-auto rounded-none border-none bg-transparent px-1 py-0 text-xs tracking-wide uppercase shadow-none hover:bg-transparent focus-visible:border-transparent focus-visible:ring-0 ${className}`}
                aria-label="Language"
            >
                <span>{locale.toUpperCase()}</span>
            </SelectTrigger>
            <SelectContent align="end">
                {availableLocales.map((l) => (
                    <SelectItem key={l} value={l}>
                        {labels[l] || l.toUpperCase()}
                    </SelectItem>
                ))}
            </SelectContent>
        </Select>
    );
}
