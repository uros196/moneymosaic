import InputError from '@/components/input-error';
import { DateInput } from '@/components/ui/date-input';
import { Drawer, DrawerClose, DrawerContent, DrawerDescription, DrawerFooter, DrawerHeader, DrawerTitle } from '@/components/ui/drawer';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Select, SelectContent, SelectItem, SelectTrigger } from '@/components/ui/select';
import { TagInput } from '@/components/ui/tag-input';
import { Textarea } from '@/components/ui/textarea';
import { Form, router, usePage, useRemember, Link } from '@inertiajs/react';
import { useI18n } from '@/i18n/index.js';
import { useEffect, useRef, useState } from 'react';
import ConfirmDialog from '@/components/ui/confirm-dialog';
import { Button } from '@/components/ui/button';
import SelectWithCreate from '@/components/ui/select-with-create.jsx';
import { HoverCard, HoverCardTrigger, HoverCardContent } from '@/components/ui/hover-card';
import { Info } from 'lucide-react';

export default function IncomeDrawer({ open }) {
    const { __ } = useI18n();
    const props = usePage().props;

    // Define a ref for the form to be able to access its methods and state
    const formRef = useRef(null);

    // define props and adjust them for later use
    const incomeData = props.income?.data ?? {};
    const tagSuggestions = props.tagSuggestions.data.map((t) => t.name);
    const defaultCurrency = props.user.data.default_currency_code;

    // define states for the drawer
    const [discardOpen, setDiscardOpen] = useState(false);
    const [isCreating, setIsCreating] = useState(false);
    const [pendingNav, setPendingNav] = useState(null);

    // define states for the custom form fields
    const [currencyCode, setCurrencyCode] = useState(incomeData.currency_code ?? defaultCurrency);
    const [amountStep, setAmountStep] = useState();

    // track changes of currency code and dynamically gets the step
    useEffect(() => {
        const currency = props.currencies.data.filter((c) => c.value === currencyCode)[0];
        setAmountStep(currency.step);
    }, [currencyCode]);

    // Updates the isCreating state based on the modal type, setting it to true when the type is 'create'
    useEffect(() => { setIsCreating(props.modal.type === 'create') }, [props.modal.type]);

    // read the query params remembered by the useRemember hook on the income index page
    const [indexQuery] = useRemember({}, 'incomes.indexQuery');

    /**
     * Redirects to the income index page while preserving scroll position and state.
     * Uses 'router.visit' with specific options to ensure smooth navigation.
     */
    function incomeIndexRedirect() {
        router.visit(route('incomes.index', { ...indexQuery }), {
            preserveScroll: true,
            preserveState: true,
            replace: true,
            only: ['income', 'incomeTypes', 'flash', 'filters'],
        });
    }

    /**
     * Leave the page by either visiting a URL or running a provided callback.
     * If the form has unsaved changes, open a confirmation dialog first.
     * @param {string | Function} target - URL to visit or a callback to execute
     */
    function leavePage(target) {
        // Watch the form dirty state
        if (formRef.current.isDirty) {
            // If the target is a function, wrap it to store as a state value, otherwise store the URL string
            setPendingNav((typeof target === 'function'
                ? () => target
                : target
            ));
            setDiscardOpen(true);
        } else {
            if (typeof target === 'function') {
                target();
            } else if (target) {
                router.visit(target, { preserveScroll: true });
            }
        }
    }

    return (
        <>
            <Drawer
                open={open}
                onOpenChange={() => {
                    // Use the unified leavePage helper so closing respects dirty state and confirmation
                    leavePage(() => incomeIndexRedirect());
                }}
            >
                <DrawerContent>
                    <DrawerHeader>
                        <DrawerTitle>{isCreating ? __('incomes.actions.add') : __('incomes.actions.edit')}</DrawerTitle>
                        <DrawerDescription>{__('incomes.description')}</DrawerDescription>
                    </DrawerHeader>

                    <Form
                        className="flex min-h-0 flex-1 flex-col"
                        ref={formRef}
                        method={props.modal.method}
                        action={props.modal.action}
                        options={{
                            preserveScroll: true,
                            // in case of errors, preserve the state of the form fields and do not render the whole page
                            preserveState: (page) => Boolean(page?.props?.errors),
                            only: ['flash'],
                        }}
                        onSuccess={() => {
                            // if the form was successful, redirect to the income index page (require full page render)
                            // if the edit mode is on, return it to the exact previous page
                            router.visit(route('incomes.index', isCreating ? {} : {...indexQuery}), { preserveScroll: true });
                        }}
                    >
                        {({ processing, errors }) => (
                            <>
                                <div className="grid content-start gap-4 px-6 py-4 flex-1 overflow-y-auto min-h-0">
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
                                        <div className="flex items-center gap-1">
                                            <Label htmlFor="income_type_id">{__('incomes.form.income_type_key')}</Label>
                                            <HoverCard>
                                                <HoverCardTrigger asChild>
                                                    <button type="button" className="text-muted-foreground hover:text-foreground" aria-label={__('incomes.form.manage_types_hint')}>
                                                        <Info className="size-4" />
                                                    </button>
                                                </HoverCardTrigger>
                                                <HoverCardContent>
                                                    <div className="text-sm text-muted-foreground">
                                                        <p>{__('incomes.form.manage_types_hint')}</p>
                                                        <p className="mt-1">
                                                            <Link
                                                                href={route('settings.lists.income-types')}
                                                                onClick={(e) => { e.preventDefault(); leavePage(route('settings.lists.income-types')); }}
                                                                className="underline decoration-neutral-300 underline-offset-4 transition-colors duration-300 ease-out hover:decoration-current! dark:decoration-neutral-500"
                                                            >
                                                                {__('incomes.form.manage_types_link')}
                                                            </Link>
                                                        </p>
                                                    </div>
                                                </HoverCardContent>
                                            </HoverCard>
                                        </div>
                                        <SelectWithCreate
                                            name="income_type_id"
                                            items={props.incomeTypes.data}
                                            defaultValue={incomeData.income_type_id}
                                            createAction={route('settings.lists.income-types.store')}
                                            labels={{
                                                addOption: __('incomes.form.add_type'),
                                                modalTitle: __('incomes.form.add_type_title'),
                                                modalDescription: __('incomes.form.add_type_desc'),
                                                inputPlaceholder: __('incomes.form.new_type_placeholder'),
                                                cancel: __('incomes.actions.cancel'),
                                                save: __('incomes.actions.save'),
                                            }}
                                            selectProps={{ id: 'income_type_id', 'aria-invalid': !!errors.income_type_id }}
                                        />
                                        <InputError message={errors.income_type_id} />
                                    </div>

                                    <div className="grid grid-cols-2 gap-3 items-start">
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
                                                    {currencyCode}
                                                </SelectTrigger>
                                                <SelectContent>
                                                    {props.currencies.data.map((currency) => (
                                                        <SelectItem value={currency.value} key={currency.value}>
                                                            {currency.display_name}
                                                        </SelectItem>
                                                    ))}
                                                </SelectContent>
                                            </Select>

                                            <InputError message={errors.currency_code} />
                                        </div>
                                    </div>

                                    <div className="grid gap-2">
                                        <Label htmlFor="tags_input">{__('incomes.form.tags')}</Label>
                                        <TagInput
                                            name="tags"
                                            defaultValue={incomeData.tags_list ?? []}
                                            suggestions={tagSuggestions}
                                            placeholder={__('incomes.form.tags_placeholder')}
                                        />
                                        <InputError errors={errors} prefix="tags." />
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
                                        <DrawerClose>
                                            <Button type="button" variant="secondary">
                                                {__('incomes.actions.cancel')}
                                            </Button>
                                        </DrawerClose>
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


            {/* Confirm discard changes */}
            <ConfirmDialog
                open={discardOpen}
                onOpenChange={setDiscardOpen}
                title={__('incomes.confirm.discard_title')}
                description={__('incomes.confirm.discard_description')}
                confirmText={__('incomes.actions.discard')}
                cancelText={__('incomes.actions.cancel')}
                onConfirm={() => {
                    typeof pendingNav === 'function'
                        ? pendingNav()
                        : router.visit(pendingNav, { preserveScroll: true });

                    setPendingNav(null);
                }}
            />
        </>
    );
}
