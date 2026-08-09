import { mount } from '@vue/test-utils';
import { describe, it, expect, vi } from 'vitest';
import FilePreviewCard from './FilePreviewCard.vue';

// Mock URL methods
global.URL.createObjectURL = vi.fn(() => 'blob:mock-preview-url');
global.URL.revokeObjectURL = vi.fn();

describe('FilePreviewCard', () => {
    it('renders image preview for image files with edit button', () => {
        const file = new File(['image content'], 'avatar.png', { type: 'image/png' });
        const wrapper = mount(FilePreviewCard, {
            props: {
                file,
                canEdit: true,
            },
        });

        expect(wrapper.text()).toContain('avatar.png');
        expect(wrapper.find('img').exists()).toBe(true);
        expect(wrapper.find('img').attributes('src')).toBe('blob:mock-preview-url');
    });

    it('renders generic icon and file extension badge for non-image files', () => {
        const file = new File(['pdf content'], 'document.pdf', { type: 'application/pdf' });
        const wrapper = mount(FilePreviewCard, {
            props: {
                file,
            },
        });

        expect(wrapper.text()).toContain('document.pdf');
        expect(wrapper.find('img').exists()).toBe(false);
        expect(wrapper.text()).toContain('pdf');
    });

    it('emits edit event when edit button is clicked on image file', async () => {
        const file = new File(['image content'], 'photo.jpg', { type: 'image/jpeg' });
        const wrapper = mount(FilePreviewCard, {
            props: {
                file,
                canEdit: true,
            },
        });

        const editBtn = wrapper.find('button[title*="Edit"]');
        expect(editBtn.exists()).toBe(true);
        await editBtn.trigger('click');

        expect(wrapper.emitted('edit')).toBeTruthy();
    });

    it('emits remove event when remove button is clicked', async () => {
        const file = new File(['dummy'], 'sample.txt', { type: 'text/plain' });
        const wrapper = mount(FilePreviewCard, {
            props: {
                file,
            },
        });

        const removeBtn = wrapper.find('button[title*="Remove"]');
        expect(removeBtn.exists()).toBe(true);
        await removeBtn.trigger('click');

        expect(wrapper.emitted('remove')).toBeTruthy();
    });
});
