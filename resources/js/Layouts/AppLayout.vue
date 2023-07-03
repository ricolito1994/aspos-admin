<script setup>
import TopNav from "@/Components/TopNav.vue";
import SideNav from "@/Components/SideNav.vue";
</script>
<template>
    <div id="body-container">
        <TopNav></TopNav>
        <div id="app-container">
            <SideNav></SideNav>
            <div id="main-container">
                <div class="container-pane">
                    <slot />
                </div>
            </div>
            <div style="clear:both"></div>
        </div>
    </div>
</template>
<style scoped>
    #body-container {
        position:absolute;
        height:100%;
        width: 100%;
    }
    #app-container {
        position: relative;
        width:100%;
        height:94%;
    }
    #main-container {
        position: relative;
        float:right;
        height: 100%;
        width: 85%;
        padding: 1%;
    }

    .container-pane {
        background-color:#fff;
        border: 1px solid #ccc;
        height:100%;
        padding:1%;
    }
</style>