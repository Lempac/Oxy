import PocketBase from 'pocketbase';

export function getPocketBaseUrl(): string {
    if (import.meta.env.VITE_POCKETBASE_URL) {
        return import.meta.env.VITE_POCKETBASE_URL;
    }
    if (typeof window !== 'undefined') {
        const origin = window.location.origin;
        if (origin && !origin.startsWith('file:') && origin !== 'null') {
            return origin;
        }
    }
    return 'http://127.0.0.1:8090';
}

export const pb = new PocketBase(getPocketBaseUrl());
export default pb;
