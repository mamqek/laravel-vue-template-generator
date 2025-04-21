<template>
<div class="content">
    <img src="../../../../public/images/logo.png" alt="Logo">
    <h1>{{ $t('unauthorized') }}</h1>
    <h2 v-if="reason == 'authentication'">{{ $t('unauthorized_auth') }}</h2>
    <h2 v-if="reason == 'role'">{{ $t('unauthorized_role') }}</h2>
</div>
</template>

<script>
export default {
    name: 'Unauthorized',

    computed: {
        reason() {
            return this.$route.query.reason;
        }
  }
}
</script>

<style scoped>
.content {
    text-align: center;
}

img {
    width: 100px;
}

</style>