import { describe, it, expect, beforeEach, vi } from 'vitest';
import { useRecentUploads, RecentUpload } from './useRecentUploads';

describe('useRecentUploads', () => {
    it('recentToFile creates File instance correctly from RecentUpload item', () => {
        const { recentToFile } = useRecentUploads();
        const fakeBlob = new Blob(['hello test'], { type: 'text/plain' });
        const item: RecentUpload = {
            id: 'test-123',
            name: 'test.txt',
            type: 'text/plain',
            size: 10,
            lastModified: 1700000000000,
            timestamp: Date.now(),
            blob: fakeBlob,
        };

        const file = recentToFile(item);
        expect(file).toBeInstanceOf(File);
        expect(file.name).toBe('test.txt');
        expect(file.type).toBe('text/plain');
    });

    it('manages reactive recentUploads state cleanly', () => {
        const { recentUploads } = useRecentUploads();
        expect(Array.isArray(recentUploads.value)).toBe(true);
    });
});
