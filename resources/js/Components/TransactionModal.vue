<script setup>
import { onMounted, onUnmounted, ref, reactive, watch, computed } from 'vue';
import { getProducts1, getProduct, saveTransaction, getTransactions, getTransaction } from '@/Services/ServerRequests';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextAutoComplete from './TextAutoComplete.vue';

const props = defineProps({
    transaction : {
        type: Object,
    },
    branchObject : {
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

const transactionTypes = reactive ({
    'SALE' : {
        stock : false,
    },
    'DELIVERY' : {
        stock : true,
    },
    'RETURN' : {
        stock : true,
    },
    'DISPOSE' : {
        stock : false,
    },
})

const closeModal = () => {
    emit('closeTransactionModal')
}

const changeQuantity = (i) => {
    transactionDetails[i].total_cost = transactionDetails[i].price_per_unit * transactionDetails[i].quantity;
    transactionDetails[i].total_price = transactionDetails[i].cost_per_unit * transactionDetails[i].quantity;
    computeTotals();
    convertQuantities(i);
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

    transactionDetails[params.index]['product'] = {
        p: params.item, // from /products/get remaining balance only
        q: prod, // from /products/get/{id} with pricelist and unit
    };

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
    console.log(transactionObject.stock,p.p)
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

    transactionDetails[i].quantity = parseFloat(transactionDetails[i].quantity);
    
    let remainingBalance = 
        !transactionObject.stock ? 
        remBal - transactionDetails[i].quantity: // if stock out (ransactionObject.stock == false), deduct to remaining balance
        remBal + transactionDetails[i].quantity; // if stock in (ransactionObject.stock == true), add to remaining balance

        console.log(cdn , j , ctr , remainingBalance, remBal, transactionDetails[i].quantity)

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
    transactionDetails.push ({
        'transaction_type' : transactionObject.transaction_type,
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
    })
}

const removeItem = (i) => {
    transactionDetails.splice (i, 1);
    computeTotals(true);
}

const save = async ( ) => {
    //console.log(isUpdate.value)
    if (isUpdate.value) {
        alert("Cannot edit transaction.")
        return;
    }

    let transaction = await saveTransaction({
        transaction: transactionObject,
        transactionDetails: transactionDetails,
        isCreate : isUpdate.value,
    });

    emit('onAddTransaction', transaction.data)
}


watch(
    () => transactionObject.transaction_type , 
    (newVal) => {   
        transactionObject.stock = transactionTypes[newVal].stock;
        for (let i in transactionDetails) convertQuantities(i)
        computeTotals();
    }
);

onMounted(()=>{
    // onmounted hook
    // console.log('transactionObject', props.transaction)
    // transactionDetails = props.transaction.item_details;
    title.value = props.transaction.id ? props.transaction.transaction_code : 'NEW TRANSACTION';
    isUpdate.value = typeof props.transaction.id !== undefined;
})

onUnmounted(()=>{
    // onUnmounted hook
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
            <div style="width:33.33%;float:left;">
                <B>TRANSACTION CODE</B>
                <input  type="text" v-model="transactionObject.transaction_code" style="width:90%;"/>
            </div>

            <div style="width:33.33%;float:left;margin-left:-1.5%;">
                <B>TRANSACTION TYPE</B>
                <select v-model="transactionObject.transaction_type" style="width:100%;">
                    <option v-for="(t, index) in transactionTypes" :key="index" :value="index">
                        {{ index }}
                    </option>
                </select>
            </div>

            <div style="width:33.33%; float:right;">
                <B>STOCK TYPE</B>
                <select v-model="transactionObject.stock" disabled style="width:100%;">
                    <option value="true">IN</option>
                    <option value="false">OUT</option>
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
            <div class="scrollbar" style="width:100%;max-width:150%;height:80%;max-height:80%; overflow:auto;">
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
                        Cost
                    </div>
                   
                    <div style="float:left; width:10%; padding:1%;">
                        Price
                    </div>
                    <div style="float:left; width:10%; padding:1%;">
                        Total Cost
                    </div>
                    <div style="float:left; width:10%; padding:1%;">
                        Total Price
                    </div>
                    <div style="float:left; width:10%; padding:1%;">
                        Balance
                    </div>
                    <div style="float:left; width:5%; padding:1%;">
                        Actions
                    </div>
                    <div style="clear:both"></div>
                </div>
                <div v-for="(t, index) in transactionDetails" :key="index" style="width:100%;">
                    <div style="float:left; width:20%; padding:1%;">
                        <TextAutoComplete :getData="getProducts1" itemName="product_name" :itmName="t.pp ? t.pp.product_name : ''" :itemIndex="index" @onSelectItem="onSelectProduct"/>
                    </div>
                    <div style="float:left; width:10%; padding:1%;">
                        <input v-model="t.quantity" @keyup="changeQuantity(index)" type="text" style="width:99%;"/>
                    </div>
                    <div style="float:left; width:13%; padding:1%;"> 
                        <select @change="changeUnit(index)" v-model="t.unit_id" type="text" style="width:99%;">
                            <option v-if='t.units' v-for="(unit, uIndex) in t.units" :value="unit.id">
                                {{ unit.unit_name }}
                            </option>

                            <option v-if='t.pp' v-for="(unit, uIndex) in t.pp.unit" :value="unit.id">
                                {{ unit.unit_name }}
                            </option>
                        </select>
                    </div>
                    <div style="float:left; width:10%; padding:1%;">
                        <input disabled v-model="t.price_per_unit" type="text" style="width:100%;"/>
                    </div>
                    <div style="float:left; width:10%; padding:1%;">
                        <input disabled v-model="t.cost_per_unit" type="text" style="width:100%;"/>
                    </div>
                    <div style="float:left; width:10%; padding:1%;">
                        <input disabled v-model="t.total_cost" type="text" style="width:100%;"/>
                    </div>
                    <div style="float:left; width:10%; padding:1%;">
                        <input disabled v-model="t.total_price" type="text" style="width:100%;"/>
                    </div>
                    <div style="float:left; width:10%; padding:1%;">
                        <input disabled v-model="t.remaining_balance" type="text" style="width:100%;"/>
                    </div>
                    <div style="float:left; width:5%; padding:1%;"> 
                        <PrimaryButton additionalStyles="background:#f05340;" @click=removeItem(index)>X </PrimaryButton>
                    </div>
                    <div style="clear:both"></div>
                </div>
            </div>
            <div style="width:100%;height:10%;">
                <br>
                <div style='float:left; width:15%;'>
                    total price: <b>{{ transactionObject.total_price }}</b> 
                </div>
                
                <div style='float:left; width:15%;'>
                    total cost: <b>{{ transactionObject.total_cost }}</b> 
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