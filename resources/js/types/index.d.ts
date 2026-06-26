interface Object {
    readonly id: number;
    readonly update_at: string;
}

export const ChannelType = {
    Text: 'text',
    Voice: 'voice',
    Board: 'board',
    Whiteboard: 'whiteboard',
} as const;

export const MessageType = {
    Text: 'text',
    Image: 'image',
    File: 'file',
} as const;

export const Themes = {
    OXY: 'oxy',
    LIGHT: 'light',
    DARK: 'dark',
    CUPCAKE: 'cupcake',
    BUMBLEBEE: 'bumblebee',
    EMERALD: 'emerald',
    CORPORATE: 'corporate',
    SYNTHWAVE: 'synthwave',
    RETRO: 'retro',
    CYBERPUNK: 'cyberpunk',
    VALENTINE: 'valentine',
    HALLOWEEN: 'halloween',
    GARDEN: 'garden',
    FOREST: 'forest',
    AQUA: 'aqua',
    LOFI: 'lofi',
    PASTEL: 'pastel',
    FANTASY: 'fantasy',
    WIREFRAME: 'wireframe',
    BLACK: 'black',
    LUXURY: 'luxury',
    DRACULA: 'dracula',
    CMYK: 'cmyk',
    AUTUMN: 'autumn',
    BUSINESS: 'business',
    ACID: 'acid',
    LEMONADE: 'lemonade',
    NIGHT: 'night',
    COFFEE: 'coffee',
    WINTER: 'winter',
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
    CAN_SEE_CHANNEL: 'CAN_SEE_CHANNEL', //TODO: Add logic for minimum role to see channel
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
    mdata: string;
    user_id: number;
    readonly created_at: string;
    readonly sender: User;
}

export interface Call {
    id: number;
    start_at: string;
    end_at: string;
}

export interface User {
    id: number;
    icon: string | null;
    name: string;
    email: string;
    readonly email_verified_at: string | null;
    theme: ThemeType;
    roles: Role[] | null;
    servers: Server[] | null;
}

export type PageProps<T extends Record<string, unknown> = Record<string, unknown>> = T & {
    user: User | null;
    // readonly servers: Server[] | null | undefined;
    // readonly channels: Channel[] | null | undefined;
    // readonly messages: Message[] | null | undefined;
    // readonly selected_server: Server | null | undefined;
    // readonly selected_channel: Channel | null | undefined;
    // readonly selected_message: Message | null | undefined;
    // readonly invite_code: string | undefined;
};
