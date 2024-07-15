<script setup>
import { 
    onMounted, 
    onUnmounted, 
    ref, 
    reactive, 
    watch, 
    computed ,
    getCurrentInstance
} from 'vue';
import { 
    getProducts1, 
    getProduct, 
    saveTransaction, 
    getCustomers,
    getCurrentBalance,
    searchTransaction
} from '@/Services/ServerRequests';
import {
    TRANSACTION_MODAL_CONSTANTS,
    DISCOUNT_LIST,
    VAT_PERCENT,
} from '@/Constants'
import {event} from '@/Services/EventBus';
import {
    alertBox,
    customerModal,
    ALERT_TYPE
} from '@/Services/Alert'
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextAutoComplete from './TextAutoComplete.vue';
import Modal from '@/Components/Modal.vue';
import ProductModal from '@/Components/ProductModal.vue';
import PendingTransactionModal from '@/Components/PendingTransactionModal.vue';
const props = defineProps({
    transaction : {
        type: Object,
    },
    branchObject : {
        type: Object,
    },
    defaultValues : {
        type: Object,
    },
    product: {
        type: Object,
        required: false,
        default : null,
    },
    userDesignation: {
        type: Boolean,
        required: false,
    },
    isNotFromDialog: {
        type: Boolean,
        required: false,
    },
});

let transactionDetails = reactive (props.transaction.id ? props.transaction.item_details : []);

let searchProductName = ref("")

const emit = defineEmits(['closeTransactionModal', 'onAddTransaction'])

const isUpdate = ref(true);

const title = ref ("NEW TRANSACTION");

const userObject = ref(JSON.parse(localStorage.getItem('user')));

const companyObject = ref(JSON.parse(localStorage.getItem('company')));

const branchObject = ref(JSON.parse(localStorage.getItem('selected_branch')));

const transactionObject = reactive(props.transaction);

const alphaNumeric = ref('0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ');

const isCOD = ref(false);

const isVAT = ref(false);

const isShowProductModal = ref(false);

const change = computed(() => transactionObject.amt_received - amount_payable.value);

const customerType = ref(2);

const customerNamePlacer = ref ("");

const productsError = ref([]);

const currentCashBalance = ref (parseFloat(0));

const createdBy = ref (transactionObject.createdBy ? transactionObject.createdBy : userObject);

const isShowPendingTransactionModal = ref(false);

const currentQTYindex = ref(-1)

let selectedProductObject = ref({
    product_name : '',
    product_desc : '',
    product_code : '-',
    user_id : userObject.value.id,
    company_id : companyObject.value.id,
});

const searchTransactions = (index, searchString) => {
    return new Promise ( async (resolve, reject) => {
        try {
            let transactions = await searchTransaction (
                searchString,
                companyObject.value.id,
                branchObject.value.id
            );
            resolve (transactions)
        } catch (e) {
            reject (e);
        }
    })
}


const amount_payable = computed(()=>{
    if (transactionObject.item_transaction_type == 'DELIVERY' || transactionObject.item_transaction_type == 'STOCK OUT') {
        return 0;
    }

    let discount = DISCOUNT_LIST.find(x => x.id === transactionObject.discount_type).value;
    let vat = transactionObject.vat == null ? 0 : transactionObject.vat;
    let discounted = transactionObject.total_price * discount;
    let vattable = transactionObject.total_price * vat;
    
    transactionObject.discount_percent = discount;

    return ((transactionObject.total_price - discounted) + vattable);
})

const itemTransactionTypes = reactive (TRANSACTION_MODAL_CONSTANTS.ITEM_TRANSACTION.types)

const closeModal = () => {
    emit('closeTransactionModal')
}

const changeQuantity = (i) => {
    transactionDetails[i].total_cost = 
        transactionDetails[i].cost_per_unit * transactionDetails[i].quantity;
    transactionDetails[i].total_price = 
        transactionDetails[i].price_per_unit * transactionDetails[i].quantity;
    computeTotals();
    convertQuantities(i);
}

const randomString = (length, chars) => {
    let result = '';
    for (let i = length; i > 0; --i) result += chars[Math.round(Math.random() * (chars.length - 1))];
    return result;
}

const changeUnit = (i) => {
    let indexUnit = transactionDetails[i].units.findIndex(x => x.heirarchy === transactionDetails[i].unit_id);
    transactionDetails[i].unit = transactionDetails[i].units[indexUnit].unit_name;
    transactionDetails[i].price_per_unit = transactionDetails[i].units[indexUnit].price_per_unit;
    transactionDetails[i].cost_per_unit = transactionDetails[i].units[indexUnit].cost_per_unit;
    changeQuantity(i); 
    computeTotals();
}

const onSavePriceList = (product) => {
    let selectedProductIndex = transactionDetails.findIndex(x=>x.product_code == product.product_code);   
    let selectedProduct = transactionDetails[selectedProductIndex]
    //let priceList = product.pricelist.find(x => x.is_default);
    //console.log('selectedProduct', selectedProduct.product.p) 
    onSelectProduct({
        index: selectedProductIndex,
        item: selectedProduct.product.p,
        pricelist: true
    });
}

const onSelectItem = (params) => {
    addItem();
    setTimeout(() => {
        onSelectProduct({
            index : transactionDetails.length - 1,
            item : params.item,
            pricelist: true
        })
    },100);
}

const onSelectProduct = async (params) => {
    var prod = await getProduct(params.item.id)
        prod = prod.data;
    let indexItem = params.index;
    let indexSimilarProduct = transactionDetails.findIndex(x=>x.product_code == params.item.product_code); 
    if (indexSimilarProduct > -1) {
        //alertBox("Item already existing on the list. Adjust the quantity instead.", ALERT_TYPE.ERR);
        transactionDetails.splice(params.index, 1)
        return;
    }
    /*if (indexSimilarProduct > -1) {
        transactionDetails[indexSimilarProduct].quantity++;
        indexItem = indexSimilarProduct;
        transactionDetails.splice(params.index, 1)
        //return;
    }*/
    transactionDetails[indexItem]['product'] = {
        p: params.item, // from /products/get remaining balance only
        q: prod, // from /products/get/{id} with pricelist and unit
    };

    transactionDetails[indexItem]['product_code'] = prod.product_code
    transactionDetails[indexItem]['product_name'] = prod.product_name
    transactionDetails[indexItem].product_id = prod.id;

    if(prod.pricelist.length == 0) {
        let errmsg = `${prod.product_name} no pricelist found.`;
        alertBox (errmsg, ALERT_TYPE.ERR)
        return;
    }
    //console.log('transactionDetails', transactionDetails[indexItem])   
    let defaultPriceList = prod.pricelist.find(x => x.is_default == true)
    //let selectedUnit = defaultPriceList.unit.find(x => x.)
    transactionDetails[indexItem].units = defaultPriceList.unit;
    if (!params.onSelectTransaction)  {
        transactionDetails[indexItem].unit = defaultPriceList.unit[0].unit_name;
        transactionDetails[indexItem].unit_id = defaultPriceList.unit[0].heirarchy;

        transactionDetails[indexItem].price_per_unit = parseFloat(defaultPriceList.unit[0].price_per_unit);
        transactionDetails[indexItem].cost_per_unit = parseFloat(defaultPriceList.unit[0].cost_per_unit);
    } else {
        let unitid = transactionDetails[indexItem].unit_id;
        let unitobj = defaultPriceList.unit.find(x=>x.heirarchy == unitid)
        transactionDetails[indexItem].unit = unitobj.unit_name
    }
    transactionDetails[indexItem].total_cost = 
        transactionDetails[indexItem].cost_per_unit * parseFloat(transactionDetails[indexItem].quantity);
    transactionDetails[indexItem].total_price = 
        transactionDetails[indexItem].price_per_unit * parseFloat(transactionDetails[indexItem].quantity);
    
    computeTotals();
    convertQuantities(indexItem);
}

const convertQuantities = (i) => {
    let p = transactionDetails[i].product; 
    //console.log('p', p)
    //console.log('is_pending_transaction', is_pending_transaction)
   
    if (!p) return;
    let remBal = parseFloat(p.p.remaining_balance);
    
    let errmsg1 = `${p.p.product_name} remaining balance is negative`;
    if (p.p.remaining_balance == 0 && !transactionObject.stock) {
        if (!productsError.value.find(x => x === errmsg1)) productsError.value.push(errmsg1);
        transactionDetails[i].quantity = 0;
        return;
    } else {
        let indx = productsError.value.findIndex(x => x === errmsg1);
        if (indx >= 0) productsError.value.splice(indx, 1);
    }

    
    let errmsg = `${p.p.product_name} quantity must be greater than 0`;
    if (transactionDetails[i].quantity <= 0) {
        if (!productsError.value.find(x => x === errmsg)) productsError.value.push(errmsg);
        transactionDetails[i].quantity = 0;
        return;
    } else {
        let indx = productsError.value.findIndex(x => x === errmsg);
        if (indx >= 0) productsError.value.splice(indx, 1);
    }

    let errmsg2 = `${p.p.product_name} no pricelist found`;
    if (p.q.pricelist == 0) {
        if (!productsError.value.find(x => x === errmsg2)) productsError.value.push(errmsg2);
        transactionDetails[i].quantity = 0;
        return;
    } else {
        let indx = productsError.value.findIndex(x => x === errmsg2);
        if (indx >= 0) productsError.value.splice(indx, 1);
    }

    let defaultPriceList = p.q.pricelist.find(x => x.is_default == true)

    let selectedProductUnits = defaultPriceList.unit;

    let selUnit = selectedProductUnits.find(x => x.heirarchy === transactionDetails[i].unit_id)
    
    let heirarchy = p.p.unit_obj ? p.p.unit_obj.heirarchy : 0

    let j = heirarchy - 1;
    let cdn = selUnit.heirarchy >= heirarchy;
    let ctr =  selUnit.heirarchy - 1 ;
  
    while ( cdn ? ctr >= j : ctr <= j ) {
        
        let sUnit = selectedProductUnits[j];
        
        // if unit of remaining balance 
        // is less than of the selected unit of qty
        if (!cdn) {
            remBal /= parseFloat(sUnit.parent_quantity);
            j--;
        } else {
            
            if (sUnit.heirarchy == heirarchy) {
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

    transactionDetails[i].quantity = 
        isNaN(parseFloat(transactionDetails[i].quantity)) ? 0 : parseFloat(transactionDetails[i].quantity);
    
    let remainingBalance = 
        !transactionObject.stock ? 
        parseFloat(remBal) - parseFloat(transactionDetails[i].quantity): // if stock out (ransactionObject.stock == false), deduct to remaining balance
        parseFloat(remBal) + parseFloat(transactionDetails[i].quantity); // if stock in (ransactionObject.stock == true), add to remaining balance
        
    if (remainingBalance >= 0) {
        let errmsg = `${p.p.product_name} remaining balance is negative`;
        let errIndx = productsError.value.find(x => x === errmsg);
        //if (!transactionDetails[i].is_pending_transaction)
            transactionDetails[i].remaining_balance = parseFloat(remainingBalance);
        //else transactionDetails[i].remaining_balance = 0
        if (errIndx > -1) productsError.value.splice(errIndx, 1)
    } else {
        let errmsg = `${p.p.product_name} remaining balance is negative`;
        if (!productsError.value.find(x => x === errmsg)) productsError.value.push(errmsg);
        return;
    }
}

const computeTotals = (watchChangeVal) => {
    let total_cost = 0;
    let total_price = 0;
    for (let i in transactionDetails) {
        total_cost += transactionDetails[i].total_cost;
        total_price += transactionDetails[i].total_price;
        if(watchChangeVal) convertQuantities(i)
    }
    transactionObject.total_price = total_price;
    transactionObject.total_cost = total_cost;
}

const addItem = () => {
    if (isUpdate.value) {
        alertBox("Cannot Edit Transaction", ALERT_TYPE.ERR);
        return;
    }
    transactionDetails.push ({
        'transaction_type' : transactionObject.transaction_type,
        'item_transaction_type' : transactionObject.item_transaction_type,
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
        'indx' : randomString(15, alphaNumeric.value),
        'is_pending_transaction' : props.transaction.is_pending_transaction
    })
}

const removeItem = (transactionDetailIndex) => {
    if (isUpdate.value) {
        alertBox("Cannot Edit Transaction", ALERT_TYPE.ERR);
        return;
    }
    transactionDetails.splice (transactionDetailIndex, 1);
    currentQTYindex.value = transactionDetails.length - 1;
    computeTotals(true);
}

const save = async ( ) => {
    tDetails()
    //console.log('transactionObject', transactionObject, transactionDetails)
    if (productsError.value.length > 0) {
        alertBox(productsError.value, ALERT_TYPE.ERR);
        productsError.value = [];
        return;
    }

    if (transactionObject.item_transaction_type=='SALE' && change.value < 0) {
        alertBox('Insufficient amount', ALERT_TYPE.ERR);
        return;
    }

    if (transactionDetails.length == 0) {
        alertBox('Please Choose a Product.', ALERT_TYPE.ERR);
        return;
    }

    if (transactionObject.item_transaction_type=='DELIVERY' && userObject.value.designation != 1) {
        alertBox('Your designation cannot add products.', ALERT_TYPE.ERR);
        return;
    }

    try {

        if (transactionObject.item_transaction_type == 'DELIVERY') {
            if (transactionObject.amt_released) {
                transactionObject['remaining_balance'] = 
                    parseFloat(transactionObject['remaining_balance']) -
                    parseFloat(transactionObject.amt_released); 
            }
        } else {
            transactionObject['remaining_balance'] = 
                parseFloat(transactionObject['remaining_balance']) +
                parseFloat(transactionObject.final_amt_received); 
        }

        if (transactionObject['remaining_balance'] < 0) {
            alertBox(`Remaining balance is negative: ${transactionObject['remaining_balance']}`
                , ALERT_TYPE.ERR);
            return;
        }
        
        let confirm = await alertBox('Is this final?', ALERT_TYPE.CONFIRMATION);

        if (!confirm) return;
        transactionObject['user_id'] = userObject.value.id
        transactionObject.transaction_code = transactionObject.transaction_code.toUpperCase();
        let transaction = await saveTransaction({
            transaction: transactionObject,
            transactionDetails: transactionDetails,
            isCreate : !isUpdate.value,
        });

        transaction.data.res['isUpdate'] = !isUpdate.value;
        transaction.data.res['td'] = transactionDetails
        transactionObject.value = transaction.data.res;
        alertBox('Transaction success!', ALERT_TYPE.MSG);
        emit('onAddTransaction', transaction.data.res);
        
        if (!isUpdate.value) isUpdate.value = true;

        if (props.isNotFromDialog) {
            await clearItemDetails();
        }

    } catch (e) {
        if (e.response) {
        alertBox(e.response.data.err ? e.response.data.err : e.response.data.message, 
            ALERT_TYPE.ERR);
        } else {
            console.log(e)
        }
    }
}

const onSelectCustomer = (data) => {
    transactionObject.customer_id = data.item.id;
}

const getCustomers1 = async (company_id, searchString) => {
    let data = await getCustomers(company_id, searchString, customerType.value);
    return data;
}

const tDetails = () => {
    for (let i in transactionDetails) {
        convertQuantities(i);
    }
}

const clearItemDetails = () => {
    return new Promise ( (resolve, reject) => {
        let intervals = []
        for (let i in transactionDetails) {
            intervals[i] = setInterval(()=>{
                transactionDetails.splice(i, 1)
                computeTotals(false)
            }, 100)
        }
        setTimeout( () => {
            for (let i in intervals) 
                clearInterval(intervals[i])
            transactionObject['ref_transaction_id'] = null;
            event.emit('TextAutoCompleteComponent:clearSearchText', "product_name");
            currentQTYindex.value = -1;
            resolve();
        }, 300);
        init();
    });
}

const openNewCustomer = () => {
    customerModal({
        'customer_type' : customerType.value,
        'customer_code' : randomString(15, alphaNumeric.value),
        'company_id' : companyObject.value.id,
        'customer_name': '',
        'pwd_no' : '',
        'senior_citizen_no' : '',
        'address' : '',
    })
}

const showProductModal =  async ( product ) => {
    if(typeof product == 'object') {
        let productRes = await getProduct (product.id);
        selectedProductObject.value = productRes.data;
        selectedProductObject.value ['transactions'] = product.transactions;
    } else {
        selectedProductObject.value = {
            product_name : '',
            product_desc : '',
            product_code : '-',
            user_id : userObject.value.id,
            company_id : companyObject.value.id,
        };
    }

    isShowProductModal.value = !isShowProductModal.value;
}

const onSelectPendingTransaction = async (selectedPendingTransaction) => {
    let successSelectTransaction = await onSelectTransaction(selectedPendingTransaction)
    // console.log(selectedPendingTransaction)
    if (successSelectTransaction) isShowPendingTransactionModal.value = false
}

const onSelectTransaction = async (selectedTransaction) => {
    if (selectedTransaction.item.item_transaction_type !== 'SALE') { 
        alertBox("Only sales transaction is allowed.", ALERT_TYPE.ERR);
        return false;
    }
    
    if (!selectedTransaction.item.is_pending_transaction) {
        alertBox("We only process pending transactions.", ALERT_TYPE.ERR);
        return false;
    }

    if (selectedTransaction.item.is_done_pending_transaction) {
        alertBox("This transaction is already done.", ALERT_TYPE.ERR);
        return false;
    }

    await clearItemDetails();
    // console.log(selectedTransaction.item)
    //await getCustomers1(branchObject.branch_id,)
    // for (let i in selectedTransaction.item) {
        // if(transactionObject[i] && selectedTransaction.item[i]) {
            /*if (i === 'transaction_code') transactionObject[i] = `${selectedTransaction.item[i]}-FINAL`
            else if (i === 'transaction_date' | i === 'created_at') continue
            else if(transactionObject[i] && selectedTransaction.item[i])*/
        //    transactionObject[i] = selectedTransaction.item[i];
        // }
    // }
    
    // transactionObject = {...selectedTransaction.item}
    // let intervals = []
    setTimeout(async ()=> {
        for (let i in selectedTransaction.item.item_details) {
            let product = selectedTransaction.item.item_details[i].product
            let prd = await getProducts1(branchObject.value.id, product.product_code)
            //console.log('prd', prd)
            //intervals[i] = setInterval(()=>{
            selectedTransaction.item.item_details[i]['is_pending_transaction'] = null
            transactionDetails.push(selectedTransaction.item.item_details[i])
            onSelectProduct({
                index: i,
                item: prd.data.data[0],
                pricelist: true,
                onSelectTransaction: true,
            }); 
            //}, 100)
        }
        transactionObject['ref_transaction_id_'] = selectedTransaction.item['id']
        transactionObject['amt_received'] = selectedTransaction.item['amt_received']
        transactionObject['transaction_code'] = selectedTransaction.item['transaction_code'].replace('TEMP', 'FINAL')
        transactionObject['user_id'] = userObject.value.id
        transactionObject['is_pending_transaction'] = null
        transactionObject['is_done_pending_transaction'] = null
        transactionObject['customer_id'] = selectedTransaction.item?.customer?.id
        customerNamePlacer.value = selectedTransaction.item?.customer?.customer_name
    });
    //setTimeout( () => {
    //    for (let i in intervals)
    //        clearInterval(intervals[i])
    //}, 1000);
   // transactionDetails = selectedTransaction.item.item_details
    

    if (transactionObject['amt_received']) {
    //    transactionObject['amt_released'] = transactionObject['amt_received'];
    } else {
    //    transactionObject['amt_received'] = transactionObject['amt_released'];
    }
    return true;
}

const showPendingTransactionModal = () => {
    isShowPendingTransactionModal.value = !isShowPendingTransactionModal.value;
}

watch (
    () => currentQTYindex.value,
    (newVal) => {
        let f = document.getElementById(`form-qty-${newVal}`)
        if (f) f.focus()
    }
)

watch (
    ()=> props.transaction.is_pending_transaction,
    async (newVal) => {
        if(newVal == null) return;
        await clearItemDetails();
    }
)

watch(
    () => transactionObject.item_transaction_type, 
    (newVal) => {   
        isVAT.value = false;
        transactionObject.stock = itemTransactionTypes[newVal].stock;
        customerType.value = ((newVal == 'DELIVERY') ? 2 : 1);
        //console.log(newVal)
        if (newVal == 'STOCK OUT') transactionObject.amt_released = null;
        transactionObject.customer_id = '';
        event.emit('TextAutoCompleteComponent:clearSearchText', "customer_name");
        for (let i in transactionDetails) {
            transactionDetails[i].item_transaction_type = newVal;
            transactionDetails[i].stock = transactionObject.stock;
            convertQuantities(i);
        }
        computeTotals();
    }
);


watch(
    () => isCOD.value, 
    (newVal) => {   
        if (newVal) {
            transactionObject.amt_released = transactionObject.total_cost;
        } else {
            transactionObject.amt_released = null;
        }
    }
);

watch(
    () => isVAT.value, 
    (newVal) => {   
        if (!newVal) {
            transactionObject.vat = null;
        } else {
            transactionObject.vat = VAT_PERCENT;
        }
    }
);

watch(
    () => transactionObject.item_transaction_type,
    (newVal, oldVal)=>{
        if (newVal !== 'SALE' && props.isNotFromDialog) {
            transactionObject.item_transaction_type = 'SALE';
            alertBox("Only sales transactions allowed.", ALERT_TYPE.ERR)
            //return;
        }
        if(!transactionObject.id) {
            let tcsplit = transactionObject.transaction_code.split('-');
            transactionObject.transaction_code = `${newVal[0]}-${tcsplit[1]}-${tcsplit[2]}`;
        }
    }
)

const init = async () => {
    if (props.product) {
        let lt = props.product.transactions[0];
        let pl = props.product.pricelist.find(x=>x.is_default === true);
        let unit_obj = lt ? pl.unit.find(x=>x.unit_name === lt.unit) : pl.unit.find(x=>x.heirarchy === pl.unit.length);
        props.product['unit_obj'] = unit_obj;
        props.product['remaining_balance'] = lt ? lt.remaining_balance : 0;
        isUpdate.value = false;
        addItem();
        onSelectProduct({
            index: 0,
            item: props.product,
            pricelist: true
        });
     }

    if(!transactionObject.id) {
        let temp = !transactionObject['is_pending_transaction'] ? "" : 'TEMP-';
        let tdate = transactionObject.transaction_date.replace(/-/g, "")
        transactionObject.transaction_code = 
        `${temp}${transactionObject.item_transaction_type}-${tdate}-${randomString(4, alphaNumeric.value)}`;
    }

    let latestTransaction = await getCurrentBalance(
        transactionObject.transaction_date,
        userObject.value.id,
    );
    let cb = latestTransaction.data.res ? latestTransaction.data.res.remaining_balance : 0;
    currentCashBalance.value = cb;
    transactionObject['remaining_balance'] = currentCashBalance.value;
    isVAT.value = transactionObject.vat !== null;
    isCOD.value = transactionObject.amt_released !== null && transactionObject.item_transaction_type == 'DELIVERY';
    title.value = props.transaction.id ? props.transaction.transaction_code : 'NEW TRANSACTION';
    props.transaction.stock = props.transaction.stock == 1;
    isUpdate.value = !props.transaction.id ? false : true;
    transactionObject.final_amt_received = amount_payable;
    transactionObject.change = change;

    transactionObject.user_id = !transactionObject.user_id ? userObject.value.id : transactionObject?.user_id
    
    // console.log('init', userObject.value.id)
}


const keyBoardShortcuts = async (e) => {
    if (e.key =='F1') {
        if (!props.transaction.is_pending_transaction){
            showPendingTransactionModal();
        }
        e.preventDefault();
    }
    if (e.key == 'F4') {
        //save()
        document.getElementById('save-transaction-button-1').click();
        e.preventDefault();
    }
    
    if (e.key == 'F3') {
        if (userObject.value.designation != 3) {
            //alertBox("You are not allowed to switch.", ALERT_TYPE.ERR);
            props.transaction.is_pending_transaction = false;
            props.transaction.is_pending_transaction = true;
            e.preventDefault();
            return;
        }
        props.transaction.is_pending_transaction = !props.transaction.is_pending_transaction
        // event.emit('TextAutoCompleteComponent:focus', "product_name");
        e.preventDefault();
    }

    if (e.key == 'F2') { 
        if (transactionDetails.length == 0) {
            alertBox("Please chose products.");
            return;
        }

        document.getElementById('form-amt').focus()
        e.preventDefault();
    }

    if (e.key == 'F6') { 
        if (transactionDetails.length > 0)
            currentQTYindex.value ++;
        if (currentQTYindex.value === transactionDetails.length)
            currentQTYindex.value = 0;
        
        e.preventDefault();
    }

    if (e.key == 'F7') {
        event.emit('TextAutoCompleteComponent:clearSearchText', "product_name");
        e.preventDefault();
    }

    if (e.key == 'F8') {
        event.emit('TextAutoCompleteComponent:clearSearchText', "customer_name");
        e.preventDefault();
    }

    if (e.ctrlKey && (e.key === 'x' | e.key === 'X')) {
        //console.log(`currentQTYindex.value`, currentQTYindex.value)
        if (currentQTYindex.value < 0) return;
        let confirm = await alertBox('Is this final ?', ALERT_TYPE.CONFIRMATION);

        if (!confirm) return;
        console.log('currentQTYindex.value', currentQTYindex.value)
        removeItem(currentQTYindex.value)
        currentQTYindex.value = 0;
        e.preventDefault();
    }
}

onMounted( ()=>{
    // onmounted hook
    // console.log('transactionObject', transactionObject)
    // transactionDetails = props.transaction.item_details;
    init();
    if (props.isNotFromDialog)
        window.addEventListener('keydown', keyBoardShortcuts);
})

onUnmounted(()=>{
    // onUnmounted hook
    // when component is destroyed/closed
    delete transactionObject.transactionDetails;
    transactionObject.vat = null;
    transactionObject.total_price = 0.0;
    transactionObject.total_cost = 0.0;
    amount_payable.value = 0.0;
    change.value = 0.0;
    productsError.value = [];
    window.removeEventListener('keydown', keyBoardShortcuts);
})

</script>
<template>
    <Modal 
        :show=isShowProductModal 
        @close="showProductModal" 
        @onDialogDisplay='()=>{}'
    >
        <ProductModal 
            :productObject="selectedProductObject" 
            :branchObject="branchObject"
            :activeTab=2
            @closeProductModal="showProductModal" 
        />
    </Modal>
    <Modal 
        :show=isShowPendingTransactionModal 
        @close="showPendingTransactionModal" 
        @onDialogDisplay='()=>{}'
    >
        <PendingTransactionModal 
            @close="showPendingTransactionModal"
            @onSelectPendingTransaction="onSelectPendingTransaction" 
        />
    </Modal>
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
            <div style="width:33.33%; float:left; ">
                <B>{{ transactionObject.item_transaction_type == 'SALE' ? 'CUSTOMER NAME' : 'SUPPLIER' }} (F8)</B>
                <TextAutoComplete 
                    :itmName="transactionObject.customer ? transactionObject.customer.customer_name : customerNamePlacer" 
                    :getData="getCustomers1" 
                    :itemName="'customer_name'" 
                    :itemIndex="0"
                    :style="'width:90%'"
                    :addNew=openNewCustomer
                    @onSelectItem="onSelectCustomer"
                />
            </div>
            <div style="width:33.33%;float:left; ">
                <B v-if="!transaction.is_pending_transaction && isNotFromDialog">TRANSACTION CODE</B>
                <B v-else>PRODUCT CODE (F7)</B>
                <a v-if="!transaction.is_pending_transaction && isNotFromDialog" href="javascript:void(0);" @click=showPendingTransactionModal >&#128221; F1</a>
                <TextAutoComplete 
                    v-if="!transaction.is_pending_transaction && isNotFromDialog"
                    :itmName="transactionObject.transaction_code" 
                    :getData="searchTransactions" 
                    :itemName="'transaction_code'" 
                    :itemIndex="0"
                    :style="'width:90%'"
                    @onSelectItem="onSelectTransaction"
                />
                <!---<input 
                    v-else
                    class="uppercase"
                    :disabled="isUpdate" 
                    type="text" 
                    v-model="transactionObject.transaction_code" 
                    style="width:90%;"
                />--> 
                <TextAutoComplete 
                    v-else
                    :getData="getProducts1" 
                    itemName="product_name" 
                    :fieldNames="['product_name', 'remaining_balance,unit_name', 'price']"
                    :fieldLabels="['Product Name', 'Balance', 'Price']"
                    :itmName="searchProductName" 
                    :itemIndex="0"
                    @onSelectItem="onSelectItem" 
                    style="width:90%;"
                />
            </div>
            <div style="width:33.33%;float:left;">
                <B>TRANSACTION TYPE</B>
                <select :disabled="isUpdate" v-model="transactionObject.item_transaction_type" style="width:100%;">
                    <option v-for="(t, index) in itemTransactionTypes" :key="index" :value="index">
                        {{ index }}
                    </option>
                </select>
            </div>
            <div style="clear:both"></div>
        </div>

        <div style="background-color: #f0534017; width:100%; height:65%; margin-top:1%; padding:1%;" >
           <!-- <div style="width:100%;height:10%;">
                <div style='float:left; width:20%;' v-if="!product">
                    <PrimaryButton :additionalStyles="'background: green;'" @click=addItem>+ ADD ITEM</PrimaryButton>
                </div>
            </div>-->
            <div class="scrollbar" style="width:100%;max-width:150%;height:80%;max-height:80%; overflow:auto;">
                <div style="width:100%;">
                    <div style="float:left; width:30%; padding:0.5%;">
                        Product Name
                    </div>
                    <div style="float:left; width:5%; padding:0.5%;">
                        Qty (F6)
                    </div>
                    <div style="float:left; width:13%; padding:0.5%;">
                        Unit
                    </div>
                    <div style="float:left; width:10%; padding:0.5%;">
                        Price
                    </div>
                    <div style="float:left; width:10%; padding:0.5%;">
                        Cost
                    </div>
                    <div style="float:left; width:10%; padding:0.5%;">
                        Total Price
                    </div>
                    <div style="float:left; width:10%; padding:0.5%;">
                        Total Cost
                    </div>
                    <div style="float:left; width:5%; padding:0.5%;">
                        Balance
                    </div>
                    <div style="float:left; width:5%; padding:0.5%;">
                        Actions
                    </div>
                    <div style="clear:both"></div>
                </div>
                <div 
                    v-for="(transactionDetail, transactionDetailIndex) in transactionDetails" 
                    v-bind:key="transactionDetail.indx" 
                    style="width:100%;"
                >
                    <div style="float:left; width:30%; ">
                        <!--<TextAutoComplete 
                            v-if="!product"
                            :getData="getProducts1" 
                            itemName="product_name" 
                            :fieldNames="['product_name', 'remaining_balance,unit_name', 'price']"
                            :itmName="transactionDetail.pp ? transactionDetail.pp.product_name : ''" 
                            :itemIndex="transactionDetailIndex"
                            @onSelectItem="onSelectProduct"
                        />-->
                        <input v-if="transactionDetail.pp" type ='text' style="text-transform: uppercase; width: 90%;" v-model="transactionDetail.pp.product_name" disabled/>
                        <input v-else type ='text' style="text-transform: uppercase; width: 90%;" v-model="transactionDetail.product_name" disabled/>
                        <a href="javascript:void(0);" @click=showProductModal(transactionDetail.product.p) >&#128221;</a>
                    </div>
                    <div style="float:left; width:5%; ">
                        <input v-model="transactionDetail.quantity"
                            :id="`form-qty-${transactionDetailIndex}`"
                            @keyup="changeQuantity(transactionDetailIndex)" 
                            type="text" 
                            style="width:99%;"
                        />
                    </div>
                    <div style="float:left; width:13%; "> 
                        <select @change="changeUnit(transactionDetailIndex)" v-model="transactionDetail.unit_id" style="width:99%;">
                            <option 
                                v-if='transactionDetail.units' 
                                v-for="(unit, uIndex) in transactionDetail.units" 
                                :key="uIndex" 
                                :value="unit.heirarchy"
                            >
                                {{ unit.unit_name }}
                            </option>
                            <option  
                                v-if='transactionDetail.pp' 
                                v-for="(unit, uIndex) in transactionDetail.pp.unit" 
                                :key="uIndex" 
                                :value="unit.heirarchy"
                            >
                                {{ unit.unit_name }}
                            </option>
                        </select>
                    </div>
                    <div style="float:left; width:10%; cursor:pointer;">
                        <input  v-model="transactionDetail.price_per_unit" type="text" style="width:100%;"/>
                    </div>
                    <div style="float:left; width:10%; ">
                        <input disabled v-model="transactionDetail.cost_per_unit" type="text" style="width:100%;"/>
                    </div>
                    <div style="float:left; width:10%; ">
                        <input disabled v-model="transactionDetail.total_price" type="text" style="width:100%;"/>
                    </div>
                    <div style="float:left; width:10%;">
                        <input disabled v-model="transactionDetail.total_cost" type="text" style="width:100%;"/>
                    </div>
                    <div style="float:left; width:5%;">
                        <input disabled v-model="transactionDetail.remaining_balance" type="text" style="width:100%;"/>
                    </div>
                    <div style="float:right; width:5%;"> 
                        <PrimaryButton 
                            additionalStyles="background:#f05340;" 
                            @click=removeItem(transactionDetailIndex)
                        >
                            X
                        </PrimaryButton>
                        <!--<PrimaryButton v-if='transactionDetail.product'
                            additionalStyles="background:#f05340;" 
                            @click=showProductModal(transactionDetail.product.p)
                        > Price List
                        </PrimaryButton>--> 
                    </div>
                    <div style="clear:both"></div>
                </div>
            </div>
            <div style="width:100%;height:10%;padding-top:1%;">
                <div v-if="transactionObject.item_transaction_type == 'SALE'" style='float:left; width:15%;'>
                    Total Price <input type="text" v-model="transactionObject.total_price" />
                </div>
                <div v-if="transactionObject.item_transaction_type == 'DELIVERY'" style='float:left; width:15%;'>
                    Total Cost <input type="text" v-model="transactionObject.total_cost" />
                </div>
                <div 
                    v-if="transactionObject.item_transaction_type == 'DELIVERY'" 
                    style='float:left; padding-top: 0.5%; width:25%; padding-left: 25px; cursor: pointer;'
                >
                   <br> 
                   <input type="checkbox" v-model="isCOD"  id="is_cod" /> &nbsp;
                   <label style="cursor: pointer;" :for="`is_cod`" >
                        <b>Cash on Delivery</b>
                    </label>&nbsp;
                </div>
                <div v-if="transactionObject.item_transaction_type == 'SALE'" style='float:left; width:15%;'>
                    Amount Payable <input disabled type="text" v-model="transactionObject.final_amt_received" />
                </div>
                <div  v-if="transactionObject.item_transaction_type == 'SALE'" style='float:left; width:15%;'>
                    Amount (F2) <input id="form-amt" type="text" v-model="transactionObject.amt_received" />
                </div>
                <div v-if="transactionObject.item_transaction_type == 'SALE'" style='float:left; width:15%;'>
                    Change <input disabled type="text" v-model="change" />
                </div>
                <div v-if="transactionObject.item_transaction_type == 'SALE'" style='float:left; width:15%;'>
                    Discount<br>
                    <select v-model="transactionObject.discount_type">
                        <option 
                            v-for="(discount, index) in DISCOUNT_LIST" 
                            :key="index" 
                            :value="discount.id"
                        >
                            {{ discount.text }}
                        </option>
                    </select>
                </div>
                <div 
                    v-if="transactionObject.item_transaction_type == 'SALE'" 
                    style='float:left; padding-top: 0.5%; width:10%; padding-left: 0px; cursor: pointer;'
                >
                   <br> 
                   <input type="checkbox" v-model="isVAT"  id="is_vat" /> &nbsp;
                   <label style="cursor: pointer;" :for="`is_vat`" >
                        <b>Vat {{ VAT_PERCENT * 100 }}%</b>
                    </label>&nbsp;
                </div>
                <div 
                    v-if="userDesignation" 
                    style='float:left; padding-top: 0.5%; width:15%; padding-left: 0px; cursor: pointer;'
                >
                   <br> 
                   <input v-if="userDesignation == 3" type="checkbox" v-model="transaction.is_pending_transaction"  id="is_temp" /> &nbsp;
                   <input v-else type="checkbox" v-model="transaction.is_pending_transaction"  id="is_temp" disabled /> &nbsp;
                   <label style="cursor: pointer;" :for="`is_temp`" >
                        <b>Temp (F3)</b>
                    </label>&nbsp;
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
                    <input disabled v-model="createdBy.name" type="text" style="width:95%;"/>
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
                <div v-if="transaction.is_pending_transaction | !isNotFromDialog" style="margin-top:2%;">
                    <B>TRANSACTION CODE</B>
                    <input v-model="transactionObject.transaction_code" type="text" style="width:100%;"/>
                </div>
            </div>
        </div>
    </div>
    <div id="modal-footer">
        <div style="float:right">
            <PrimaryButton @click=save buttonID='save-transaction-button-1'>+ SAVE (F4)</PrimaryButton>
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