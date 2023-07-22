<script setup>
import { ref, computed } from 'vue';
import { Link } from '@inertiajs/vue3';

const props = defineProps({
    isActive : {
        type: Boolean,
    },
    route : {
        type: String,
    },
    link : {
        type: String,
    }
})

const activeLink = computed(() => props.isActive ? 'active' : '');

</script>
<template>
    <Link :href="route" :class="activeLink">
        {{ link }}
    </Link>
</template>
<style scoped>
.active {
    background-color: white;
    color: #F05340;
}

</style>