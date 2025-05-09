import fa_lang from '@/i18n/fa_lang'

import { createI18n, useI18n } from 'vue-i18n'
import en_lang from "@/i18n/en_lang";

const messages = {
  fa: fa_lang,
  en: en_lang
}
let activeLanguageCode = localStorage.getItem('UI_LANG');
if(activeLanguageCode == null || activeLanguageCode == undefined){ 
  localStorage.setItem('UI_LANG','fa')
  activeLanguageCode='fa';
}
const i18n = createI18n({
  legacy: false,
  locale: activeLanguageCode,
  fallbackLocale: activeLanguageCode,
  messages,
});

export default i18n;
