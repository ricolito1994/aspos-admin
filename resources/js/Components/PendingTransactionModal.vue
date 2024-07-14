<script setup>
import { 
    onMounted, 
    onUnmounted, 
    ref, 
    reactive, 
    watch, 
    computed ,
    getCurrentInstance
} from 'vue';
import { 
    getProducts1, 
    getProduct, 
    saveTransaction, 
    getCustomers ,
    getTransactions,
    searchTransaction,
    getUsers,
    getCurrentBalance,
} from '@/Services/ServerRequests';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import DataTable from '@/Components/DataTable.vue';
import TextAutoComplete from '@/Components/TextAutoComplete.vue';
import moment from 'moment';
const currentDate = moment().format('YYYY-MM-DD');
const nextMonth = moment().format('YYYY-MM-DD');
const transactionDateFrom = ref(currentDate);
const transactionDateTo = ref(nextMonth);
const userObject = ref(JSON.parse(localStorage.getItem('user')));
const companyObject = ref(JSON.parse(localStorage.getItem('company')));
const branchObject = ref(JSON.parse(localStorage.getItem('selected_branch')));
const resultData = ref([]);
const nameUser = ref("");
const emit = defineEmits(['onSelectPendingTransaction', 'close'])

const tableHeaders = ref([
    {
        name : 'TRANSACTION CODE',
        style: 'width:250px',
        field : 'transaction_code',
    },
    {
        name : 'CREATED BY',
        style: 'width:150px',
        //field : 'transaction_type',
        fxn : (res) => {
            return res['created_by']['name'];
        }
    },
    {
        name : 'CUSTOMER',
        style: 'width:150px',
        //field : 'transaction_type',
        fxn : (res) => {
            return res.customer?.customer_name;
        }
    },
    {
        name : 'STATUS',
        style: 'width:100px',
        //field : 'transaction_type',
        fxn : (res) => {
            return res['is_done_pending_transaction'] ? 'Done' : 'Pending';
        }
    },
    {
        name : 'DATE',
        field : 'transaction_date',
        fxn : (res) => {
            return res['transaction_date'];
        }
    },
    {
        name : 'ACTIONS',
        style: 'width:100px;',
        actions : {
            view : {
                color : '#1f2937',
                label : '/',
                func : async (res)=> {
                    let pendingTransaction = await searchTransaction(
                        res.transaction_code,
                        companyObject.value.id,
                        branchObject.value.id
                    );
                    emit('onSelectPendingTransaction', {
                        item : pendingTransaction.data.res.data[0]
                    });
                },
            },
            /*delete : {
                color : '#f05340',
                label : 'X',
                func : () => {}
            }*/
        }
    }
]);

const searchUsers = (index, searchString) => {
    return new Promise ( async (resolve, reject) => {
        try {
            let users = await getUsers (searchString);
            resolve (users)
        } catch (e) {
            reject (e);
        }
    })
}

const resetSearch = () => {
    nameUser.value = "";
    transactionDateFrom.value = currentDate;
    loadTransactions();
}

const loadTransactions = async (userId = null) => {
    let transaction = await getTransactions(
        companyObject.value.id,
        branchObject.value.id,
        false,
        transactionDateFrom.value,
        transactionDateTo.value,
        userId,
        'PENDING',
    );
    resultData.value = transaction.data.res;
}

const onSelectUser = (user) => {
    nameUser.value = user.item.name;
    loadTransactions(user.item.id)
}

onMounted (() => {
    loadTransactions();
});

onUnmounted (() => {
    
});
</script>
<template>
    <div id="modal-title">
        <div style="float:left">
            <b>Choose A Pending Transaction</b>
        </div>
        <div style="float:right;cursor:pointer;" @click="()=>emit('close')">
            <b>X</b>
        </div>
        <div style="clear:both"></div>
    </div>
    <div id="modal-content">
        <div style="width:100%;height:10%;">
            <div style="width:10%; float:left;padding-top:0.5%">
                <b>Teller/cashier</b>
            </div>
            <div style="width:40%; float:left;">
                <TextAutoComplete 
                    :itmName="nameUser" 
                    :getData="searchUsers" 
                    :itemName="'name'" 
                    :itemIndex="0"
                    :style="'width:95%'"
                    :addNew="()=>{}"
                    :disabled="false"
                    @onSelectItem="onSelectUser"
                />
            </div>
            <div style="width:4%; float:left;padding-top:0.5%;padding-left:0%;">
                <b>Date</b>
            </div>
            <div style="width:20%; float:left;">
                <input v-model="transactionDateFrom" @change="loadTransactions" type="date" style="width:100%;margin-left:1%;"/>
            </div>
            <div style="width:20%; float:left;padding-left:1%;">
                <PrimaryButton 
                    :additionalStyles="'padding:2%;background:#c9b500;'" 
                    @click="resetSearch"
                >
                    - RESET SEARCH
                </PrimaryButton>
            </div>
        </div>
        <div style="width:100%;height:90%;">
            <DataTable @viewItemDetails='()=>{}' :tableHeaders="tableHeaders" :resultData="resultData" />
        </div>
    </div>
    <div id="modal-footer">
        <div style="float:right">
            <PrimaryButton @click="()=>emit('close')">x close</PrimaryButton>
        </div>
        <div style="clear:both"></div>
    </div>
</template>
<style>

</style>