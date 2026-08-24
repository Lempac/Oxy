import PocketBase from 'pocketbase';

const pocketbaseHost = import.meta.env.VITE_POCKETBASE_URL || (typeof window !== 'undefined' ? window.location.origin : 'http://127.0.0.1:8090');

export const pb = new PocketBase(pocketbaseHost);
export default pb;
