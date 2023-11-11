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
    getProducts1, 
    getProduct, 
    saveTransaction, 
    getCustomers ,
    getTransactions,
    searchTransaction,
} from '@/Services/ServerRequests';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextAutoComplete from './TextAutoComplete.vue';
import {
    TRANSACTION_MODAL_CONSTANTS,
    DISCOUNT_LIST,
    VAT_PERCENT,
} from '@/Constants';
import moment from 'moment';
import {event} from '@/Services/EventBus';
import {
    alertBox,
    customerModal,
    ALERT_TYPE
} from '@/Services/Alert'
import { convertQuantity } from '@/Services/StockService'

import TransactionModalLayout from '@/Layouts/TransactionModalLayout.vue'

const itemTransactionTypes = reactive (TRANSACTION_MODAL_CONSTANTS.ITEM_TRANSACTION.types)

const companyObject = reactive (JSON.parse(localStorage.getItem('company')));

const totalAmountRefund = ref(0);

const currentDate = ref(moment().format('YYYY-MM-DD'));

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

const custName = ref("");

const errors = ref([]);

const confirms = ref([]);

const transactionDetails = ref ([]);

let transactionObject = reactive(props.transaction);

const emit = defineEmits(['closeTransactionModal', 'onAddTransaction'])

const closeTModal = () => {
    emit('closeTransactionModal');
}

const addTransaction = (arg) => {
    emit('closeTransactionModal', arg);
}

const title = ref (props.type.title);

const save = async () => {
    recheckForQtyZero();

    if (errors.value.length > 0) {
        alertBox(errors.value, ALERT_TYPE.ERR);
        return;
    }

    let confirm = true;
    if (confirms.value.length > 0) {
        confirm = await alertBox(confirms.value, ALERT_TYPE.CONFIRMATION);
    }


    if (confirm) {
        console.log(confirm, transactionObject.stock)
    };
}

const recheckForQtyZero = () => {
    let numZeroQty = transactionDetails.value.length;
    for (let i in transactionDetails.value) {
        let sel = transactionDetails.value[i];
        if (sel.quantity <= 0) {
            numZeroQty--;
        }
    }

    let errMsg2 = `Please make at least 1 item to have a quantity more than 0.`;
    if ( numZeroQty <= 0 ) {
        if (!errors.value.find(x=>x === errMsg2)) errors.value.push(errMsg2);
    } else {
        let indxErr = errors.value.findIndex(x=>x === errMsg2);
        if (indxErr > -1) errors.value.splice(indxErr, 1);
    }
}

const searchTransactions = (index, searchString) => {
    return new Promise ( async (resolve, reject) => {
        try {
            let transactions = await searchTransaction (
                searchString,
                companyObject.id,
                props.branchObject.id
            );
            resolve (transactions)
        } catch (e) {
            reject (e);
        }
    })
}


const onSelectTransaction = (selectedTransaction) => {
    selectedTransaction = selectedTransaction.item ? selectedTransaction.item : selectedTransaction; 
    if (selectedTransaction.item) {
        let msg = `This transaction is already referenced to another transaction. Cannot select this transaction.`;
        if (selectedTransaction.ref_transaction_id) {
            alertBox(msg, ALERT_TYPE.ERR)
            if (!errors.value.find(x=>x === msg)) errors.value.push(msg);
        } else {
            let indxErr = errors.value.findIndex(x=>x === msg)
            if (indxErr > -1) errors.value.splice(indxErr, 1);
        }
    }

    transactionObject = selectedTransaction;
    transactionObject.stock = selectedTransaction.stock == 0 ? 1 : 0;
    transactionObject.ref_transaction_id = selectedTransaction.id;

    for (let i in selectedTransaction.item_details) {
        let sel = selectedTransaction.item_details[i];
        let latestUnitName = sel.product.unit[sel.product.unit.findIndex(x => x.id === parseInt(sel.latest_rem_bal_unit))].unit_name;
        let latestRemainingBalance = selectedTransaction.item_details[i]['latest_rem_bal_qty'];
        selectedTransaction.item_details[i]['old_qty'] = parseFloat(sel.quantity);
        selectedTransaction.item_details[i]['quantity'] = selectedTransaction.item_details[i]['old_qty'];
        selectedTransaction.item_details[i]['latest_rem_bal_unit_name'] = latestUnitName;
        selectedTransaction.item_details[i]['remaining_balance'] = latestRemainingBalance;
    }
    totalAmountRefund.value = 0;
    transactionDetails.value = selectedTransaction.item_details;
    for (let i in selectedTransaction.item_details) {
        changeQuantity(i, true)
    }
}

const changeQuantity = (transactionIndex, onSelectProduct) => {
    if(!onSelectProduct) totalAmountRefund.value = 0;
    let prodCost = transactionObject.item_details[transactionIndex].cost_per_unit;
    let prodPrice = transactionObject.item_details[transactionIndex].price_per_unit;
 
    let transPrice = transactionObject.item_details[transactionIndex].old_qty * prodPrice;
    let transCost = transactionObject.item_details[transactionIndex].old_qty * prodCost;

    let quantity = parseFloat(transactionDetails.value[transactionIndex].quantity); 
    let latestRemainingBalance = transactionObject.item_details[transactionIndex].latest_rem_bal_qty;

    let productName = transactionObject.item_details[transactionIndex].product.product_name;
    
    let errMsg1 = `${productName} must be greater than 0`;
    if (quantity < 0) {
        if (!errors.value.find(x=>x === errMsg1)) errors.value.push(errMsg1);
    } else {
        let indxErr = errors.value.findIndex(x=>x === errMsg1);
        if (indxErr > -1) errors.value.splice(indxErr, 1);
    }


    let newPrice = Math.abs((quantity * prodPrice) - transPrice);
    let newCost = Math.abs((quantity * prodCost) - transCost);
        newPrice = Math.abs(newPrice - transPrice);
        newCost = Math.abs(newCost - transCost);

    let convertedQuantity = convertQuantity(
        transactionObject.item_details[transactionIndex].product.unit,
        quantity,
        transactionObject.item_details[transactionIndex].unit[0]
    ); 

    let remainingBalance =  parseFloat(latestRemainingBalance) - convertedQuantity;

    if (transactionObject.item_transaction_type == 'SALE') {
        totalAmountRefund.value += newPrice;
        remainingBalance = convertedQuantity + parseFloat(latestRemainingBalance)
    } else {
        totalAmountRefund.value += newCost;
    }

    let errMsg2 = `${productName} quantity must not be greater than the ordered quantity.`;
    if (convertedQuantity > transactionObject.item_details[transactionIndex].old_qty) {
        if (!errors.value.find(x=>x === errMsg2)) errors.value.push(errMsg2);
    } else {
        let indxErr = errors.value.findIndex(x=>x === errMsg2);
        if (indxErr > -1) errors.value.splice(indxErr, 1);
    }

    let confirmMsg1 = `${productName} quantity is less than 0.`;
    if (remainingBalance < 0) {
        if (!confirms.value.find(x=>x === confirmMsg1)) confirms.value.push(confirmMsg1);
    } else {
        let indxConf = confirms.value.findIndex(x=>x === confirmMsg1);
        if (indxConf > -1) confirms.value.splice(indxConf, 1);
    }
    
    transactionDetails.value[transactionIndex].total_cost = newCost; 
    transactionDetails.value[transactionIndex].total_price = newPrice; 
    transactionDetails.value[transactionIndex].remaining_balance = remainingBalance;

    recheckForQtyZero();

    if(!onSelectProduct) {  
        calculateTotals(transactionIndex);
    }

    if (errors.value.length > 0) {
        alertBox(errors.value, ALERT_TYPE.ERR)
    }

    transactionObject[transactionObject.item_transaction_type == 'SALE' ? 'amt_received' : 'amt_released']
        = totalAmountRefund.value;

    if (transactionObject.item_transaction_type == 'SALE') {
        transactionObject['final_amt_released'] = totalAmountRefund.value;
    }
}

const calculateTotals = (excludeIndex) => {
    for (let i in transactionDetails.value) {
        if (i != excludeIndex) {
            let sel = transactionDetails.value[i];
            if (transactionObject.item_transaction_type == 'SALE') {
                totalAmountRefund.value += sel.total_price;
            } else {
                totalAmountRefund.value += sel.total_cost;
            }
        }
    }
}

onMounted (() => {
    transactionDetails.value = props.transaction.id ? 
        props.transaction.item_details : [];

    currentDate.value = 
        transactionObject.created_at ? transactionObject.created_at : currentDate.value;

    transactionObject.transaction_type = props.type;

    if (transactionObject.id) 
        onSelectTransaction(transactionObject)
})

</script>
<template>
    <TransactionModalLayout 
        :transaction="transaction"
        :branchObject="branchObject"
        :type="type"
        @closeTransactionModal=closeTModal
        @onAddTransaction=addTransaction
        @onSave=save
    >
        <template #transaction="{ branchObject, userObject }">
            <div style="width:100%;" >
                <div style="width:33.33%;float:left; ">
                    <B>CUSTOMER</B>
                    <input 
                        v-if="transactionObject.customer" 
                        v-model='transactionObject.customer.customer_name' 
                        style="width:90%;"
                        type="text"  
                        disabled 
                    />
                    <input v-else disabled v-model='custName' type="text"  style="width:90%;"/>
                </div>
                <div style="width:33.33%; float:left; ">
                    <B>SEARCH TRANSACTION CODE</B>
                    <TextAutoComplete 
                        :itmName="transactionObject.ref_transaction_id" 
                        :getData="searchTransactions" 
                        :itemName="'transaction_code'" 
                        :itemIndex="0"
                        :style="'width:90%'"
                        @onSelectItem="onSelectTransaction"
                    />
                </div>
                <div style="width:33.33%;float:left;;">
                    <B>TRANSACTION TYPE</B>
                    <select disabled v-model="transactionObject.item_transaction_type" style="width:100%;">
                        <option v-for="(t, index) in itemTransactionTypes" :key="index" :value="index">
                            {{ index }}
                        </option>
                    </select>
                </div>
                <div style="clear:both"></div>
            </div>
            <div style="background-color: #f0534017; width:100%; height:65%; margin-top:1%; padding:0.5%;" >
                <div class="scrollbar" style="width:100%;max-width:150%;height:85%;max-height:85%; overflow:auto;">
                    <div style="width:100%;height:15%;">
                        <div style="float:left; width:35%; padding:1%;">
                            Product Name
                        </div>
                        <div style="float:left; width:10%; padding:1%;">
                            Ordered Qty
                        </div>
                        <div style="float:left; width:10%; padding:1%;">
                            New Qty
                        </div>
                        <div style="float:left; width:15%; padding:1%;">
                            Unit
                        </div>
                        <div 
                            v-if="transactionObject.item_transaction_type == 'DELIVERY'" 
                            style="float:left; width:15%; padding:1%;"
                        >
                            Total Cost
                        </div>
                        <div v-else style="float:left; width:15%; padding:1%;">
                            Total Price
                        </div>
                        <div style="float:left; width:15%; padding:1%;">
                            Balance
                        </div>
                        
                        <div style="clear:both"></div>
                    </div>
                    <div 
                        v-for="(transactionDetail, transactionDetailIndex) in transactionDetails" 
                        v-bind:key="transactionDetail.indx" 
                        style="width:100%;"
                    >
                        <div style="float:left; width:35%; padding:1%;">
                            <input disabled type="text" v-model="transactionDetail.product.product_name" style="width:100%;"/>
                        </div>
                        <div style="float:left; width:10%; padding:1%;">
                            <input 
                                disabled
                                type="text" 
                                style="width:99%;"
                                v-model="transactionDetail.old_qty"
                            />
                        </div>
                        <div style="float:left; width:10%; padding:1%;">
                            <input 
                                v-model="transactionDetail.quantity"
                                @keyup="changeQuantity(transactionDetailIndex)"
                                style="width:100%;"   
                                type="text"
                            />
                        </div>

                        <div style="float:left; width:15%; padding:1%;"> 
                            <select disabled v-model="transactionDetail.unit_id" type="text" style="width:99%;">
                                <option v-for="(unit, uIndex) in transactionDetail.unit" :key="uIndex" :value="unit.id">
                                    {{ unit.unit_name }}
                                </option>
                            </select>
                        </div>
                        
                        <div v-if="transactionObject.item_transaction_type == 'DELIVERY'" style="float:left; width:15%; padding:1%;">
                            <input disabled v-model="transactionDetail.total_cost" type="text" style="width:100%;"/>
                        </div>
                        <div v-else style="float:left; width:15%; padding:1%;">
                            <input disabled v-model="transactionDetail.total_price" type="text" style="width:100%;"/>
                        </div>
                        <div style="float:left; width:15%; padding:1%;">
                            <input disabled v-model="transactionDetail.remaining_balance" type="text" style="width:50%;"/> 
                            &nbsp;<b>{{ transactionDetail.latest_rem_bal_unit_name }}</b>
                        </div>
                        
                        <div style="clear:both"></div>
                        </div>
                        <div  style="width:100%;height:85%;">
                    </div>
                </div>
                <div style="width:100%;height:10%;">
                    <div style='float:left; width:15%;'>
                        Refund Amount
                        <input  
                            type="text" 
                            v-model="transactionObject.amt_released"
                            v-if="transactionObject.item_transaction_type == 'DELIVERY'" 
                        />
                        <input v-else type="text" v-model="transactionObject.amt_received" />
                    </div>
                    <div 
                        style='float:left; padding-top: 0.5%; width:25%; padding-left: 25px; cursor: pointer;'
                    >
                    <br> 
                    <!---<input type="checkbox"   id="is_full_refund" /> &nbsp;
                    <label style="cursor: pointer;" :for="`is_full_refund`" >
                        <b>FULL REFUND</b>
                    </label>&nbsp;-->
                </div>
                </div>
                
            </div>
            <div style=" width:100%; height:23%; margin-top:1%;" >
                <div style="width:33.33%;float:left;">
                    <B>REASON</B>
                    <textarea style="width:95%; resize:none;" rows="4"/>
                </div>
                <div style="width:33.33%;float:left;">
                    <div>
                        <B>USER</B>
                        <input disabled v-model="userObject.name" type="text" style="width:95%;"/>
                    </div>
                    <div style="margin-top:2%;">
                        <B>BRANCH</B>
                        <input disabled type="text" v-model="branchObject.branch_name" style="width:95%;"/>
                    </div>
                </div>  
                <div style="width:33.33%;float:left;">
                    <div>
                        <B>DATE</B>
                        <input disabled v-model="transactionObject.transaction_date"  type="date" style="width:100%;"/>
                    </div>
                    <div style="margin-top:2%;">
                        <B>REFUND DATE</B>
                        <input disabled type="date" v-model="currentDate" style="width:100%;"/>
                    </div>
                </div>
            </div>
        </template>
    </TransactionModalLayout>
</template>
<style scoped>
</style>