<script setup>
import { 
    getTransaction, 
    getTransactions,
    getStartingBalance,
    getCurrentBalance,
    getTotalSales,
    getTotalExpenses,
    getUsers,
} from '@/Services/ServerRequests';
import { 
    usePage, 
    Head, 
    Link 
} from '@inertiajs/vue3';
import Dropdown from '@/Components/Dropdown.vue'
import { 
    onMounted, 
    onUnmounted, 
    ref, 
    reactive 
} from 'vue';
import AppLayout from '@/Layouts/AppLayout.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import DataTable from '@/Components/DataTable.vue';
import Modal from '@/Components/Modal.vue';
import TransactionModal from '@/Components/TransactionModal.vue';
import DepositCashModal from '@/Components/DepositCashModal.vue';
import WithrawCashModal from '@/Components/WithrawCashModal.vue';
import StartOfShiftModal from '@/Components/StartOfShiftModal.vue';
import EndOfShiftModal from '@/Components/EndOfShiftModal.vue';
import RefundTransactionModal from '@/Components/RefundTransactionModal.vue';
import ReturnItemsModal from '@/Components/ReturnItemsModal.vue';
import TextAutoComplete from '@/Components/TextAutoComplete.vue';
import {
    TRANSACTION_MODAL_CONSTANTS,
    IS_VAT,
    VAT_PERCENT,
    ALPHA_NUMERIC,
    USER_ROLES,
} from '@/Constants'
import {
    alertBox,
    userModal,
    ALERT_TYPE
} from '@/Services/Alert'
import { randomString } from '@/Services/CodeGenerator';
import {event} from '@/Services/EventBus';
import moment from 'moment';

const props = defineProps({
    user: {
        type: Object,
    },
    company: {
        type: Object,
    },
});

const currentDate = moment().format('YYYY-MM-DD');
const nextMonth = moment().format('YYYY-MM-DD');

const resultData = ref([]);
const searchString = ref("");
const transactionDateFrom = ref(currentDate);
const transactionDateTo = ref(nextMonth);

const startingCash = ref(parseFloat(0.0));
const currentCash = ref(parseFloat(0.0));
const totalSales = ref(parseFloat(0.0));
const totalCost = ref(parseFloat(0.0));
const totalPrice = ref(parseFloat(0.0));
const totalExpenses = ref(parseFloat(0.0));

const isShowTransactionModal = ref(false);
const isShowRefundModal = ref(false);
const isShowReturnModal = ref(false);
const isShowStartOfShiftModal = ref(false);
const isShowEndOfShiftModal = ref(false);
const isShowDepositCashModal = ref(false);
const isShowWithrawCashModal = ref(false);

const tmpUserObject = JSON.parse(localStorage.getItem('user'));
const userObject = ref(JSON.parse(localStorage.getItem('user')));
const companyObject = ref(JSON.parse(localStorage.getItem('company')));
const branchObject = ref(JSON.parse(localStorage.getItem('selected_branch')));
const searchUser = ref(userObject);

/* const tempTransaction = {
    transaction_code : '',
    item_transaction_type : 'DELIVERY',
    transaction_type: TRANSACTION_MODAL_CONSTANTS.ITEM_TRANSACTION.value,
    stock : true,
    transaction_desc: 'a transaction',
    transaction_date : currentDate,
    total_price : parseFloat(0.0),
    total_cost : parseFloat(0.0),
    supplier_id : 0,
    branch_id : branchObject.value.id,
    user_id : userObject.value.id,
    company_id : companyObject.value.id,
    amt_received: 0.00,
    final_amt_received: 0.00,
    discount_type: 1,
    discount_percent: 0,
    vat: IS_VAT ? VAT_PERCENT : 0,
    customer_id : null,
    amt_released : null,
    change : 0.00,
    ref_transaction_id: null,
    is_expense: null,
    requested_by: null,
} */

const transaction = ref({});

const dropDownMenuButton = ref(null);

const displayDropdown = ref(false);

const openUserModal = () => {
    let rand = randomString(15, ALPHA_NUMERIC);
    userModal({
        'name' : `${companyObject.value.company_code} employee`,
        'username' : rand,
        'company_id' : companyObject.value.id,
        'branch_id' : branchObject.value.id,
        'email': `${companyObject.value.company_code}_employee@${companyObject.value.company_code}.com`,
        'password' : rand,
        'phone' : '123',
        'selected_branch': branchObject.value.id,
        'designation' : USER_ROLES[3].id,
        'is_active' : true,
        'created_by' : tmpUserObject.id,    
        'is_owner' : false,
    })
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

const onSelectUser = (user) => {
    searchUser.value = user.item;
    loadTransactions();
}

onMounted ( () => {
    loadTransactions();
})

onUnmounted(()=>{
    // unmount webhook
    // if element is destroyed
})


const loadTransactions = async () => {
    let transaction = await getTransactions(
        companyObject.value.id,
        branchObject.value.id,
        searchString.value,
        transactionDateFrom.value,
        transactionDateTo.value,
        searchUser.value.id,
    );
    resultData.value = transaction.data.res;
    loadBalances();
}

const catchChangeBranch = (branch) => {
    branchObject.value = branch;
    loadTransactions();
}

const loadBalances = async () => {
    let balances = await Promise.all([
        getStartingBalance (transactionDateFrom.value, searchUser.value.id),
        getCurrentBalance (transactionDateFrom.value, searchUser.value.id),
        getTotalSales (transactionDateFrom.value, searchUser.value.id),
        getTotalExpenses (transactionDateFrom.value, searchUser.value.id),
    ]);
   
    startingCash.value = balances[0].data.res ? balances[0].data.res.remaining_balance : 0;
    currentCash.value = balances[1].data.res ? balances[1].data.res.remaining_balance : 0;

    totalSales.value = balances[2].data.res ? balances[2].data.res.total_sale : 0;
    totalPrice.value = balances[2].data.res ? balances[2].data.res.total_price : 0;
    totalCost.value = balances[2].data.res ? balances[2].data.res.total_cost : 0;

    totalExpenses.value = balances[3].data.res ? balances[3].data.res.total_expenses : 0;
}

const onAddTransaction = (transaction) => {
    if (transaction.isUpdate) {
        searchTransactions();
        //resultData.value.push(transaction);
        //console.log('shift', resultData.value)
        return;
    } else {
        // update product
        let indx = resultData.value.findIndex(x => x.id == transaction.id)
        if (indx > -1)
            resultData.value[indx] = transaction;
    }
    loadBalances();
}

const searchTransactions = async ( reset ) => {
    if(typeof reset == "boolean") {
        searchString.value = "";
        transactionDateFrom.value = currentDate;
        transactionDateTo.value = nextMonth;
        searchUser.value = tmpUserObject.designation == 1 ? tmpUserObject : {};
        searchUser.value = {};
        event.emit('TextAutoCompleteComponent:clearSearchText', "name");
    }
   
    let transactions = await getTransactions(
        companyObject.value.id, 
        branchObject.value.id,
        searchString.value, 
        transactionDateFrom.value, 
        transactionDateFrom.value,
        searchUser.value.id,
    );
    resultData.value = transactions.data.res;
    loadBalances();
}

const showTransactionModal = async (transactionArgument) => {
    if (typeof transactionArgument == 'object') {
        let transactionResult = await getTransaction (transactionArgument.id);
        transaction.value = transactionResult.data.res;
    } else {
        transaction.value = {
            transaction_code : '',
            item_transaction_type : 'DELIVERY',
            transaction_type: TRANSACTION_MODAL_CONSTANTS.ITEM_TRANSACTION.value,
            stock : true,
            transaction_desc: 'a transaction',
            transaction_date : currentDate,
            total_price : parseFloat(0.0),
            total_cost : parseFloat(0.0),
            supplier_id : 0,
            branch_id : branchObject.value.id,
            user_id : userObject.value.id,
            company_id : companyObject.value.id,
            amt_received: 0.00,
            final_amt_received: 0.00,
            discount_type: 1,
            discount_percent: 0,
            vat: IS_VAT ? VAT_PERCENT : 0,
            customer_id : null,
            amt_released : null,
            change : 0.00,
            ref_transaction_id: null,
            is_expense: null,
            requested_by: null,
        };
    }

    if (
        transactionArgument && 
        transactionArgument.transaction_type != 'ITEM_TRANSACTION'
    ) {
        switch (transactionArgument.transaction_type) {
            case TRANSACTION_MODAL_CONSTANTS.REFUND.value:
                showRefundModal ();
                break;
            case TRANSACTION_MODAL_CONSTANTS.RETURN.value:
                showReturnModal ();
                break;
            case TRANSACTION_MODAL_CONSTANTS.START_SHIFT.value:
                showStartOfShiftModal ();
                break;
            case TRANSACTION_MODAL_CONSTANTS.END_SHIFT.value:
                showEndOfShiftModal ();
                break;
            case TRANSACTION_MODAL_CONSTANTS.DEPOSIT.value:
                showDepositCashModal ();
                break;
            case TRANSACTION_MODAL_CONSTANTS.WITHRAW.value:
                //showWithrawCashModal ();
                showDepositCashModal ();
                break;
        }
        return;
    }
    isShowTransactionModal.value = !isShowTransactionModal.value;
}

const showRefundModal = () => {
    isShowRefundModal.value = !isShowRefundModal.value;   
    if (!transaction.value.id) {
        transaction.value = {
            transaction_code : '',
            item_transaction_type : 'DELIVERY',
            transaction_type: TRANSACTION_MODAL_CONSTANTS.ITEM_TRANSACTION.value,
            stock : true,
            transaction_desc: 'a transaction',
            transaction_date : currentDate,
            total_price : parseFloat(0.0),
            total_cost : parseFloat(0.0),
            supplier_id : 0,
            branch_id : branchObject.value.id,
            user_id : userObject.value.id,
            company_id : companyObject.value.id,
            amt_received: 0.00,
            final_amt_received: 0.00,
            discount_type: 1,
            discount_percent: 0,
            vat: IS_VAT ? VAT_PERCENT : 0,
            customer_id : null,
            amt_released : null,
            change : 0.00,
            ref_transaction_id: null,
            is_expense: null,
            requested_by: null,
        };
    }
    if (!isShowRefundModal.value)  transaction.value = {};
}

const showReturnModal = () => {
    isShowReturnModal.value = !isShowReturnModal.value;
    if (!isShowReturnModal.value)  transaction.value = {};
}

const showStartOfShiftModal = () => {
    isShowStartOfShiftModal.value = !isShowStartOfShiftModal.value;
    if (!isShowStartOfShiftModal.value)  transaction.value = {};
}

const showEndOfShiftModal = () => {
    isShowEndOfShiftModal.value = !isShowEndOfShiftModal.value;
    if (!isShowEndOfShiftModal.value)  transaction.value = {};
}

const showDepositCashModal = () => {
    isShowDepositCashModal.value = !isShowDepositCashModal.value;
    if (!transaction.value.id) {
        transaction.value = {
            transaction_code : '',
            item_transaction_type : 'DELIVERY',
            transaction_type: TRANSACTION_MODAL_CONSTANTS.ITEM_TRANSACTION.value,
            stock : true,
            transaction_desc: 'a transaction',
            transaction_date : currentDate,
            total_price : parseFloat(0.0),
            total_cost : parseFloat(0.0),
            supplier_id : 0,
            branch_id : branchObject.value.id,
            user_id : userObject.value.id,
            company_id : companyObject.value.id,
            amt_received: 0.00,
            final_amt_received: 0.00,
            discount_type: 1,
            discount_percent: 0,
            vat: IS_VAT ? VAT_PERCENT : 0,
            customer_id : null,
            amt_released : null,
            change : 0.00,
            ref_transaction_id: null,
            is_expense: null,
            requested_by: null,
        };
    }
    if (!isShowDepositCashModal.value)  transaction.value = {};
}

const showWithrawCashModal = () => {
    isShowWithrawCashModal.value = !isShowWithrawCashModal.value;
    if (!isShowWithrawCashModal.value)  transaction.value = {};
}

const onCloseDropDown = ( ) => {
    displayDropdown.value = false;
}

const options = ref([
    {
        label : 'Items Transaction',
        func : () => showTransactionModal(),
    },
    {
        label : `Refund Items`,
        func : () => showRefundModal()
    },
    /* {
        label : `Return Items`,
        func : () => showReturnModal()
    }, 
    {
        label : `Start of Shift`,
        func : () => showStartOfShiftModal()
    },
    {
        label : `End of Shift`,
        func : () => showEndOfShiftModal()
    },*/
    {
        label : `Cash Transactions`,
        func : () => showDepositCashModal()
    },
    /* {
        label : `Withraw Cash Onhand`,
        func : () => showWithrawCashModal()
    }, */
    {
        label : `Reset Search`,
        func : () => {
            searchTransactions(true)
        }
    },
    {
        label : `Search Transaction Code`,
        func : async () => {
            let transactions = await getTransactions(
                companyObject.value.id, 
                branchObject.value.id,
                searchString.value, 
            );
            resultData.value = transactions.data.res;
            loadBalances();
        }
    },
]);

const toggleDisplayDropdown = ( ) => {
    displayDropdown.value = !displayDropdown.value;
}

const tableHeaders = ref([
    {
        name : 'TRANSACTION CODE',
        //style: 'width:300px',
        field : 'transaction_code',
    },
    {
        name : 'TRANSACTION TYPE',
        field : 'transaction_type',
        fxn : (res) => {
            return res['transaction_type'];
        }
    },
    {
        name : 'AMOUNT RELEASED',
        field : 'amt_released',
        fxn : (res) => {
            return res['amt_released'];
        }
    },
    {
        name : 'AMOUNT RECEIVED',
        field : 'final_amt_received',
        fxn : (res) => {
            return res['final_amt_received'];
        }
    },
    /* {
        name : 'STOCK',
        field : 'stock',
    }, */
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
                func : showTransactionModal,
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
        <Modal 
            :show=isShowTransactionModal 
            @close="showTransactionModal" 
            @onDialogDisplay="null" 
            extraWidth="max-width:90rem"
        >
            <TransactionModal 
                :transaction="transaction" 
                :branchObject="branchObject" 
                @closeTransactionModal="showTransactionModal"
                @onAddTransaction=onAddTransaction 
            />
        </Modal>
        <Modal 
            :show=isShowRefundModal 
            @close="showRefundModal" 
            @onDialogDisplay="null" 
            extraWidth="max-width:90rem"
        >
            <RefundTransactionModal 
                :transaction="transaction" 
                :branchObject="branchObject"
                :type=TRANSACTION_MODAL_CONSTANTS.REFUND
                @closeTransactionModal=showRefundModal
                @onAddTransaction=onAddTransaction 
            />
        </Modal>
        <Modal 
            :show=isShowReturnModal 
            @close="showReturnModal" 
            @onDialogDisplay="null" 
            extraWidth="max-width:90rem"
        >
            <ReturnItemsModal 
                :transaction="transaction" 
                :branchObject="branchObject"
                :type=TRANSACTION_MODAL_CONSTANTS.RETURN
                @closeTransactionModal=showReturnModal
                @onAddTransaction=onAddTransaction 
            />
        </Modal>
        <Modal 
            :show=isShowStartOfShiftModal
            @close="showStartOfShiftModal" 
            @onDialogDisplay="null" 
            extraWidth="max-width:90rem"
        >
            <StartOfShiftModal 
                :transaction="transaction" 
                :branchObject="branchObject"
                :type=TRANSACTION_MODAL_CONSTANTS.START_SHIFT
                @closeTransactionModal=showStartOfShiftModal
                @onAddTransaction=onAddTransaction 
            />
        </Modal>
        <Modal 
            :show=isShowEndOfShiftModal
            @close="showEndOfShiftModal" 
            @onDialogDisplay="null" 
            extraWidth="max-width:90rem"
        >
            <EndOfShiftModal 
                :transaction="transaction" 
                :branchObject="branchObject"
                :type=TRANSACTION_MODAL_CONSTANTS.END_SHIFT
                @closeTransactionModal=showEndOfShiftModal
                @onAddTransaction=onAddTransaction 
            />
        </Modal>
        <Modal 
            :show=isShowDepositCashModal 
            @close="showDepositCashModal" 
            @onDialogDisplay="null" 
            extraWidth="max-width:90rem"
        >
            <DepositCashModal 
                :transaction="transaction" 
                :branchObject="branchObject"
                :type=TRANSACTION_MODAL_CONSTANTS.CASH_TRANSACTION
                @closeTransactionModal=showDepositCashModal
                @onAddTransaction=onAddTransaction 
            />
        </Modal>
        <Modal 
            :show=isShowWithrawCashModal 
            @close="showWithrawCashModal" 
            @onDialogDisplay="null" 
            extraWidth="max-width:90rem"
        >
            <WithrawCashModal 
                :transaction="transaction" 
                :branchObject="branchObject"
                :type=TRANSACTION_MODAL_CONSTANTS.WITHRAW
                @closeTransactionModal=showWithrawCashModal
                @onAddTransaction=onAddTransaction 
            />
        </Modal>

        <div style="background: #F05340;padding:1%; color:#fff;">
            <b>TRANSACTIONS </b>
        </div>
        
        <div style="width:100%;padding:1%;">
            <div style="width:6%; float:left;padding-top:0.5%">
                <b>SEARCH</b>
            </div>
            <div style="width:15%; float:left;">
                <input placeholder="Transaction Code" v-model="searchString" @change="searchTransactions" type="text" style="width:100%;margin-left:1%;"/>
            </div>
            <div style="width:6%; float:left;padding-top:0.5%;padding-left:2%;">
                <b>USER</b>
            </div>
            <div style="width:30%; float:left;">
                <TextAutoComplete 
                    :itmName="searchUser ? searchUser.name : ''" 
                    :getData="searchUsers" 
                    :itemName="'name'" 
                    :itemIndex="0"
                    :style="'width:95%'"
                    :addNew=openUserModal
                    :disabled="tmpUserObject.designation != 1"
                    @onSelectItem="onSelectUser"
                />
            </div>
            <div style="width:4%; float:left;padding-top:0.5%;padding-left:0%;">
                <b>Date</b>
            </div>
            <div style="width:10%; float:left;">
                <input v-model="transactionDateFrom" @change="searchTransactions" type="date" style="width:100%;margin-left:1%;"/>
            </div>
            <div style="width:10%; padding-left:1%; float:left;">
                <span ref="dropDownMenuButton">
                    <PrimaryButton 
                        :additionalStyles="'padding:8%;'" 
                        @click="toggleDisplayDropdown"
                    >
                        + ACTIONS
                    </PrimaryButton>&nbsp;
                </span>
                <Dropdown 
                    :options="options" 
                    :dropDownMenuButton="dropDownMenuButton" 
                    :onCloseDropDown="onCloseDropDown" 
                    v-if="displayDropdown"
                />
                <!--<PrimaryButton 
                    :additionalStyles="'padding:2%;background:#c9b500;'" 
                    @click="searchTransactions(true)"
                >
                    - RESET SEARCH
                </PrimaryButton>-->
            </div>
        </div>
        
        <div style="width:100%;height:85%;">
            <DataTable @viewItemDetails=showTransactionModal :tableHeaders="tableHeaders" :resultData="resultData" />
        </div>
        <div style="width:100%;height:15%;">
            <div style="width:25%;float:left;border-right:1px solid rgb(240, 83, 64);">
                <div><B>{{ transactionDateFrom }}</B></div>
                <div><B>STARTING CASH : {{ startingCash }}</B></div>
                <div><B>CURRENT CASH : {{ currentCash }}</B></div>
            </div>
            <div style="width:25%;float:left;padding-left:2%;border-right:1px solid rgb(240, 83, 64);">
                <div><B>TOTAL COST : {{ totalCost }}</B></div>
                <div><B>TOTAL PRICE : {{ totalPrice }}</B></div>
                <div><B>TOTAL SALE : {{ totalSales }}</B></div>
            </div>
            <div style="width:25%;float:left;padding-left:2%;border-right:1px solid rgb(240, 83, 64);">
                <div><B>EXPENSES</B></div>
                <div><B>TOTAL : {{ totalExpenses }}</B></div>
                <div><B>NET SALES : {{ totalSales - totalExpenses }}</B></div>
            </div>
            <div style="clear:both"></div>
        </div>
    </AppLayout>
</template>

<style scoped>
</style>
