<script setup>
import {event} from '@/Services/EventBus';
import Modal from '@/Components/Modal.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import { 
    onMounted, 
    onUnmounted, 
    ref,
} from 'vue';
import {CONFIRMATION_MESSAGE_ALERT} from '@/Services/Alert'

const props = defineProps({
})

const emits = defineEmits(['showAlertBox'])

const isShowAlertBox = ref(false);

const message = ref(null);

const alertType = ref({});

const title = ref("");

const messageIsObject = ref(false);

const showAlertBox = ( messageObject ) => {
    isShowAlertBox.value = !isShowAlertBox.value;
    if (messageObject) {
        messageIsObject.value = typeof messageObject.message === 'object';
        message.value = messageObject.message;
        alertType.value = messageObject.alertType;
        title.value = messageObject.alertType.title;
    }
}

const actions = ( action ) => {
    const confirmActions = {
        OK : () => {
            event.emit("AlertBox:ok", null)
            showAlertBox();
        },
        CANCEL : () => {
            event.emit("AlertBox:cancel", null)
            showAlertBox();
        }
    }
    confirmActions[action]();
}

onMounted (() => {
    event.on("AlertBox:open", messageObject => {
        showAlertBox(messageObject)
    })
})

onUnmounted (() => { 

});

</script>

<template>
    <Modal 
        :show=isShowAlertBox 
        @close=showAlertBox 
        @onDialogDisplay="null" 
        customStyle="z-index:100"
        extraWidth="max-width:30rem;"
        :isAlertBox="true"
    >
        <div id="modal-title">
            <div style="float:left">
                <b>{{title}}</b>
            </div>
            <div style="float:right;cursor:pointer;" @click=showAlertBox>
                <b>X</b>
            </div>
            <div style="clear:both"></div>
        </div>
        <div id="modal-content">
            <div v-if="!messageIsObject">
                {{ message }}
            </div>
            <div v-else>
                <ul>
                    <li v-for="(msg, index) in message" :key="index"> {{ msg }}</li>
                </ul>
            </div>   
            <div style="margin-top:1%;font-size: 20px;" v-if="alertType.value === 'confirmation'">
                <b>{{ CONFIRMATION_MESSAGE_ALERT }}</b>
            </div>
        </div>
    
        <div id="modal-footer">
            <div style="float:right">
                <PrimaryButton @click='actions("OK")'>OK</PrimaryButton>
            </div>
            <div v-if="alertType.value === 'confirmation'" style="float:right;margin-right:1%;">
                <PrimaryButton additionalStyles="background:#f05340;" @click='actions("CANCEL")'>CANCEL</PrimaryButton>
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
    height:20vh;
    max-height: 20vh;
    overflow-y: scroll;
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