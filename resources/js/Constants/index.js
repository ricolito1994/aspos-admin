export const TRANSACTION_MODAL_CONSTANTS = {
    REFUND: {
        value:'REFUND_TRANSACTION', 
        title: 'REFUND'
    },
    RETURN: {
        value:'RETURN_TRANSACTION', 
        title: 'RETURN ITEMS'
    },
    START_SHIFT: {
        value:'START_OF_SHIFT_TRANSACTION', 
        title: 'START OF SHIFT'
    },
    END_SHIFT: {
        value:'END_OF_SHIFT_TRANSACTION', 
        title: 'END OF SHIFT'
    },
    DEPOSIT: {
        value:'DEPOSIT_CASH_TRANSACTION', 
        title: 'DEPOSIT CASH'
    },
    WITHRAW: {
        value:'WITHRAW_CASH_TRANSACTION', 
        title: 'WITHRAW CASH'
    },
    ITEM_TRANSACTION : {
        value: 'ITEM_TRANSACTION',
        types : {
            'SALE' : {
                stock : false,
            },
            'DELIVERY' : {
                stock : true,
            },
        }
    }
}

export const DISCOUNT_LIST = [
    {
        text: 'None',
        value: 0,
        id : 1,
    },
    {
        text: 'Senior Citizen',
        value: 0.20,
        id : 2,
    },
    {
        text: 'PWD',
        value: 0.20,
        id : 3,
    },
]

export const CUSTOMER_TYPES = [
    {
        value: 1,
        label : 'CUSTOMER',
    },
    {
        value: 2,
        label : 'SUPPLIER',
    },
]

export const IS_VAT = false;
export const VAT_PERCENT = 0.12;