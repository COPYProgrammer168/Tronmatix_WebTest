/**
 * Translation helper for React components
 * Usage: import { t } from '@/lib/translations';
 * Then use: t('auth.login.title') or trans('auth.login.title')
 */

// Global locale state - updated by Laravel backend via SetLocale middleware
let currentLocale: string = 'en';

/**
 * Set the current locale (called by Laravel blade template)
 */
export function setLocale(locale: string): void {
    currentLocale = locale;
}

/**
 * Get the current locale
 */
export function getLocale(): string {
    return currentLocale;
}

/**
 * Translation registry - populated from Laravel backend
 * Add your translation files here
 */
const translations: Record<string, Record<string, any>> = {
    en: {},
    km: {},
};

/**
 * Register translations for a locale
 */
export function registerTranslations(locale: string, trans: Record<string, any>): void {
    translations[locale] = trans;
}

/**
 * Get a translation by key
 * @param key - Dot notation key (e.g., 'auth.login.title')
 * @param replacements - Optional key-value pairs for placeholder replacement
 * @returns Translated string or the key if not found
 */
export function t(key: string, replacements: Record<string, string> = {}): string {
    const keys = key.split('.');
    let result: any = translations[currentLocale];

    for (const k of keys) {
        if (result && typeof result === 'object' && k in result) {
            result = result[k];
        } else {
            // Fallback to English
            result = translations['en'];
            for (const fallbackKey of keys) {
                if (result && typeof result === 'object' && fallbackKey in result) {
                    result = result[fallbackKey];
                } else {
                    // Return key if not found in both locales
                    return key;
                }
            }
            break;
        }
    }

    // If result is not a string, return the key
    if (typeof result !== 'string') {
        return key;
    }

    // Replace placeholders
    let translated = result;
    for (const [placeholder, value] of Object.entries(replacements)) {
        translated = translated.replace(new RegExp(`:${placeholder}`, 'g'), value);
    }

    return translated;
}

/**
 * Alias for t() - Laravel-style translation
 */
export const trans = t;

/**
 * Check if current locale is Khmer
 */
export function isKhmer(): boolean {
    return currentLocale === 'km';
}

/**
 * Check if current locale is English
 */
export function isEnglish(): boolean {
    return currentLocale === 'en';
}
