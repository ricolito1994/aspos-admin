<script setup>
import { 
    onMounted, 
    onUnmounted, 
    ref, 
    reactive, 
    watch, 
    computed 
} from 'vue';
import {
    alertBox,
    customerModal,
    userModal,
    ALERT_TYPE
} from '@/Services/Alert'
import { 
    saveTransaction, 
    getCurrentBalance,
    getUsers,
} from '@/Services/ServerRequests';
import { randomString } from '@/Services/CodeGenerator';
import { 
    CASH_TRANSACTIONS, 
    USER_ROLES, 
    ALPHA_NUMERIC 
} from '@/Constants'
import TransactionModalLayout from '@/Layouts/TransactionModalLayout.vue'
import TextAutoComplete from './TextAutoComplete.vue';
import moment from 'moment';

const props = defineProps({
    transaction : {
        type: Object,
    },
    branchObject : {
        type: Object,
    },
    type: {
        type: Object,
    }
});

let transactionDetails = reactive (props.transaction.id ? props.transaction.item_details : []);


const userObject = ref(JSON.parse(localStorage.getItem('user')));

const companyObject = ref(JSON.parse(localStorage.getItem('company')));

const isUpdate = ref(false);

const emit = defineEmits(['closeTransactionModal', 'onAddTransaction'])

const closeTModal = () => {
    emit('closeTransactionModal');
}

const addTransaction = (arg) => {
    emit('closeTransactionModal', arg);
}

const errs = ref([]);

const title = ref (props.type.title);

const currentDate = ref(moment().format('YYYY-MM-DD'));

const currentCashBalance = ref(0);

const tempCashBal = ref(0);

const amountRequested = ref(0);

const requestType = ref(CASH_TRANSACTIONS.REQUESTS[0].value)

const createdBy = computed (() => props.transaction.user ? props.transaction.user.name : userObject.value.name);

const changeTransactionRequest = () => {
    props.transaction.transaction_type = requestType.value;
    computeTotals()
}


const save = async () => {
    
    if (currentCashBalance.value < 0) {
        alertBox("Current balance is less than 0.", ALERT_TYPE.ERR);
        return;
    }

    if (amountRequested.value <= 0 || isNaN(parseFloat(amountRequested.value))) {
        alertBox("Please enter a valid amount.", ALERT_TYPE.ERR);
        return;
    }

    if (props.transaction.is_expense && requestType.value == 'CASH_DEPOSIT') {
        alertBox("Uncheck expense if cash deposit.", ALERT_TYPE.ERR);
        return;
    }

    try {
        isUpdate.value = !isUpdate.value;
        props.transaction['remaining_balance'] = currentCashBalance.value;
        let transaction = await saveTransaction({
            transaction: props.transaction,
            transactionDetails: transactionDetails,
            isCreate :  true,
        });
        transaction.data.res['isUpdate'] = isUpdate.value;
        props.transaction = transaction.data.res;

        alertBox('Transaction success!', ALERT_TYPE.MSG);
        emit('onAddTransaction', transaction.data.res);        
    } catch (e) {
        alertBox(e.response.data.err ? e.response.data.err : e.response.data.message, 
            ALERT_TYPE.ERR);
    }
}


const computeTotals = () => {
    let cashbal = tempCashBal.value;
    let amtreq = isNaN(parseFloat(amountRequested.value)) ? 0 : 
        parseFloat(amountRequested.value);
    if (requestType.value == 'CASH_DEPOSIT') {
        currentCashBalance.value = amtreq + cashbal;
        props.transaction['amt_released'] = 0;
        props.transaction['final_amt_received'] = amountRequested.value;
    } else {
        currentCashBalance.value = cashbal - amtreq;
        props.transaction['final_amt_received'] = 0;
        props.transaction['amt_released'] = amountRequested.value;
    }
}

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

const onSelectRequestedBy = (user) => {
    props.transaction.requested_by = user.item.id;
}

const openNewUser = () => {
    let rand = randomString(15, ALPHA_NUMERIC);
    userModal({
        'name' : `${companyObject.value.company_code} employee`,
        'username' : rand,
        'company_id' : companyObject.value.id,
        'branch_id' : props.branchObject.id,
        'email': `${companyObject.value.company_code}_employee@${companyObject.value.company_code}.com`,
        'password' : rand,
        'phone' : '123',
        'selected_branch': props.branchObject.id,
        'designation' : USER_ROLES[3].id,
        'is_active' : true,
        'created_by' : userObject.value.id,    
        'is_owner' : false,
    })
}


onMounted (async () => {
    props.transaction['item_transaction_type'] = '-';
    if (!props.transaction.id) {
        transactionDetails[0] = {
            'transaction_type' : requestType.value,
            'item_transaction_type' : '-',
            'unit': '-',
            'units' : [],
            'quantity' : 0,
            'price_per_unit' : 0.0,
            'cost_per_unit' : 0.0,
            'total_cost' : 0.0,
            'total_price' : 0.0,
            'remaining_balance' : 0,
            'product_id' : 0,
            'unit_id' : 0,
            'branch_id' : props.branchObject.id,
            'company_id' : companyObject.value.id,
            'supplier' : 0,
            'stock' : 0,
        };
        props.transaction.transaction_code = randomString(15)
        props.transaction.transaction_type = requestType.value;
        let latestTransaction = await getCurrentBalance(
            currentDate.value,
            userObject.value.id);
        let cb = latestTransaction.data.res ? latestTransaction.data.res.remaining_balance : 0;
        currentCashBalance.value = cb;
        tempCashBal.value = cb;
    } else {
        isUpdate.value = !isUpdate.value;
        //transactionDetails[0] = requestType.value;
        requestType.value = props.transaction['transaction_type'];
        currentCashBalance.value = props.transaction['remaining_balance'];
        amountRequested.value = requestType.value == 'CASH_DEPOSIT' ? props.transaction['final_amt_received'] 
            : props.transaction['amt_released'];
    }
})

onUnmounted(() => {
})

</script>
<template>
    <TransactionModalLayout 
        :transaction="transaction"
        :brancObject="branchObject"
        :type="type"
        @closeTransactionModal=closeTModal
        @onAddTransaction=addTransaction
        @onSave=save
    >
        <template #transaction="{ branchObject, userObject }">
            <div style="width:100%; height:15%;" >
                <div style="width:33.33%;float:left; ">
                    <B>REQUESTED BY</B>
                    <TextAutoComplete 
                        :itmName="transaction.requested_by ? transaction.requested_by.name : ''" 
                        :getData="searchUsers" 
                        :itemName="'name'" 
                        :itemIndex="0"
                        :style="'width:95%'"
                        :addNew=openNewUser
                        @onSelectItem="onSelectRequestedBy"
                    />
                </div>
                <div style="width:33.33%;float:left; ">
                    <B>DATE</B>
                    <input type="date" v-model="currentDate" style="width:95%;"/>
                </div>
                <div style="width:33.33%;float:left; ">
                    <B>REQUEST TYPE</B>
                    <select 
                        style="width:95%;" 
                        @change="changeTransactionRequest" 
                        v-model="requestType"
                    >
                        <option 
                            v-for="(T, index) in CASH_TRANSACTIONS.REQUESTS" 
                            :key="index"
                            :value="T.value"
                        >
                            {{T.label}}
                        </option>
                    </select>
                </div>
                <div style="clear:both"></div>
            </div>
            <div style="width:100%; height:15%;" >
                <div style="width:33.33%;float:left; ">
                    <B>AMOUNT REQUESTED</B>
                    <input type="text"  v-model="amountRequested" @keyup="computeTotals" style="width:95%;"/>
                </div>
                <div style="width:33.33%;float:left; ">
                    <B>CURRENT CASH BALANCE</B>
                    <input disabled type="text" v-model="currentCashBalance" style="width:95%;"/>
                </div>
                <div v-if="requestType=='CASH_WITHRAWAL'" style="width:33.33%;float:left; ">
                    <br> 
                    <input type="checkbox" v-model="transaction.is_expense"  id="is_expense" /> &nbsp;
                    <label style="cursor: pointer;" :for="`is_expense`" >
                        <b>Expense</b>
                    </label>&nbsp;
                </div>
            </div>
            <div style="width:100%;" >
                <div style="width:33.33%;float:left; ">
                    <B>NOTES</B>
                    <textarea v-model="transaction.transaction_desc" style="width:95%; resize:none" rows="16"/>
                </div>
                <div style="width:33.33%;float:left; ">
                    <B>CREATED BY</B>
                    <input disabled type="text" v-model="createdBy" style="width:95%;"/>
                </div>
            </div>
        </template>
    </TransactionModalLayout>
</template>
<style scoped>
</style>

