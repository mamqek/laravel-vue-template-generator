
import { createI18n } from 'vue-i18n';
import { $axios } from './axios.js'
import { useUserStore } from '../stores/userStore.js';
import { notify } from "@kyvg/vue3-notification";

// Grab <html lang="..."> value
const htmlLang = document.documentElement.lang || 'en';

const i18n = createI18n({
  locale: htmlLang,
  fallbackLocale: 'en',
  messages: {},
});

export async function initLanguage(){
    try {        
        loadMessages(i18n.global.fallbackLocale);

        const store = useUserStore();

        // if language is not saved in the store
        if (!store.language) {
            let defaultLang = i18n.global.locale;
            await changeLanguage(defaultLang);
            return
        }

        await changeLanguage(store.language, store.translations);

        checkForUpdate(store.language, store.translations);
    } catch (error) {
        console.error('Error initializing language:', error);
        notify({
            type: "error",
            title: "Language settings error",
            text: "Could not initialize saved language",
        });
    }
}

export async function loadMessages(locale) {
    try {
        // Check if the translations for this locale are already loaded
        let translations = i18n.global.messages[locale];
        if (!translations) {       
            ({data : translations} = await $axios.get(`/translations/${locale}`));
            i18n.global.setLocaleMessage(locale, translations);
        } else {
            // If translations are already loaded, fetch them in the background to check for updates
            checkForUpdate(locale, translations);
        }

        return translations;
    } catch (error) {
        console.error(`Failed to load messages for locale ${locale}:`, error);
        throw error;
    }
}

export async function changeLanguage(locale, translations = null){
    try {
        if (translations) {
            i18n.global.setLocaleMessage(locale, translations);
        } else {
            translations = await loadMessages(locale);
        }

        i18n.global.locale = locale;
        useUserStore().setLanguage(locale, translations);
        
        $axios.post(`/translations/${locale}`, {
            locale : locale
        })
        .catch((error) => {
            console.error(`Could not change backend locale:`, error);
        })

    } catch (error) {
        console.error(`Failed to change language to ${locale}:`, error);
        notify({
            type: "error",
            title: "Language settings error",
            text: "Could not change the language",
        });
    }
} 

function checkForUpdate(locale, translations){
    $axios.get(`/translations/${locale}`)
    .then(({ data: fetchedTranslations }) => {                            
        if (JSON.stringify(translations) !== JSON.stringify(fetchedTranslations)) {
            i18n.global.setLocaleMessage(locale, fetchedTranslations);
            useUserStore().setLanguage(locale, fetchedTranslations);
        }      
    })
    .catch(error => {
        console.error(`Failed to fetch updated messages for locale ${locale}:`, error);
    });
}


export default i18n;
export const { t } = i18n.global;
