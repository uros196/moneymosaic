import HeadingSmall from '@/components/heading-small';
import InputError from '@/components/input-error';
import { Button } from '@/components/ui/button';
import { useConfirmDelete } from '@/components/ui/confirm-delete-provider.jsx';
import { Input } from '@/components/ui/input';
import { useI18n } from '@/i18n';
import AppLayout from '@/layouts/app-layout';
import SettingsLayout from '@/layouts/settings/layout';
import TextLink from '@/components/text-link';
import { Form, Head, Link, router } from '@inertiajs/react';
import { useEffect, useRef, useState } from 'react';

/**
 * Main page component: shows create form and a list of existing income types.
 */
export default function IncomeTypesPage({ incomeTypes = [] }) {
    const { __ } = useI18n();
    const breadcrumbs = [
        { title: __('settings.lists.title'), href: route('settings.lists') },
        { title: __('settings.lists.income_types_title'), href: route('settings.lists.income-types') },
    ];

    const [newName, setNewName] = useState('');

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title={__('settings.lists.income_types_title')} />

            <SettingsLayout>
                <div className="space-y-8">
                    <div className="space-y-4">
                        <div className="flex items-start justify-between gap-4">
                            <HeadingSmall title={__('settings.lists.income_types_title')} description={__('settings.lists.income_types_desc')} />
                            <TextLink href={route('settings.lists')} className="mt-1 text-sm">{__('common.back_to_lists')}</TextLink>
                        </div>

                        {/* Create new */}
                        <Form
                            method="post"
                            action={route('settings.lists.income-types.store')}
                            className="flex flex-col gap-2 sm:flex-row sm:items-start"
                            options={{ preserveScroll: true }}
                            onSubmitComplete={(form) => {
                                form.reset();
                                setNewName('');
                            }}
                        >
                            {({ processing, errors, isDirty }) => (
                                <>
                                    <div className="grid gap-2 max-w-xs">
                                        <Input
                                            name="name"
                                            defaultValue={newName}
                                            placeholder={__('settings.lists.name_placeholder')}
                                            aria-invalid={!!errors.name}
                                        />
                                        <InputError message={errors.name} />
                                    </div>
                                    <div className="flex items-center gap-2">
                                        <Button type="submit" disabled={processing || !isDirty} isLoading={processing}>
                                            {__('common.add_new')}
                                        </Button>
                                    </div>
                                </>
                            )}
                        </Form>

                        {/* Existing items */}
                        <div className="mt-6 rounded-lg border">
                            <div className="divide-y">
                                {incomeTypes.data?.length === 0 ? (
                                    <div className="p-4 text-sm text-muted-foreground">{__('common.nothing_here_yet')}</div>
                                ) : (
                                    incomeTypes.data.map((t) => <TypeRow key={t.id} type={t} />)
                                )}
                            </div>
                        </div>
                    </div>
                </div>
            </SettingsLayout>
        </AppLayout>
    );
}

// Single row for an income type with inline edit and delete actions.
function TypeRow({ type }) {
    const { __ } = useI18n();
    const [editing, setEditing] = useState(false);
    const [value, setValue] = useState(type.name);

    const inputRef = useRef(null);

    useEffect(() => {
        if (editing) {
            inputRef.current?.focus();
        }
    }, [editing]);

    // Whether the type is in use by any incomes (disables delete and shows a hint).
    const isLinked = (type.incomes_count ?? 0) > 0;

    const { openConfirmDelete } = useConfirmDelete();

    // Open a shared confirmation modal before deleting a type.
    function confirmDelete(type) {
        openConfirmDelete({
            title: __('settings.lists.delete_type_title'),
            description: __('settings.lists.delete_type_description'),
            confirmText: __('common.confirm_delete'),
            onConfirm: () => performDelete(type.id),
        });
    }

    // Perform the actual delete request via Inertia router.
    function performDelete(id) {
        router.delete(route('settings.lists.income-types.destroy', id), { preserveScroll: true });
    }

    // Exit edit mode and sync the local value after a successful save.
    function finishEditing(name) {
        setEditing(false);
        setValue(name);
    }

    return (
        <div className="flex flex-col gap-3 p-4 sm:flex-row sm:items-start sm:justify-between">
            <div className="min-w-0">
                {editing ? (
                    <Form
                        method="put"
                        action={route('settings.lists.income-types.update', type.id)}
                        className="flex flex-col gap-2 sm:flex-row sm:items-start"
                        options={{ preserveScroll: true }}
                        onSuccess={() => finishEditing(inputRef.current.value)}
                    >
                        {({ processing, errors, isDirty }) => (
                            <>
                                <div className="grid gap-2 max-w-xs">
                                    <Input name="name" defaultValue={value} aria-invalid={!!errors.name} ref={inputRef} />
                                    <InputError message={errors.name} />
                                </div>
                                <div className="flex items-center gap-2">
                                    <Button type="submit" disabled={processing || !isDirty} isLoading={processing}>
                                        {__('common.save')}
                                    </Button>
                                    <Button type="button" variant="secondary" onClick={() => finishEditing(type.name)}>
                                        {__('common.cancel')}
                                    </Button>
                                </div>
                            </>
                        )}
                    </Form>
                ) : (
                    <div className="flex items-center gap-2">
                        <span className="truncate font-medium" title={type.name}>
                            {type.name}
                        </span>
                        {type.is_system && (
                            <span className="rounded-full bg-neutral-500/10 px-2 py-0.5 text-xs text-neutral-600 dark:text-neutral-400">
                                {__('common.system')}
                            </span>
                        )}
                        {isLinked && (
                            <span className="rounded-full bg-amber-500/10 px-2 py-0.5 text-xs text-amber-700 dark:text-amber-400">
                                {__('settings.lists.used_by_incomes', { count: type.incomes_count })}
                            </span>
                        )}
                    </div>
                )}

                {isLinked && (
                    <p className="mt-1 text-xs text-muted-foreground">
                        {!type.is_system && (
                            __('settings.lists.delete_disabled_hint')
                        )}
                        <Link
                            href={route('incomes.index') + `?type=${type.id}`}
                            className="underline decoration-neutral-300 underline-offset-4 transition-colors duration-300 ease-out hover:decoration-current! dark:decoration-neutral-500"
                        >
                            {__('settings.lists.view_incomes_with_type')}
                        </Link>
                    </p>
                )}
            </div>

            <div className="flex shrink-0 items-center gap-2">
                {!editing && !type.is_system && (
                    <Button size="sm" variant="secondary" onClick={() => setEditing(true)} disabled={type.is_system}>
                        {__('common.edit')}
                    </Button>
                )}
                {!type.is_system && (
                    <Button
                        size="sm"
                        variant="destructive"
                        disabled={isLinked || type.is_system}
                        title={isLinked ? __('settings.lists.cannot_delete_reason') : undefined}
                        onClick={() => confirmDelete(type)}
                    >
                        {__('common.delete')}
                    </Button>
                )}
            </div>
        </div>
    );
}
