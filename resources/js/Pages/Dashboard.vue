<script setup>
import { 
    usePage, 
    Head, 
    Link 
} from '@inertiajs/vue3';
import { 
    onMounted, 
    onUnmounted, 
    ref, 
    reactive 
} from 'vue';
import {
    TRANSACTION_MODAL_CONSTANTS,
    IS_VAT,
    VAT_PERCENT,
    ALPHA_NUMERIC,
    USER_ROLES,
} from '@/Constants'
import AppLayout from '@/Layouts/AppLayout.vue';
import TransactionModal from '@/Components/TransactionModal.vue';
import moment from 'moment';
const user = usePage().props.auth.user;
const props = defineProps({
    user: {
        type: Object,
    },
    company: {
        type: Object,
    },
    branch : {
        type: Object,
    }
});
const currentDate = moment().format('YYYY-MM-DD');
const branchObject = ref(JSON.parse(localStorage.getItem('selected_branch')));
const userObject = ref(JSON.parse(localStorage.getItem('user')));
const companyObject = ref(JSON.parse(localStorage.getItem('company')));
var fullTextDate = ref(moment().format("LLLL"));

console.log('branchObject', branchObject.value, props.branch.branch_name)

const transactionObject = ref({
    transaction_code : '',
    item_transaction_type : 'SALE',
    transaction_type: TRANSACTION_MODAL_CONSTANTS.ITEM_TRANSACTION.value,
    stock : false,
    transaction_desc: 'a transaction',
    transaction_date : currentDate,
    total_price : parseFloat(0.0),
    total_cost : parseFloat(0.0),
    supplier_id : 0,
    branch_id : branchObject.value ? branchObject.value.id : props.branch.id,
    user_id : userObject.value ? userObject.value.id : props.user.id,
    company_id : companyObject.value ? companyObject.value.id : props.company.id,
    amt_received: 0.00,
    final_amt_received: 0.00,
    discount_type: 1,
    discount_percent: 0,
    vat: null,
    customer_id : null,
    amt_released : null,
    change : 0.00,
    ref_transaction_id: null,
    is_expense: null,
    requested_by: null,
    is_pending_transaction: (props.user.designation == 5 | props.user.designation == 1) ? true : null
})

localStorage.setItem('user', JSON.stringify(props.user));
localStorage.setItem('company', JSON.stringify(props.company));

onMounted (() => {
    setInterval(() => {
        fullTextDate.value = moment().format("LLLL");
    },1000)
})

</script>

<template>
    <Head title="Dashboard" />
    <AppLayout>
        <div style="width:100%;">
            <TransactionModal 
                :transaction="transactionObject" 
                :branchObject="props.branch" 
                :userDesignation="props.user.designation"
                :isNotFromDialog="true"
                @closeTransactionModal="()=>{}"
                @onAddTransaction="()=>{ }" 
            />
        </div>
    </AppLayout>
</template>

<style>

</style>
