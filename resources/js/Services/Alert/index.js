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