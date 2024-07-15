<script setup>
import { 
    computed, 
    onMounted, 
    onUnmounted, 
    watch 
} from 'vue';
import {event} from '@/Services/EventBus';

const props = defineProps({
    show: {
        type: Boolean,
        default: false,
    },
    maxWidth: {
        type: String,
        default: '2xl',
    },
    closeable: {
        type: Boolean,
        default: true,
    },
    extraWidth : {
        type: String,
        default: 'max-width:70rem'
    },
    customStyle : {
        type: String,
        default: '',
    },
    isAlertBox : {
        type: Boolean,
        default: false,
    }
});

const emit = defineEmits(['close', 'onDialogDisplay']);

watch(
    () => props.show,
    () => {
        if (props.show) {
            document.body.style.overflow = 'hidden';
        } else {
            document.body.style.overflow = null;
        }
    }
);

const close = () => {
    if (props.closeable) {
        emit('close');
    }
};

const closeOnEscape = (e) => {
    if (e.key === 'Escape' && props.show) {
        event.emit("AlertBox:cancel", null)
        close();
    }
    if (e.key === 'Enter' && (props.isAlertBox && props.show)) {
        event.emit("AlertBox:ok", null)
        close();
    }
};

onMounted(() => {
    emit('onDialogDisplay');
    document.addEventListener('keydown', closeOnEscape);
});

onUnmounted(() => {
    document.removeEventListener('keydown', closeOnEscape);
    document.body.style.overflow = null;
});

const maxWidthClass = computed(() => {
    return {
        sm: 'sm:max-w-sm',
        md: 'sm:max-w-md',
        lg: 'sm:max-w-lg',
        xl: 'sm:max-w-xl',
        '2xl': 'sm:max-w-2xl',
    }[props.maxWidth];
});

</script>

<template>
    <teleport to="body">
        <transition leave-active-class="duration-200">
            <div 
                v-show="show" 
                class="fixed inset-0 overflow-y-auto px-4 py-6 sm:px-0 z-50" 
                :style="customStyle" 
                scroll-region
            >
                <transition
                    enter-active-class="ease-out duration-300"
                    enter-from-class="opacity-0"
                    enter-to-class="opacity-100"
                    leave-active-class="ease-in duration-200"
                    leave-from-class="opacity-100"
                    leave-to-class="opacity-0"
                >
                    <div v-show="show" class="fixed inset-0 transform transition-all" @click="close">
                        <div class="absolute inset-0 bg-gray-500 opacity-75" />
                    </div>
                </transition>

                <transition
                    enter-active-class="ease-out duration-300"
                    enter-from-class="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                    enter-to-class="opacity-100 translate-y-0 sm:scale-100"
                    leave-active-class="ease-in duration-200"
                    leave-from-class="opacity-100 translate-y-0 sm:scale-100"
                    leave-to-class="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                >
                    <div
                        v-show="show"
                        class="mb-6 bg-white rounded-lg overflow-hidden shadow-xl transform transition-all sm:w-full sm:mx-auto"
                        :class="maxWidthClass"
                        :style="extraWidth"
                    >
                        <slot v-if="show" />
                    </div>
                </transition>
            </div>
        </transition>
    </teleport>
</template>
<style>
#modal-content, #modal-title, #modal-footer {
    padding: 1.7%;
}

#modal-title {
    background-color: #f05340;
    color: #fff;
}

#modal-content {
    height:60vh;
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
</style>