<template>
<div class="content">

    <img src="@images/logo.png" alt="Logo">

    <p>{{ $t('welcome_to_dev') }}</p>

    <p>{{ $t('welcome_description_1') }}</p>

    <p>{{ $t('welcome_description_2') }}</p>
</div>
</template>

<script>
export default {
    name: 'Home',
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