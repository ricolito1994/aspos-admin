import axios from 'axios';

const host = import.meta.env.VITE_APP_BASE_URL;

let loading = false;

let activeRequests = 0;

// Show or hide the loading overlay
const toggleLoadingOverlay = (show) => {
  const loadingOverlay = document.getElementById('loading-overlay');
  loadingOverlay.style.display = show ? 'flex' : 'none';
};

const axiosLoading = axios.create();

axiosLoading.interceptors.request.use((config) => {
    loading = true;
    activeRequests++;
    toggleLoadingOverlay(loading);
    return config;
});
  
// Response interceptor
axiosLoading.interceptors.response.use( 
    (response) => {
        activeRequests--;
        if (activeRequests === 0) {
            loading=false;
            toggleLoadingOverlay(loading);
        }
        return response;
    },
    (error) => {
        loading = false;
        activeRequests--;
        if (activeRequests === 0) {
            toggleLoadingOverlay(loading);
        }
        return Promise.reject(error);
    }
);

export function getBranches ( ) {
    let branches = axiosLoading.get(`${host}/branches`);
    return new Promise ((resolve, reject) => {
        branches.then(res => {
            resolve(res)
        })
        .catch(err=>{
            reject(err)
        })
    })
}

export function changeBranch ( branchId ) {
    let changedBranch = axiosLoading.post(`${host}/branch/change/${branchId}`);
    return new Promise ((resolve, reject) => {
        changedBranch.then(res => {
            resolve(res)
        })
        .catch(err=>{
            reject(err)
        })
    })
}

export function saveProduct ( product ) {
    let newProduct = axiosLoading.post(`${host}/product/create`, product);
    return new Promise ((resolve, reject) => {
        newProduct.then(res => {
            resolve(res)
        })
        .catch(err=>{
            reject(err)
        })
    })
}


export function getProducts ( company_id , searchString ) {
    searchString = searchString ? searchString : false;
    let productsRequest = axiosLoading.get(`${host}/products/get/${company_id}/${searchString}`);
    return new Promise ((resolve, reject) => {
        productsRequest.then(res => {
            resolve(res)
        })
        .catch(err=>{
            reject(err)
        })
    })
}

export function getProducts1 ( company_id , searchString ) {
    searchString = searchString ? searchString : false;
    let productsRequest = axios.get(`${host}/products/get/${company_id}/${searchString}`);
    return new Promise ((resolve, reject) => {
        productsRequest.then(res => {
            resolve(res)
        })
        .catch(err=>{
            reject(err)
        })
    })
}


export function getProduct ( product_id ) {
    let productsRequest = axiosLoading.get(`${host}/product/get/${product_id}`);
    return new Promise ((resolve, reject) => {
        productsRequest.then(res => {
            resolve(res)
        })
        .catch(err=>{
            reject(err)
        })
    })
}


export function getTransaction (transactionId) {
    let productsRequest = axiosLoading.get(`${host}/transaction/get/${transactionId}`);
    return new Promise ((resolve, reject) => {
        productsRequest.then(res => {
            resolve(res)
        })
        .catch(err=>{
            reject(err)
        })
    })
}


export function getTransactions (company_id, branch_id, searchString, transFrom, transTo) {
    searchString = searchString ? searchString : false;
    let productsRequest = axiosLoading.get(`${host}/transactions/get/${company_id}/${branch_id}/${searchString}/${transFrom}/${transTo}`);
    return new Promise ((resolve, reject) => {
        productsRequest.then(res => {
            resolve(res)
        })
        .catch(err=>{
            reject(err)
        })
    })
}

export function saveTransaction (transaction) {
    let productsRequest = axiosLoading.post(`${host}/transaction/save`, transaction);
    return new Promise ((resolve, reject) => {
        productsRequest.then(res => {
            resolve(res)
        })
        .catch(err=>{
            reject(err)
        })
    })
}


export function getCustomers ( company_id , searchString , customerType ) {
    searchString = searchString ? searchString : false;
    let productsRequest = axios.get(`${host}/customers/get/${company_id}/${customerType}/${searchString}`);
    return new Promise ((resolve, reject) => {
        productsRequest.then(res => {
            resolve(res)
        })
        .catch(err=>{
            reject(err)
        })
    })
}


export function cancelTransaction (id) {
    let productsRequest = axios.get(`${host}/transaction/post/cancel/${id}`);
    
}