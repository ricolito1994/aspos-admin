<script setup>
import { 
    ref, 
    onMounted, 
    watch 
} from 'vue';
import { providePaginationData } from '@/Services/ServerRequests'
import PrimaryButton from '@/Components/PrimaryButton.vue';

let props = defineProps({
    tableHeaders : {
        type: Array,
    },
    resultData : {
        type: Object,
    },
    getData : {
        type: Function,
    }
})

let emit = defineEmits(['viewItemDetails'])

const tableData = ref(null)

const actions = ref ([]);

/* const paginate = (index) => {
    selectedPage.value = index;
}

props.resultData = props.resultData ? props.resultData : [];

const calculatePaginateButtons = (totalItems, itemsPerPage) => {
    const numberOfButtons = Math.ceil(totalItems / itemsPerPage);
    return numberOfButtons;
}
 */
const viewItemDetails = (data) => {
    emit('viewItemDetails', data)
}

const provideData = async (url) => {
    let res = [];
    if (!props.getData) {
        res = await providePaginationData(url);
    } else {
        res = await props.getData(url);
    }
    tableData.value = res.data.res ? res.data.res : res.data;
}

//const totalItems = props.resultData?.length;
//const itemsPerPage = 100;
//let numPaginate = calculatePaginateButtons(totalItems, itemsPerPage);
//let selectedPage = ref(1);

onMounted (() => {
});

watch (
    () => props.resultData, 
    (newValue) => {
        console.log('newValue', newValue)
        tableData.value = newValue;
    }
);

</script>
<template> 
    <div id="table-container">
        <div id="main-table" class="scrollbar">
            <table>
                <thead>
                    <tr>
                        <th 
                            v-for="(thead, index) in tableHeaders" 
                            :key="index" 
                            :style="thead.style ? thead.style : ''"
                        >
                            {{ thead.name }}
                        </th>
                    </tr>
                </thead>
                <tbody>
                    <!--result goes here-->
                    <tr v-if="!tableData" v-for="(res, index) in resultData.data" :key="index">
                        <td v-for="(thead, index0) in tableHeaders" :key="index0" >
                            <span v-if="thead.name !== 'ACTIONS' && !thead.fxn">
                                {{ res[thead.field] }}
                            </span>
                            <span v-else-if="thead.fxn">
                                {{ thead.fxn(res) }}
                            </span>
                            <span v-else>
                                <PrimaryButton 
                                    v-for="(action, index1) in thead.actions" 
                                    :key="index1" 
                                    :additionalStyles="`background:${action.color}`"
                                    @click="action.func(res, index)"
                                >
                                    {{ action.label }}
                                </PrimaryButton>
                            </span>
                        </td>
                    </tr>
                    <tr v-else v-for="(res, index01) in tableData.data" :key="index01">
                        <td v-for="(thead, index02) in tableHeaders" :key="index02" >
                            <span v-if="thead.name !== 'ACTIONS' && !thead.fxn">
                                {{ res[thead.field] }}
                            </span>
                            <span v-else-if="thead.fxn">
                                {{ thead.fxn(res) }}
                            </span>
                            <span v-else>
                                <PrimaryButton 
                                    v-for="(action, index03) in thead.actions" 
                                    :key="index03" 
                                    :additionalStyles="`background:${action.color}`"
                                    @click="action.func(res, index01)"
                                >
                                    {{ action.label }}
                                </PrimaryButton>
                            </span>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
        <div id="pagination" align="center">
            <div v-if="!tableData" v-for="link in resultData.links" :key="index" class="paginate-btn-container">
                <span v-if="link.url!==null">
                    <PrimaryButton 
                        :additionalStyles="link.active ? 'padding:10px; background:#f05340':'padding:10px;'"
                        @click="provideData(link.url)"
                    >
                        <span v-html="link.label"></span>
                    </PrimaryButton>
                </span>
            </div>
            <div v-else v-for="link in tableData.links" :key="index001" class="paginate-btn-container">
                <span v-if="link.url!==null">
                    <PrimaryButton 
                        :additionalStyles="link.active ? 'padding:10px; background:#f05340':'padding:10px;'"
                        @click="provideData(link.url)"
                    >
                        <span v-html="link.label"></span>
                    </PrimaryButton>
                </span>
            </div>
        </div>
    </div>
</template>
<style scoped>
#table-container {
    width:100%;
    height:95%;
    background-color: #f0534017;
    border:1px solid #ddd;
}

#main-table, #main-table table {
    width:100%;
}

#main-table table tr td, #main-table table tr th {
    border-collapse: collapse;
}

#main-table table tr td {
    padding:1%;
    border-bottom: 1px solid #f05340;
}


#main-table {
    height:90%;
    max-height: 90%;
    overflow-y:auto;
}

thead th { 
    position: sticky; top: 0; 
    padding:1%;
    background-color: #f05340;
    color: #fff;
    text-align: left;
}

#pagination {
    width:100%;
    height:10%;
    background-color: #fff;
    padding:1%;
    display: flex;
    justify-content: center;
    align-items: center;
}

.paginate-btn-container {
    padding-left:0.5%;
}
</style>