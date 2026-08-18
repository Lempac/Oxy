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

    it('opens and closes fullscreen image lightbox modal', async () => {
        const attachment: MessageAttachment = {
            id: 'att-3',
            message_id: 'msg-1',
            filename: 'photo.jpg',
            path: 'uploads/photo.jpg',
            mime_type: 'image/jpeg',
            size: 524288,
            width: 800,
            height: 600,
            url: '/storage/uploads/photo.jpg',
        };

        const wrapper = mount(FileAttachmentDisplay, {
            props: {
                attachment,
            },
            attachTo: document.body,
        });

        // Initially lightbox modal should not be visible
        expect(document.body.querySelector('[role="dialog"]')).toBeNull();

        // Click fullscreen button
        const fullscreenBtn = wrapper.find('button[title="Fullscreen"]');
        expect(fullscreenBtn.exists()).toBe(true);
        await fullscreenBtn.trigger('click');

        // Modal should now be in the DOM
        let modal = document.body.querySelector('[role="dialog"]');
        expect(modal).not.toBeNull();
        expect(modal?.textContent).toContain('photo.jpg');
        expect(modal?.textContent).toContain('800×600px');

        // Click close button
        const closeBtn = modal?.querySelector('button[title="Close (Esc)"]') as HTMLButtonElement;
        expect(closeBtn).not.toBeNull();
        closeBtn.click();
        await wrapper.vm.$nextTick();

        modal = document.body.querySelector('[role="dialog"]');
        expect(modal).toBeNull();

        wrapper.unmount();
    });

    it('renders video attachments and opens video player modal', async () => {
        const attachment: MessageAttachment = {
            id: 'att-video-1',
            message_id: 'msg-1',
            filename: 'clip.mp4',
            path: 'uploads/clip.mp4',
            mime_type: 'video/mp4',
            size: 5242880,
            url: '/storage/uploads/clip.mp4',
        };

        const wrapper = mount(FileAttachmentDisplay, {
            props: {
                attachment,
            },
            attachTo: document.body,
        });

        const video = wrapper.find('video');
        expect(video.exists()).toBe(true);

        const fullscreenBtn = wrapper.find('button[title="Fullscreen Lightbox"]');
        expect(fullscreenBtn.exists()).toBe(true);
        await fullscreenBtn.trigger('click');

        const modal = document.body.querySelector('[role="dialog"]');
        expect(modal).not.toBeNull();
        expect(modal?.textContent).toContain('clip.mp4');
        expect(modal?.querySelector('video')).not.toBeNull();

        wrapper.unmount();
    });

    it('renders audio attachments and opens audio player modal', async () => {
        const attachment: MessageAttachment = {
            id: 'att-audio-1',
            message_id: 'msg-1',
            filename: 'voice_note.mp3',
            path: 'uploads/voice_note.mp3',
            mime_type: 'audio/mpeg',
            size: 1048576,
            url: '/storage/uploads/voice_note.mp3',
        };

        const wrapper = mount(FileAttachmentDisplay, {
            props: {
                attachment,
            },
            attachTo: document.body,
        });

        const audio = wrapper.find('audio');
        expect(audio.exists()).toBe(true);
        expect(wrapper.text()).toContain('voice_note.mp3');

        const modalBtn = wrapper.find('button[title="Open in player modal"]');
        expect(modalBtn.exists()).toBe(true);
        await modalBtn.trigger('click');

        const modal = document.body.querySelector('[role="dialog"]');
        expect(modal).not.toBeNull();
        expect(modal?.textContent).toContain('voice_note.mp3');
        expect(modal?.querySelector('audio')).not.toBeNull();

        wrapper.unmount();
    });
});
