import axios from "axios";
import { notify } from "@kyvg/vue3-notification";


axios.defaults.xsrfCookieName = 'csrftoken'

const axiosInstance  = axios.create({
    // change server url dependingon on vue mode (dev or production)
    baseURL: process.env.NODE_ENV === 'production' ? '' : 'http://localhost:8000/',
    timeout: 15000,
    withCredentials: true,
    headers: {
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
    }
})

axiosInstance.interceptors.response.use(
    response => {
        if (response?.data?.message) {
            notify({
                type: "success",
                title: response.data?.status || "Success",
                text: response.data.message,
            });
        }

        return response
    }, 
    error => {
        
        let status = error.response?.status;
        let message = error.response?.data?.message;
        // if validation error 
        if (status == 422 && error.response?.statusText == "Unprocessable Content") {
            message = error.response?.data?.errors
        } else if (status == 401){
            
        } else {
            notify({
                type: "error",
                title: `Error ${status}`,
                text: message,
            });
        }
        
        // Create an object with message, error, status code
        const errorDetails = {
            message: message || 'An error occurred',
            error: error.response?.data?.error || error.response?.statusText,
            status: status || 500,
        };

        console.error(errorDetails)
        return Promise.reject(errorDetails);
    }
);

export const $axios = axiosInstance;
