import HeadingSmall from '@/components/heading-small';
import { useLocaleRefreshOnly } from '@/components/language-switcher';
import TableSkeleton from '@/components/skeletons/table-skeleton.jsx';
import { Button } from '@/components/ui/button';
import DataTable from '@/components/ui/data-table';
import { Badge } from '@/components/ui/badge';
import CurrencyConversion from '@/components/ui/currency-conversion';
import { Tooltip, TooltipTrigger, TooltipContent } from '@/components/ui/tooltip';
import { useI18n } from '@/i18n';
import AppLayout from '@/layouts/app-layout';
import { Deferred, Head, router, usePage, useRemember } from '@inertiajs/react';
import { PlusIcon } from 'lucide-react';
import { TableActionsMenu, ViewAction, EditAction, DeleteAction } from '@/components/ui/table-actions';
import { useEffect, useMemo, useState } from 'react';
import { toast } from 'react-hot-toast';
import IncomeDrawer from './income-drawer';
import TagsOverflow from '@/components/ui/tags-overflow';
import FilterSheet from '@/components/ui/filter-sheet';
import { getQueryObject } from '@/lib/url-query';
import { Separator } from '@/components/ui/separator.jsx';

export default function IncomesIndex() {
    const { __ } = useI18n();

    // After a user changes language using a LanguageSwitcher, refresh additional props
    useLocaleRefreshOnly(['incomeTypes', 'currencies', 'filters']);

    // Conversion UI state (display-only)
    const pageProps = usePage().props || {};
    const hasModal = Boolean(pageProps.modal);

    // Updates the indexQuery state when the URL changes.
    // This effect ensures the stored query parameters stay in sync with the URL.
    // It runs whenever the page URL changes, updating the remembered query state.
    const [indexQuery, setIndexQuery] = useRemember(getQueryObject(), 'incomes.indexQuery');
    useEffect(() => { setIndexQuery(getQueryObject()) }, [usePage().url]);

    // Modal state
    const [open, setOpen] = useState(false);

    // Open the drawer based on modal props (URL-driven)
    useEffect(() => { setOpen(hasModal) }, [pageProps.modal]);

    /**
     * Opens the income creation drawer by navigating to the 'create' route
     * while preserving scroll position and state
     */
    function openCreate() {
        router.visit(route('incomes.create'), {
            preserveScroll: true,
            preserveState: true,
            only: ['tagSuggestions'],
        });
    }

    /**
     * Opens the income editing drawer for a specific income item
     * @param {Object} item - The income item to edit
     */
    function openEdit(item) {
        router.visit(route('incomes.edit', item.id), {
            preserveScroll: true,
            preserveState: true,
            only: ['income', 'tagSuggestions'],
        });
    }

    /**
     * Deletes an income entry with the specified ID
     * Shows error toast on failure and reloads data on success
     * @param {number} id - The ID of the income to delete
     */
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

    // Define breadcrumbs
    const breadcrumbs = [{ title: __('incomes.title'), href: route('incomes.index') }];

    // Define incomes table columns
    const columns = useMemo(
        () => [
            { id: 'occurred_on', header: __('incomes.table.date'), accessor: (r) => r.occurred_on_display },
            { id: 'description', header: __('incomes.table.name'), accessor: (r) => r.name || r.description },
            { id: 'income_type', header: __('incomes.table.type'), accessor: (r) => r.income_type.name },
            {
                id: 'amount',
                header: __('incomes.table.amount'),
                className: 'text-right',
                cell: (row) => {
                    if (row.converted_amount) {
                        return (
                            <div className="flex flex-col items-end gap-0.5">
                                <div className="flex items-center gap-2">
                                    <div className="font-medium">{row.converted_amount}</div>
                                    <Badge variant="secondary" title="Converted">FX</Badge>
                                </div>
                                <div className="text-xs text-muted-foreground">{row.amount_formatted}</div>
                            </div>
                        );
                    }
                    return <span className="font-medium">{row.amount_formatted}</span>;
                },
            },
            {
                id: 'currency_code',
                header: __('incomes.table.currency'),
                cell: (r) => (
                    <Tooltip>
                        <TooltipTrigger>
                            <span className="underline decoration-dotted underline-offset-2">{r.currency.value}</span>
                        </TooltipTrigger>
                        <TooltipContent>{r.currency.label}</TooltipContent>
                    </Tooltip>
                ),
            },
            { id: 'tags', header: __('incomes.table.tags'), className: 'max-w-[240px]', cell: (r) => <TagsOverflow tags={r.tags_list ?? []} /> },
            {
                id: 'actions',
                header: __('incomes.table.actions'),
                cell: (income) => (
                    <div className="flex items-center justify-end">
                        <TableActionsMenu label={__('incomes.table.actions')}>
                            <ViewAction inMenu href={route('incomes.show', income.id)} label={__('incomes.actions.view')} />
                            <EditAction inMenu onClick={() => openEdit(income)} label={__('incomes.actions.edit')} />
                            <Separator className="my-1" />
                            <DeleteAction
                                inMenu
                                label={__('incomes.actions.delete')}
                                confirmTitle={__('incomes.confirm.delete_title')}
                                confirmDescription={__('incomes.confirm.delete_description')}
                                onConfirm={() => performDelete(income.id)}
                            />
                        </TableActionsMenu>
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
                            <FilterSheet
                                title={__('incomes.filters.toggle')}
                                tooltip={__('incomes.filters.toggle')}
                                filters={pageProps.filters ?? []}
                                onlyKeys={['incomes']}
                            />
                            <CurrencyConversion
                                routeName="incomes.index"
                                currencies={pageProps.currencies.data}
                                defaultCurrency={pageProps.user.data.default_currency_code}
                                onlyKeys={['incomes']}
                                labels={{ toggle: __('incomes.filters.convert'), select: __('incomes.filters.display_currency') }}
                            />
                        </div>
                        <div className="flex items-center gap-3">
                            <Button type="button" title={__('incomes.actions.add')} onClick={openCreate} className="whitespace-nowrap">
                                <PlusIcon className="size-4" /> {__('incomes.actions.add')}
                            </Button>
                        </div>
                    </div>

                    <FilterSheet.Chips chips={pageProps.filterChips ?? []} onlyKeys={['incomes']} />
                </div>

                <Deferred fallback={<TableSkeleton columns={columns} />} data="incomes">
                    <DataTable
                        columns={columns}
                        data={pageProps.incomes}
                        emptyText={__('incomes.table.empty')}
                    />
                </Deferred>
            </div>

            {/* Drawer: render only when modal needs to be opened */}
            {hasModal && <IncomeDrawer open={open} />}
        </AppLayout>
    );
}
