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
} from '@/Constants'
import {event} from '@/Services/EventBus';
import {
    alertBox,
    customerModal,
    ALERT_TYPE
} from '@/Services/Alert'


import TransactionModalLayout from '@/Layouts/TransactionModalLayout.vue'

const itemTransactionTypes = reactive (TRANSACTION_MODAL_CONSTANTS.ITEM_TRANSACTION.types)

const companyObject = reactive (JSON.parse(localStorage.getItem('company')));


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

const transactionDetails = ref ([]);

let tempTransactionDetails = reactive ([]);

let transactionObject = reactive(props.transaction);

const emit = defineEmits(['closeTransactionModal', 'onAddTransaction'])

const closeTModal = () => {
    emit('closeTransactionModal');
}

const addTransaction = (arg) => {
    emit('closeTransactionModal', arg);
}

const title = ref (props.type.title);

const save = () => {

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
    selectedTransaction = selectedTransaction.item; 
    let msg = `This transaction is already referenced to another transaction. Cannot select this transaction.`;
    if (selectedTransaction.ref_transaction_id) {
        alertBox(msg, ALERT_TYPE.ERR)
        if (!errors.value.find(x=>x === msg)) errors.value.push(msg);
    } else {
        let indxErr = errors.value.findIndex(x=>x === msg)
        if (indxErr > -1) errors.value.splice(indxErr, 1);
    }
    transactionObject = selectedTransaction;
    for (let i in selectedTransaction.item_details) {
        let sel = selectedTransaction.item_details[i];
        let latestUnitName = sel.product.unit[sel.product.unit.findIndex(x => x.id === parseInt(sel.latest_rem_bal_unit))].unit_name;
        let latestRemainingBalance = selectedTransaction.item_details[i]['latest_rem_bal_qty'];
        selectedTransaction.item_details[i]['old_qty'] = parseFloat(sel.quantity);
        selectedTransaction.item_details[i]['quantity'] = 0;
        selectedTransaction.item_details[i]['latest_rem_bal_unit_name'] = latestUnitName;
        selectedTransaction.item_details[i]['remaining_balance'] = latestRemainingBalance;
    }
    transactionDetails.value = selectedTransaction.item_details;
}

const changeQuantity = (transactionIndex) => {
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
    let qty =  parseFloat(latestRemainingBalance) - quantity;
    
    if (transactionObject.item_transaction_type == 'SALE') {
        newPrice = Math.abs(newPrice - transPrice);
        newCost = Math.abs(newCost - transCost);
        qty = quantity + parseFloat(latestRemainingBalance)
    }

    let errMsg2 = `${productName} quantity must not be greater than the ordered quantity.`;
    if (quantity > transactionObject.item_details[transactionIndex].old_qty) {
        if (!errors.value.find(x=>x === errMsg2)) errors.value.push(errMsg2);
    } else {
        let indxErr = errors.value.findIndex(x=>x === errMsg2);
        if (indxErr > -1) errors.value.splice(indxErr, 1);
    }
    
    transactionDetails.value[transactionIndex].total_cost = newCost; 
    transactionDetails.value[transactionIndex].total_price = newPrice; 
    transactionDetails.value[transactionIndex].remaining_balance = qty;

    if (errors.value.length > 0) {
        alertBox(errors.value, ALERT_TYPE.ERR)
    }
}
onMounted (() => {
    transactionDetails.value = props.transaction.id ? 
        props.transaction.item_details : [];
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
                        <div style="float:left; width:20%; padding:1%;">
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
                        <div style="float:left; width:15%; padding:1%;">
                            Total Cost
                        </div>
                        <div style="float:left; width:15%; padding:1%;">
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
                        <div style="float:left; width:20%; padding:1%;">
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
                                @change="changeQuantity(transactionDetailIndex)"
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
                        
                        <div style="float:left; width:15%; padding:1%;">
                            <input disabled v-model="transactionDetail.total_cost" type="text" style="width:100%;"/>
                        </div>
                        <div style="float:left; width:15%; padding:1%;">
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
                        Refund Amount <input type="text" />
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
                        <input disabled  type="date" style="width:100%;"/>
                    </div>
                </div>
            </div>
        </template>
    </TransactionModalLayout>
</template>
<style scoped>
</style>