interface Object {
    readonly id: number;
    readonly update_at: string;
}

export const UserStatus = {
    Online: 'online',
    Offline: 'offline',
    Idle: 'idle',
    Invisible: 'invisible',
    DoNotDisturb: 'do_not_disturb',
} as const;

export type UserStatusType = typeof UserStatus[keyof typeof UserStatus];

export const VoiceCallStatus = {
    Idle: 'idle',
    Ringing: 'ringing',
    Connecting: 'connecting',
    Active: 'active',
    Disconnected: 'disconnected',
    Ended: 'ended',
} as const;

export type VoiceCallStatusType = typeof VoiceCallStatus[keyof typeof VoiceCallStatus];

export const VoiceParticipantState = {
    Disconnected: 'disconnected',
    Joining: 'joining',
    Connected: 'connected',
    Muted: 'muted',
    Deafened: 'deafened',
    Leaving: 'leaving',
} as const;

export type VoiceParticipantStateType = typeof VoiceParticipantState[keyof typeof VoiceParticipantState];

export const ApplicationState = {
    Initializing: 'initializing',
    Unauthenticated: 'unauthenticated',
    Authenticating: 'authenticating',
    Ready: 'ready',
    Reconnecting: 'reconnecting',
    Error: 'error',
} as const;

export type ApplicationStateType = typeof ApplicationState[keyof typeof ApplicationState];

export const WhiteboardSyncState = {
    Uninitialized: 'uninitialized',
    Synced: 'synced',
    Dirty: 'dirty',
    Saving: 'saving',
    SaveFailed: 'save_failed',
} as const;

export type WhiteboardSyncStateType = typeof WhiteboardSyncState[keyof typeof WhiteboardSyncState];

export const MessageStatus = {
    Sending: 'sending',
    Sent: 'sent',
    Delivered: 'delivered',
    Edited: 'edited',
    Deleted: 'deleted',
    Failed: 'failed',
} as const;

export type MessageStatusType = typeof MessageStatus[keyof typeof MessageStatus];

export const ServerMemberStatus = {
    Invited: 'invited',
    Active: 'active',
    Muted: 'muted',
    Suspended: 'suspended',
    Left: 'left',
} as const;

export type ServerMemberStatusType = typeof ServerMemberStatus[keyof typeof ServerMemberStatus];

export const ChannelType = {
    Text: 'text',
    Voice: 'voice',
    Whiteboard: 'whiteboard',
} as const;

export type ChannelType = typeof ChannelType[keyof typeof ChannelType];

export const MessageType = {
    Text: 'text',
    Image: 'image',
    File: 'file',
} as const;

export type MessageType = typeof MessageType[keyof typeof MessageType];

export const Themes = {
    Oxy: 'oxy',
    Light: 'light',
    Dark: 'dark',
    Cupcake: 'cupcake',
    Bumblebee: 'bumblebee',
    Emerald: 'emerald',
    Corporate: 'corporate',
    Synthwave: 'synthwave',
    Retro: 'retro',
    Cyberpunk: 'cyberpunk',
    Valentine: 'valentine',
    Halloween: 'halloween',
    Garden: 'garden',
    Forest: 'forest',
    Aqua: 'aqua',
    Lofi: 'lofi',
    Pastel: 'pastel',
    Fantasy: 'fantasy',
    Wireframe: 'wireframe',
    Black: 'black',
    Luxury: 'luxury',
    Dracula: 'dracula',
    Cmyk: 'cmyk',
    Autumn: 'autumn',
    Business: 'business',
    Acid: 'acid',
    Lemonade: 'lemonade',
    Night: 'night',
    Coffee: 'coffee',
    Winter: 'winter',

    // Alias uppercase for backwards compatibility
    OXY: 'oxy',
    LIGHT: 'light',
    DARK: 'dark',
} as const;

export type ThemeType = typeof Themes[keyof typeof Themes];

export const PermType = {
    CAN_DELETE_SERVER: 'CAN_DELETE_SERVER',
    CAN_EDIT_SERVER: 'CAN_EDIT_SERVER',
    CAN_CREATE_CHANNEL: 'CAN_CREATE_CHANNEL',
    CAN_DELETE_CHANNEL: 'CAN_DELETE_CHANNEL',
    CAN_EDIT_CHANNEL: 'CAN_EDIT_CHANNEL',
    CAN_CREATE_MESSAGE: 'CAN_CREATE_MESSAGE',
    CAM_CREATE_ATTACHMENTS: 'CAM_CREATE_ATTACHMENTS',
    CAN_DELETE_MESSAGE: 'CAN_DELETE_MESSAGE',
    CAN_MANAGE_CHANNEL: 'CAN_MANAGE_CHANNEL',
    CAN_CREATE_ROLE: 'CAN_CREATE_ROLE',
    CAN_DELETE_ROLE: 'CAN_DELETE_ROLE',
    CAN_EDIT_ROLE: 'CAN_EDIT_ROLE',
    CAN_MANAGE_MEMBERS: 'CAN_MANAGE_MEMBERS',
    CAN_MANAGE_ROLE: 'CAN_MANAGE_ROLE',
    CAN_MANAGE_SERVER: 'CAN_MANAGE_SERVER',
    CAN_SEE_CHANNEL: 'CAN_SEE_CHANNEL',
    CAN_INVITE: 'CAN_INVITE',
    CAN_KICK: 'CAN_KICK',
    CAN_EDIT_MEMBER_ROLES: 'CAN_EDIT_MEMBER_ROLES',
} as const;

export interface Role extends Object {
    name: string;
    color: string;
    perms: string[];
    importance: number;
    readonly created_at: string;
    users: User[] | null;
    server: Server | null;
}

export interface Whiteboard extends Object {
    channel_id: number;
    state: string | null;
    sync_status?: WhiteboardSyncStateType;
}

export interface Channel extends Object {
    name: string;
    type: ChannelType;
    server_id: number;
    route_key: string;
}

export interface Server extends Object {
    name: string;
    description: string;
    icon: string | null;
    users: User[] | null;
    roles: Role[] | null;
    route_key: string;
}

export interface Perms {
    perms: string[];
    has: (perm: string | string[]) => boolean;
    hasAny: (perm: string | string[]) => boolean;
    add: (perm: string | string[]) => void;
    remove: (perm: string | string[]) => void;
}

export interface Message extends Object {
    type: MessageType;
    status?: MessageStatusType;
    mdata: string;
    user_id: number;
    readonly created_at: string;
    readonly sender: User;
}

export interface Call {
    id: number;
    status: VoiceCallStatusType;
    start_at: string;
    end_at: string;
}

export interface User {
    id: number;
    icon: string | null;
    name: string;
    email: string;
    readonly email_verified_at: string | null;
    status: UserStatusType;
    light_theme: ThemeType;
    dark_theme: ThemeType;
    roles: Role[] | null;
    servers: Server[] | null;
}

export type PageProps<T extends Record<string, unknown> = Record<string, unknown>> = T & {
    user: User | null;
};
