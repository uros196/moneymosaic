import InputError from '@/components/input-error';
import { DateInput } from '@/components/ui/date-input';
import { Drawer, DrawerContent, DrawerDescription, DrawerFooter, DrawerHeader, DrawerTitle } from '@/components/ui/drawer';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Select, SelectContent, SelectItem, SelectSeparator, SelectTrigger, SelectValue } from '@/components/ui/select';
import { TagInput } from '@/components/ui/tag-input';
import { Textarea } from '@/components/ui/textarea';
import { Form, router, usePage } from '@inertiajs/react';
import { Plus } from 'lucide-react';
import { useI18n } from '@/i18n/index.js';
import { useEffect, useRef, useState } from 'react';
import ConfirmDialog from '@/components/ui/confirm-dialog';
import { Button } from '@/components/ui/button';
import { Dialog, DialogClose, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle } from '@/components/ui/dialog';

export default function IncomeDrawer({ open, setOpen }) {
    const { __ } = useI18n();
    const props = usePage().props;

    // define props and adjust them for later use
    const incomeData = props.income?.data ?? {};
    const tagSuggestions = props.tagSuggestions.data.map((t) => t.name);

    // define states for the drawer
    const [discardOpen, setDiscardOpen] = useState(false);
    const [addTypeOpen, setAddTypeOpen] = useState(false);
    const [isDirtyDrawer, setDirtyDrawer] = useState(false);

    // define states for the custom form fields
    const [incomeTypeId, setIncomeTypeId] = useState(incomeData.income_type_id ?? '');
    const [currencyCode, setCurrencyCode] = useState(incomeData.currency_code ?? '');
    const [tags, setTags] = useState(incomeData.tags?.map((t) => t.name) ?? []);
    const [amountStep, setAmountStep] = useState('0.01');

    // track changes of currency code and dynamically gets the step
    useEffect(() => {
        const currency = props.currencies.data.filter((c) => c.value === currencyCode)[0];
        setAmountStep(currency.step);
    }, [currencyCode]);

    // define form field references
    const newIncomeType = useRef(null);

    /**
     * Synchronizes the drawer's dirty state with the form's dirty state
     * @param {boolean} isDirty - Indicates if the form has unsaved changes
     * @returns {boolean} Always returns true to satisfy React's rendering requirements
     */
    function dirtySync(isDirty) {
        useEffect(() => { setDirtyDrawer(isDirty) }, [isDirty]);
        return true;
    }

    /**
     * Redirects to the income index page while preserving scroll position and state.
     * Uses 'router.visit' with specific options to ensure smooth navigation.
     */
    function incomeIndexRedirect() {
        router.visit(route('incomes.index'), { preserveScroll: true, preserveState: true, replace: true });
    }

    return (
        <>
            <Drawer
                open={open}
                onOpenChange={(v) => {
                    if (!v && isDirtyDrawer) {
                        setOpen(true);
                        return;
                    }
                    setOpen(v);
                    if (!v) {
                        incomeIndexRedirect();
                    }
                }}
            >
                <DrawerContent preventClose={isDirtyDrawer} showClose={!isDirtyDrawer}>
                    <DrawerHeader>
                        <DrawerTitle>{props.modal.type === 'create' ? __('incomes.actions.add') : __('incomes.actions.edit')}</DrawerTitle>
                        <DrawerDescription>{__('incomes.description')}</DrawerDescription>
                    </DrawerHeader>

                    <Form
                        method={props.modal.method}
                        action={props.modal.action}
                        options={{ preserveScroll: true }}
                        onSuccess={() => {
                            incomeIndexRedirect();
                        }}
                    >
                        {({ processing, errors, isDirty }) => (
                            <>
                                {(() => {
                                    return dirtySync(isDirty);
                                })()}

                                <div className="grid gap-3 px-6 py-4">
                                    <div className="grid gap-2">
                                        <Label htmlFor="name">{__('incomes.form.name')}</Label>
                                        <Input
                                            id="name"
                                            type="text"
                                            name="name"
                                            defaultValue={incomeData.name}
                                            placeholder={__('incomes.form.name_placeholder')}
                                            aria-invalid={!!errors.name}
                                        />
                                        <InputError message={errors.name} />
                                    </div>

                                    <div className="grid gap-2">
                                        <Label htmlFor="occurred_on">{__('incomes.form.occurred_on')}</Label>
                                        <DateInput
                                            id="occurred_on"
                                            name="occurred_on"
                                            value={incomeData.occurred_on}
                                            aria-invalid={!!errors.occurred_on}
                                        />
                                        <InputError message={errors.occurred_on} />
                                    </div>

                                    <div className="grid gap-2">
                                        <Label htmlFor="income_type_id">{__('incomes.form.income_type_key')}</Label>
                                        <input type="hidden" name="income_type_id" value={incomeTypeId} />
                                        <Select
                                            value={incomeTypeId}
                                            onValueChange={(v) => {
                                                if (v === '__add_type__') {
                                                    setAddTypeOpen(true);
                                                    return;
                                                }
                                                setIncomeTypeId(v);
                                            }}
                                        >
                                            <SelectTrigger id="income_type_id" aria-invalid={!!errors.income_type_id}>
                                                <SelectValue />
                                            </SelectTrigger>
                                            <SelectContent>
                                                {props.incomeTypes.data.map((type) => (
                                                    <SelectItem value={type.id} key={type.id}>
                                                        {type.name}
                                                    </SelectItem>
                                                ))}
                                                <SelectSeparator />
                                                <SelectItem value="__add_type__">
                                                    <span className="inline-flex items-center gap-2">
                                                        <Plus className="size-4" /> {__('incomes.form.add_type')}
                                                    </span>
                                                </SelectItem>
                                            </SelectContent>
                                        </Select>
                                        <InputError message={errors.income_type_id} />
                                    </div>

                                    <div className="grid grid-cols-2 gap-3">
                                        <div className="grid gap-2">
                                            <Label htmlFor="amount">{__('incomes.form.amount')}</Label>
                                            <Input
                                                id="amount"
                                                type="number"
                                                step={amountStep}
                                                min="0"
                                                name="amount"
                                                defaultValue={incomeData.amount}
                                                aria-invalid={!!errors.amount_minor}
                                            />
                                            <InputError message={errors.amount_minor} />
                                        </div>

                                        <div className="grid gap-2">
                                            <Label htmlFor="currency_code">{__('incomes.form.currency_code')}</Label>

                                            <input type="hidden" name="currency_code" value={currencyCode} />
                                            <Select value={currencyCode} onValueChange={setCurrencyCode}>
                                                <SelectTrigger id="currency_code" aria-invalid={!!errors.currency_code}>
                                                    <SelectValue />
                                                </SelectTrigger>
                                                <SelectContent>
                                                    {props.currencies.data.map((c) => (
                                                        <SelectItem value={c.value} key={c.value}>
                                                            {c.value}
                                                        </SelectItem>
                                                    ))}
                                                </SelectContent>
                                            </Select>

                                            <InputError message={errors.currency_code} />
                                        </div>
                                    </div>

                                    <div className="grid gap-2">
                                        {tags.map((t, idx) => (
                                            <input type="hidden" name="tags[]" value={t} key={`tag-${t}-${idx}`} />
                                        ))}

                                        <Label htmlFor="tags_input">{__('incomes.form.tags')}</Label>
                                        <TagInput
                                            value={tags}
                                            onChange={setTags}
                                            suggestions={tagSuggestions}
                                            placeholder={__('incomes.form.tags_placeholder')}
                                        />
                                        {Object.entries(errors)
                                            .filter(([field]) => field.startsWith('tags.'))
                                            .map(([field, msg], idx) => idx === 0 && <InputError message={msg} />)}
                                    </div>

                                    <div className="grid gap-2">
                                        <Label htmlFor="description">{__('incomes.form.description')}</Label>
                                        <Textarea
                                            id="description"
                                            name="description"
                                            rows={4}
                                            defaultValue={incomeData.description}
                                            placeholder={__('incomes.form.description_placeholder')}
                                            aria-invalid={!!errors.description}
                                        />
                                        <InputError message={errors.description} />
                                    </div>
                                </div>

                                <DrawerFooter>
                                    <div className="flex items-center justify-end gap-2">
                                        <Button
                                            type="button"
                                            variant="secondary"
                                            onClick={() => {
                                                isDirty ? setDiscardOpen(true) : incomeIndexRedirect();
                                            }}
                                        >
                                            {__('incomes.actions.cancel')}
                                        </Button>
                                        <Button disabled={processing} isLoading={processing}>
                                            {__('incomes.actions.save')}
                                        </Button>
                                    </div>
                                </DrawerFooter>
                            </>
                        )}
                    </Form>
                </DrawerContent>
            </Drawer>

            {/* Add Type Dialog */}
            <Dialog open={addTypeOpen} onOpenChange={setAddTypeOpen}>
                <DialogContent>
                    <DialogHeader>
                        <DialogTitle>{__('incomes.form.add_type_title')}</DialogTitle>
                        <DialogDescription>{__('incomes.form.add_type_desc')}</DialogDescription>
                    </DialogHeader>
                    <Form
                        method="post"
                        action={route('incomes.types.store')}
                        onError={() => newIncomeType.current?.focus()}
                        onSubmitComplete={(form) => {
                            form.reset();
                            setAddTypeOpen(false);
                        }}
                    >
                        {({ resetAndClearErrors, processing, errors }) => (
                            <>
                                <div className="grid gap-2">
                                    <Label htmlFor="new_type_name">{__('incomes.form.new_type_label')}</Label>
                                    <Input name="name" ref={newIncomeType} placeholder={__('incomes.form.new_type_placeholder')} />
                                    <InputError message={errors.name} />
                                </div>
                                <DialogFooter className="gap-2">
                                    <DialogClose asChild>
                                        <Button variant="secondary" onClick={() => resetAndClearErrors()}>
                                            {__('incomes.actions.cancel')}
                                        </Button>
                                    </DialogClose>
                                    <Button isLoading={processing} disabled={processing}>
                                        {__('incomes.actions.save')}
                                    </Button>
                                </DialogFooter>
                            </>
                        )}
                    </Form>
                </DialogContent>
            </Dialog>

            {/* Confirm discard changes */}
            <ConfirmDialog
                open={discardOpen}
                onOpenChange={setDiscardOpen}
                title={__('incomes.confirm.discard_title')}
                description={__('incomes.confirm.discard_description')}
                confirmText={__('incomes.actions.discard')}
                cancelText={__('incomes.actions.cancel')}
                onConfirm={() => {
                    incomeIndexRedirect();
                }}
            />
        </>
    );
}
