<script setup>
import { 
    onMounted, 
    onUnmounted, 
    ref, 
    reactive, 
    watch, 
    computed 
} 
from 'vue';
import { 
    getProducts1, 
    getProduct, 
    saveTransaction, 
    getTransactions, 
    getTransaction 
} 
from '@/Services/ServerRequests';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextAutoComplete from '@/Components/TextAutoComplete.vue';

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

let transactionDetails = 
    reactive (props.transaction.id ? props.transaction.item_details : []);

const emit = defineEmits([
    'closeTransactionModal', 
    'onAddTransaction', 
    'onSave'
])

const title = ref (props.type.title);

const closeModal = () => {
    emit('closeTransactionModal')
}

const save = () => {

}
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
            <slot/>
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
    text-align: left;
}
</style>