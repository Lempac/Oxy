import { addUser } from '@/routes/server';
import axios, {AxiosError} from 'axios';
import './echo.ts';
import {router} from "@inertiajs/vue3";
import {Perms} from "@/types";

if (typeof window !== 'undefined') {
    window.axios = axios;
    window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
}
export const defaultIcon = "https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcS78CXwhRL-71jDHotN6WOTp9dC1RWPQEAJUA&s";

export const baseUrl = typeof window !== 'undefined' ? window.location.origin : '';

export const bigIntToPerms = (newPrem: string[]): Perms => ({
    perms: [...newPrem],
    add(addPerm) {
        const permsToAdd = Array.isArray(addPerm) ? addPerm : [addPerm];
        for (const p of permsToAdd) {
            if (!this.perms.includes(p)) {
                this.perms.push(p);
            }
        }
    },
    has(otherPerm) {
        const permsToCheck = Array.isArray(otherPerm) ? otherPerm : [otherPerm];
        return permsToCheck.every(p => this.perms.includes(p));
    },
    hasAny(otherPerm) {
        const permsToCheck = Array.isArray(otherPerm) ? otherPerm : [otherPerm];
        return permsToCheck.some(p => this.perms.includes(p));
    },
    remove(removePerm) {
        const permsToRemove = Array.isArray(removePerm) ? removePerm : [removePerm];
        this.perms = this.perms.filter(p => !permsToRemove.includes(p));
    }
});

export const joinServer = async (code: string): Promise<[number, string?]> => {
    return Promise.resolve(await axios.post(addUser.url(), {code: code})
        .then(() => {
            router.reload({only: ['servers']});
            return [200, 'Successfully joined to server.'] as [number, string];
        })
        .catch((err: AxiosError<{ message: string }>) => {
            return [err.status!, err.response?.data.message];
        }))
}

