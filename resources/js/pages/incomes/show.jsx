import HeadingSmall from '@/components/heading-small';
import { useI18n } from '@/i18n';
import AppLayout from '@/layouts/app-layout';
import { Head, Link, usePage } from '@inertiajs/react';

function formatAmountMajor(major, currency) {
    try {
        const n = Number(major ?? 0);
        return new Intl.NumberFormat(undefined, { style: 'currency', currency }).format(n);
    } catch (_) {
        return `${major ?? '0'} ${currency}`;
    }
}

export default function IncomeShow() {
    const { __ } = useI18n();
    const { props } = usePage();
    const item = props.income?.data ?? {};

    const hasTags = (item.tags_list ?? []).length > 0;

    const breadcrumbs = [
        { title: __('incomes.title'), href: route('incomes.index') },
        { title: __('incomes.details') ?? 'Details', href: route('incomes.show', item.id) },
    ];

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title={`${__('incomes.title')} · #${item.id}`} />

            <div className="space-y-6">
                <HeadingSmall title={__('incomes.details_title') ?? __('incomes.title')} description={__('incomes.description')} />

                <div className="rounded-lg border">
                    <div className="grid gap-0 divide-y">
                        <div className="flex items-center justify-between px-4 py-3">
                            <span className="text-sm text-muted-foreground">{__('incomes.table.date')}</span>
                            <span className="text-sm font-medium">{item.occurred_on_display}</span>
                        </div>
                        <div className="flex items-center justify-between px-4 py-3">
                            <span className="text-sm text-muted-foreground">{__('incomes.table.name')}</span>
                            <span className="text-sm font-medium">{item.name || '—'}</span>
                        </div>
                        <div className="flex items-center justify-between px-4 py-3">
                            <span className="text-sm text-muted-foreground">{__('incomes.table.type')}</span>
                            <span className="text-sm font-medium">{item.income_type?.name ?? '—'}</span>
                        </div>
                        <div className="flex items-center justify-between px-4 py-3">
                            <span className="text-sm text-muted-foreground">{__('incomes.table.amount')}</span>
                            <span className="text-sm font-medium">{formatAmountMajor(item.amount, item.currency_code)}</span>
                        </div>
                        <div className="flex items-center justify-between px-4 py-3">
                            <span className="text-sm text-muted-foreground">{__('incomes.table.currency')}</span>
                            <span className="text-sm font-medium">{item.currency_code}</span>
                        </div>
                        <div className="px-4 py-3">
                            <div className="mb-1 text-sm text-muted-foreground">{__('incomes.form.tags')}</div>
                            <div className="text-sm flex flex-wrap gap-2">
                                {hasTags ? (
                                    (item.tags_list).map((t, idx) => (
                                        <span key={idx} className="inline-flex items-center rounded-md border px-2 py-0.5 text-xs">
                                            {t}
                                        </span>
                                    ))
                                ) : (
                                    <span className="text-muted-foreground">—</span>
                                )}
                            </div>
                        </div>
                        <div className="px-4 py-3">
                            <div className="mb-1 text-sm text-muted-foreground">{__('incomes.table.description')}</div>
                            <div className="text-sm whitespace-pre-line">{item.description || '—'}</div>
                        </div>
                    </div>
                </div>

                <div className="flex items-center gap-3">
                    <Link href={route('incomes.index')} className="inline-flex items-center rounded-md border px-3 py-2 hover:bg-accent">
                        {__('common.back') ?? 'Back'}
                    </Link>
                    <Link
                        href={route('incomes.edit', item.id)}
                        className="inline-flex items-center rounded-md bg-primary px-3 py-2 text-primary-foreground hover:bg-primary/90"
                    >
                        {__('incomes.actions.edit')}
                    </Link>
                </div>
            </div>
        </AppLayout>
    );
}
