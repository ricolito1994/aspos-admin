<script setup>
import { getTransaction, getTransactions } from '@/Services/ServerRequests';
import { usePage, Head, Link } from '@inertiajs/vue3';
import { onMounted, onUnmounted, ref, reactive } from 'vue';
import AppLayout from '@/Layouts/AppLayout.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import DataTable from '@/Components/DataTable.vue';
import Modal from '@/Components/Modal.vue';
import TransactionModal from '@/Components/TransactionModal.vue';
import moment from 'moment';

const currentDate = moment().format('YYYY-MM-DD');
const nextMonth = moment(currentDate).add(1, 'M').format('YYYY-MM-DD');

const resultData = ref([]);
const searchString = ref("");
const transactionDateFrom = ref(currentDate);
const transactionDateTo = ref(nextMonth);
const isShowTransactionModal = ref(false);
const userObject = ref(JSON.parse(localStorage.getItem('user')));
const companyObject = ref(JSON.parse(localStorage.getItem('company')));
const branchObject = ref(JSON.parse(localStorage.getItem('selected_branch')));

const tempTransaction = {
    transaction_code : '',
    transaction_type : 'SALE',
    stock : false,
    transaction_desc: 'a transaction',
    transaction_date : currentDate,
    total_price : parseFloat(0.0),
    total_cost : parseFloat(0.0),
    supplier_id : 0,
    branch_id : branchObject.value.id,
    user_id : userObject.value.id,
    company_id : companyObject.value.id,
}

const transaction = ref(tempTransaction);

const props = defineProps({
    user: {
        type: Object,
    },
    company: {
        type: Object,
    },
});




onMounted ( async () => {
    let transaction = await getTransactions(
        companyObject.value.id,
        branchObject.value.id,
        searchString.value,
        transactionDateFrom.value,
        transactionDateTo.value,
    );
    resultData.value = transaction.data.res;
})

onUnmounted(()=>{
    // unmount webhook
    // if element is destroyed
})

/* const showProductModal =  async ( product ) => {
    if(typeof product == 'object') {
        let productRes = await getProduct (product.id);
        productObject.value = productRes.data;
    } else {
        productObject.value = {
            product_name : '',
            product_desc : '',
            product_code : '-',
            user_id : userObject.value.id,
            company_id : companyObject.value.id,
        };
    }

    isShowProductModal.value = !isShowProductModal.value;
} */

const catchChangeBranch = (branch) => {
    branchObject.value = branch;
}

const onAddTransaction = (transaction) => {
    if (transaction.isUpdate) {
        resultData.value.unshift(transaction);
    } else {
        // update product
        let indx = resultData.value.findIndex(x => x.id == transaction.id)
        if (indx > -1)
            resultData.value[indx] = transaction;
    }
}

const searchTransactions = async ( reset ) => {
    if(typeof reset == "boolean") {
        searchString.value = "";
        transactionDateFrom.value = currentDate;
        transactionDateTo.value = nextMonth;
    }

    let transactions = await getTransactions(
        companyObject.value.id, 
        branchObject.value.id,
        searchString.value, 
        transactionDateFrom.value, 
        transactionDateTo.value
    );
    resultData.value = transactions.data.res;
}

const showTransactionModal = async (t) => {
    if(typeof t == 'object') {
        let tres = await getTransaction (t.id);
        transaction.value = tres.data.res;
    } else {
        //
        transaction.value = tempTransaction;
    }
    isShowTransactionModal.value = !isShowTransactionModal.value;
}


/*const onOpenProductDialog = ( ) => {
}*/


const tableHeaders = ref([
    {
        name : 'TRANSACTION CODE',
        //style: 'width:300px',
        field : 'transaction_code',
    },
    {
        name : 'TRANSACTION TYPE',
        field : 'transaction_type',
    },
    {
        name : 'STOCK',
        field : 'stock',
    },
    /*{
        name : 'UNIT',
        style: 'width:169px'
    },
    {
        name : 'BALANCE',
        style: 'width:169px'
    },*/
    {
        name : 'ACTIONS',
        style: 'width:300px;',
        actions : {
            view : {
                color : 'blue',
                label : 'View',
                func : showTransactionModal
            },
            /* delete : {
                color : 'red',
                label : 'X Delete',
                func : deleteProduct
            } */
        }
    }
]);

</script>

<template>
    <Head title="Dashboard" />
    <AppLayout :catchChangeBranch="catchChangeBranch">
        <Modal :show=isShowTransactionModal @close="showTransactionModal" @onDialogDisplay="null" extraWidth="max-width:90rem">
            <TransactionModal :transaction="transaction" :branchObject="branchObject" @closeTransactionModal="showTransactionModal" @onAddTransaction=onAddTransaction />
        </Modal>

        <div style="background: #F05340;padding:1%; color:#fff;">
            <b>TRANSACTIONS </b>
        </div>
        
        <div style="width:100%;padding:1%;">
            <div style="width:6%; float:left;padding-top:0.5%">
                <b>SEARCH</b>
            </div>
            <div style="width:30%; float:left;">
                <input placeholder="DELIVERY RECEIPT / ORIGINAL RECEIPT" v-model="searchString" @change="searchTransactions" type="text" style="width:100%;margin-left:1%;"/>
            </div>
            <div style="width:5%; float:left;padding-top:0.5%;padding-left:1%;">
                <b>FROM</b>
            </div>
            <div style="width:10%; float:left;">
                <input v-model="transactionDateFrom" @change="searchTransactions" type="date" style="width:100%;margin-left:1%;"/>
            </div>
            <div style="width:3%; float:left;padding-top:0.5%; padding-left:1%;">
                <b>TO</b>
            </div>
            <div style="width:10%; float:left;">
                <input v-model="transactionDateTo" @change="searchTransactions" type="date" style="width:100%;margin-left:1%;"/>
            </div>
            <div style="width:36%; padding-left:1%; float:left;">
                <PrimaryButton :additionalStyles="'padding:2%;'" @click="showTransactionModal(true)">+ NEW TRANSACTION</PrimaryButton>&nbsp;
                <PrimaryButton :additionalStyles="'padding:2%;background:#c9b500;'" @click="searchTransactions(true)">- RESET SEARCH</PrimaryButton>
            </div>
        </div>
        
        <div style="width:100%;height:85%;">
            <DataTable @viewItemDetails=showTransactionModal :tableHeaders="tableHeaders" :resultData="resultData" />
        </div>
    </AppLayout>
</template>

<style scoped>

</style>
