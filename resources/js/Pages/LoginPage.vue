<script setup>
import PrimaryButton from '@/Components/PrimaryButton.vue'
import { Head, useForm } from '@inertiajs/vue3'
import { ref } from 'vue';
import poslogo from '@/assets/4blogo.jpg';

defineProps({
    canResetStatus: {
        type: Boolean,
    },
    status: {
        type: String,
    },
    
});

const form = useForm({
    email: '',
    password: '',
    remember: false,
});

const submitLogin = ( ) => {
    form.post(route('login.request'), {
        onFinish: () => {
            form.reset('password');
        },
    });
}


</script>
<template>
    <Head title="login" />
    <main>
        <div id="login-main-container">
            <div id="login-pane">
                <div id="login-logo">
                    <img :src="poslogo" />
                </div>
                <div id="login-inputs">
                    <div>
                        Username or Email <br>
                        <input v-model="form.email" @keyup.enter="submitLogin" type="text" />
                    </div>
                    <div>
                        Password<br>
                        <input v-model="form.password" @keyup.enter="submitLogin" type="password" />
                    </div>
                    <!--<div id="remember-me">
                        <br><input v-model="form.remember" id="remember-me-cb"  type="checkbox" />
                        <label for="remember-me-cb"> Remember Me</label>
                    </div>-->
                    <div id="auth-fail-msg" align="center" v-if="form.errors.email || form.errors.password">
                        Invalid Username or Password!
                    </div>
                    <div>
                        <PrimaryButton @click="submitLogin">
                            Login 
                        </PrimaryButton>
                    </div>
                </div><br>
                <i style="font-size:11px;">Powered by Autosave systems (c) 2023 {{ form.errors }}</i>
                
            </div>
            
        </div>
        
    </main>
</template>

<style scoped>
    #login-main-container {
        position:absolute;
        width:100%;
        height:100%;
        top:0;
        bottom: 0;
    }

    #login-pane {
        position: relative;
        top:10%;
        left: 35%;
        width:30%;
        border:1px solid #ccc;
        padding : 1%;
    }

    #login-logo {
        position: relative;
        width:30%;
        height: 30%;
        left: 35%;
    }

    #login-inputs div#remember-me > label,  #login-inputs div#remember-me > input{
        cursor: pointer;
    }

    #login-inputs div:not(#remember-me) {
        padding-top:3%;
        width:100%;
    }
    #login-inputs div:not(#remember-me) input {
        width: 100%;
    }

    #auth-fail-msg {
        position:relative;
        background-color: rgba(255,0,0,0.2);
        margin-top:3%;
        padding:2%;
        color:red;
    }
</style>