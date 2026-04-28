<?php
// ============================================================================
//  resources/lang/km/auth.php
//  Khmer translations for authentication pages
//  Font: Kh_Jrung_Thom for titles | KantumruyPro for body text
// ============================================================================

return [

    // ── Login page ───────────────────────────────────────────────────────────
    'login' => [
        'title'       => 'ចូលគណនីរបស់អ្នក',
        'description' => 'បញ្ចូលអ៊ីមែល និងលេខសំងាត់របស់អ្នកខាងក្រោមដើម្បីចូល',
        'emailLabel'  => 'អាសយដ្ឋានអ៊ីមែល',
        'emailPlaceholder' => 'email@example.com',
        'passwordLabel' => 'លេខសំងាត់',
        'passwordPlaceholder' => 'លេខសំងាត់',
        'rememberMe'  => 'ចងចាំខ្ញុំ',
        'forgotPassword' => 'ភ្លេចលេខសំងាត់?',
        'loginButton' => 'ចូល',
        'noAccount'   => 'មិនទាន់មានគណនី?',
        'signUp'      => 'ចុះឈ្មោះ',
    ],

    // ── Register page ────────────────────────────────────────────────────────
    'register' => [
        'title'       => 'បង្កើតគណនី',
        'description' => 'បញ្ចូលព័ត៌មានរបស់អ្នកខាងក្រោមដើម្បីបង្កើតគណនី',
        'nameLabel'   => 'ឈ្មោះ',
        'namePlaceholder' => 'ឈ្មោះពេញ',
        'emailLabel'  => 'អាសយដ្ឋានអ៊ីមែល',
        'emailPlaceholder' => 'email@example.com',
        'passwordLabel' => 'លេខសំងាត់',
        'passwordPlaceholder' => 'លេខសំងាត់',
        'confirmPasswordLabel' => 'បញ្ជាក់លេខសំងាត់',
        'confirmPasswordPlaceholder' => 'បញ្ជាក់លេខសំងាត់',
        'createAccountButton' => 'បង្កើតគណនី',
        'hasAccount'  => 'មានគណនីរួចហើយ?',
        'logIn'       => 'ចូល',
    ],

    // ── Forgot password page ─────────────────────────────────────────────────
    'forgotPassword' => [
        'title'       => 'ភ្លេចលេខសំងាត់',
        'description' => 'បញ្ចូលអ៊ីមែលរបស់អ្នកដើម្បីទទួលបានតំណកំណត់លេខសំងាត់ឡើងវិញ',
        'emailLabel'  => 'អាសយដ្ឋានអ៊ីមែល',
        'emailPlaceholder' => 'email@example.com',
        'sendButton'  => 'ផ្ញើតំណកំណត់លេខសំងាត់ឡើងវិញតាមអ៊ីមែល',
        'returnToLogin' => 'ឬ ត្រឡប់ទៅ',
        'logIn'       => 'ចូល',
    ],

    // ── Reset password page ──────────────────────────────────────────────────
    'resetPassword' => [
        'title'       => 'កំណត់លេខសំងាត់ឡើងវិញ',
        'description' => 'សូមបញ្ចូលលេខសំងាត់ថ្មីរបស់អ្នកខាងក្រោម',
        'emailLabel'  => 'អ៊ីមែល',
        'passwordLabel' => 'លេខសំងាត់',
        'passwordPlaceholder' => 'លេខសំងាត់',
        'confirmPasswordLabel' => 'បញ្ជាក់លេខសំងាត់',
        'confirmPasswordPlaceholder' => 'បញ្ជាក់លេខសំងាត់',
        'resetButton' => 'កំណត់លេខសំងាត់ឡើងវិញ',
    ],

    // ── Confirm password page ────────────────────────────────────────────────
    'confirmPassword' => [
        'title'       => 'បញ្ជាក់លេខសំងាត់របស់អ្នក',
        'description' => 'នេះជាផ្នែកមានសុវត្ថិភាពនៃកម្មវិធី។ សូមបញ្ជាក់លេខសំងាត់របស់អ្នកមុនពេលបន្ត។',
        'passwordLabel' => 'លេខសំងាត់',
        'passwordPlaceholder' => 'លេខសំងាត់',
        'confirmButton' => 'បញ្ជាក់លេខសំងាត់',
    ],

    // ── Verify email page ────────────────────────────────────────────────────
    'verifyEmail' => [
        'title'       => 'ផ្ទៀងផ្ទាត់អ៊ីមែល',
        'description' => 'សូមផ្ទៀងផ្ទាត់អាសយដ្ឋានអ៊ីមែលរបស់អ្នកដោយចុចលើតំណដែលយើងបានផ្ញើទៅកាន់អ៊ីមែលរបស់អ្នក។',
        'resendButton' => 'ផ្ញើសំណើផ្ទៀងផ្ទាត់ម្តងទៀត',
        'logout'      => 'ចេញពីប្រព័ន្ធ',
        'verificationSent' => 'តំណផ្ទៀងផ្ទាត់ថ្មីត្រូវបានផ្ញើទៅកាន់អាសយដ្ឋានអ៊ីមែលដែលអ្នកបានផ្តល់នៅពេលចុះឈ្មោះ។',
    ],

    // ── Two-factor challenge page ────────────────────────────────────────────
    'twoFactor' => [
        'authCodeTitle' => 'លេខកូដផ្ទៀងផ្ទាត់',
        'authCodeDesc' => 'បញ្ចូលលេខកូដផ្ទៀងផ្ទាត់ដែលផ្តល់ដោយកម្មវិធីផ្ទៀងផ្ទាត់របស់អ្នក។',
        'recoveryCodeTitle' => 'លេខកូដសង្គ្រោះ',
        'recoveryCodeDesc' => 'សូមបញ្ជាក់ការចូលប្រើគណនីរបស់អ្នកដោយបញ្ចូលលេខកូដសង្គ្រោះបន្ទាន់មួយក្នុងចំណោមលេខកូដរបស់អ្នក។',
        'authCodePlaceholder' => 'លេខកូដផ្ទៀងផ្ទាត់',
        'recoveryCodePlaceholder' => 'បញ្ចូលលេខកូដសង្គ្រោះ',
        'continueButton' => 'បន្ត',
        'useAuthCode' => 'ចូលដោយប្រើលេខកូដផ្ទៀងផ្ទាត់',
        'useRecoveryCode' => 'ចូលដោយប្រើលេខកូដសង្គ្រោះ',
        'or' => 'ឬ',
    ],

    // ── Common auth messages ─────────────────────────────────────────────────
    'common' => [
        'secureArea' => 'តំបន់មានសុវត្ថិភាព',
        'required' => 'ចាំបាច់',
        'loading' => 'កំពុងផ្ទុក...',
    ],

];
