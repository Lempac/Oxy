import {Config} from 'ziggy-js';

interface Object {
    id: number;
    update_at: string;
}

export enum ChannelType {
    Text = 'text',
    Voice = 'voice',
    Board = 'board',
}

export enum MessageType {
    Text = 'text',
    Image = 'image',
}

export interface Note extends Object {
    title: string;
    text: string;
    board_id: number;
}

export interface Board extends Object {
    name: string;
    notes: Note[];
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

export interface Role extends Object {
    name: string;
    color: string;
    perms: number;
    importance: number;
    created_at: string;
    users: User[] | null;
    server: Server | null;
}

export interface Message extends Object {
    type: MessageType;
    mdata: string;
    user_id: number;
    created_at: string;
    sender: User;
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
    email_verified_at: string | null;
    roles: Role[] | null;
    servers: Server[] | null;
}

export type PageProps<T extends Record<string, unknown> = Record<string, unknown>> = T & {
    auth: {
        user: User;
    };
    servers: Server[] | null | undefined;
    channels: Channel[] | null | undefined;
    messages: Message[] | null | undefined;
    selected_server: Server | null | undefined;
    selected_channel: Channel | null | undefined;
    selected_message: Message | null | undefined;
    invite_code: string | undefined;
    ziggy: Config & { location: string };
};
