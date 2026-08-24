import { ref } from 'vue';

export interface RecentUpload {
    id: string;
    name: string;
    type: string;
    size: number;
    lastModified: number;
    timestamp: number;
    dataUrl?: string; // thumbnail for images
    blob?: Blob;
}

const DB_NAME = 'oxy_uploads_db';
const DB_VERSION = 1;
const STORE_NAME = 'recent_files';
const MAX_RECENT_ITEMS = 15;

let dbPromise: Promise<IDBDatabase> | null = null;
const memoryStore: Map<string, RecentUpload> = new Map();

function isIndexedDBAvailable(): boolean {
    try {
        return typeof window !== 'undefined' && Boolean(window.indexedDB);
    } catch {
        return false;
    }
}

function getDB(): Promise<IDBDatabase> {
    if (!isIndexedDBAvailable()) {
        return Promise.reject(new Error('IndexedDB is not available'));
    }
    if (!dbPromise) {
        dbPromise = new Promise((resolve, reject) => {
            const request = indexedDB.open(DB_NAME, DB_VERSION);
            request.onupgradeneeded = () => {
                const db = request.result;
                if (!db.objectStoreNames.contains(STORE_NAME)) {
                    db.createObjectStore(STORE_NAME, { keyPath: 'id' });
                }
            };
            request.onsuccess = () => resolve(request.result);
            request.onerror = () => reject(request.error);
        });
    }
    return dbPromise;
}

async function createThumbnail(file: File): Promise<string | undefined> {
    if (!file.type.startsWith('image/')) return undefined;
    return new Promise((resolve) => {
        const reader = new FileReader();
        reader.onload = () => {
            const img = new Image();
            img.onload = () => {
                const maxDim = 120;
                let w = img.width;
                let h = img.height;
                if (w > h) {
                    if (w > maxDim) {
                        h = Math.round((h * maxDim) / w);
                        w = maxDim;
                    }
                } else {
                    if (h > maxDim) {
                        w = Math.round((w * maxDim) / h);
                        h = maxDim;
                    }
                }
                const canvas = document.createElement('canvas');
                canvas.width = w;
                canvas.height = h;
                const ctx = canvas.getContext('2d');
                if (ctx) {
                    ctx.drawImage(img, 0, 0, w, h);
                    resolve(canvas.toDataURL('image/jpeg', 0.8));
                } else {
                    resolve(typeof reader.result === 'string' ? reader.result : undefined);
                }
            };
            img.onerror = () => resolve(undefined);
            img.src = reader.result as string;
        };
        reader.onerror = () => resolve(undefined);
        reader.readAsDataURL(file);
    });
}

const recentUploads = ref<RecentUpload[]>([]);
let isLoaded = false;

export function useRecentUploads() {
    const loadRecentUploads = async () => {
        if (!isIndexedDBAvailable()) {
            recentUploads.value = Array.from(memoryStore.values())
                .sort((a, b) => b.timestamp - a.timestamp)
                .slice(0, MAX_RECENT_ITEMS);
            isLoaded = true;
            return;
        }

        try {
            const db = await getDB();
            const tx = db.transaction(STORE_NAME, 'readonly');
            const store = tx.objectStore(STORE_NAME);
            const req = store.getAll();
            req.onsuccess = () => {
                const items = (req.result as RecentUpload[]) || [];
                items.sort((a, b) => b.timestamp - a.timestamp);
                recentUploads.value = items.slice(0, MAX_RECENT_ITEMS);
                isLoaded = true;
            };
        } catch {
            recentUploads.value = Array.from(memoryStore.values())
                .sort((a, b) => b.timestamp - a.timestamp)
                .slice(0, MAX_RECENT_ITEMS);
            isLoaded = true;
        }
    };

    if (!isLoaded && typeof window !== 'undefined') {
        loadRecentUploads();
    }

    const addRecentUpload = async (file: File): Promise<RecentUpload | null> => {
        try {
            const thumbnail = await createThumbnail(file);
            const item: RecentUpload = {
                id: `${file.name}-${file.size}-${Date.now()}-${Math.random().toString(36).slice(2, 7)}`,
                name: file.name,
                type: file.type,
                size: file.size,
                lastModified: file.lastModified,
                timestamp: Date.now(),
                dataUrl: thumbnail,
                blob: file,
            };

            memoryStore.set(item.id, item);

            if (isIndexedDBAvailable()) {
                const db = await getDB();
                const tx = db.transaction(STORE_NAME, 'readwrite');
                const store = tx.objectStore(STORE_NAME);
                store.put(item);

                tx.oncomplete = async () => {
                    await loadRecentUploads();
                    // Prune old items if over capacity
                    if (recentUploads.value.length > MAX_RECENT_ITEMS) {
                        const toDelete = recentUploads.value.slice(MAX_RECENT_ITEMS);
                        const pruneTx = db.transaction(STORE_NAME, 'readwrite');
                        const pruneStore = pruneTx.objectStore(STORE_NAME);
                        toDelete.forEach((d) => pruneStore.delete(d.id));
                    }
                };
            } else {
                await loadRecentUploads();
            }

            return item;
        } catch {
            return null;
        }
    };

    const removeRecentUpload = async (id: string) => {
        memoryStore.delete(id);
        if (!isIndexedDBAvailable()) {
            recentUploads.value = recentUploads.value.filter((i) => i.id !== id);
            return;
        }
        try {
            const db = await getDB();
            const tx = db.transaction(STORE_NAME, 'readwrite');
            const store = tx.objectStore(STORE_NAME);
            store.delete(id);
            tx.oncomplete = () => {
                recentUploads.value = recentUploads.value.filter((i) => i.id !== id);
            };
        } catch {
            recentUploads.value = recentUploads.value.filter((i) => i.id !== id);
        }
    };

    const clearAllRecentUploads = async () => {
        memoryStore.clear();
        if (!isIndexedDBAvailable()) {
            recentUploads.value = [];
            return;
        }
        try {
            const db = await getDB();
            const tx = db.transaction(STORE_NAME, 'readwrite');
            const store = tx.objectStore(STORE_NAME);
            store.clear();
            tx.oncomplete = () => {
                recentUploads.value = [];
            };
        } catch {
            recentUploads.value = [];
        }
    };

    const recentToFile = (item: RecentUpload): File => {
        if (item.blob) {
            return new File([item.blob], item.name, {
                type: item.type,
                lastModified: item.lastModified || Date.now(),
            });
        }
        return new File([''], item.name, { type: item.type });
    };

    return {
        recentUploads,
        loadRecentUploads,
        addRecentUpload,
        removeRecentUpload,
        clearAllRecentUploads,
        recentToFile,
    };
}
