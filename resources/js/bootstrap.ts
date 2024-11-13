import axios from 'axios';
import './echo.ts';
import {router} from "@inertiajs/vue3";
import {Perms} from "@/types";

window.axios = axios;

window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
export const defaultIcon = "https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcS78CXwhRL-71jDHotN6WOTp9dC1RWPQEAJUA&s";

export const baseUrl = window.location.origin;

export const numberToPerms = (newPrem: number): Perms => ({
    perm: newPrem,
    add(addPerm) {
        this.perm |= Array.isArray(addPerm) ? addPerm.reduce((accumulator, currentValue) => accumulator | currentValue) : addPerm
    },
    has(otherPerm) {
        return (this.perm & (Array.isArray(otherPerm) ? otherPerm.reduce((accumulator, currentValue) => accumulator | currentValue) : otherPerm)) === this.perm
    },
    hasAny(otherPerm) {
        return (this.perm & (Array.isArray(otherPerm) ? otherPerm.reduce((accumulator, currentValue) => accumulator | currentValue) : otherPerm)) !== 0
    },
    remove(removePerm) {
        this.perm &= Array.isArray(removePerm) ? ~removePerm.reduce((accumulator, currentValue) => accumulator | currentValue) : ~removePerm
    }
});

export const joinServer = (code: string) => {
    axios.post(route('server.addUser'), {code: code}).then(() => {
        console.log("You're in :)");
        router.reload();
    })
}

