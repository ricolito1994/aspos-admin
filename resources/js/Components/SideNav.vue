<script setup>
import { Link } from '@inertiajs/vue3';
import { ref } from 'vue';
import SideNavLink from './SideNavLink.vue';
import poslogo from '@/assets/4blogo.jpg';

const companyObject = JSON.parse(localStorage.getItem('company'))

const activePage = ref(route().current());

</script>

<template>
    <div id="side-nav-container">
        <div align="center">
            <img :src="poslogo" width="100" />
            <b style="color:white;">{{ companyObject.company_name }}</b>
        </div>
        <div id="side-menu-container">
            <ul>
                <li><SideNavLink :link="'Dashboard'"    :isActive="activePage == 'dashboard' || !activePage" :route="route('dashboard')"></SideNavLink></li>
                <li><SideNavLink :link="'Products'"     :isActive="activePage == 'products'" :route="route('products')"></SideNavLink></li>
                <li><SideNavLink :link="'Transactions'" :isActive="activePage == 'transactions'" :route="route('transactions')"></SideNavLink></li>
                <!--<li><SideNavLink :link="'Settings'"     :isActive="activePage == 'settings'" :route="route('settings')"></SideNavLink></li>-->
            </ul>
        </div>
    </div>
</template>

<style scoped>      
    #side-nav-container {
        position:relative;
        float:left;
        top:-50px;
        width:280px;
        height:100%;
        background-color: #F05340;
        padding: 1%;
    }

    #side-menu-container {
        position:relative;
        top:5%;
    }
    #side-menu-container li a {
        color:white;
        width: 100%;
        margin-bottom:2%;
        display: block;
        padding:1%;
    }

    #side-menu-container li a:hover, #side-menu-container li a.active {
        background-color: white;
        color: #F05340;
    }
</style>