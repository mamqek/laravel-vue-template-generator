import { defineStore } from 'pinia'
import { $axios } from '@plugins/axios.js'
import { notify } from "@kyvg/vue3-notification";


// Grab <html lang="..."> value
const htmlLang = document.documentElement.lang || 'lol';

export const useLanguageStore = defineStore('lang', {
    state: () => ({
        languages: null, // Fetched from server
        language: htmlLang,
    }),

    persist: true,

    actions: {
        setup() {
            this.fetch_languages();
        },

        fetch_languages() {
            $axios.get('translations')
            .then(({data}) => {
                this.languages = data.languages;
            })
        },

        set_language(langCode) {
            this.language = langCode;
            $axios.post(`translations/${langCode}`)
        },
    },
});
