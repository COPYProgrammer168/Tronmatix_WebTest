import { useEffect } from 'react';

/**
 * Hook to manage Khmer language styling
 * Applies lang="km" attribute to html element when Khmer language is active
 * This triggers the Khmer font styles defined in app.css
 */
export function useKhmerStyling(isKhmer: boolean) {
    useEffect(() => {
        const htmlElement = document.documentElement;

        if (isKhmer) {
            htmlElement.setAttribute('lang', 'km');
            htmlElement.style.setProperty('font-family', 'var(--font-khmer-body)');
        } else {
            htmlElement.removeAttribute('lang');
            htmlElement.style.removeProperty('font-family');
        }

        return () => {
            htmlElement.removeAttribute('lang');
            htmlElement.style.removeProperty('font-family');
        };
    }, [isKhmer]);

    return null;
}

/**
 * Helper function to check if current locale is Khmer
 */
export function isKhmerLocale(): boolean {
    if (typeof document === 'undefined') return false;

    const htmlLang = document.documentElement.getAttribute('lang');
    const bodyClass = document.body?.classList?.contains('km');

    // Check for various indicators of Khmer language
    return htmlLang === 'km' ||
           bodyClass ||
           document.cookie.includes('app_lang=km') ||
           localStorage.getItem('app_lang') === 'km';
}
