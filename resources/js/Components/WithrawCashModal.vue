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

import TransactionModalLayout from '@/Layouts/TransactionModalLayout.vue'

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

const closeTModal = () => {
    emit('closeTransactionModal');
}

const addTransaction = (arg) => {
    emit('closeTransactionModal', arg);
}

const title = ref ("NEW TRANSACTION");

const save = () => {

}

</script>
<template>
    <TransactionModalLayout 
        :transaction="transaction"
        :brancObject="branchObject"
        @closeTransactionModal=closeTModal
        @onAddTransaction=addTransaction
        @onSave=save
    >
        wew
    </TransactionModalLayout>
</template>
<style scoped>
</style>