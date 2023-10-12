<script setup>
import Dropdown from '@/Components/Dropdown.vue';

import { getBranches, changeBranch } from '@/Services/ServerRequests';
import { reactive } from 'vue';
import { onMounted, ref } from 'vue';

let user = ref(JSON.parse(localStorage.getItem('user')));
let branches = ref ([]);
let selected_branch = ref({});
let displayDropdown = ref(false);
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


defineProps({
    company: {
        type: Object,
    },
    userLogin: {
        type: Object,
    },
});

const emit = defineEmits(['catchChangeBranch']);

onMounted(() => loadbranches())


const loadbranches = async () => {
    let data = await getBranches();
    selected_branch.value = data.data.selected_branch;
    localStorage.setItem('selected_branch', JSON.stringify(data.data.selected_branch))
    branches.value = data.data.branches;
};

const selectBranch = async ( ) => {
    let data = await changeBranch(selected_branch.value.id);
    selected_branch.value = data.data.selected_branch;
    localStorage.setItem('selected_branch', JSON.stringify(data.data.selected_branch))
    emit('catchChangeBranch', selected_branch.value)
}

const toggleDisplayDropdown = ( ) => {
    displayDropdown.value = !displayDropdown.value;
}

const onCloseDropDown = ( ) => {
    displayDropdown.value = false;
}

const dropDownMenuButton = ref(null);

</script>
<template>
    <div id="top-nav-bar"> 
        <div style="height:100%;width:100%;">
            <div style="width:20%;float:left">
                <div style="margin-top:8.5px;float:right;">
                    <B>BRANCH</B>&nbsp;&nbsp;
                </div>
            </div>
            <div style="width:40%;float:left;">
                <select @change="selectBranch" v-model="selected_branch.id" style="border:1px solid #ccc;width:100%;">
                    <option v-for="(branch, index) in branches" :key="index" :value="branch.id">{{ branch.branch_name }} - {{ branch.branch_address }}</option>
                </select>
            </div>
            <div style="width:40%;float:right">
                <div style="float:right;">
                    <div style="margin-top:8.5px; display:flex; justify-content: space-between; ">
                        <div style="align-self:center;padding-right:12px;" ref="dropDownMenuButton">
                            <button @click="toggleDisplayDropdown">{{ user.name }} <span style="font-size:12px;">🔻</span></button>
                        </div>
                    </div>
                    <Dropdown 
                        :options="options" 
                        :dropDownMenuButton="dropDownMenuButton" 
                        :onCloseDropDown="onCloseDropDown"
                        :displayDropdown="displayDropdown" 
                        v-if="displayDropdown"
                    />
                </div>
            </div>
        </div>
    </div>
</template>
<style scoped>
    #top-nav-bar {
        position:relative;
        width: calc(100% - 280px);
        height:50px;
        left:280px;
        padding:3px 2% 3px 2%;
    }
</style>