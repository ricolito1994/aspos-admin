<script setup>
import {event} from '@/Services/EventBus';
import Modal from '@/Components/Modal.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import { 
    onMounted, 
    onUnmounted, 
    ref,
} from 'vue';
import { saveCustomer } from '@/Services/ServerRequests';
import { CUSTOMER_TYPES } from '@/Constants'

const props = defineProps({/* in case */})

const emits = defineEmits(['showCustomerModal'])

const errors = ref([]);

const isShowCustomerModal = ref(false);

const customer = ref({
    'customer_code' : '',
    'customer_name': '',
    'pwd_no' : '',
    'senior_citizen_no' : '',
    'address' : '',
    'customer_type' : 1,
    'company_id' : 1,
});

const title = ref("Customer Modal");

const showCustomerModal = (customerData) => {
    isShowCustomerModal.value = !isShowCustomerModal.value;
    if (customerData) customer.value = customerData;
}

const saveCustomerData = async ( ) => {
    try {
        await saveCustomer(customer.value);
        showCustomerModal()
    } catch (e) {
        for (let i in e.response.data.err) {
            errors.value[i] = e.response.data.err[i][0];
        }
    }
}

onMounted (() => {
    event.on("CustomerModal:open", customer => {
        showCustomerModal(customer)
    })
})

onUnmounted (() => { 
    // onUnmounted hook
    errors.value = {};
});

</script>

<template>
    <Modal 
        :show=isShowCustomerModal 
        @close=showCustomerModal 
        @onDialogDisplay="null" 
        customStyle="z-index:200"
        extraWidth="max-width:40rem;"
    >
        <div id="modal-title">
            <div style="float:left">
                <b>{{title}}</b>
            </div>
            <div style="float:right;cursor:pointer;" @click=showCustomerModal>
                <b>X</b>
            </div>
            <div style="clear:both"></div>
        </div>
        <div id="modal-content">
            <div style="width:100%; height:35%;" >
                <div style="width:50%;float:left;">
                    <B>CUSTOMER CODE</B>
                    <input class='uppercase' type="text" v-model="customer.customer_code" style="width:99%;"/>
                    <span v-if="errors.customer_code" class="error">{{ errors.customer_code }}</span>
                </div>
                <div style="width:50%;float:left;">
                    <B>CUSTOMER NAME</B>
                    <input class='uppercase' type="text" v-model="customer.customer_name" style="width:99%;"/>
                    <span v-if="errors.customer_name" class="error">{{ errors.customer_name }}</span>
                </div>
                <div style="clear:both" />
            </div>
            <div style="width:100%; height:35%;" >
                <div style="width:50%;float:left; ">
                    <B>PWD NO</B>
                    <input :disabled="customer.customer_type==2" class='uppercase' type="text" v-model="customer.pwd_no" style="width:99%;"/>
                    <span v-if="errors.pwd_no" class="error">{{ errors.pwd_no }}</span>
                </div>
                <div style="width:50%;float:left;">
                    <B>SENIOR CITIZEN NO</B>
                    <input :disabled="customer.customer_type==2" class='uppercase' type="text" v-model="customer.senior_citizen_no" style="width:99%;"/>
                    <span v-if="errors.senior_citizen_no" class="error">{{ errors.senior_citizen_no }}</span>
                </div>
                <div style="clear:both" />
            </div>
            <div style="width:100%; height:35%;" >
                <div style="width:50%;float:left; ">
                    <B>CUSTOMER TYPE</B>
                    <select v-model="customer.customer_type" style="width:99%;">
                        <option v-for="(t, index) in CUSTOMER_TYPES" :key="index" :value="t.value">
                            {{ t.label }}
                        </option>
                    </select>
                </div>
                <div style="width:50%;float:left;;">
                    <B>ADDRESS</B>
                    <input class='uppercase' type="text" v-model="customer.address" style="width:99%;"/>
                    <span v-if="errors.address" class="error">{{ errors.address }}</span>
                </div>
                <div style="clear:both" />
            </div>
        </div>
    
        <div id="modal-footer">
            <div style="float:right">
                <PrimaryButton @click=saveCustomerData>OK</PrimaryButton>
            </div>
            
            <div style="clear:both"></div>
        </div>
    </Modal>
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
    height:35vh;
    max-height: 35vh;
}

#modal-content > .prod-m-main-form {
    padding-top:20px;
    padding-bottom:20px;
    overflow:auto;
    max-height: 50vh;
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

.error {
    color:red;
    font-weight: bold;
    font-size:15px;
}
</style>