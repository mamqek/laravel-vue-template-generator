<template>
    <div class="content container" id="container" :class="{ active: registerSide }">
        <div class="form-container sign-up">
            <form @submit.prevent="register">
                <h1>{{ $t('create_account') }}</h1>

                <input type="text" v-model="username" :placeholder="$t('how_call')">
                <span v-if="validationErrors.username" class="error">{{ validationErrors.username[0] }}</span>

                <input type="text" v-model="email" :placeholder="$t('your_*', {item: $t('email')} )">
                <span v-if="validationErrors.email" class="error">{{ validationErrors.email[0] }}</span>

                <input type="password" v-model="password" :placeholder="$t('password')">
                <span v-if="validationErrors.password" class="error">{{ validationErrors.password[0] }}</span>

                <input type="password" v-model="password_confirmation" :placeholder="$t('confirm_password')">
                <button>{{ $t('sign_up') }}</button>
            </form>
        </div>


        <div class="form-container sign-in">
            <form @submit.prevent="login">
                <h1>{{ $t('login') }}</h1>
                <input type="text" v-model="identifier" :placeholder="$t('username') +  $t('or') + $t('email')">
                <input type="password" v-model="password" :placeholder="$t('password')">
                <span v-if="validationErrors.login" class="error">{{ validationErrors.login }}</span>
                <a href="#">{{ $t('forget_password') }} WIP</a>
                <button>{{ $t('sign_in') }}</button>
            </form>
        </div>


        <div class="toggle-container">
            <div class="toggle">
                <div class="toggle-panel toggle-left">
                    <h1>{{ $t('already_have') }}</h1>
                    <button class="non-active" id="login" @click="registerSide = false">{{ $t('sign_in') }}</button>
                </div>
                <div class="toggle-panel toggle-right">
                    <h1>{{ $t('dont_have_an_account') }}</h1>
                    <button class="non-active" id="register" @click="registerSide = true">{{ $t('create_account') }}</button>
                </div>
            </div>
        </div>
    </div>

</template>
    
<script>
import { useUserStore } from '@/stores/userStore';

export default {
    name: 'Authorization',

    data(){
        return {
            identifier: "",
            username: "",
            email: "",
            password: "",
            password_confirmation: "",
            registerSide: false, 
            validationErrors: {}
        }
    },

    methods: {
        login() {
            this.$axios.post("/auth/login", {
                identifier: this.identifier,
                password: this.password
            })
            .then(({data}) => {
                useUserStore().authorize(data.user)
                this.$router.push('/');
            })
            .catch(error => {
                if (error.status == 401) {
                    this.validationErrors = {
                        login: error.message
                    };
                    return
                }
            })
        },

        register() {
            this.$axios.post("/auth/register", {
                username: this.username,
                email: this.email,
                password: this.password,
                password_confirmation: this.password_confirmation,
            })
            .then(({data}) => {
                this.registerSide = false;
            })
            .catch(error => {
                if (error.status == 422) {
                    this.validationErrors = error.message;
                    return
                }
            })
        },

        loginWithGoogle() {
            window.location.href = '/auth/google/redirect';
        }
    }
}
</script>

<style scoped>

.container{
    box-shadow: 0 5px 15px rgba(255, 255, 255, 0.35);
    position: relative;
    overflow: hidden;

    p {
        font-size: 14px;
        line-height: 20px;
        letter-spacing: 0.3px;
        margin: 20px 0;
    }

    span{
        font-size: 12px;
    }

    a {
        color: #333;
        font-size: 13px;
        text-decoration: none;
        margin: 15px 0 10px;
    }

    button {
        background-color: rgb(255, 13, 13);
        color: #fff;
        font-size: 12px;
        padding: 10px 45px;
        border: 1px solid transparent;
        border-radius: 8px;
        font-weight: 600;
        letter-spacing: 0.5px;
        text-transform: uppercase;
        margin-top: 10px;
        cursor: pointer;
    }

    button.non-active {
        background-color: transparent;
        border-color: #fff;
    }

    form {
        background-color: #fff;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-direction: column;
        padding: 0 40px;
        height: 100%;
    }

    input {
        background-color: #eee;
        border:none;
        margin: 8px 0;
        padding: 10px 15px;
        font-size: 13px;
        border-radius: 8px;
        width: 100%;
        outline: none;
    }
}

.form-container {
    position: absolute;
    top: 0;
    height: 100%;
    transition: all 0.6s ease-in-out;
}

.sign-in, .sign-up {
    left: 0;
    width: 50%;
}

/* base animation state */
.sign-in {
    z-index: 2;
    visibility: visible;
}

.sign-up {
    z-index: 1;
    visibility: hidden;
}

/* after animation */
.container.active {

    .sign-in {
        transform: translateX(100%);
        z-index: 1;
        visibility: hidden;
        animation: move 0.6s reverse;
    }

    .sign-up {
        transform: translateX(100%);
        z-index: 2;
        visibility: visible;
        animation: move 0.6s;
    }
}

/* animation */
@keyframes move {
    0%, 49.99%{
        z-index: 1;
    }
    50%, 100%{
        z-index: 2;
    }
}

.social-icons{
    margin: 20px 0;
}

.social-icons a{
    border: 1px solid #ccc;
    border-radius: 20%;
    display: inline-flex;
    justify-content: center;
    align-items: center;
    margin: 0 3px;
    width: 40px;
    height: 40px;
}

.toggle-container {
    position: absolute;
    top: 0;
    left: 50%;
    width: 50%;
    height: 100%;
    overflow: hidden;
    transition: all 0.6s ease-in-out;
    z-index: 2;
}

.toggle {
    background-color: var(--dark-background);
    height: 100%;
    color: #fff;
    position: relative;
    left: -100%;
    height: 100%;
    width: 200%;
    transform: translateX(0);
    transition: all 0.6s ease-in-out;
}

.toggle-panel {
    position: absolute;
    width: 50%;
    height: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-direction: column;
    padding: 0 30px;
    text-align: center;
    top: 0;
    transform: translateX(0);
    transition: all 0.6s ease-in-out;
}

.toggle-left{
    transform: translateX(-200%);
}

.toggle-right{
    right: 0;
    transform: translateX(0);
}

.container.active {

    .toggle-container {
        transform: translateX(-100%);
    }

    .toggle-left {
        transform: translateX(0);
    }

    .toggle {
        transform: translateX(50%);
    }
}

@media (max-width: 780px) {

    .sign-in, .sign-up {
        top: 0;
        width: 100%;
        height: 50%;
    }

    /* base animation state */
    .sign-in{
        z-index: 2;
        visibility: visible;
    }
        /* after animation */
    .container.active {

        .sign-in{
            transform: translateY(100%);
            z-index: 1;
            visibility: hidden;
            animation: move 0.6s reverse;
        }

        .sign-up{
            transform: translateY(100%);
            z-index: 2;
            visibility: visible;
            animation: move 0.6s;
        }
    }

    .toggle-container {
        position: absolute;
        top: 50%;
        left: 0;
        width: 100%;
        height: 50%;
        overflow: hidden;
        transition: all 0.6s ease-in-out;
        z-index: 2;
    }

    .toggle {
        background-color: var(--dark-background);
        color: #fff;
        position: relative;
        height: 200%;
        width: 100%;
        left: 0%;
        /* transform: translateY(0); */
        transition: all 0.6s ease-in-out;
    }

    .toggle-panel {
        position: absolute;
        width: 100%;
        height: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-direction: column;
        padding: 0 30px;
        text-align: center;
        top: 0;
        /* transform: translateY(0); */
        transition: all 0.6s ease-in-out;
    }

    .toggle-left {
        transform: translate(0, -200%);
    }

    .container.active {
        .toggle-container {
            transform: translateY(-100%);
        }

        .toggle {
            transform: translate(0);
        }

        .toggle-right {
            transform: translateY(100%);
            top: 0;
        }
    }
}

</style>