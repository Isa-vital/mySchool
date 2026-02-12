/**
 * MySchool Offline Sync Queue
 * Allows forms to be submitted offline and synced when back online.
 *
 * Usage:
 *   import { OfflineSync } from './offline-sync';
 *   const sync = new OfflineSync();
 *   sync.enqueue('/api/attendance', 'POST', { ... });
 */

const DB_NAME = 'myschool-sync';
const DB_VERSION = 1;
const STORE_NAME = 'queue';

class OfflineSync {
    constructor() {
        this._db = null;
    }

    async _open() {
        if (this._db) return this._db;
        return new Promise((resolve, reject) => {
            const req = indexedDB.open(DB_NAME, DB_VERSION);
            req.onupgradeneeded = () => {
                req.result.createObjectStore(STORE_NAME, { keyPath: 'id', autoIncrement: true });
            };
            req.onsuccess = () => {
                this._db = req.result;
                resolve(this._db);
            };
            req.onerror = () => reject(req.error);
        });
    }

    /**
     * Add a request to the offline queue
     */
    async enqueue(url, method, data, headers = {}) {
        const db = await this._open();
        return new Promise((resolve, reject) => {
            const tx = db.transaction(STORE_NAME, 'readwrite');
            const store = tx.objectStore(STORE_NAME);
            store.add({
                url,
                method,
                body: JSON.stringify(data),
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    ...headers,
                },
                createdAt: new Date().toISOString(),
            });
            tx.oncomplete = () => {
                resolve();
                this._requestSync();
            };
            tx.onerror = () => reject(tx.error);
        });
    }

    /**
     * Get count of pending items
     */
    async pendingCount() {
        const db = await this._open();
        return new Promise((resolve) => {
            const tx = db.transaction(STORE_NAME, 'readonly');
            const req = tx.objectStore(STORE_NAME).count();
            req.onsuccess = () => resolve(req.result);
            req.onerror = () => resolve(0);
        });
    }

    /**
     * Get all pending items
     */
    async pendingItems() {
        const db = await this._open();
        return new Promise((resolve) => {
            const tx = db.transaction(STORE_NAME, 'readonly');
            const req = tx.objectStore(STORE_NAME).getAll();
            req.onsuccess = () => resolve(req.result);
            req.onerror = () => resolve([]);
        });
    }

    /**
     * Process the queue manually (when online)
     */
    async processQueue() {
        const items = await this.pendingItems();
        const db = await this._open();

        for (const item of items) {
            try {
                const response = await fetch(item.url, {
                    method: item.method,
                    headers: item.headers,
                    body: item.body,
                });

                if (response.ok || response.status < 500) {
                    // Remove from queue
                    const tx = db.transaction(STORE_NAME, 'readwrite');
                    tx.objectStore(STORE_NAME).delete(item.id);
                }
            } catch {
                // Network still down, stop processing
                break;
            }
        }

        // Dispatch event for UI updates
        window.dispatchEvent(new CustomEvent('sync-complete', {
            detail: { remaining: await this.pendingCount() }
        }));
    }

    /**
     * Request background sync if available
     */
    async _requestSync() {
        if ('serviceWorker' in navigator && 'SyncManager' in window) {
            try {
                const reg = await navigator.serviceWorker.ready;
                await reg.sync.register('sync-queue');
            } catch {
                // Fallback: try processing immediately if online
                if (navigator.onLine) {
                    this.processQueue();
                }
            }
        } else if (navigator.onLine) {
            this.processQueue();
        }
    }
}

// Auto-process queue when coming back online
window.addEventListener('online', () => {
    const sync = new OfflineSync();
    sync.processQueue();
});

// Export for ES module usage via Vite
export { OfflineSync };
export default OfflineSync;
