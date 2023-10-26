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
    getCustomers 
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
    ALERT_TYPE
} from '@/Services/Alert'

const props = defineProps({
    transaction : {
        type: Object,
    },
    branchObject : {
        type: Object,
    },
    defaultValues : {
        type: Object,
    }
});

let transactionDetails = reactive (props.transaction.id ? props.transaction.item_details : []);

const emit = defineEmits(['closeTransactionModal', 'onAddTransaction'])

const isUpdate = ref(true);

const title = ref ("NEW TRANSACTION");

const userObject = ref(JSON.parse(localStorage.getItem('user')));

const companyObject = ref(JSON.parse(localStorage.getItem('company')));

const transactionObject = reactive(props.transaction);

const alphaNumeric = ref('0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ');

const isCOD = ref(false);

const isVAT = ref(false);

const change = computed(() => transactionObject.amt_received - amount_payable.value);

const customerType = ref(2);

const customerNamePlacer = ref ("");

const amount_payable = computed(()=>{
    if (transactionObject.item_transaction_type == 'DELIVERY') {
        return 0;
    }

    let discount = DISCOUNT_LIST.find(x => x.id === transactionObject.discount_type).value;
    let vat = transactionObject.vat == null ? 0 : transactionObject.vat;
    let discounted = transactionObject.total_price * discount;
    let vattable = transactionObject.total_price * vat;
    
    transactionObject.discount_percent = discount;

    return ((transactionObject.total_price - discounted) + vattable);
})

const itemTransactionTypes = reactive (TRANSACTION_MODAL_CONSTANTS.ITEM_TRANSACTION.types)

const closeModal = () => {
    emit('closeTransactionModal')
}

const changeQuantity = (i) => {
    transactionDetails[i].total_cost = transactionDetails[i].price_per_unit * transactionDetails[i].quantity;
    transactionDetails[i].total_price = transactionDetails[i].cost_per_unit * transactionDetails[i].quantity;
    computeTotals();
    convertQuantities(i);
}

const randomString = (length, chars) => {
    let result = '';
    for (let i = length; i > 0; --i) result += chars[Math.round(Math.random() * (chars.length - 1))];
    return result;
}

const changeUnit = (i) => {
    let indexUnit = transactionDetails[i].units.findIndex(x => x.id === transactionDetails[i].unit_id);
    transactionDetails[i].unit = transactionDetails[i].units[indexUnit].unit_name;
    transactionDetails[i].price_per_unit = transactionDetails[i].units[indexUnit].price_per_unit;
    transactionDetails[i].cost_per_unit = transactionDetails[i].units[indexUnit].cost_per_unit;
    changeQuantity(i); 
    computeTotals();
}

const onSelectProduct = async (params) => {
    var prod = await getProduct(params.item.id)
        prod = prod.data;

    console.log('params', params)

    if (transactionDetails.find(x=>x.product_code == params.item.product_code)) {
        alert (`${params.item.product_name} already exists.`);
        return;
    }

    transactionDetails[params.index]['product'] = {
        p: params.item, // from /products/get remaining balance only
        q: prod, // from /products/get/{id} with pricelist and unit
    };

    transactionDetails[params.index]['product_code'] = prod.product_code
    transactionDetails[params.index].product_id = prod.id;

    transactionDetails[params.index].units = prod.pricelist[0].unit;
    transactionDetails[params.index].unit = prod.pricelist[0].unit[0].unit_name;
    transactionDetails[params.index].unit_id = prod.pricelist[0].unit[0].id;

    transactionDetails[params.index].price_per_unit = prod.pricelist[0].unit[0].price_per_unit;
    transactionDetails[params.index].cost_per_unit = prod.pricelist[0].unit[0].cost_per_unit;

    transactionDetails[params.index].total_cost = prod.pricelist[0].unit[0].price_per_unit * transactionDetails[params.index].quantity;
    transactionDetails[params.index].total_price = prod.pricelist[0].unit[0].cost_per_unit * transactionDetails[params.index].quantity;

    computeTotals();
    convertQuantities(params.index);
}

const convertQuantities = (i) => {
    let p = transactionDetails[i].product;
    if (!p) return;
    let remBal = p.p.remaining_balance;
    if (p.p.remaining_balance == 0 && !transactionObject.stock) {
        alert("Remaining balance is negative!")
        transactionDetails[i].remaining_balance = transactionDetails[i].quantity;
        return;
    }

    let selectedProductUnits = p.q.pricelist[0].unit;

    let selUnit = selectedProductUnits.find(x => x.id === transactionDetails[i].unit_id)

    let j = p.p.unit_obj.heirarchy - 1;
    let cdn = selUnit.heirarchy >= p.p.unit_obj.heirarchy;
    let ctr =  selUnit.heirarchy - 1 ;
  
    while ( cdn ? ctr >= j : ctr <= j ) {
        
        let sUnit = selectedProductUnits[j];
        
        // if unit of remaining balance 
        // is less than of the selected unit of qty
        if (!cdn) {
            remBal /= parseFloat(sUnit.parent_quantity);
            j--;
        } else {
            
            if (sUnit.heirarchy == p.p.unit_obj.heirarchy) {
                remBal = p.p.remaining_balance;
            } else {
                remBal *= parseFloat(sUnit.parent_quantity);
            }
            j++;
        }

        if (sUnit.heirarchy == selUnit.heirarchy) {
            break;
        }
    }

    transactionDetails[i].quantity = 
        isNaN(parseFloat(transactionDetails[i].quantity)) ? 0 : parseFloat(transactionDetails[i].quantity);
    
    let remainingBalance = 
        !transactionObject.stock ? 
        remBal - transactionDetails[i].quantity: // if stock out (ransactionObject.stock == false), deduct to remaining balance
        remBal + transactionDetails[i].quantity; // if stock in (ransactionObject.stock == true), add to remaining balance

    if (remainingBalance >= 0) {
        transactionDetails[i].remaining_balance = remainingBalance;
    } else {
        alert("Remaining balance is negative!") 
        return;
    }
}

const computeTotals = (watchChangeVal) => {
    let total_cost = 0;
    let total_price = 0;
    for (let i in transactionDetails) {
        total_cost += transactionDetails[i].total_price;
        total_price += transactionDetails[i].total_cost;
        if(watchChangeVal) convertQuantities(i)
    }
    transactionObject.total_price = total_price;
    transactionObject.total_cost = total_cost;
}

const addItem = () => {
    if (isUpdate.value) {
        alertBox("Cannot Edit Transaction", ALERT_TYPE.ERR);
        return;
    }
    transactionDetails.push ({
        'transaction_type' : transactionObject.transaction_type,
        'item_transaction_type' : transactionObject.item_transaction_type,
        'unit': '',
        'units' : [],
        'quantity' : 1,
        'price_per_unit' : 0.0,
        'cost_per_unit' : 0.0,
        'total_cost' : 0.0,
        'total_price' : 0.0,
        'remaining_balance' : 0,
        'product_id' : '',
        'unit_id' : '',
        'branch_id' : props.branchObject.id,
        'company_id' : companyObject.value.id,
        'supplier' : 0,
        'stock' : transactionObject.stock,
        'indx' : randomString(15, alphaNumeric.value),
    })
}

const removeItem = (transactionDetailIndex) => {
    if (isUpdate.value) {
        alertBox("Cannot Edit Transaction", ALERT_TYPE.ERR);
        return;
    }
    transactionDetails.splice (transactionDetailIndex, 1);
    computeTotals(true);
}

const save = async ( ) => {
    if (transactionObject.item_transaction_type=='SALE' && change.value < 0) {
        alertBox('Insufficient amount', ALERT_TYPE.ERR);
        return;
    }

    if (transactionDetails.length == 0) {
        alertBox('Please Choose a Product.', ALERT_TYPE.ERR);
        return;
    }
    try {
        let transaction = await saveTransaction({
            transaction: transactionObject,
            transactionDetails: transactionDetails,
            isCreate : !isUpdate.value,
        });

        transaction.data.res['isUpdate'] = !isUpdate.value;
        transactionObject.value = transaction.data.res;
        alertBox('Transaction success!', ALERT_TYPE.MSG);
        emit('onAddTransaction', transaction.data.res); 
        
        if (!isUpdate.value) isUpdate.value = true;
    } catch (e) {
        alertBox(e.response.data.err ? e.response.data.err : e.response.data.message, 
            ALERT_TYPE.ERR);
    }
}

const onSelectCustomer = (data) => {
    transactionObject.customer_id = data.item.id;
}

const getCustomers1 = async (company_id, searchString) => {
    let data = await getCustomers(company_id, searchString, customerType.value);
    return data;
}

watch(
    () => transactionObject.item_transaction_type, 
    (newVal) => {   
        isVAT.value = false;
        transactionObject.stock = itemTransactionTypes[newVal].stock;
        customerType.value = ((newVal == 'DELIVERY') ? 2 : 1);
        transactionObject.customer_id = '';
        event.emit('TextAutoCompleteComponent:clearSearchText', "");
        for (let i in transactionDetails) {
            transactionDetails[i].item_transaction_type = newVal;
            transactionDetails[i].stock = transactionObject.stock;
            convertQuantities(i);
        }
        computeTotals();
    }
);


watch(
    () => isCOD.value, 
    (newVal) => {   
        if (newVal) {
            transactionObject.amt_released = transactionObject.total_cost;
        } else {
            transactionObject.amt_released = null;
        }
    }
);

watch(
    () => isVAT.value, 
    (newVal) => {   
        if (!newVal) {
            transactionObject.vat = null;
        } else {
            transactionObject.vat = VAT_PERCENT;
        }
    }
);

onMounted(()=>{
    // onmounted hook
    // console.log('transactionObject', props.transaction)
    // transactionDetails = props.transaction.item_details;
    isVAT.value = transactionObject.vat !== null;
    isCOD.value = transactionObject.amt_released !== null && transactionObject.item_transaction_type == 'DELIVERY';
    title.value = props.transaction.id ? props.transaction.transaction_code : 'NEW TRANSACTION';
    props.transaction.stock = props.transaction.stock == 1;
    isUpdate.value = !props.transaction.id ? false : true;
    transactionObject.final_amt_received = amount_payable;
    transactionObject.change = change;
})

onUnmounted(()=>{
    // onUnmounted hook
    // when component is destroyed/closed
    delete transactionObject.transactionDetails;
    transactionObject.vat = null;
    transactionObject.total_price = 0.0;
    transactionObject.total_cost = 0.0;
    amount_payable.value = 0.0;
    change.value = 0.0;
})

</script>
<template>
    <div id="modal-title">
        <div style="float:left">
            <b>{{title}}</b>
        </div>
        <div style="float:right;cursor:pointer;" @click=closeModal>
            <b>X</b>
        </div>
        <div style="clear:both"></div>
    </div>
    <div id="modal-content">
        <div style="width:100%; height:10%;" >
            <div style="width:33.33%; float:left; ">
                <B>{{ transactionObject.item_transaction_type == 'SALE' ? 'CUSTOMER NAME' : 'SUPPLIER' }}</B>
                <TextAutoComplete 
                    :getData="getCustomers1" 
                    itemName="customer_name" 
                    :itmName="transactionObject.customer ? transactionObject.customer.customer_name : customerNamePlacer" 
                    :itemIndex="0"
                    :style="'width:90%'"
                    @onSelectItem="onSelectCustomer"
                />
            </div>
            <div style="width:33.33%;float:left; ">
                <B>TRANSACTION CODE</B>
                <input :disabled="isUpdate" type="text" v-model="transactionObject.transaction_code" style="width:90%;"/>
            </div>
            <div style="width:33.33%;float:left;;">
                <B>TRANSACTION TYPE</B>
                <select :disabled="isUpdate" v-model="transactionObject.item_transaction_type" style="width:100%;">
                    <option v-for="(t, index) in itemTransactionTypes" :key="index" :value="index">
                        {{ index }}
                    </option>
                </select>
            </div>
            <div style="clear:both"></div>
        </div>

        <div style="background-color: #f0534017; width:100%; height:65%; margin-top:1%; padding:1%;" >
            <div style="width:100%;height:10%;">
                <div style='float:left; width:20%;'>
                    <PrimaryButton :additionalStyles="'background: green;'" @click=addItem>+ ADD ITEM</PrimaryButton>
                </div>
            </div>
            <div class="scrollbar" style="width:100%;max-width:150%;height:75%;max-height:75%; overflow:auto;">
                <div style="width:100%;">
                    <div style="float:left; width:20%; padding:1%;">
                        Product Name
                    </div>
                    <div style="float:left; width:10%; padding:1%;">
                        Qty
                    </div>
                    <div style="float:left; width:13%; padding:1%;">
                        Unit
                    </div>
                    <div style="float:left; width:10%; padding:1%;">
                        Price
                    </div>
                    <div style="float:left; width:10%; padding:1%;">
                        Cost
                    </div>
                    <div style="float:left; width:10%; padding:1%;">
                        Total Price
                    </div>
                    <div style="float:left; width:10%; padding:1%;">
                        Total Cost
                    </div>
                    <div style="float:left; width:10%; padding:1%;">
                        Balance
                    </div>
                    <div style="float:left; width:5%; padding:1%;">
                        Actions
                    </div>
                    <div style="clear:both"></div>
                </div>
                <div 
                    v-for="(transactionDetail, transactionDetailIndex) in transactionDetails" 
                    v-bind:key="transactionDetail.indx" 
                    style="width:100%;"
                >
                    <div style="float:left; width:20%; padding:1%;">
                        <TextAutoComplete 
                            :getData="getProducts1" 
                            itemName="product_name" 
                            :itmName="transactionDetail.pp ? transactionDetail.pp.product_name : ''" 
                            :itemIndex="transactionDetailIndex"
                            @onSelectItem="onSelectProduct"
                        />
                    </div>
                    <div style="float:left; width:10%; padding:1%;">
                        <input v-model="transactionDetail.quantity"
                            @keyup="changeQuantity(transactionDetailIndex)" 
                            type="text" 
                            style="width:99%;"
                        />
                    </div>
                    <div style="float:left; width:13%; padding:1%;"> 
                        <select @change="changeUnit(transactionDetailIndex)" v-model="transactionDetail.unit_id" type="text" style="width:99%;">
                            <option v-if='transactionDetail.units' v-for="(unit, uIndex) in transactionDetail.units" :key="uIndex" :value="unit.id">
                                {{ unit.unit_name }}
                            </option>

                            <option v-if='transactionDetail.pp' v-for="(unit, uIndex) in transactionDetail.pp.unit" :key="uIndex" :value="unit.id">
                                {{ unit.unit_name }}
                            </option>
                        </select>
                    </div>
                    <div style="float:left; width:10%; padding:1%;">
                        <input  v-model="transactionDetail.price_per_unit" type="text" style="width:100%;"/>
                    </div>
                    <div style="float:left; width:10%; padding:1%;">
                        <input disabled v-model="transactionDetail.cost_per_unit" type="text" style="width:100%;"/>
                    </div>
                    <div style="float:left; width:10%; padding:1%;">
                        <input disabled v-model="transactionDetail.total_cost" type="text" style="width:100%;"/>
                    </div>
                    <div style="float:left; width:10%; padding:1%;">
                        <input disabled v-model="transactionDetail.total_price" type="text" style="width:100%;"/>
                    </div>
                    <div style="float:left; width:10%; padding:1%;">
                        <input disabled v-model="transactionDetail.remaining_balance" type="text" style="width:100%;"/>
                    </div>
                    <div style="float:left; width:5%; padding:1%;"> 
                        <PrimaryButton additionalStyles="background:#f05340;" @click=removeItem(transactionDetailIndex)>X</PrimaryButton>
                    </div>
                    <div style="clear:both"></div>
                </div>
            </div>
            <div style="width:100%;height:10%;;">
                <div v-if="transactionObject.item_transaction_type == 'SALE'" style='float:left; width:15%;'>
                    Total Price <input type="text" v-model="transactionObject.total_price" />
                </div>
                <div v-if="transactionObject.item_transaction_type == 'DELIVERY'" style='float:left; width:15%;'>
                    Total Cost <input type="text" v-model="transactionObject.total_cost" />
                </div>
                <div 
                    v-if="transactionObject.item_transaction_type == 'DELIVERY'" 
                    style='float:left; padding-top: 0.5%; width:25%; padding-left: 25px; cursor: pointer;'
                >
                   <br> 
                   <input type="checkbox" v-model="isCOD"  id="is_cod" /> &nbsp;
                   <label style="cursor: pointer;" :for="`is_cod`" >
                        <b>Cash on Delivery</b>
                    </label>&nbsp;
                </div>
                <div v-if="transactionObject.item_transaction_type == 'SALE'" style='float:left; width:15%;'>
                    Amount Payable <input type="text" v-model="transactionObject.final_amt_received" />
                </div>
                <div v-if="transactionObject.item_transaction_type == 'SALE'" style='float:left; width:15%;'>
                    Amount <input type="text" v-model="transactionObject.amt_received" />
                </div>
                <div v-if="transactionObject.item_transaction_type == 'SALE'" style='float:left; width:15%;'>
                    Change <input type="text" v-model="change" />
                </div>
                <div v-if="transactionObject.item_transaction_type == 'SALE'" style='float:left; width:15%;'>
                    Discount
                    <select v-model="transactionObject.discount_type">
                        <option 
                            v-for="(discount, index) in DISCOUNT_LIST" 
                            :key="index" 
                            :value="discount.id"
                        >
                            {{ discount.text }}
                        </option>
                    </select>
                </div>
                <div 
                    v-if="transactionObject.item_transaction_type == 'SALE'" 
                    style='float:left; padding-top: 0.5%; width:25%; padding-left: 25px; cursor: pointer;'
                >
                   <br> 
                   <input type="checkbox" v-model="isVAT"  id="is_vat" /> &nbsp;
                   <label style="cursor: pointer;" :for="`is_vat`" >
                        <b>IS Vattable {{ VAT_PERCENT * 100 }}%</b>
                    </label>&nbsp;
                </div>
            </div>
        </div>
        <div style=" width:100%; height:23%; margin-top:1%;" >
            <div style="width:33.33%;float:left;">
                <B>DESCRIPTION</B>
                <textarea v-model="transactionObject.transaction_desc"  style="width:95%; resize:none;" rows="4"/>
            </div>
            <div style="width:33.33%;float:left;">
                <div>
                    <B>USER</B>
                    <input disabled v-model="userObject.name" type="text" style="width:95%;"/>
                </div>
                <div style="margin-top:2%;">
                    <B>BRANCH</B>
                    <input disabled v-model="branchObject.branch_name"  type="text" style="width:95%;"/>
                </div>
            </div>
            <div style="width:33.33%;float:left;">
                <div>
                    <B>DATE</B>
                    <input disabled v-model="transactionObject.transaction_date" type="date" style="width:100%;"/>
                </div>
            </div>
        </div>
    </div>
    <div id="modal-footer">
        <div style="float:right">
            <PrimaryButton @click=save>+ SAVE</PrimaryButton>
        </div>
        <div style="clear:both"></div>
    </div>
</template>
<style scoped>
#modal-content, #modal-title, #modal-footer {
    padding: 1.5%;
}

#modal-title {
    background-color: #f05340;
    color: #fff;
}

#modal-content {
    height:77vh;
}

#modal-content > .prod-m-main-form {
    padding-top:20px;
    padding-bottom:20px;
    overflow:auto;
    max-height: 49vh;
}

#modal-footer {
    border-top:1px solid #ccc;
}

#product-menu {
    border-bottom:1px solid #ccc;
    padding-top: 0px;
    padding-bottom: 17px;
}

.price-list-container {
    width:100%; 
    background:#f0534017;
    padding:1.5%;
}
.price-list-container .unit-table {
    width:100%;
}

.price-list-container .unit-table thead tr th {
    text-align: left;;
}
</style>