<script setup>
import { 
    usePage, 
    Head, 
    Link 
} from '@inertiajs/vue3';
import { 
    onMounted, 
    onUnmounted, 
    ref, 
    reactive 
} from 'vue';
import AppLayout from '@/Layouts/AppLayout.vue';
import moment from 'moment';
const user = usePage().props.auth.user;
const props = defineProps({
    user: {
        type: Object,
    },
    company: {
        type: Object,
    },
});
const currentDate = moment().format('YYYY-MM-DD');
var fullTextDate = ref(moment().format("LLLL"));

localStorage.setItem('user', JSON.stringify(props.user));
localStorage.setItem('company', JSON.stringify(props.company));

onMounted (() => {
    setInterval(() => {
        fullTextDate.value = moment().format("LLLL");
    },1000)
})

</script>

<template>
    <Head title="Dashboard" />
    <AppLayout>
        <div style="width:100%;">
            <div style="font-size:30px;width:100%;float:left;">
                Hi! <b>{{ user.name }} </b>
            </div>
            <div style="font-size:15px;width:100%;float:right;">
                <b>{{ fullTextDate }}</b>
            </div>
        </div>
    </AppLayout>
</template>

<style>

</style>
