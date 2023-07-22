<template>
    <div id="loading-overlay" style="display: none;"></div>
</template>
<style scoped>
    #loading-overlay {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0, 0, 0, 0.5);
        z-index: 9999;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    #loading-overlay::after {
        content: "Loading...";
        color: white;
        font-size: 1.5rem;
    }
</style>