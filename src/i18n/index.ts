import { ref, computed } from 'vue';
import en from './en';
import lv from './lv';

export type SupportedLocale = 'en' | 'lv';

const messages: Record<SupportedLocale, Record<string, unknown>> = { en, lv };

export const currentLocale = ref<SupportedLocale>('en');

export function setLocale(lang: SupportedLocale) {
  currentLocale.value = lang;
  if (typeof localStorage !== 'undefined') {
    localStorage.setItem('oxy_locale', lang);
  }
}

export function initLocale() {
  if (typeof localStorage !== 'undefined') {
    const saved = localStorage.getItem('oxy_locale') as SupportedLocale | null;
    if (saved && (saved === 'en' || saved === 'lv')) {
      currentLocale.value = saved;
    }
  }
}

initLocale();

export function t(key: string): string {
  const lang = currentLocale.value;
  const translationObj = messages[lang] || messages.en;

  const keys = key.split('.');
  let current: unknown = translationObj;
  for (const k of keys) {
    if (current && typeof current === 'object' && k in current) {
      current = (current as Record<string, unknown>)[k];
    } else {
      return key;
    }
  }

  return typeof current === 'string' ? current : key;
}

export const useI18n = () => {
  return {
    locale: computed(() => currentLocale.value),
    setLocale,
    t
  };
};

export default useI18n;
