import { mount } from '@vue/test-utils';
import { describe, it, expect } from 'vitest';
import ApplicationLogo from './ApplicationLogo.vue';

describe('ApplicationLogo', () => {
    it('renders an image with correct src and alt', () => {
        const wrapper = mount(ApplicationLogo);
        const img = wrapper.find('img');
        
        expect(img.exists()).toBe(true);
        expect(img.attributes('src')).toBe('images/icon.svg');
        expect(img.attributes('alt')).toBe('Application Logo');
    });
});
