<script setup>
import NavLink from '@/Components/NavLink.vue';

const user = JSON.parse(localStorage.getItem('user'));

defineProps({
    company: {
        type: Object,
    },
    userLogin: {
        type: Object,
    },
});
</script>
<template>
    <div id="top-nav-bar"> 
        <div id="top-left-bar">
            <div align="center">
                <B>4B HARDWARE</B>
            </div>
        </div>
        <div id="top-right-bar">
            <div id="main-top-nav-bar">
               
            </div>
            <div id="secondary-top-nav-bar">
                <NavLink :href="route('dashboard')" :active="false">{{ user.name }}</NavLink>
                &nbsp;
                <NavLink :href="route('logout')" method="post" :active="false">LOGOUT</NavLink>
            </div>
            <div style="clear:both"></div>
        </div>
        <div style="clear:both"></div>
    </div>
</template>
<style scoped>
    #top-nav-bar {
        position:relative;
        width:100%;
        height:6%;
        border-bottom: 1px solid #ccc;
    }
    
    #top-left-bar {
        position:relative;
        float:left;
        width:15%;
        height:100%;
    }

    #top-right-bar {
        position:relative;
        float:right;
        width:85%;
        height:100%;
    }

    #top-right-bar > div , #top-left-bar > div {
        position:relative;
        top:25%;
    }

    #main-top-nav-bar {
        position:relative;
        float:left;
        width:80%;
    }

    #secondary-top-nav-bar {
        position:relative;
        float:right;
        width:20%;
    }
</style>