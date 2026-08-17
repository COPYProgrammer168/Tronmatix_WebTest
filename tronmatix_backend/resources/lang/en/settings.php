<?php
// ============================================================================
//  resources/lang/en/settings.php
//  English translations for settings pages
//  This is the fallback language; all keys must match km/settings.php
// ============================================================================

return [

    // ── Profile settings ─────────────────────────────────────────────────────
    'profile' => [
        'title'              => 'Profile settings',
        'heading'            => 'Profile information',
        'description'        => 'Update your name and email address',
        'nameLabel'          => 'Name',
        'namePlaceholder'    => 'Full name',
        'emailLabel'         => 'Email address',
        'emailPlaceholder'   => 'Email address',
        'saveButton'         => 'Save',
        'saved'              => 'Saved',
        'emailUnverified'    => 'Your email address is unverified.',
        'resendVerification' => 'Click here to resend the verification email.',
        'verificationSent'   => 'A new verification link has been sent to your email address.',
    ],

    // ── Password settings ────────────────────────────────────────────────────
    'password' => [
        'title'              => 'Password settings',
        'heading'            => 'Update password',
        'description'        => 'Ensure your account is using a long, random password to stay secure',
        'currentPasswordLabel' => 'Current password',
        'currentPasswordPlaceholder' => 'Current password',
        'newPasswordLabel'   => 'New password',
        'newPasswordPlaceholder' => 'New password',
        'confirmPasswordLabel' => 'Confirm password',
        'confirmPasswordPlaceholder' => 'Confirm password',
        'saveButton'         => 'Save password',
        'saved'              => 'Saved',
    ],

    // ── Two-factor authentication settings ───────────────────────────────────
    'twoFactor' => [
        'title'              => 'Two-factor authentication',
        'heading'            => 'Two-factor authentication',
        'description'        => 'Manage your two-factor authentication settings',
        'enabled'            => 'Enabled',
        'disabled'           => 'Disabled',
        'enabledDesc'        => 'When two-factor authentication is enabled, you will be prompted for a secure PIN during login, which you can retrieve from the TOTP-supported application on your phone.',
        'disabledDesc'       => 'When you enable two-factor authentication, you will be prompted for a secure PIN during login. This PIN can be retrieved from a TOTP-supported application on your phone.',
        'continueSetup'      => 'Continue setup',
        'enableButton'       => 'Enable 2FA',
        'disableButton'      => 'Disable 2FA',
        'recoveryCodesTitle' => 'Recovery codes',
        'recoveryCodesDesc'  => 'These recovery codes can be used once. Keep them in a secure place.',
        'regenerateCodes'    => 'Regenerate recovery codes',
        'regenerateWarning'  => 'Regenerating recovery codes will make old codes unusable.',
    ],

    // ── Appearance settings ──────────────────────────────────────────────────
    'appearance' => [
        'title'              => 'Appearance settings',
        'heading'            => 'Appearance settings',
        'description'        => 'Update your account appearance settings',
        'themeLabel'         => 'Theme',
        'light'              => 'Light',
        'dark'               => 'Dark',
        'system'             => 'System',
    ],

    // ── Delete account dialog ────────────────────────────────────────────────
    'delete' => [
        'title'              => 'Delete account',
        'heading'            => 'Delete your account',
        'description'        => 'Delete your account and all of its resources',
        'warning'            => 'Warning',
        'warningDesc'        => 'Please proceed with caution, this cannot be undone.',
        'confirmTitle'       => 'Are you sure you want to delete your account?',
        'confirmDesc'        => 'Once your account is deleted, all of its resources and data will also be permanently deleted. Please enter your password to confirm.',
        'passwordLabel'      => 'Password',
        'passwordPlaceholder' => 'Password',
        'cancelButton'       => 'Cancel',
        'deleteButton'       => 'Delete account',
    ],

    // ── Breadcrumbs ──────────────────────────────────────────────────────────
    'breadcrumbs' => [
        'profile'            => 'Profile settings',
        'password'           => 'Password settings',
        'twoFactor'          => 'Two-factor authentication',
        'appearance'         => 'Appearance settings',
    ],

    // ── Common settings ──────────────────────────────────────────────────────
    'common' => [
        'settings'           => 'Settings',
        'saveChanges'        => 'Save changes',
        'saved'              => 'Saved',
        'loading'            => 'Loading...',
    ],

];
