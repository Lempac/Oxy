import { addUser } from '@/routes/server';
import axios, {AxiosError} from 'axios';
import './echo.ts';
import {router} from "@inertiajs/vue3";
import {Perms} from "@/types";

window.axios = axios;

window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
export const defaultIcon = "https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcS78CXwhRL-71jDHotN6WOTp9dC1RWPQEAJUA&s";

export const baseUrl = window.location.origin;
//TODO: Need to go around and check if the perm never gets put in as number, before converted to bigint as it losses precision
export const bigIntToPerms = (newPrem: bigint): Perms => ({
    perm: newPrem,
    add(addPerm) {
        this.perm |= Array.isArray(addPerm) ? addPerm.map(perm => BigInt(perm)).reduce((acc, curr) => acc | curr) : BigInt(addPerm)
    },
    has(otherPerm) {
        const comOtherPerm = Array.isArray(otherPerm) ? otherPerm.map(perm => BigInt(perm)).reduce((acc, curr) => acc | curr) : BigInt(otherPerm);
        return (this.perm & comOtherPerm) === comOtherPerm;
    },
    hasAny(otherPerm) {
        return (this.perm & (Array.isArray(otherPerm) ? otherPerm.map(perm => BigInt(perm)).reduce((acc, curr) => acc | curr) : BigInt(otherPerm))) !== BigInt(0)
    },
    remove(removePerm) {
        this.perm &= Array.isArray(removePerm) ? ~removePerm.map(perm => BigInt(perm)).reduce((acc, curr) => acc | curr) : ~BigInt(removePerm)
    }
});

export const joinServer = (code: string): Promise<[number, string?]> => {
    return new Promise((resolve) => {
        router.post(addUser.url(), {code: code}, {
            onSuccess: () => {
                resolve([200, 'Successfully joined to server.']);
            },
            onError: (errors) => {
                const message = errors.message || errors.code || 'Failed to join server.';
                resolve([400, message]);
            }
        });
    });
}

