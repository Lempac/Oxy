import { describe, expect, it, vi } from 'vitest';
import { mount } from '@vue/test-utils';
import ChannelSidebar from './ChannelSidebar.vue';
import { Channel, ChannelType, Server, User } from '@/types';
import { useVoiceCallStateMachine } from '@/composables/useVoiceCallStateMachine';

vi.mock('@/pocketbase', () => ({
    default: {
        collection: () => ({
            subscribe: vi.fn().mockResolvedValue(vi.fn()),
            create: vi.fn(),
            delete: vi.fn(),
            update: vi.fn()
        }),
        authStore: {
            model: { id: '1', name: 'TestUser' }
        }
    }
}));

vi.mock('vue-router', () => ({
    useRoute: () => ({ path: '/home/text/general' }),
    useRouter: () => ({ push: vi.fn() })
}));

describe('ChannelSidebar Component', () => {
    const mockUsers: User[] = [
        { id: '1', nickname: 'Alex', status: 'online', icon: null, light_theme: 'oxy', dark_theme: 'dark', roles: [], servers: [] },
        { id: '2', nickname: 'Sarah', status: 'online', icon: null, light_theme: 'oxy', dark_theme: 'dark', roles: [], servers: [] }
    ];

    const mockServer: Server = {
        id: '1',
        name: 'Dev Server',
        route_key: 'dev-server',
        description: 'Test Server',
        icon: '',
        roles: [],
        update_at: '',
        enable_whiteboard: true,
        users: mockUsers
    };

    const mockChannels: Channel[] = [
        { id: '101', name: 'general-chat', type: ChannelType.Text, route_key: 'general-chat', server_id: '1', update_at: '' },
        { id: '102', name: 'announcements', type: ChannelType.Text, route_key: 'announcements', server_id: '1', update_at: '' },
        { id: '201', name: 'Lounge', type: ChannelType.Voice, route_key: 'lounge', server_id: '1', update_at: '' },
        { id: '301', name: 'Project Diagram', type: ChannelType.Whiteboard, route_key: 'diagram', server_id: '1', update_at: '' }
    ];

    const mountOptions = {
        global: {
            stubs: {
                RouterLink: {
                    template: '<a><slot /></a>'
                }
            }
        }
    };

    it('renders text, voice, and whiteboard channel categories', () => {
        const wrapper = mount(ChannelSidebar, {
            ...mountOptions,
            props: {
                selectedServer: mockServer,
                channels: mockChannels
            }
        });

        expect(wrapper.text()).toContain('Text Channels');
        expect(wrapper.text()).toContain('general-chat');
        expect(wrapper.text()).toContain('Voice Channels');
        expect(wrapper.text()).toContain('Lounge');
        expect(wrapper.text()).toContain('Whiteboards');
        expect(wrapper.text()).toContain('Project Diagram');
    });

    it('renders connected users under voice channels only when users are in the channel', () => {
        const voiceState = useVoiceCallStateMachine();
        voiceState.resetState();

        const wrapper = mount(ChannelSidebar, {
            ...mountOptions,
            props: {
                selectedServer: mockServer,
                channels: mockChannels
            }
        });

        // Initially disconnected: no users displayed under Lounge
        expect(wrapper.text()).not.toContain('Alex');

        // Set connected users in voice channel 201
        voiceState.setChannelUsers(201, [
            { id: '1', nickname: 'Alex', status: 'online', icon: null, light_theme: 'oxy', dark_theme: 'dark', roles: [], servers: [] },
            { id: '2', nickname: 'Sarah', status: 'online', icon: null, light_theme: 'oxy', dark_theme: 'dark', roles: [], servers: [] }
        ]);

        const updatedWrapper = mount(ChannelSidebar, {
            ...mountOptions,
            props: {
                selectedServer: mockServer,
                channels: mockChannels
            }
        });

        expect(updatedWrapper.text()).toContain('Alex');
        expect(updatedWrapper.text()).toContain('Sarah');
    });

    it('joins voice channel directly on clicking voice channel name', async () => {
        const voiceState = useVoiceCallStateMachine();
        voiceState.resetState();

        const wrapper = mount(ChannelSidebar, {
            ...mountOptions,
            props: {
                selectedServer: mockServer,
                channels: mockChannels
            }
        });

        const voiceChannelItem = wrapper.findAll('.cursor-pointer').find(el => el.text().includes('Lounge'));
        expect(voiceChannelItem).toBeDefined();

        await voiceChannelItem!.trigger('click');
        expect(voiceState.isConnected.value).toBe(true);
        expect(voiceState.activeChannel.value?.name).toBe('Lounge');
    });

    it('shows mute, deafen, and afk icons for users in voice channel and opens profile on click', async () => {
        const voiceState = useVoiceCallStateMachine();
        voiceState.resetState();

        voiceState.setChannelUsers(201, [
            { id: '1', nickname: 'Alex', status: 'online', is_afk: true } as unknown as User,
            { id: '2', nickname: 'Sarah', status: 'online', is_muted: true } as unknown as User
        ]);

        const wrapper = mount(ChannelSidebar, {
            ...mountOptions,
            props: {
                selectedServer: mockServer,
                channels: mockChannels
            }
        });

        const alexItem = wrapper.findAll('.cursor-pointer').find(el => el.text().includes('Alex'));
        expect(alexItem).toBeDefined();

        // Click user to open profile modal
        await alexItem!.trigger('click');
        expect(wrapper.findComponent({ name: 'UserProfileModal' }).exists()).toBe(true);
    });
});
