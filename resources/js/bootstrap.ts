import axios from 'axios';
import './echo.ts';
import {router} from "@inertiajs/vue3";
import {Perms} from "@/types";

window.axios = axios;

window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
export const defaultIcon = "https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcS78CXwhRL-71jDHotN6WOTp9dC1RWPQEAJUA&s";

export const baseUrl = window.location.origin;

export const bigIntToPerms = (newPrem: bigint): Perms => ({
    perm: newPrem,
    add(addPerm) {
        this.perm |= Array.isArray(addPerm) ? addPerm.map(perm => BigInt(perm)).reduce((accumulator, currentValue) => accumulator | currentValue) : BigInt(addPerm)
    },
    has(otherPerm) {
        return (this.perm & (Array.isArray(otherPerm) ? otherPerm.map(perm => BigInt(perm)).reduce((accumulator, currentValue) => accumulator | currentValue) : BigInt(otherPerm))) === otherPerm
    },
    hasAny(otherPerm) {
        return (this.perm & (Array.isArray(otherPerm) ? otherPerm.map(perm => BigInt(perm)).reduce((accumulator, currentValue) => accumulator | currentValue) : BigInt(otherPerm))) !== BigInt(0)
    },
    remove(removePerm) {
        this.perm &= Array.isArray(removePerm) ? ~removePerm.map(perm => BigInt(perm)).reduce((accumulator, currentValue) => accumulator | currentValue) : ~BigInt(removePerm)
    }
});

export const joinServer = (code: string) => {
    axios.post(route('server.addUser'), {code: code}).then(() => {
        console.log("You're in :)");
        router.reload();
    })
}

