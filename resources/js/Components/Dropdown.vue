<script setup>
import NavLink from '@/Components/NavLink.vue';
import { Link } from '@inertiajs/vue3';
import { onMounted, onUnmounted, ref } from 'vue';

const props = defineProps({
    displayDropdown : {
        type: Boolean,
    },
    onCloseDropDown : {
        type: Function,
    },
    dropDownMenuButton : {
        type: String,
    }
})


const dropdownMenuRef = ref(null);

onMounted(() => {
    document.addEventListener('click', handleClickOutside);
})

onUnmounted(() => {
    document.removeEventListener('click', handleClickOutside);
});

const user = ref(JSON.parse(localStorage.getItem('user')));

const options = ref([
    {
        href : route('logout'),
        meth : 'post',
        label : '🏃 Logout',
    },
    {
        href : route('dashboard'),
        meth : 'get',
        label : `🧑 ${user.value.name}`,
    },
]);

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
            <Link :href="option.href" :method="option.meth" :class="classes">{{ option.label }}</Link>
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
  top: 100%;
  right: 2%;
  width:200px;
  display: block;
  background-color: #fff;
  padding: 8px;
  border: 1px solid #ccc;
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