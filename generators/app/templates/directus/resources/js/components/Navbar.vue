<template>
    <!-- Navbar -->
    <div
        class=" fixed top-0 inset-x-0 z-51 flex justify-between items-center bg-transparent backdrop-blur-[3px] h-20 lg:h-24 md:px-8 bg-gradient-to-b from-black/50 via-black/30 to-transparent"
    >
        <!-- Logo as home link -->
        <router-link to="/" class="flex-shrink-0 focus:outline-none ">
            <img src="@images/logo.png" alt="AXAL Logo" class="h-10 md:h-12" />
        </router-link>

        <!-- nav links -->
        <nav class="hidden lg:flex gap-10 font-normal">

        </nav>

        <div class="relative flex md:gap-4 items-center">
            <!-- menu button also above backdrop -->
            <lottie-player
                ref="menuPlayer"
                src="/images/icons/animated/menuV4.json"
                background="transparent"
                speed="1"
                class="size-14 filter invert brightness-0 cursor-pointer relative z-80"
            />
        </div>
    </div>

    <!-- Sidebar + Backdrop -->
    <div class="fixed inset-0 z-50 pointer-events-none ">
        <!-- Backdrop -->
        <div
            v-show="open"
            @click="closeOnOutsideClick && closeSidebar()"
            class="absolute inset-x-0 top-0 bottom-0 bg-black/50 transition-opacity duration-300 pointer-events-auto"
            :class="open ? 'opacity-100 backdrop-blur-[3px]' : 'opacity-0'"
        ></div>

        <!-- Sliding Sidebar -->
        <aside
            @click.stop
            class="absolute right-0 top-0 h-full w-full lg:w-80 bg-black/90 text-white transform transition-transform duration-300 ease-in-out z-60 pointer-events-auto"
            :class="open ? 'translate-x-0' : 'translate-x-full'"
        >
            <div class="h-full flex flex-col justify-center gap-8 px-8">
                <router-link  to="/" class="text-3xl hover:underline">Home</router-link >
                <router-link  v-if="!userStore.authenticated" to="/auth" class="text-3xl hover:underline">Login</router-link >
                <button v-if="userStore.authenticated" @click="userStore.logout" class="text-3xl hover:underline">Logout</button>
            </div>

            <!-- <Select
                v-model="currentLanguage"
                class="absolute bottom-4 left-8 text-sm"
                label="Language"
                :options="availableLanguages"
                value-key="code"
                display-key="name"
                @change="onChanged"
            ></Select> -->

        </aside>
    </div>
</template>

<script>
import { useLanguageStore } from '@/stores/languageStore.js';
import { useUserStore } from '@/stores/userStore.js';
import { computed } from 'vue';

export default {
    name: 'Navbar',
    data() {
        return {
            open: false,
        };
    },
    props: {
        closeOnOutsideClick: { type: Boolean, default: true }
    },
    setup() {
        const languageStore = useLanguageStore();
        const userStore = useUserStore();
        const currentLanguage = computed(() => languageStore.language);
        const availableLanguages = computed(() => languageStore.languages);
        const onChanged = (value) => {
            languageStore.set_language(value);
        };

        return {
            currentLanguage,
            availableLanguages,
            onChanged,
            userStore
        };
    },  
    watch: {
        // open(state) {
        //     if (state) {
        //         window.lenis?.stop();
        //     } else {
        //         window.lenis?.start();
        //     }
        // },
        // whenever the route changes, close the sidebar
        $route() {
            this.closeSidebar();
        }
    },
    methods: {
        // Close sidebar menu
        closeSidebar() {
            if (!this.open) return;
            const player = this.$refs.menuPlayer;
            player.setDirection(-1);
            player.play();
            this.open = false;
        },
    },
    mounted() {
        const player = this.$refs.menuPlayer;
        player.addEventListener('click', () => {
            player.setDirection(this.open ? -1 : 1);
            player.play();
            this.open = !this.open;
        });
    }
};
</script>

<style scoped>
.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.3s ease, transform 0.3s ease;
}

.fade-enter-from,
.fade-leave-to {
  opacity: 0;
  transform: translateY(-5px);
}

.fade-enter-to,
.fade-leave-from {
  opacity: 1;
  transform: translateY(0);
}
</style>
