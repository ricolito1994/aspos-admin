<script setup>
import { Link } from '@inertiajs/vue3';
import poslogo from '@/assets/4blogo.jpg';
</script>

<template>
    <div id="side-nav-container">
        <div align="center">
            <img :src="poslogo" width="100" />
        </div>
        <div>
            <ul>
                <li>Dashboard</li>
                <li>Products</li>
                <li>Transactions</li>
                <li>Settings</li>
            </ul>
        </div>
    </div>
</template>

<style scoped>      
    #side-nav-container {
        position:relative;
        float:left;
        width:15%;
        height:100%;
        background-color: #F05340;
        padding: 1%;
    }
</style>