<script setup>
import { onMounted, onUnmounted, ref, reactive, watch } from 'vue';
import { saveProduct } from '@/Services/ServerRequests';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import DataTable from '@/Components/DataTable.vue';

const props = defineProps({
    productObject : {
        type: Object,
    },
    branchObject : {
        type: Object,
    }
});

const userObject = ref(JSON.parse(localStorage.getItem('user')));
const companyObject = reactive(JSON.parse(localStorage.getItem('company')));
const product = reactive(props.productObject);
const branch = reactive(props.branchObject);
const defaultProdMenu = ref(0);
const alphaNumeric = ref('0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ');
const title = ref('')
const isUpdate = ref(false);

let priceList = reactive([
    {
        pricelist_name: 'DEFAULT PRICE',
        is_default : true,
        company_id : companyObject.id,
        branch_id : branch.id,
        unit : [
            {
                unit_name : 'PIECE',
                parent_quantity : 1,
                branch_id : branch.id,
                heirarchy : 1,
                price_per_unit : 0.0,
                cost_per_unit : 0.0,
                is_default: true,
            }
        ],
    }
]);

const tableHeaders = ref([
    {
        name : 'id',
        field : 'id',
    },
    {
        name : 'DATE',
        field : 'created_at',
    },
    {
        name : 'TRANSACTION TYPE',
        field : 'transaction_type',
    },
    {
        name : 'STOCK',
        field : 'stock',
    },
    {
        name : 'UNIT',
        field : 'unit',
    },
    {
        name : 'QTY',
        field : 'quantity',
    },
    {
        name : 'BALANCE',
        style: 'width:169px',
        field : 'remaining_balance',
    },
]);

const emit = defineEmits(['closeProductModal', 'onAddProduct'])

const newPriceList = ( ) => {
    let isDefault = priceList.length == 0;
    priceList.push({
        pricelist_name: `PRICE # ${priceList.length}`,
        is_default : isDefault,
        company_id : companyObject.id,
        branch_id : branch.id,
        unit : [
            {
                unit_name : 'PIECE',
                parent_quantity : 1,
                branch_id : branch.id,
                heirarchy : 1,
                price_per_unit : 0.0,
                cost_per_unit : 0.0,
                is_default: isDefault,
            }
        ],
    })
    if(!isDefault)
        priceList[priceList.length - 1].is_default = false;
}

const removePricelist = (priceListIndex) => {
    if(priceList[priceListIndex].is_default) {
        return;
    }

    priceList.splice(priceListIndex, 1)
}


const randomString = (length, chars) => {
    let result = '';
    for (let i = length; i > 0; --i) result += chars[Math.round(Math.random() * (chars.length - 1))];
    return result;
}

const save = async () => {
    //console.log('product ',product)
    let newProduct = await saveProduct({
        product : product,
        prices : priceList,
    });
    
    newProduct.data['isUpdate'] = isUpdate.value;
    isUpdate.value = false;

    emit('onAddProduct', newProduct.data)
}

const genarateProductCode = () => {
    let randString = randomString(15, alphaNumeric.value);
    return companyObject.company_code + '-PRD-' + randString;
}

const prodMenuSelect = (index) => {
    defaultProdMenu.value = index;
}

const closeModal = ( ) => {
    emit('closeProductModal')
}

const removeUnit = (priceIndex, unitIndex) => {
    if(unitIndex == 0 || unitIndex < priceList[priceIndex].unit.length - 1) {
        return;
    }
    priceList[priceIndex].unit.splice(unitIndex, 1)
}

const newUnit = (priceIndex, unitIndex) => {
    //let parentUnit =  priceList[priceIndex].unit[unitIndex];
    let unit = {
        unit_name : 'PIECE',
        parent_quantity : 1,
        branch_id : 1,
        heirarchy : priceList[priceIndex].unit.length + 1,
        price_per_unit : 0.0,
        cost_per_unit : 0.0,
        is_default: priceList[priceIndex].is_default,
    }
    priceList[priceIndex].unit.push (unit);
}

const setToDefaultPrice = (priceIndex) => {
    if (priceList.length == 1 || !priceList[priceIndex].is_default) {
        priceList[priceIndex].is_default = true;
        return;
    }

    for (let i in priceList) {
        if (priceIndex!==parseInt(i)) {
            priceList[i].is_default = false ;
            for(let j in priceList[i].unit) {
                priceList[i].unit[j].is_default = false;
            }
        }
    } 
    for(let j in priceList[priceIndex].unit) { 
        priceList[priceIndex].unit[j].is_default = priceList[priceIndex].is_default;
    }
}


onMounted (() => {
    isUpdate.value = !product.id ? true : false;
    console.log(product)
    if (!product.id) {
        product.product_code = genarateProductCode()
        title.value = "NEW PRODUCT";
    } else {
        // update/edit product mode
        title.value = product.product_name;
        priceList = product.pricelist
    }
});

onUnmounted (() => {
    // IF MODAL CLOSES
})

watch (priceList, (oldval, newval) => {
    //console.log(oldval, newval)
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
        <div id="product-menu">
            <PrimaryButton :additionalStyles="defaultProdMenu == 0 ? 'background: #f05340' : ''" @click=prodMenuSelect(0)>PRODUCT DETAILS</PrimaryButton>&nbsp;
            <PrimaryButton :additionalStyles="defaultProdMenu == 1 ? 'background: #f05340' : ''" @click=prodMenuSelect(1)>PRICE LIST</PrimaryButton>&nbsp;
            <PrimaryButton v-if="!isUpdate" :additionalStyles="defaultProdMenu == 2 ? 'background: #f05340' : ''" @click=prodMenuSelect(2)>TRANSACTIONS</PrimaryButton>
        </div>
        <div class="prod-m-main-form" v-if="defaultProdMenu == 0">
            <div style="width:100%;">
                <div style="width:50%;float:left;">
                    <B>PRODUCT NAME</B>
                    <input v-model="product.product_name" type="text" style="width:90%;"/>
                </div>
                <div style="width:50%;float:left;">
                    <B>PRODUCT CODE</B>
                    <input disabled v-model="product.product_code" type="text" style="width:100%;"/>
                </div>
                <div style="clear:both"></div>
            </div>

            <div style="width:100%;margin-top:50px;">
                <div style="width:45%;float:left;">
                    <B>PRODUCT DESCRIPTION</B>
                    <textarea v-model="product.product_desc" type="text" style="width:100%;resize:none;" rows="5"></textarea>
                </div>
                <div style="width:50%;float:left;margin-left:5%;">
                    <B>ENTERED BY</B>
                    <input v-model="userObject.name" disabled type="text" style="width:100%;"/>
                </div>
                <div style="clear:both"></div>
            </div>

            <div style="width:100%;margin-top:50px;">
                <div style="width:45%;float:left;">
                    <B>COMPANY</B>
                    <input disabled type="text" v-model="companyObject.company_name" style="width:100%;"/>
                </div>
            </div>
        </div>
        <div class="prod-m-main-form scrollbar" v-if="defaultProdMenu == 1">
            <div style="width:100%;">
                <PrimaryButton :additionalStyles="'background: #f05340'" @click=newPriceList()>+ NEW PRICE LIST</PrimaryButton>
                &nbsp;<b>Remember: </b> these prices are only available in <b>{{ branchObject.branch_name }}</b>.
            </div>
            <div v-for="(price, priceIndex) in priceList" :key="priceIndex" style="width:100%;margin-top:2%">
                <div style="background: #f05340; width: 100%; padding:1.5%; color: white;">
                    <div style="float:left;margin-top:4px;">
                        <b>{{price.pricelist_name}}</b>
                    </div>
                    <div style="float:right;">
                        <input 
                            @change="setToDefaultPrice(priceIndex)" 
                            style="cursor: pointer;" 
                            v-model="price.is_default" 
                            :id="`default-price-list-${priceIndex}`"
                            type="checkbox" 
                        />&nbsp;
                        <label style="cursor: pointer;" :for="`default-price-list-${priceIndex}`" @change="setToDefaultPrice(priceIndex)">
                            <b>DEFAULT PRICE</b>
                        </label>&nbsp;
                        <PrimaryButton :additionalStyles="'background: #e17b7b;'" @click=removePricelist(priceIndex)>X REMOVE</PrimaryButton>
                    </div>
                    <div style="float:right;">
                        
                    </div>
                    <div style="clear:both"></div>
                </div>
                <div class="price-list-container">
                    <div style="width:100%;">
                        <div style="float:left; width:45%">
                            PRICE LIST NAME
                            <input type="text" v-model="price.pricelist_name" style="width:100%"/>
                        </div>
                        <div style="float:right; width:50%">
                            SUPPLIER
                            <input type="text" style="width:100%"/>
                        </div>
                        <div style="clear:both"></div>
                    </div>
                    <div style="width:100%;margin-top:40px">
                        <table class="unit-table">
                            <thead>
                                <tr>
                                    <th>EQUIVALENT TO</th>
                                    <th>UNIT</th>
                                    <th>DEFAULT</th>
                                    <th>HEIRARCHY</th>
                                    <th>PRICE</th>
                                    <th>COST</th>
                                    <th>ACTIONS</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="(unit, unitIndex) in price.unit" :key="unitIndex">
                                    <td>
                                        <span v-if="price.unit[unitIndex - 1]">
                                            1 - <input  v-model="price.unit[unitIndex - 1].unit_name" type="text" style="width:100px;">&nbsp;=
                                        </span>
                                        <span v-if="!price.unit[unitIndex - 1]">
                                            1 - <input  v-model="unit.unit_name" type="text" style="width:100px;">&nbsp;=
                                        </span>
                                        <input v-model="unit.parent_quantity" type="text" style="width:100px;">
                                    </td>
                                    <td>
                                        <input v-model="unit.unit_name" type="text" >
                                    </td>
                                    <td >
                                        <input disabled v-model="unit.is_default" type="checkbox" >
                                    </td>
                                   
                                    <td>
                                        <input v-model="unit.heirarchy" disabled type="text" style="width:100px;">
                                    </td>
                                    <td>
                                        <input v-model="unit.price_per_unit" type="text" style="width:100px;">
                                    </td>
                                    <td>
                                        <input v-model="unit.cost_per_unit" type="text" style="width:100px;">
                                    </td>
                                    <td>
                                        <PrimaryButton :additionalStyles="'background: #e17b7b;'" @click=removeUnit(priceIndex,unitIndex)>X</PrimaryButton>&nbsp;
                                        <PrimaryButton :additionalStyles="'background: #86e17b;'" @click=newUnit(priceIndex,unitIndex)>+</PrimaryButton>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <div class="prod-m-main-form" v-if="defaultProdMenu == 2">
            <div>
                Transactions at: <b>{{ branchObject.branch_name }}</b>
            </div>
            <div>
                <DataTable :tableHeaders="tableHeaders" :resultData="product.transactions" />
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
    padding: 1.7%;
}

#modal-title {
    background-color: #f05340;
    color: #fff;
}

#modal-content {
    height:60vh;
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