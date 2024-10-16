import {Config} from 'ziggy-js';

interface Object {
    id: number;
    update_at: Date;
}

export enum ChannelType {
    Text,
    Voice,
    Board
}

enum MessageType {
    Text,
    Image
}

export interface Note extends Object{
    title: string;
    text: string;
    board_id: number;
}

export interface Board extends Object{
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
    icon: string;
}

export interface Message extends Object {
    type: MessageType,
    data: string | null,
    user_id: int,
}

export interface Call {
    id: number;
    start_at: Date,
    end_at: Date
}

export interface User {
    id: number;
    icon: string;
    name: string;
    email: string;
    email_verified_at?: string;
}

export type PageProps<T extends Record<string, unknown> = Record<string, unknown>> = T & {
    auth: {
        user: User;
    };
    servers: Server[] | null;
    channels: Channel[] | null;
    messages: Message[] | null;
    selected_server: Server | null;
    selected_channel: Channel | null;
    selected_message: Message | null;
    ziggy: Config & { location: string };
};
