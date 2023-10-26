<script setup>
import {event} from '@/Services/EventBus';
import { 
    onMounted, 
    onUnmounted, 
    ref,
} from 'vue';

const props = defineProps({
    getData : {
        type: Function,
        required: true,
    },
    itemName : {
        type: String,
        required: true,
    },
    itemIndex : {
        type: Number,
        default: 0,
    },
    itmName : {
        type: String,
    },
    style : {
        type: String,
    }
})


const currentIndex = ref(-1);

const currentItem = ref({});

const dropdownResultsRef = ref(null);

const showResults = ref(false);

let results = ref([]);

const searchString = ref(props.itmName);

const emit = defineEmits(['onSelectItem']);

const searchItems = async (event) => {
    if(event.keyCode !== 13) 
        showResults.value = true;
    
    if(event.keyCode !== 38 &&  event.keyCode !== 40) {
        currentIndex.value = -1;
        let res = await props.getData(1, searchString.value)
        results.value = res.data;
    }
}

const handleClickOutside = (event) => {
    currentIndex.value = -1;
    showResults.value = false; 
}

const navResultList = (event) => {
    switch (event.keyCode) {
        case 38:
            navigateItems(-0.85)
            break;
        case 40:
            navigateItems(0.85)
            break;
        case 13:
            selectItem();
            break;
    }
}

const selectItem = (index) => {
    if(typeof index !== 'undefined') currentIndex.value = index; 
    currentItem.value = results.value[currentIndex.value];
    searchString.value = currentItem.value[props.itemName];
    emit ('onSelectItem', {
        item: currentItem.value,
        index: props.itemIndex,
    });
    currentIndex.value = -1;
    showResults.value = false;
}

const navigateItems = (scroll) => {
    if (scroll < 0 && currentIndex.value > -1) {
        currentIndex.value --;
    }
    else {
        if ( (currentIndex.value < results.value.length - 1) ) {
            currentIndex.value ++;
        }
    } 

    searchString.value = results.value[currentIndex.value][props.itemName];
    //var scrollAmount = dropdownResultsRef.value.clientHeight * scroll;
    const itemHeight = dropdownResultsRef.value.querySelector('.results').clientHeight;
    const scrollAmount = itemHeight * scroll;

    dropdownResultsRef.value.scrollTop += scrollAmount;
}

onMounted(() => {
    document.addEventListener('click', handleClickOutside);
    event.on('TextAutoCompleteComponent:clearSearchText', () => {
        searchString.value = "";
    });
})

onUnmounted(() => {
    document.removeEventListener('click', handleClickOutside);
});

</script>

<template>
    <div @keydown="navResultList" tabindex="0" style="width:100%;">
        <input type="text" v-model="searchString" @keyup="searchItems" :style="style ? style : 'width:100%;'"/>
        <div id="result-pane" class="scrollbar" v-if="showResults" ref="dropdownResultsRef">
            <div v-if="results.length > 0 && searchString !== ''">
                <div 
                    :class='currentIndex==index ? "results active" : "results"' 
                    v-for="(result, index) in results" 
                    :key="index" 
                    :id="`res-${index}`" 
                    @click="selectItem(index)"
                >
                    {{ result[itemName] }}
                </div>
            </div>
            <div v-else>
                <span>No result</span>
            </div>
        </div>
    </div>
</template>

<style scoped>
#result-pane {
    position: absolute;
    background-color: #fff;
    padding: 1%;
    border:1px solid #ccc;
    width:50%;
    max-height: 300px;
    overflow-y: auto;
}

#result-pane .results {
    padding:1%;
    cursor: pointer;
}

#result-pane .results:hover, .results.active{
    background-color: #f05340;
    color:white;
}
</style>