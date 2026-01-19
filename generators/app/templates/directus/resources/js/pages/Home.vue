<template>
<div class="content">

    <img :src="$assetUrl(page.image)" alt="Logo">

    <p>{{ page.header }}</p>

    <p>{{ page.description }}</p>

    <p>{{ page.note }}</p>

    <br>
    <p class="font-bold">Shadcn-vue component import example:</p>
    <p>Download via: npx shadcn-vue@latest add switch</p>
    <p>From https://www.shadcn-vue.com/docs/components/switch.html</p>
    <Switch/>

</div>
</template>


<script>

import { Switch } from '@shadcn-vue-components/switch';

export default {
    name: 'Home',

    components: {
        Switch
    },

    inject: ['dynamicPage', 'dynamicPageLoading', 'dynamicPageError'],
    computed: {
        page()    { console.log(this.dynamicPage);
            return this.dynamicPage },
        loading() { return this.dynamicPageLoading },
        error()   { return this.dynamicPageError },
    }
}
</script>

<style scoped>

.content {
    text-align: center;
}

    img {
        width: 200px;
    }

</style>