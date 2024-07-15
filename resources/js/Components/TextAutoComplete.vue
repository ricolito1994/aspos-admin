<script setup>
import {watch} from 'vue';
import {event} from '@/Services/EventBus';
import { 
    reactive,
    computed,
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
    fieldNames : {
        type: Array,
        required: true,
    },
    fieldLabels : {
        type: Array,
        required: false,
    },
    itmName : {
        type: String,
    },
    style : {
        type: String,
    },
    addNew : {
        type: Function,
    },
    disabled: {
        type: Boolean,
    }
})

const searchItmNameTmp = props.itmName;

const currentIndex = ref(-1);

const currentItem = ref({});

const dropdownResultsRef = ref(null);

const showResults = ref(false);

let results = ref([]);

let searchString = reactive(props.itmName);

const refSearchString = ref(null)

const emit = defineEmits(['onSelectItem']);

const isLoading = ref (true);

const widthItemsResults = computed(() => {
    // console.log((props.fieldNames))
    if(props.fieldNames)
        return 100 / props.fieldNames.length;
    return 1
})

const resultsStyleItem = reactive({
    float: 'left',
    width: `${widthItemsResults.value}%`
});

const searchItems = async (event) => {
    isLoading.value = true;

    if(event.keyCode !== 13) 
        showResults.value = true;
    
    if(event.keyCode !== 38 &&  event.keyCode !== 40) {
        searchString = searchString.toUpperCase();
        currentIndex.value = -1;
        let res = await props.getData(1, encodeURIComponent(searchString))
        isLoading.value = false;
        let r = res.data.res ? res.data.res : res.data;
        if (r.data) {
            results.value = r.data;
        } else {
            results.value = r;
        }
    } else {
        isLoading.value = false;
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
    if (
        (index == 'addnew' && typeof props.addNew === 'function') || 
        (currentIndex.value == results.value.length)
    ) {
        props.addNew()
        return;
    }

    if(typeof index !== 'undefined') currentIndex.value = index; 
    currentItem.value = results.value[currentIndex.value];
    searchString = currentItem.value[props.itemName];
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
    else if ( (currentIndex.value < results.value.length) ) {
        currentIndex.value ++;
    }

    searchString = 
        results.value[currentIndex.value] ? results.value[currentIndex.value][props.itemName] : '';

        searchString = searchString.toUpperCase();
    //var scrollAmount = dropdownResultsRef.value.clientHeight * scroll;
    const itemHeight = dropdownResultsRef.value.querySelector('.results').clientHeight;
    const scrollAmount = itemHeight * scroll;

    dropdownResultsRef.value.scrollTop += scrollAmount;
}

onMounted(() => {
    document.addEventListener('click', handleClickOutside);
    event.on('TextAutoCompleteComponent:clearSearchText', (modelName) => {
        if (props.itemName == modelName) { 
            searchString = "";
            setTimeout( () => {
                console.log(document.getElementById(`searchTextBox-${props.itemName}`))
                document.getElementById(`searchTextBox-${props.itemName}`).focus();
            },100);
        }
    });
    event.on('TextAutoCompleteComponent:reset', (modelName) => {
        if (props.itemName == modelName) searchString = searchItmNameTmp;
    });
    //event.on('TextAutoCompleteComponent:focus', (modelName) => {
        //if (props.itemName == modelName) {
            //console.log('searchString', searchString)
            //refSearchString.value.focus()
            //console.log('modelName', modelName)
            setTimeout( () => {
                document.getElementById(`searchTextBox-${props.itemName}`).focus();
                //refSearchString.value.focus()
            },100);
        //}
    //});
})

onUnmounted(() => {
    document.removeEventListener('click', handleClickOutside);
});

watch (() => props.itmName, (newVal) => {
    searchString = props.itmName
})

</script>

<template>
    <div @keydown="navResultList" style="width:100%;position: relative;">
        <input 
            :id="`searchTextBox-${itemName}`"
            type="text"
            class="uppercase"
            label="INPUT YOUR TEXT HERE..."
            v-model="searchString" 
            @keyup="searchItems" 
            :style="style ? style : 'width:100%;'"
            :disabled="disabled"
            ref="refSearchString"
            autocomplete="off"
        />
        <div id="result-pane" class="scrollbar" v-if="showResults" ref="dropdownResultsRef">
            <div v-if="isLoading">
                <span>Loading ... </span>
            </div>
            <div v-if="(results.length > 0 && searchString !== '') && !isLoading">
                <div 
                    v-if="fieldLabels" 
                    v-for="(r, i) in fieldLabels" 
                    :style="resultsStyleItem"
                >
                    {{ r }}
                </div>
                <div 
                    :class='currentIndex==index ? "results active" : "results"' 
                    v-for="(result, index) in results" 
                    :key="index" 
                    :id="`res-${index}`" 
                    @click="selectItem(index)"
                    style="width:100%;"
                >   
                    
                    <div 
                        v-if="fieldNames" 
                        v-for="(r, i) in fieldNames" 
                        :style="resultsStyleItem"
                    >
                        <span v-for="(r1, i1) in r.split(',')">
                            {{ result[r1] }}
                        </span>
                    </div>
                    <div v-else>
                        {{ result[itemName] }}
                    </div>
                    <div style="clear:both"></div>
                </div>
            </div>
            <div v-else-if="!isLoading">
                <span>No Result</span>
            </div>
            <div 
                v-if="typeof addNew === 'function' && !isLoading" 
                :class='currentIndex==results.length ? "results active" : "results"'
                @click="selectItem('addnew')" 
            >
                Add New
            </div>
        </div>
    </div>
</template>

<style scoped>
#result-pane {
    position: absolute;
    left: 0;
    right: 0;
    background-color: #fff;
    padding: 1%;
    border:1px solid #ccc;
    width:500px;
    max-height: 300px;
    overflow-y: auto;
    z-index: 9999;
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