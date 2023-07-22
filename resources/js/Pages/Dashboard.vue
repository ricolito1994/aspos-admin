<script setup>
import { usePage, Head, Link } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
const user = usePage().props.auth.user;
const props = defineProps({
    user: {
        type: Object,
    },
    company: {
        type: Object,
    },
});

localStorage.setItem('user', JSON.stringify(props.user));
localStorage.setItem('company', JSON.stringify(props.company));

</script>

<template>
    <Head title="Dashboard" />
    <AppLayout>
        <div>
            DASHBOARD 
        </div>
    </AppLayout>
</template>

<style>

</style>
