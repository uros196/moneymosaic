<?php

return [
    'title' => 'Settings',
    'description' => 'Manage your profile and account settings',

    'nav' => [
        'profile' => 'Profile settings',
        'password' => 'Password settings',
        'appearance' => 'Appearance settings',
        'lists' => 'Manage lists',
    ],

    'profile' => [
        'title' => 'Profile settings',
        'profile_information' => 'Profile information',
        'profile_information_desc' => 'Update your name and email address',

        'name' => 'Name',
        'full_name' => 'Full name',
        'email' => 'Email address',
        'language' => 'Language',
        'english' => 'English',
        'serbian' => 'Serbian',
        'default_currency' => 'Default currency',

        'require_password_after_inactivity' => 'Require password after inactivity',
        'require_password_hint' => 'You will be asked to confirm your password after being inactive for the selected time.',
        'inactivity_off' => 'Off',
        'inactivity_30' => 'After 30 minutes',
        'inactivity_60' => 'After 1 hour',
        'inactivity_240' => 'After 4 hours',
        'inactivity_600' => 'After 10 hours',

        'email_unverified' => 'Your email address is unverified.',
        'resend_verification' => 'Click here to resend the verification email.',
        'verification_link_sent' => 'A new verification link has been sent to your email address.',

        // Two-Factor section
        'two_factor' => 'Two-factor authentication',
        'two_factor_desc' => 'Add an extra layer of security to your account.',
        'status_enabled' => 'Enabled',
        'status_setting_up' => 'Setting up',
        'status_disabled' => 'Disabled',

        // Sessions card
        'sessions_card_title' => 'Active sessions',
        'sessions_card_desc' => 'Review and log out devices signed in to your account.',

        // Delete account
        'delete_account' => 'Delete account',
        'delete_account_desc' => 'Delete your account and all of its resources',
        'delete_warning' => 'Warning',
        'delete_warning_desc' => 'Please proceed with caution, this cannot be undone.',
        'delete_confirm_title' => 'Are you sure you want to delete your account?',
        'delete_confirm_desc' => 'Once your account is deleted, all of its resources and data will also be permanently deleted. Please enter your password to confirm you would like to permanently delete your account.',
    ],

    'appearance' => [
        'title' => 'Appearance settings',
        'description' => "Update your account's appearance settings",
    ],

    'password' => [
        'title' => 'Password settings',
        'update_password' => 'Update password',
        'update_password_desc' => 'Ensure your account is using a long, random password to stay secure',

        'current_password' => 'Current password',
        'new_password' => 'New password',
        'confirm_password' => 'Confirm password',

        'placeholder_current' => 'Current password',
        'placeholder_new' => 'New password',
        'placeholder_confirm' => 'Confirm password',

        'save_password' => 'Save password',
    ],

    'lists' => [
        'title' => 'Manage lists',
        'description' => 'Create, rename, and delete items used across the app.',

        'income_types_title' => 'Income types',
        'income_types_desc' => 'Add custom types for your incomes. You can rename them anytime.',
        'name_placeholder' => 'Enter type name',
        'used_by_incomes' => '{1} Used by :count income|[2,*] Used by :count incomes',
        'delete_disabled_hint' => 'Delete is disabled because this type is used.',
        'cannot_delete_reason' => 'This type is used by one or more incomes.',
        'view_incomes_with_type' => 'View incomes with this type',
        'delete_type_title' => 'Delete income type',
        'delete_type_description' => 'Are you sure you want to delete this custom type?',
    ],

    'sessions' => [
        'title' => 'Active sessions',
        'description' => 'Review devices currently signed in to your account.',
        'no_active' => 'No active sessions.',
        'unknown_ip' => 'Unknown IP',
        'current' => 'Current',
        'device_suffix' => ':label device',
        'last_active' => 'Last active: :time',
        'phone' => 'Phone',
        'tablet' => 'Tablet',
        'computer' => 'Computer',
    ],

    'security' => [
        'title' => 'Security settings',
        'two_factor' => 'Two-factor authentication',
        'two_factor_desc' => 'Add an extra layer of security to your account.',

        'enabled_using' => '2FA is enabled using :type.',

        'enabled_using_prefix' => '2FA is enabled using',

        'email_code_title' => 'Email code',
        'email_code_desc' => 'Receive a 6-digit code to your email address when signing in.',

        'totp_title' => 'Authenticator app (TOTP)',
        'totp_desc' => 'Use Google Authenticator or a compatible app to generate 6-digit codes.',

        'recovery_codes_title' => 'Save these recovery codes',
        'recovery_codes_desc' => 'Store these one-time codes in a safe place. Each code can be used once if you lose access to your authenticator app.',

        'tip_lost_access' => 'Tip: If you enable 2FA and lose access, you will need to contact support or use email 2FA to regain access.',

        'email_confirm_title' => 'Confirm Email 2FA',
        'email_confirm_desc' => "We've sent a 6-digit code to your email address. Enter it below to enable Email two-factor authentication.",
        'authentication_code' => 'Authentication code',

        'totp_setup_title' => 'Set up your authenticator app',
        'totp_scan_alt' => 'Scan this QR with your authenticator app',
        'enter_code_to_confirm' => 'Enter code to confirm',

        'begin_setup_desc' => 'Click the button below to begin setup and generate your QR code.',

        // Recovery codes actions
        'copy_codes' => 'Copy codes',
        'copied_recovery_codes' => 'Recovery codes copied',
        'copy_failed' => 'Failed to copy. Please try again.',
    ],
];
