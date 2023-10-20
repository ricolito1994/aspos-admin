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
    },
    type: {
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

const title = ref (props.type.title);

const save = () => {

}

</script>
<template>
    <TransactionModalLayout 
        :transaction="transaction"
        :brancObject="branchObject"
        :type="type"
        @closeTransactionModal=closeTModal
        @onAddTransaction=addTransaction
        @onSave=save
    >
        <div style="width:100%;">
            <div style="width:33.33%;float:left;">
                <B>DATE</B><br>
                <input  type="date" style="width:90%;"/>
            </div>

            <div style="width:66%;float:left;">
                <B>USER</B><br>
                <input  type="text" style="width:90%;"/>
            </div>
            
        </div>
       
        <div style="width:100%;">
            <div style="width:33.33%;float:left;">
                <br><B>YESTERDAY</B><br>
                <input  type="date" style="width:90%;"/>
            </div>

            <div style="width:40%;float:left;">
                <br><B>AMOUNT REMAINING</B><br>
                <input  type="text" style="width:90%;"/>
            </div>
            
        </div>

        <div style="width:100%;">
            <div style="width:33.33%;float:left;">
                <br><B>TODAY</B><br>
                <input  type="date" style="width:90%;"/>
            </div>

            <div style="width:40%;float:left;">
                <br><B>STARTING AMOUNT</B><br>
                <input  type="text" style="width:90%;"/>
            </div>
            
        </div>

        <div style="width:100%;">
            <div style="width:33.33%;float:left;font-size:30px;">
                <br><B>TOTAL STARTING AMOUNT</B><br>
            </div>
            <div style="width:40%;float:left;font-size:30px;">
                <br><B>0.00</B><br>
            </div>
        </div>

        <div style="width:100%;">
            <div style="width:69.66%;float:left;">
                <br><B>Description/Comments</B><br>
                <textarea style="width:100%;resize:none" rows="10"></textarea>
            </div>
            
        </div>
    </TransactionModalLayout>
</template>
<style scoped>
</style>