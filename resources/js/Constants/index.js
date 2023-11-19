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
        value:'CASH_DEPOSIT', 
        title: 'DEPOSIT CASH'
    },
    WITHRAW: {
        value:'CASH_WITHRAWAL', 
        title: 'WITHRAW CASH'
    },
    CASH_TRANSACTION: {
        value:'CASH_TRANSACTION', 
        title: 'CASH TRANSACTIONS'
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

export const CASH_TRANSACTIONS = {
    REQUESTS : [
        {
            label : 'CASH DEPOSIT',
            value : 'CASH_DEPOSIT'
        },
        {
            label : 'CASH WITHRAWAL',
            value : 'CASH_WITHRAWAL'
        },
    ]
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