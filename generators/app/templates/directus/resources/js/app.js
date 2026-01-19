import router from "@/router.js";

import { useUserStore } from './stores/userStore.js'
import { useDynamicPageStore } from '@/stores/dynamicPageStore'
import { useLanguageStore } from '@/stores/languageStore.js'

// Create the Vue app
import { createApp } from 'vue';
import App from "./App.vue";
const app = createApp(App);

// Axios
import { $axios } from '@plugins/axios.js'
app.config.globalProperties.$axios = $axios;

// Pinia Store
import pinia from '@plugins/pinia';
app.use(pinia)

// Notifications library
import Notifications from '@kyvg/vue3-notification'
app.use(Notifications)

// Utils plugin (directus images)
import utils from '@plugins/utils.js'
app.use(utils);


// Function to initialize the language before mounting the app
async function initializeApp() {
    useLanguageStore().setup();
    useUserStore().setup();
    // 👇 Hydrate the dynamic page store from the server-injected window vars
    const pageStore = useDynamicPageStore()
    pageStore.hydrateFromWindow()

    // Router
    app.use(router)

    // 👇 Install the path-prefetch guard AFTER pinia & router are ready
    router.beforeResolve( (to) => {
        try {
            pageStore.fetchByPath(to.fullPath)
        } catch {}
    })

    // Once the language is set, mount the app
    app.mount('#app');
}

// Start the initialization process
initializeApp();