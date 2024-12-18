import {Config} from 'ziggy-js';

interface Object {
    readonly id: number;
    readonly update_at: string;
}

export enum ChannelType {
    Text = 'text',
    Voice = 'voice',
    Board = 'board',
}

export enum MessageType {
    Text = 'text',
    Image = 'image',
    File = 'file',
}

export enum PermType {
    CAN_DELETE_SERVER = 1 << 0,
    CAN_EDIT_SERVER = 1 << 1,
    CAN_CREATE_CHANNEL = 1 << 2,
    CAN_DELETE_CHANNEL = 1 << 3,
    CAN_EDIT_CHANNEL = 1 << 4,
    CAN_CREATE_MESSAGE = 1 << 5,
    CAM_CREATE_ATTACHMENTS = 1 << 6,
    CAN_DELETE_MESSAGE = 1 << 7,
    CAN_MANAGE_CHANNEL = 1 << 8,
    CAN_CREATE_ROLE = 1 << 9,
    CAN_DELETE_ROLE = 1 << 10,
    CAN_EDIT_ROLE = 1 << 11,
    CAN_MANAGE_MEMBERS = 1 << 12,
    CAN_MANAGE_ROLE = 1 << 13,
    CAN_MANAGE_SERVER = 1 << 14,
    CAN_SEE_CHANNEL = 1 << 15, //TODO: Add logic for minimum role to see channel
    CAN_INVITE = 1 << 16,
    CAN_KICK = 1 << 17,
    CAN_EDIT_MEMBER_ROLES = 1 << 18,
}

export interface Role extends Object {
    name: string;
    color: string;
    perms: string;
    importance: number;
    readonly created_at: string;
    users: User[] | null;
    server: Server | null;
}

export interface Task extends Object {
    title: string;
    description: string;
    kanban_column_id: number;
    position: number;
}

export interface BoardColumn extends Object {
    name: string;
    kanban_column_id: number;
    position: number;
    tasks: Task[];
}

export interface Board extends Object {
    name: string;
    bio: string;
    columns: BoardColumn[];
    server_id: number;
}

export interface Channel extends Object {
    name: string;
    type: ChannelType;
    server_id: number;
}

export interface Server extends Object {
    name: string;
    description: string;
    icon: string | null;
    users: User[] | null;
    roles: Role[] | null;
}

export interface Perms {
    perm: bigint;
    has: (perm: bigint | PermType | PermType[]) => boolean;
    hasAny: (perm: bigint | PermType | PermType[]) => boolean;
    add: (perm: bigint | PermType | PermType[]) => void;
    remove: (perm: bigint | PermType | PermType[]) => void;
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
    ziggy: Config & { location: string };
};
