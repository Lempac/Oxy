import axios from 'axios';
window.axios = axios;

window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
export const defaultIcon = "https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcS78CXwhRL-71jDHotN6WOTp9dC1RWPQEAJUA&s";

export const joinServer = (code: string) => {
    axios.post(route('server.addUser'), {code: code}).then(() => {
        console.log("You're in :)");
        router.reload();
    })
}

import './echo.ts';
import {router} from "@inertiajs/vue3";
