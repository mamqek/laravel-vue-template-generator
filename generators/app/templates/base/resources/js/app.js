import router from "@/router.js";

import { useUserStore } from './stores/userStore.js'

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

// Language lbrary
import i18n, {initLanguage} from '@plugins/lang.js'
app.use(i18n);


// Function to initialize the language before mounting the app
async function initializeApp() {
    useUserStore().setup();

    await initLanguage();

    // Router
    app.use(router)

    // Once the language is set, mount the app
    app.mount('#app');
}

// Start the initialization process
initializeApp();