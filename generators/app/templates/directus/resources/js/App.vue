<template>
    <div id="layout" class="relative overflow-hidden">
        <Navbar  />

        <!-- Main app content -->
        <router-view v-slot="{ Component, route }">
            <!-- Fade for all routes and no animation for direct /shop -->
            <transition :name="route.name === 'Shop' ? 'fade' : 'fade'" mode="out-in">
                <div
                    v-if="Component && !providedLoading"
                    :key="route.fullPath"
                    class="size-full relative z-10 flex flex-col gap-20 md:gap-30 px-2 lg:px-8 mt-nav-sm lg:mt-nav "
                >
                    <component  :is="Component" />

                </div>
                <div v-else  class="h-svh">
                    <div class=" absolute top-1/2 left-1/2 transform -translate-y-1/2 -translate-x-1/2 py-16 text-center text-5xl text-neutral-400  motion-safe:animate-pulse">
                        Loading <span class="-ml-1 inline-block align-baseline motion-safe:animate-[dots_1.2s_steps(4,end)_infinite]">…</span>
                    </div>
                </div>
            </transition>
        </router-view>
    </div>

    <!-- Notifications -->
    <notifications
        position="bottom center"
        :duration="5000"
        :max="5"
        :pauseOnHover="true"
    >
        <template #body="props">
            <div :class="['notify', props.item?.type]" @click="props.close">
                <div class="notification-content">
                    <div class="notification-title">{{ props.item.title }}</div>
                    <div class="notification-content">{{ props.item.text }}</div>
                </div>
            </div>
        </template>
    </notifications>

</template>

<script>
import { useUserStore } from '@/stores/userStore';
import { mapState } from 'pinia';
import Navbar from '@/components/Navbar.vue';
import { computed, provide } from 'vue'
import { useRoute } from 'vue-router'
import { useDynamicPageStore } from '@/stores/dynamicPageStore'

export default {
    name: 'App',
    components: { Navbar },
    computed: {
        ...mapState(useUserStore, ['authenticated']),
    },
    setup() {
        const route = useRoute()
        const store = useDynamicPageStore()
        const providedPage    = computed(() => store.getByPath(route.fullPath))
        const providedLoading = computed(() => store.isLoading(route.fullPath))
        const providedError   = computed(() => store.errorFor(route.fullPath))

        provide('dynamicPage', providedPage)
        provide('dynamicPageLoading', providedLoading)
        provide('dynamicPageError', providedError)
        return { providedPage, providedLoading, providedError }
    },

    methods: {
        useUserStore,
    },
};
</script>

<style>

/* Fade for all routes */
.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.4s ease;
}
.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}

/* Slide-down for overlay only */
.slide-down-enter-active,
.slide-down-leave-active {
  transition: transform 0.4s ease, opacity 0.4s ease;
}
.slide-down-enter-from {
  transform: translateY(-100%);
  opacity: 0;
}
.slide-down-enter-to {
  transform: translateY(0);
  opacity: 1;
}
.slide-down-leave-from {
  transform: translateY(0);
  opacity: 1;
}
.slide-down-leave-to {
  transform: translateY(-100%);
  opacity: 0;
}
@keyframes dots { 0% { clip-path: inset(0 0 0 0) } 100% { clip-path: inset(0 0 0 100%) } }

</style>
