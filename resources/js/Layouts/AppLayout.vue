<script setup>
import TopNav from "@/Components/TopNav.vue";
import SideNav from "@/Components/SideNav.vue";
import LoadingComponent from "@/Components/LoadingComponent.vue";
import AlertBox from "@/Components/AlertBox.vue";
import CustomerModal from "@/Components/CustomerModal.vue";
defineProps({
    catchChangeBranch : {
        type: Function,
    }
})

</script>
<template>
    <AlertBox />
    <CustomerModal />
    <LoadingComponent />
    <div id="body-container">
        <TopNav @catchChangeBranch="catchChangeBranch"></TopNav>
        <SideNav />
        <div id="app-container">
            <slot />
        </div>
    </div>
</template>
<style scoped>
    #body-container {
        position:absolute;
        height:100%;
        width: 100%;
        overflow:hidden;
    }
    #app-container {
        padding:1%;
        margin-left: 0;
        margin-right: 0;
        display: flex;
        flex-direction: column;
        justify-content: flex-start;
        flex-grow: 1;
        height:94.7%;
        max-height: 100%;
        border:1px solid #ccc;
        overflow-y:auto;
    }
    #main-container {
        position: relative;
        /*float:right;*/
        top: -20px;
        left: 280px;
        height: 105.5%;
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