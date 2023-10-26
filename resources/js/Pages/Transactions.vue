<script setup>
import { getTransaction, getTransactions } from '@/Services/ServerRequests';
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
import {
    TRANSACTION_MODAL_CONSTANTS,
    IS_VAT,
    VAT_PERCENT,
} from '@/Constants'
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

const isShowTransactionModal = ref(false);
const isShowRefundModal = ref(false);
const isShowReturnModal = ref(false);
const isShowStartOfShiftModal = ref(false);
const isShowEndOfShiftModal = ref(false);
const isShowDepositCashModal = ref(false);
const isShowWithrawCashModal = ref(false);

const userObject = ref(JSON.parse(localStorage.getItem('user')));
const companyObject = ref(JSON.parse(localStorage.getItem('company')));
const branchObject = ref(JSON.parse(localStorage.getItem('selected_branch')));

const tempTransaction = {
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
}

const transaction = ref(tempTransaction);

const dropDownMenuButton = ref(null);

const displayDropdown = ref(false);

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
    );
    resultData.value = transaction.data.res;
}

const catchChangeBranch = (branch) => {
    branchObject.value = branch;
    loadTransactions();
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
        transactionDateFrom.value
    );
    resultData.value = transactions.data.res;
}

const showTransactionModal = async (transactionArgument) => {
    if (typeof transactionArgument == 'object') {
        let transactionResult = await getTransaction (transactionArgument.id);
        transaction.value = transactionResult.data.res;
    } else {
        transaction.value = tempTransaction;
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
                showWithrawCashModal ();
                break;
        }
        return;
    }
    isShowTransactionModal.value = !isShowTransactionModal.value;
}

const showRefundModal = () => {
    isShowRefundModal.value = !isShowRefundModal.value;   
}

const showReturnModal = () => {
    isShowReturnModal.value = !isShowReturnModal.value;
}

const showStartOfShiftModal = () => {
    isShowStartOfShiftModal.value = !isShowStartOfShiftModal.value;
}

const showEndOfShiftModal = () => {
    isShowEndOfShiftModal.value = !isShowEndOfShiftModal.value;
}

const showDepositCashModal = () => {
    isShowDepositCashModal.value = !isShowDepositCashModal.value;
}

const showWithrawCashModal = () => {
    isShowWithrawCashModal.value = !isShowWithrawCashModal.value;
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
    {
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
    },
    {
        label : `Deposit Cash Onhand`,
        func : () => showDepositCashModal()
    },
    {
        label : `Withraw Cash Onhand`,
        func : () => showWithrawCashModal()
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
    },
    {
        name : 'CANCELLED',
        field : 'is_cancelled',
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
                :defaultValues=tempTransaction
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
                :type=TRANSACTION_MODAL_CONSTANTS.DEPOSIT
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
            <div style="width:30%; float:left;">
                <input placeholder="Transaction Code" v-model="searchString" @change="searchTransactions" type="text" style="width:100%;margin-left:1%;"/>
            </div>
            <div style="width:4%; float:left;padding-top:0.5%;padding-left:1%;">
                <b>Date</b>
            </div>
            <div style="width:20%; float:left;">
                <input v-model="transactionDateFrom" @change="searchTransactions" type="date" style="width:100%;margin-left:1%;"/>
            </div>
            
            <div style="width:36%; padding-left:1%; float:left;">
                <span ref="dropDownMenuButton">
                    <PrimaryButton 
                        :additionalStyles="'padding:2%;'" 
                        @click="toggleDisplayDropdown"
                    >
                        + NEW TRANSACTION
                    </PrimaryButton>&nbsp;
                </span>
                <Dropdown 
                    :options="options" 
                    :dropDownMenuButton="dropDownMenuButton" 
                    :onCloseDropDown="onCloseDropDown" 
                    v-if="displayDropdown"
                />
                <PrimaryButton 
                    :additionalStyles="'padding:2%;background:#c9b500;'" 
                    @click="searchTransactions(true)"
                >
                    - RESET SEARCH
                </PrimaryButton>
            </div>
        </div>
        
        <div style="width:100%;height:85%;">
            <DataTable @viewItemDetails=showTransactionModal :tableHeaders="tableHeaders" :resultData="resultData" />
        </div>
    </AppLayout>
</template>

<style scoped>

</style>
