<?php

return [
    'title' => 'Incomes',
    'description' => 'Manage your income entries. Add salaries, bonuses, and other earnings. Filter by period, type, and currency.',
    'details' => 'Income details',
    'details_title' => 'Income details',

    'actions' => [
        'add' => 'Add income',
        'save' => 'Save',
        'cancel' => 'Cancel',
        'discard' => 'Discard changes',
        'edit' => 'Edit',
        'delete' => 'Delete',
        'view' => 'View',
    ],

    'filters' => [
        'month' => 'Month',
        'year' => 'Year',
        'type' => 'Type',
        'currency' => 'Currency',
        'all' => 'All',
        'toggle' => 'Filters',
        'convert' => 'Convert all amounts',
        'display_currency' => 'Display currency',
    ],

    'types' => [
        'salary' => 'Salary',
        'bonus' => 'Bonus',
        'other' => 'Other',
    ],

    'table' => [
        'date' => 'Date',
        'name' => 'Name',
        'description' => 'Description',
        'type' => 'Type',
        'amount' => 'Amount',
        'currency' => 'Currency',
        'actions' => 'Actions',
        'empty' => 'No incomes for the selected filters. Add your first income to get started.',
    ],

    'form' => [
        'name' => 'Name',
        'name_placeholder' => 'e.g. Monthly salary',
        'tags' => 'Tags',
        'tags_placeholder' => 'Type and press Enter to add a tag',
        'amount' => 'Amount',
        'description' => 'Description (optional)',
        'description_placeholder' => 'e.g. Monthly salary for January 2020',
        'occurred_on' => 'Date',
        'income_type_key' => 'Income type',
        'currency_code' => 'Currency',
        'notes' => 'Notes',
        'add_type' => 'Add new type',
        'add_type_title' => 'Add income type',
        'add_type_desc' => 'Create a custom income type to reuse later.',
        'new_type_label' => 'Type name',
        'new_type_placeholder' => 'e.g. Royalty, Freelance, Gift',
    ],
    'confirm' => [
        'delete_title' => 'Delete income',
        'delete_description' => 'Are you sure you want to delete this income? This action cannot be undone.',
        'discard_title' => 'Discard changes?',
        'discard_description' => 'You have unsaved changes. Do you want to discard them?',
        'delete_type_title' => 'Delete income type',
        'delete_type_description' => 'Are you sure you want to delete this custom type? Affected entries will switch to Salary.',
    ],
    'original_value' => 'Original: :value',

    // Toast messages
    'toasts' => [
        'created' => 'New income created.',
        'updated' => 'Income updated.',
        'deleted' => 'Income ":name" is deleted.',
        'delete_failed' => 'Failed to delete income.',
        'delete_forbidden' => 'You are not allowed to delete this income.',
    ],
];
