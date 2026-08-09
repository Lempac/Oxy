import { mount } from '@vue/test-utils';
import { describe, it, expect } from 'vitest';
import FileAttachmentDisplay from './FileAttachmentDisplay.vue';
import { MessageAttachment } from '@/types';

describe('FileAttachmentDisplay', () => {
    it('renders non-image files with icon on the same line as filename and download button', () => {
        const attachment: MessageAttachment = {
            id: 'att-1',
            message_id: 'msg-1',
            filename: 'AutoClicker.exe',
            path: 'uploads/autoclicker.exe',
            mime_type: 'application/x-msdownload',
            size: 2048576,
            url: '/storage/uploads/autoclicker.exe',
        };

        const wrapper = mount(FileAttachmentDisplay, {
            props: {
                attachment,
            },
        });

        expect(wrapper.text()).toContain('AutoClicker.exe');
        expect(wrapper.text()).toContain('2 MB');
        expect(wrapper.find('img').exists()).toBe(false);

        const downloadLink = wrapper.find('a[download]');
        expect(downloadLink.exists()).toBe(true);
        expect(downloadLink.attributes('download')).toBe('AutoClicker.exe');
    });

    it('renders images with inline preview and hover download button', () => {
        const attachment: MessageAttachment = {
            id: 'att-2',
            message_id: 'msg-1',
            filename: 'screenshot.png',
            path: 'uploads/screenshot.png',
            mime_type: 'image/png',
            size: 1048576,
            width: 1920,
            height: 1080,
            url: '/storage/uploads/screenshot.png',
        };

        const wrapper = mount(FileAttachmentDisplay, {
            props: {
                attachment,
            },
        });

        const img = wrapper.find('img');
        expect(img.exists()).toBe(true);
        expect(img.attributes('alt')).toBe('screenshot.png');

        const downloadLink = wrapper.find('a[download]');
        expect(downloadLink.exists()).toBe(true);
        expect(downloadLink.attributes('download')).toBe('screenshot.png');
    });
});
