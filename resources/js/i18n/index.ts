import { createI18n } from 'vue-i18n';
import es from './locales/es.json';
import en from './locales/en.json';

const locale = document.documentElement.lang || 'es';

const i18n = createI18n({
    legacy: false,
    locale,
    fallbackLocale: 'en',
    messages: { es, en },
});

export default i18n;
