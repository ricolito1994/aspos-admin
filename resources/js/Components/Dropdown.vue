<script setup>
import NavLink from '@/Components/NavLink.vue';
import { Link } from '@inertiajs/vue3';
import { onMounted, onUnmounted, ref } from 'vue';

const props = defineProps({
    options : {
        required:true,
    },
    displayDropdown : {
        type: Boolean,
    },
    onCloseDropDown : {
        type: Function,
    },
    dropDownMenuButton : {
        type: String,
    },
})


const dropdownMenuRef = ref(null);

onMounted(() => {
    document.addEventListener('click', handleClickOutside);
})

onUnmounted(() => {
    document.removeEventListener('click', handleClickOutside);
});

const options = ref(props.options);

const handleClickOutside = (event) => {
    if (props.dropDownMenuButton && props.dropDownMenuButton.contains(event.target)) {
        return;
    }
    props.onCloseDropDown()
    document.removeEventListener('click', handleClickOutside);
}

</script>
<template>
    <div class="dropdown-menu" ref="dropdownMenuRef">
        <div class="dropdown-item"  v-for="(option,index) in options" :key="index">
            <Link v-if="option.href" :href="option.href" :method="option.meth" :class="classes">{{ option.label }}</Link>
            <a v-else href="javascript:void(0)" @click="option.func(option)">{{ option.label }}</a>
        </div>
    </div>
</template>
<style scoped>
.dropdown {
  position: relative;
  display: inline-block;
}

.dropdown-toggle {
  cursor: pointer;
}

.dropdown-menu {
  position: absolute;
  width:300px;
  display: block;
  background-color: #fff;
  padding: 8px;
  border: 1px solid #ccc;
  z-index: 999;
}

.dropdown-menu.show {
  display: block;
}

.dropdown-item {
  cursor: pointer;
  padding: 4px 0;
  width:100%;
}

.dropdown-item a {
    display: block;
}

.dropdown-item:hover {
  background-color: #f2f2f2;
}
</style>