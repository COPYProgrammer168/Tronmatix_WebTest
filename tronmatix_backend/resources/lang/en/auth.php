<?php
// ============================================================================
//  resources/lang/en/auth.php
//  English translations for authentication pages
//  This is the fallback language; all keys must match km/auth.php
// ============================================================================

return [

    // ── Login page ───────────────────────────────────────────────────────────
    'login' => [
        'title'       => 'Log in to your account',
        'description' => 'Enter your email and password below to log in',
        'emailLabel'  => 'Email address',
        'emailPlaceholder' => 'email@example.com',
        'passwordLabel' => 'Password',
        'passwordPlaceholder' => 'Password',
        'rememberMe'  => 'Remember me',
        'forgotPassword' => 'Forgot password?',
        'loginButton' => 'Log in',
        'noAccount'   => "Don't have an account?",
        'signUp'      => 'Sign up',
    ],

    // ── Register page ────────────────────────────────────────────────────────
    'register' => [
        'title'       => 'Create an account',
        'description' => 'Enter your details below to create your account',
        'nameLabel'   => 'Name',
        'namePlaceholder' => 'Full name',
        'emailLabel'  => 'Email address',
        'emailPlaceholder' => 'email@example.com',
        'passwordLabel' => 'Password',
        'passwordPlaceholder' => 'Password',
        'confirmPasswordLabel' => 'Confirm password',
        'confirmPasswordPlaceholder' => 'Confirm password',
        'createAccountButton' => 'Create account',
        'hasAccount'  => 'Already have an account?',
        'logIn'       => 'Log in',
    ],

    // ── Forgot password page ─────────────────────────────────────────────────
    'forgotPassword' => [
        'title'       => 'Forgot password',
        'description' => 'Enter your email to receive a password reset link',
        'emailLabel'  => 'Email address',
        'emailPlaceholder' => 'email@example.com',
        'sendButton'  => 'Email password reset link',
        'returnToLogin' => 'Or, return to',
        'logIn'       => 'log in',
    ],

    // ── Reset password page ──────────────────────────────────────────────────
    'resetPassword' => [
        'title'       => 'Reset password',
        'description' => 'Please enter your new password below',
        'emailLabel'  => 'Email',
        'passwordLabel' => 'Password',
        'passwordPlaceholder' => 'Password',
        'confirmPasswordLabel' => 'Confirm password',
        'confirmPasswordPlaceholder' => 'Confirm password',
        'resetButton' => 'Reset password',
    ],

    // ── Confirm password page ────────────────────────────────────────────────
    'confirmPassword' => [
        'title'       => 'Confirm your password',
        'description' => 'This is a secure area of the application. Please confirm your password before continuing.',
        'passwordLabel' => 'Password',
        'passwordPlaceholder' => 'Password',
        'confirmButton' => 'Confirm password',
    ],

    // ── Verify email page ────────────────────────────────────────────────────
    'verifyEmail' => [
        'title'       => 'Verify email',
        'description' => 'Please verify your email address by clicking on the link we just emailed to you.',
        'resendButton' => 'Resend verification email',
        'logout'      => 'Log out',
        'verificationSent' => 'A new verification link has been sent to the email address you provided during registration.',
    ],

    // ── Two-factor challenge page ────────────────────────────────────────────
    'twoFactor' => [
        'authCodeTitle' => 'Authentication code',
        'authCodeDesc' => 'Enter the authentication code provided by your authenticator application.',
        'recoveryCodeTitle' => 'Recovery code',
        'recoveryCodeDesc' => 'Please confirm access to your account by entering one of your emergency recovery codes.',
        'authCodePlaceholder' => 'Authentication code',
        'recoveryCodePlaceholder' => 'Enter recovery code',
        'continueButton' => 'Continue',
        'useAuthCode' => 'login using an authentication code',
        'useRecoveryCode' => 'login using a recovery code',
        'or' => 'or',
    ],

    // ── Common auth messages ─────────────────────────────────────────────────
    'common' => [
        'secureArea' => 'Secure area',
        'required' => 'Required',
        'loading' => 'Loading...',
    ],

];
