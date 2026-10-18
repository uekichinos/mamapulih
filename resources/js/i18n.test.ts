import { describe, expect, it } from 'vitest';
import { i18n } from './i18n';

describe('i18n', () => {
    it('defaults to English', () => {
        expect(i18n.global.locale.value).toBe('en');
    });

    it('resolves the common.appName key in both locales', () => {
        expect(i18n.global.t('common.appName', {}, { locale: 'en' })).toBe('MamaPulih');
        expect(i18n.global.t('common.appName', {}, { locale: 'ms' })).toBe('MamaPulih');
    });

    it('translates common.language differently per locale', () => {
        expect(i18n.global.t('common.language', {}, { locale: 'en' })).toBe('Language');
        expect(i18n.global.t('common.language', {}, { locale: 'ms' })).toBe('Bahasa');
    });
});
