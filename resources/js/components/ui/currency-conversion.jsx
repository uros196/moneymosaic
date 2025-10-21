import { Toggle } from '@/components/ui/toggle';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { usePage } from '@inertiajs/react';
import { useEffect, useMemo, useState } from 'react';
import { cn } from '@/lib/utils.js';
import { useI18n } from '@/i18n';
import { getQueryObject, visitRoute } from '@/lib/url-query';

/**
 * CurrencyConversion
 *
 * Reusable conversion controls with URL sync and Inertia visit.
 * - Shows a Toggle to enable/disable conversion.
 * - When enabled, shows a Select of currency codes.
 * - On any change, it updates the URL query param (paramKey) and triggers an Inertia request.
 *
 * Props:
 * - routeName: string (required) — named route for the current listing page (e.g., 'incomes.index')
 * - currencies: string[] (required) — list of currencies
 * - defaultCurrency: string (required) — default currency code used when enabling
 * - onlyKeys?: string[] — Inertia partial reload keys to request; if omitted, performs a full reload
 * - paramKey?: string — query parameter key to use; defaults to 'currency'
 * - preserveScroll?: boolean — defaults to true
 * - preserveState?: boolean — defaults to true
 * - labels?: { toggle?: string; select?: string } — UI labels
 * - className?: string — optional wrapper class
 */
export default function CurrencyConversion({
    routeName,
    currencies,
    defaultCurrency,
    onlyKeys,
    paramKey = 'currency',
    preserveScroll = true,
    preserveState = true,
    labels = {},
    className,
}) {
    const { url } = usePage();
    const { __ } = useI18n();

    const [enabled, setEnabled] = useState(false);
    const [selected, setSelected] = useState(defaultCurrency);

    const currentQuery = useMemo(() => getQueryObject(), [url]);

    // Sync from URL whenever it changes
    useEffect(() => {
        const urlVal = currentQuery[paramKey];
        if (urlVal != null && urlVal !== '') {
            if (!enabled) {
                setEnabled(true);
            }
            if (urlVal !== selected) {
                setSelected(urlVal);
            }
        } else if (enabled) {
            setEnabled(false);
        }
        // eslint-disable-next-line react-hooks/exhaustive-deps
    }, [currentQuery[paramKey]]);

    function visitWithQuery(query) {
        const options = {
            preserveScroll,
            preserveState,
        };
        if (Array.isArray(onlyKeys) && onlyKeys.length > 0) {
            options.only = onlyKeys;
        }
        visitRoute(routeName, query, options);
    }

    function enableConversion(nextEnabled) {
        setEnabled(nextEnabled);
        if (nextEnabled) {
            const nextCurrency = selected || defaultCurrency || currencies?.[0];
            const newQuery = { ...currentQuery, [paramKey]: nextCurrency };
            // Always perform a request when toggling ON to refresh data
            visitWithQuery(newQuery);
        } else {
            // Remove the param and refresh
            // eslint-disable-next-line no-unused-vars
            const { [paramKey]: _removed, ...rest } = currentQuery;
            visitWithQuery(rest);
        }
    }

    function changeCurrency(val) {
        setSelected(val);
        if (!enabled) {
            return;
        }
        const curVal = currentQuery[paramKey];
        if (curVal === val) {
            // No URL change, but still perform a request to ensure data refresh
            visitWithQuery({ ...currentQuery });
            return;
        }
        const newQuery = { ...currentQuery, [paramKey]: val };
        visitWithQuery(newQuery);
    }

    const toggleLabel = labels.toggle ?? __('common.convert');
    const selectLabel = labels.select ?? __('common.display_currency');

    return (
        <div className={cn('flex items-center gap-2', className)}>
            <Toggle pressed={enabled} onPressedChange={enableConversion} aria-label={toggleLabel}>
                {toggleLabel}
            </Toggle>
            {enabled && (
                <div className="flex items-center gap-2">
                    <Select value={selected} onValueChange={changeCurrency}>
                        <SelectTrigger id="display_currency" aria-label={selectLabel}>
                            <span className="text-muted-foreground">{selectLabel}:</span>
                            <span className="font-medium">&nbsp;{selected}</span>
                        </SelectTrigger>
                        <SelectContent>
                            {currencies.map((currency) => (
                                <SelectItem value={currency.value} key={currency.value}>
                                    {currency.display_name}
                                </SelectItem>
                            ))}
                        </SelectContent>
                    </Select>
                </div>
            )}
        </div>
    );
}
