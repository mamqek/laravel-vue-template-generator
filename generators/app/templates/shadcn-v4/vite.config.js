import { defineConfig } from 'vite'

import vue from '@vitejs/plugin-vue'
import laravel from 'laravel-vite-plugin';
import { fileURLToPath, URL } from 'node:url'
import tailwind from '@tailwindcss/vite'

// https://vite.dev/config/
export default defineConfig({
    plugins: [
        vue(),
        laravel({
            // for files which are loaded on every page (css), other page specific are in welcome.blade (but if removed from blade are not recognized, ask someone)
            input: ["resources/js/app.js"],
            refresh: true,
        }),
        tailwind(),  
    ],
    resolve: {
        alias: {
            vue: "vue/dist/vue.esm-bundler.js",
            '@shadcn-vue-components': fileURLToPath(new URL('./resources/js/ui/shadcn-vue/ui', import.meta.url)), // Alias for Vue
            '@': fileURLToPath(new URL('./resources/js', import.meta.url)), // Alias for Vue
            '@plugins': fileURLToPath(new URL('./resources/js/plugins', import.meta.url)), // Alias for Vue
            '@images': fileURLToPath(new URL('./public/images', import.meta.url)), // Alias for Vue
        },
    },
})
