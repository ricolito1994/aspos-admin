<script setup>
import { 
    onMounted, 
    onUnmounted, 
    ref, 
    reactive 
} from 'vue';
import { 
    getProducts, 
    getProduct,
    deleteProduct,
    providePaginationData
} from '@/Services/ServerRequests';
import { 
    usePage, 
    Head, 
    Link 
} from '@inertiajs/vue3';
import {
    alertBox,
    customerModal,
    ALERT_TYPE
} from '@/Services/Alert'
import AppLayout from '@/Layouts/AppLayout.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import DataTable from '@/Components/DataTable.vue';
import Modal from '@/Components/Modal.vue';
import ProductModal from '@/Components/ProductModal.vue';

const resultData = ref([]);
const searchString = ref("");
const isShowProductModal = ref(false);
const userObject = ref(JSON.parse(localStorage.getItem('user')));
const companyObject = ref(JSON.parse(localStorage.getItem('company')));
const branchObject = ref(JSON.parse(localStorage.getItem('selected_branch')));

let productObject = ref({
    product_name : '',
    product_desc : '',
    product_code : '-',
    user_id : userObject.value.id,
    company_id : companyObject.value.id,
});

const props = defineProps({
    user: {
        type: Object,
    },
    company: {
        type: Object,
    },
});



onMounted (() => {
    loadProducts();
})

const loadProducts = async () => {
    let products = await getProducts(companyObject.value.id);
    resultData.value = products.data;
}

const showProductModal =  async ( product ) => {
    if(typeof product == 'object') {
        let productRes = await getProduct (product.id);
        productObject.value = productRes.data;
        productObject.value ['transactions'] = product.transactions;
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
}

const deleteProductItem = async (product) => {
    try {
        let confirm = await alertBox("", ALERT_TYPE.CONFIRMATION)

        if (confirm) {
            if (product.transactions.length > 0) {
                alertBox('This product has transactions, cannot delete.', ALERT_TYPE.ERR);
                return;
            }
            await deleteProduct(product.id);
            loadProducts();
            alertBox('Deleted Successfully.', ALERT_TYPE.MSG);
        }
    } catch (e) {
        if(e.response)
        alertBox(e.response.data.error, ALERT_TYPE.ERR);
    }
}

const catchChangeBranch = async (branch) => {
    branchObject.value = branch;
    let products = await getProducts(companyObject.value.id);
    resultData.value = products.data;
}

const onAddProduct = (product) => {
    if (product.isUpdateQty) {
        let indx = resultData.value.data.findIndex(x => x.product_code == product.product_code)
        if (indx > -1)
            resultData.value.data[indx]['remaining_balance'] = product.remaining_balance;
        return;
    }

    if (product.isUpdate) {
        resultData.value.unshift(product);
    } else {
        // update product
        let indx = resultData.value.data.findIndex(x => x.id == product.id)
        if (indx > -1)
            resultData.value.data[indx] = product;
    }
}

const searchProducts = async ( reset ) => {
    if(typeof reset == "boolean") searchString.value = "";

    let products = await getProducts(
        companyObject.value.id, 
        searchString.value.toUpperCase(),
    );
    resultData.value = products.data;
}


const onOpenProductDialog = ( ) => {
}



const tableHeaders = ref([
    {
        name : 'PRODUCT CODE',
        style: 'width:300px',
        field : 'product_code',
    },
    {
        name : 'PRODUCT NAME',
        field : 'product_name',
    },
    {
        name : 'PRICE',
        style: 'width:300px',
        fxn : (res) => {
            let prices = "";
            let priceList = res.pricelist.find(x => x.is_default)
            
            if (!priceList) return ;

            for (let i in priceList.unit) {
                let sel = priceList.unit[i];
                prices += `${sel.price_per_unit}/${sel.unit_name} `;
            }
            
            return prices;
        }
    },
    {
        name : 'UNIT',
        style: 'width:169px',
        field : 'unit_name',
    },
    {
        name : 'BALANCE',
        style: 'width:169px',
        field : 'remaining_balance',
    },
    {
        name : 'ACTIONS',
        style: 'width:300px;',
        actions : {
            view : {
                color : 'rgb(31 41 55 / var(--tw-bg-opacity))',
                label : 'View',
                func : showProductModal
            },
            delete : {
                color : '#f05340',
                label : 'delete',
                func : deleteProductItem
            },
        }
    }
]);

</script>

<template>
    <Head title="Dashboard" />
    <AppLayout :catchChangeBranch="catchChangeBranch">
        <Modal 
            :show=isShowProductModal 
            @close="showProductModal" 
            @onDialogDisplay="onOpenProductDialog"
        >
            <ProductModal 
                :productObject="productObject" 
                :branchObject="branchObject"
                @closeProductModal="showProductModal" 
                @onAddProduct=onAddProduct 
            />
        </Modal>

        <div style="background: #F05340;padding:1%; color:#fff;">
            <b>PRODUCTS </b>
        </div>
        
        <div style="width:100%;padding:1%;">
            <div style="width:6%; float:left;padding-top:0.5%">
                <b>SEARCH</b>
            </div>
            <div style="width:40%; float:left;">
                <input class="uppercase" placeholder="PRODUCT NAME / PRODUCT CODE" v-model="searchString" @change="searchProducts" type="text" style="width:100%;margin-left:1%;"/>
            </div>
            <div style="width:25%; padding-left:1%; float:left;">
                <PrimaryButton :additionalStyles="'padding:3%;'" @click="showProductModal(true)">+ NEW PRODUCT</PrimaryButton>&nbsp;
                <PrimaryButton :additionalStyles="'padding:3%;background:#c9b500;'" @click="searchProducts(true)">- RESET SEARCH</PrimaryButton>
            </div>
        </div>
        
        <div style="width:100%;height:85%;">
            <DataTable 
                @viewItemDetails=showProductModal 
                :tableHeaders="tableHeaders" 
                :resultData="resultData" 
                :getData="providePaginationData"
            />
        </div>
    </AppLayout>
</template>

<style scoped>

</style>
