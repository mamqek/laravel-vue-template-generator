<template>   

    <!-- Main navbar -->
    <nav class="main-nav">

        <router-link to="/" class="logo-link">
            <img class="logo" src="@images/logo.png" alt="Logo">
        </router-link>
        
        <div class="select-container" v-if="languages">
            <select class="lang-select" v-model="language" @change="changeLanguage(language)">
                <option v-for="lang in languages" :value="lang">{{ $t(`${lang}`) }}</option>
            </select>
        </div>

        <div class="page-links">
            <router-link v-if="!authenticated" to="/auth" id="authorize">{{ $t('sign_in') }}</router-link>
            <a @click="useUserStore().logout()" v-if="authenticated">{{ $t('logout') }}</a>
        </div>

    </nav>

    <!-- Loading point for the router -->
    <router-view v-slot="{ Component, route }">
        <div :key="route.name" id="layout">
            <Component :is="Component" />
        </div>
    </router-view>

    <!-- To make notifications available -->
    <!-- used template to add icon  -->
    <notifications
        position="bottom center"
        :duration="5000"
        :max="5"
        :pauseOnHover="true"
    >
        <template #body="props">
            <div :class="['notify', props.item?.type]" @click="props.close">
                <div class="notification-content">
                    <div class='notification-title'>
                        {{ props.item.title }}
                    </div>
                    <div class='notification-content'>
                        {{ props.item.text }}
                    </div>
                </div>
            </div>
        </template>
    </notifications>

</template>

<script>
import { useUserStore } from '@/stores/userStore';
import { mapState } from "pinia";
import i18n, {changeLanguage} from "@plugins/lang.js"


export default {
    name: 'App',

    data() {
        return {
            language: i18n.global.locale,
            languages: null,
        }
    },

    computed: {
        ...mapState(useUserStore, ['authenticated']),
    },

    created() {
        this.$axios.get('translations')
        .then(({data}) => {
            this.languages = data;
        })
    },

    methods: {
        changeLanguage,
        useUserStore,
    }
}
</script>
