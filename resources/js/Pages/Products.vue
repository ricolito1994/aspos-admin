<script setup>
import { getProducts, getProduct } from '@/Services/ServerRequests';
import { usePage, Head, Link } from '@inertiajs/vue3';
import { onMounted, onUnmounted, ref, reactive } from 'vue';
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



onMounted ( async () => {
    let products = await getProducts(companyObject.value.id);
    resultData.value = products.data;
})

const showProductModal =  async ( product ) => {
    if(typeof product == 'object') {
        let productRes = await getProduct (product.id);
        productObject.value = productRes.data;
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

const catchChangeBranch = async (branch) => {
    branchObject.value = branch;
    let products = await getProducts(companyObject.value.id);
    resultData.value = products.data;
}

const onAddProduct = (product) => {
    if (product.isUpdate) {
        resultData.value.unshift(product);
    } else {
        // update product
        let indx = resultData.value.findIndex(x => x.id == product.id)
        if (indx > -1)
            resultData.value[indx] = product;
    }
}

const searchProducts = async ( reset ) => {
    if(typeof reset == "boolean") searchString.value = "";

    let products = await getProducts(companyObject.value.id, searchString.value);
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
        name : 'DESCRIPTION',
        field : 'product_desc',
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
                color : 'blue',
                label : 'View',
                func : showProductModal
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
        <Modal :show=isShowProductModal @close="showProductModal" @onDialogDisplay="onOpenProductDialog">
            <ProductModal :productObject="productObject" :branchObject="branchObject" @closeProductModal="showProductModal" @onAddProduct=onAddProduct />
        </Modal>

        <div style="background: #F05340;padding:1%; color:#fff;">
            <b>PRODUCTS </b>
        </div>
        
        <div style="width:100%;padding:1%;">
            <div style="width:6%; float:left;padding-top:0.5%">
                <b>SEARCH</b>
            </div>
            <div style="width:40%; float:left;">
                <input placeholder="PRODUCT NAME / PRODUCT CODE" v-model="searchString" @change="searchProducts" type="text" style="width:100%;margin-left:1%;"/>
            </div>
            <div style="width:25%; padding-left:1%; float:left;">
                <PrimaryButton :additionalStyles="'padding:3%;'" @click="showProductModal(true)">+ NEW PRODUCT</PrimaryButton>&nbsp;
                <PrimaryButton :additionalStyles="'padding:3%;background:#c9b500;'" @click="searchProducts(true)">- RESET SEARCH</PrimaryButton>
            </div>
        </div>
        
        <div style="width:100%;height:85%;">
            <DataTable @viewItemDetails=showProductModal :tableHeaders="tableHeaders" :resultData="resultData" />
        </div>
    </AppLayout>
</template>

<style scoped>

</style>