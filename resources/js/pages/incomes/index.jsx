import HeadingSmall from '@/components/heading-small';
import { useLocaleRefreshOnly } from '@/components/language-switcher';
import TableSkeleton from '@/components/skeletons/table-skeleton.jsx';
import { Button } from '@/components/ui/button';
import DataTable from '@/components/ui/data-table';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { DeleteAction, EditAction, ViewAction } from '@/components/ui/table-actions';
import { Toggle } from '@/components/ui/toggle';
import { useI18n } from '@/i18n';
import AppLayout from '@/layouts/app-layout';
import { Deferred, Head, router, usePage, useRemember } from '@inertiajs/react';
import { PlusIcon } from 'lucide-react';
import { useEffect, useMemo, useState } from 'react';
import { toast } from 'react-hot-toast';
import IncomeDrawer from './income-drawer';

function formatAmount(minor, currency) {
    try {
        return new Intl.NumberFormat(undefined, { style: 'currency', currency }).format((minor || 0) / 100);
    } catch (_) {
        // Fallback: plain number + code
        return `${((minor || 0) / 100).toFixed(2)} ${currency}`;
    }
}

function toISO(dateLike) {
    const d = new Date(dateLike);
    if (Number.isNaN(d.getTime())) return '';
    const m = `${d.getMonth() + 1}`.padStart(2, '0');
    const day = `${d.getDate()}`.padStart(2, '0');
    return `${d.getFullYear()}-${m}-${day}`;
}

// Simple client-side conversion via EUR as a base (UI-only; mock rates)
const RATES_EUR = {
    EUR: 1,
    USD: 1.1,   // 1 EUR = 1.10 USD (example)
    RSD: 117,   // 1 EUR = 117.00 RSD (example)
    GBP: 0.85,  // 1 EUR ≈ 0.85 GBP (example)
    CHF: 0.95,  // 1 EUR ≈ 0.95 CHF (example)
    CAD: 1.45,  // 1 EUR ≈ 1.45 CAD (example)
};

function convertMinor(amountMinor, fromCode, toCode) {
    const from = String(fromCode || 'EUR').toUpperCase();
    const to = String(toCode || 'EUR').toUpperCase();
    if (from === to) return amountMinor || 0;
    const rFrom = RATES_EUR[from];
    const rTo = RATES_EUR[to];
    if (!rFrom || !rTo) {
        return amountMinor || 0;
    }
    // Convert minor->main to compute, then back to minor; round to the nearest cent
    const main = (amountMinor || 0) / 100;
    const inEurMain = main / rFrom;
    const inTargetMain = inEurMain * rTo;
    return Math.round(inTargetMain * 100);
}

export default function IncomesIndex() {
    const { __ } = useI18n();

  // Mock data for the UI (client-only)
  const [items, setItems] = useState(() => [
    { id: 1, occurred_on: '2025-08-01', name: 'Salary August', description: 'Monthly salary', income_type_key: 'salary', amount_minor: 250000, currency_code: 'EUR', tags: ['job'] },
    { id: 2, occurred_on: '2025-07-15', name: 'Quarterly Bonus', description: 'Quarterly bonus', income_type_key: 'bonus', amount_minor: 75000, currency_code: 'USD', tags: ['bonus'] },
    { id: 3, occurred_on: '2025-06-02', name: 'Freelance', description: 'Freelance gig', income_type_key: 'other', amount_minor: 42000, currency_code: 'RSD', tags: ['side'] },
  ])

    const now = new Date();
    const [filters, setFilters] = useState({
        month: `${now.getMonth() + 1}`.padStart(2, '0'),
        year: `${now.getFullYear()}`,
        type: 'all',
        currency: 'all',
    });

    // Conversion UI state (display-only)
    const pageProps = usePage().props || {};
    const [convertEnabled, setConvertEnabled] = useState(false);
    const [convertCurrency, setConvertCurrency] = useState(pageProps.user.default_currency_code);
    const hasModal = Boolean(pageProps.modal);

    const [filtersOpen, setFiltersOpen] = useState(false);
    const years = useMemo(() => {
        const rowsLocal = pageProps.incomes?.data ?? [];
        const ys = new Set(rowsLocal.map((i) => String(i.occurred_on).slice(0, 4)));
        ys.add(`${now.getFullYear()}`);
        return Array.from(ys).sort((a, b) => Number(b) - Number(a));
    }, [pageProps.incomes]);

    /**
     * Parses the query parameters from the current window's URL and returns them as an object.
     *
     * @return {Object} An object where each key-value pair corresponds to a query parameter and its value.
     */
    function getQuery() {
        return Object.fromEntries(new URL(window.location.href).searchParams.entries());
    }

    // Updates the indexQuery state when the URL changes.
    // This effect ensures the stored query parameters stay in sync with the URL.
    // It runs whenever the page URL changes, updating the remembered query state.
    const [indexQuery, setIndexQuery] = useRemember(getQuery(), 'incomes.indexQuery');
    useEffect(() => {
        setIndexQuery(getQuery());
    }, [usePage().url]);

    // Modal state
    const [open, setOpen] = useState(false);

    // Open the drawer based on modal props (URL-driven)
    useEffect(() => {
        setOpen(hasModal);
    }, [pageProps.modal]);

    function openCreate() {
        router.visit(route('incomes.create'), {
            preserveScroll: true,
            preserveState: true,
            only: ['modal', 'paging', 'tagSuggestions'],
        });
    }

    function openEdit(item) {
        router.visit(route('incomes.edit', item.id), {
            preserveScroll: true,
            preserveState: true,
            only: ['modal', 'income', 'paging', 'tagSuggestions'],
        });
    }

    function performDelete(id) {
        if (id == null) return;
        router.delete(route('incomes.destroy', id), {
            preserveScroll: true,
            replace: true,
            onSuccess: () => {
                router.reload({ preserveScroll: true, only: ['incomes', 'flash'] });
            },
            onError: () => {
                toast.error(__('incomes.toasts.delete_failed'));
            },
        });
    }

    // After a user changes language using a LanguageSwitcher, refresh additional props
    useLocaleRefreshOnly(['incomeTypes']);

    const breadcrumbs = [{ title: __('incomes.title'), href: route('incomes.index') }];

    // define incomes table columns
    const columns = useMemo(
        () => [
            { id: 'occurred_on', header: __('incomes.table.date'), accessor: (r) => r.occurred_on_display },
            { id: 'description', header: __('incomes.table.name'), accessor: (r) => r.name || r.description },
            { id: 'income_type', header: __('incomes.table.type'), accessor: (r) => r.income_type.name },
            { id: 'amount', header: __('incomes.table.amount'), className: 'text-right', accessor: (r) => r.amount_formatted },
            { id: 'currency_code', header: __('incomes.table.currency'), accessor: (r) => r.currency_code },
            {
                id: 'actions',
                header: __('incomes.table.actions'),
                cell: (income) => (
                    <div className="flex items-center gap-2">
                        <ViewAction href={route('incomes.show', income.id)} label={__('incomes.actions.view')} />
                        <EditAction onClick={() => openEdit(income)} label={__('incomes.actions.edit')} />
                        <DeleteAction
                            onConfirm={() => performDelete(income.id)}
                            label={__('incomes.actions.delete')}
                            confirmTitle={__('incomes.confirm.delete_title')}
                            confirmDescription={__('incomes.confirm.delete_description')}
                        />
                    </div>
                ),
            },
        ],
        [__],
    );

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title={__('incomes.title')} />

            <div className="space-y-6">
                <HeadingSmall title={__('incomes.title')} description={__('incomes.description')} />

                {/* Toolbar */}
                <div className="space-y-3">
                    <div className="flex items-center justify-between">
                        <div className="flex items-center gap-3">
                            <Button type="button" variant="secondary" size="sm" onClick={() => setFiltersOpen((v) => !v)} aria-expanded={filtersOpen}>
                                {__('incomes.filters.toggle')}
                            </Button>
                            <Toggle
                                pressed={convertEnabled}
                                onPressedChange={(v) => setConvertEnabled(Boolean(v))}
                                aria-label={__('incomes.filters.convert')}
                            >
                                {__('incomes.filters.convert')}
                            </Toggle>
                            {convertEnabled && (
                                <div className="flex items-center gap-2">
                                    <span className="text-sm">{__('incomes.filters.display_currency')}</span>
                                    <Select value={convertCurrency} onValueChange={setConvertCurrency}>
                                        <SelectTrigger id="display_currency">
                                            <SelectValue />
                                        </SelectTrigger>
                                        <SelectContent>
                                            {pageProps.currencies.data.map((c) => (
                                                <SelectItem value={c.value} key={c.value}>
                                                    {c.value}
                                                </SelectItem>
                                            ))}
                                        </SelectContent>
                                    </Select>
                                </div>
                            )}
                        </div>
                        <div className="flex items-center gap-3">
                            <Button type="button" title={__('incomes.actions.add')} onClick={openCreate} className="whitespace-nowrap">
                                <PlusIcon className="size-4" /> {__('incomes.actions.add')}
                            </Button>
                        </div>
                    </div>

                    {filtersOpen && (
                        <div className="grid grid-cols-2 gap-3 sm:grid-cols-4">
                            {/* Month */}
                            <label className="flex flex-col gap-1">
                                <span className="text-sm">{__('incomes.filters.month')}</span>
                                <Select value={filters.month} onValueChange={(v) => setFilters((f) => ({ ...f, month: v }))}>
                                    <SelectTrigger id="filter_month">
                                        <SelectValue />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="all">{__('incomes.filters.all')}</SelectItem>
                                        {Array.from({ length: 12 }).map((_, idx) => {
                                            const v = `${idx + 1}`.padStart(2, '0');
                                            return (
                                                <SelectItem value={v} key={v}>
                                                    {v}
                                                </SelectItem>
                                            );
                                        })}
                                    </SelectContent>
                                </Select>
                            </label>

                            {/* Year */}
                            <label className="flex flex-col gap-1">
                                <span className="text-sm">{__('incomes.filters.year')}</span>
                                <Select value={filters.year} onValueChange={(v) => setFilters((f) => ({ ...f, year: v }))}>
                                    <SelectTrigger id="filter_year">
                                        <SelectValue />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="all">{__('incomes.filters.all')}</SelectItem>
                                        {years.map((y) => (
                                            <SelectItem value={String(y)} key={y}>
                                                {y}
                                            </SelectItem>
                                        ))}
                                    </SelectContent>
                                </Select>
                            </label>

                            {/* Type */}
                            <label className="flex flex-col gap-1">
                                <span className="text-sm">{__('incomes.filters.type')}</span>
                                <Select value={filters.type} onValueChange={(v) => setFilters((f) => ({ ...f, type: v }))}>
                                    <SelectTrigger id="filter_type">
                                        <SelectValue />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="all">{__('incomes.filters.all')}</SelectItem>
                                        {pageProps.incomeTypes.data.map((type) => (
                                            <SelectItem value={type.id} key={type.id}>
                                                {type.name}
                                            </SelectItem>
                                        ))}
                                    </SelectContent>
                                </Select>
                            </label>

                            {/* Currency */}
                            <label className="flex flex-col gap-1">
                                <span className="text-sm">{__('incomes.filters.currency')}</span>
                                <Select value={filters.currency} onValueChange={(v) => setFilters((f) => ({ ...f, currency: v }))}>
                                    <SelectTrigger id="filter_currency">
                                        <SelectValue />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="all">{__('incomes.filters.all')}</SelectItem>
                                        {pageProps.currencies.data.map((c) => (
                                            <SelectItem value={c.value} key={c.value}>
                                                {c.value}
                                            </SelectItem>
                                        ))}
                                    </SelectContent>
                                </Select>
                            </label>
                        </div>
                    )}
                </div>

                <Deferred fallback={<TableSkeleton columns={columns} />} data="incomes">
                    <DataTable
                        columns={columns}
                        data={pageProps.incomes}
                        emptyText={__('incomes.table.empty')}
                        perPage={{
                            value: pageProps.paging.perPage,
                            options: pageProps.paging.options,
                            onChange: (v) => router.visit(route('incomes.index', { perPage: v, page: 1 }), { preserveScroll: true, replace: true }),
                        }}
                    />
                </Deferred>
            </div>

            {/* Drawer: render only when modal needs to be opened */}
            {hasModal && <IncomeDrawer open={open} />}
        </AppLayout>
    );
}
