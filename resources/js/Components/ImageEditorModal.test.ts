import { mount } from '@vue/test-utils';
import { describe, it, expect, vi } from 'vitest';
import ImageEditorModal from './ImageEditorModal.vue';

// Mock canvas getContext
HTMLCanvasElement.prototype.getContext = vi.fn(() => ({
    clearRect: vi.fn(),
    drawImage: vi.fn(),
    save: vi.fn(),
    restore: vi.fn(),
    translate: vi.fn(),
    rotate: vi.fn(),
    scale: vi.fn(),
    beginPath: vi.fn(),
    moveTo: vi.fn(),
    lineTo: vi.fn(),
    stroke: vi.fn(),
    fill: vi.fn(),
    fillRect: vi.fn(),
    strokeRect: vi.fn(),
    ellipse: vi.fn(),
    closePath: vi.fn(),
    fillText: vi.fn(),
})) as any;

describe('ImageEditorModal', () => {
    it('renders modal when modelValue is true', () => {
        const wrapper = mount(ImageEditorModal, {
            props: {
                modelValue: true,
                imageSource: null,
                title: 'Test Editor',
            },
        });

        expect(wrapper.text()).toContain('Test Editor');
        expect(wrapper.find('button[title*="Pencil"]').exists()).toBe(true);
        expect(wrapper.find('button[title*="Crop"]').exists()).toBe(true);
        expect(wrapper.find('button[title*="Reset"]').exists()).toBe(true);
    });

    it('emits close when Cancel button is clicked', async () => {
        const wrapper = mount(ImageEditorModal, {
            props: {
                modelValue: true,
                imageSource: null,
                title: 'Test Editor',
            },
        });

        const cancelBtn = wrapper.findAll('button').find((b) => b.text().includes('Cancel'));
        expect(cancelBtn).toBeDefined();
        await cancelBtn!.trigger('click');

        expect(wrapper.emitted('close')).toBeTruthy();
        expect(wrapper.emitted('update:modelValue')?.[0]).toEqual([false]);
    });

    it('switches to crop mode and displays crop aspect ratio controls', async () => {
        const wrapper = mount(ImageEditorModal, {
            props: {
                modelValue: true,
                imageSource: null,
                title: 'Test Editor',
            },
        });

        const cropBtn = wrapper.find('button[title*="Crop"]');
        await cropBtn.trigger('click');

        expect(wrapper.text()).toContain('1:1 Square');
        expect(wrapper.text()).toContain('Apply Crop');
    });
});
