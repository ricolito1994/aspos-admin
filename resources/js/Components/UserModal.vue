<script setup>
import {event} from '@/Services/EventBus';
import Modal from '@/Components/Modal.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import { 
    onMounted, 
    onUnmounted, 
    ref,
} from 'vue';
import { saveUser } from '@/Services/ServerRequests';
import { USER_ROLES } from '@/Constants'

const props = defineProps({/* in case */})

const emits = defineEmits(['showUserModal'])

const errors = ref([]);

const isShowUserModal = ref(false);

const typePass = ref(true);

const user = ref({
    'name' : ``,
    'username' : '',
    'company_id' : '',
    'branch_id' : '',
    'email': ``,
    'password' : '',
    'phone' : '123',
    'selected_branch':'',
    'designation' : 3,
    'is_active' : true,
    'created_by' : '',
});

const title = ref("User Modal");

const showUserModal = (userData) => {
    isShowUserModal.value = !isShowUserModal.value;
    if (userData) user.value = userData;
}

const saveUserData = async ( ) => {
    try {
        user.value['is_owner'] = user.designation == 1;
        user.value.name = user.value.name.toUpperCase();
        await saveUser(user.value);
        showUserModal()
    } catch (e) {
        for (let i in e.response.data.err) {
            errors.value[i] = e.response.data.err[i][0];
        }
        console.log(errors.value);
    }
}

onMounted (() => {
    event.on("UserModal:open", user => {
        showUserModal(user)
    })
})

onUnmounted (() => { 
    // onUnmounted hook
    errors.value = {};
});

</script>

<template>
    <Modal 
        :show=isShowUserModal 
        @close=showUserModal 
        @onDialogDisplay="null" 
        customStyle="z-index:200"
        extraWidth="max-width:40rem;"
    >
        <div id="modal-title">
            <div style="float:left">
                <b>{{title}}</b>
            </div>
            <div style="float:right;cursor:pointer;" @click=showUserModal>
                <b>X</b>
            </div>
            <div style="clear:both"></div>
        </div>
        <div id="modal-content">
            <div style="width:100%; height:35%;" >
                <div style="width:50%;float:left;">
                    <B>NAME</B>
                    <input class='uppercase' type="text" v-model="user.name" style="width:99%;"/>
                    <span v-if="errors.name" class="error">{{ errors.name }}</span>
                </div>
                <div style="width:50%;float:left;">
                    <B>USERNAME</B>
                    <input type="text" v-model="user.username" style="width:99%;"/>
                    <span v-if="errors.username" class="error">{{ errors.username }}</span>
                </div>
                <div style="clear:both" />
            </div>
            <div style="width:100%; height:35%;" >
                <div style="width:50%;float:left; ">
                    <B>EMAIL</B>
                    <input type="text" v-model="user.email" style="width:99%;"/>
                    <span v-if="errors.email" class="error">{{ errors.email }}</span>
                </div>
                <div style="width:50%;float:left;">
                    <B>PASSWORD</B>
                    <input 
                        :type="typePass ? 'password' : 'text'" 
                        v-model="user.password"
                        @click="()=>{typePass = !typePass}" 
                        style="width:99%;"
                     />
                    <span v-if="errors.password" class="error">{{ errors.password }}</span>
                </div>
                <div style="clear:both" />
            </div>
            <div style="width:100%; height:35%;" >
                <div style="width:50%;float:left; ">
                    <B>USER ROLE</B>
                    <select v-model="user.designation" style="width:99%;">
                        <option v-for="(t, index) in USER_ROLES" :key="index" :value="t.id">
                            {{ t.name }}
                        </option>
                    </select>
                </div>
                <div style="width:50%;float:left;;">
                    <B>PHONE</B>
                    <input class='uppercase' type="text" v-model="user.phone" style="width:99%;"/>
                    <span v-if="errors.address" class="error">{{ errors.address }}</span>
                </div>
                <div style="clear:both" />
            </div>
        </div>
    
        <div id="modal-footer">
            <div style="float:right">
                <PrimaryButton @click=saveUserData>OK</PrimaryButton>
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