<template>
<div class="content">

    <img src="@images/logo.png" alt="Logo">

    <p>{{ $t('welcome_to_dev') }}</p>

    <p>{{ $t('welcome_description_1') }}</p>

    <p>{{ $t('welcome_description_2') }}</p>

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