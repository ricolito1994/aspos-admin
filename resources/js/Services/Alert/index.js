import {event} from '../EventBus';

export const ALERT_TYPE = {
    ERR : {
        value : 'error',
        title : 'ERROR',
    },
    WARN : {
        value : 'warning',
        title : 'WARN',
    },
    MSG : {
        value : 'message',
        title : 'WARN',
    },
    CONFIRMATION : {
        value : 'confirmation',
        title : 'CONFIRM',
    },
}

export const CONFIRMATION_MESSAGE_ALERT 
    = `Are you sure you want to continue?`;

export const alertBox = (message, alertType) => {
    event.emit('AlertBox:open', {
        message: message,
        alertType: alertType,
    });
    if (alertType.value === 'confirmation') {
        return new Promise((resolve, reject)=>{
            event.on('AlertBox:ok', () => {
                resolve(true) 
            });
            event.on('AlertBox:cancel', () => {
                resolve(false) 
            });
        });
    }
}

export const customerModal = (customer) => {
    event.emit('CustomerModal:open', customer);
}

export const userModal = (user) => {
    event.emit('UserModal:open', user);
}