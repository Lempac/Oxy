import {Config} from 'ziggy-js';

// export interface Messages

export interface Server {
    id: number;
    name: string;
    description: string;
}

enum MessageType {
    Text,
    Image
}

export interface Message {
    type: MessageType,
    data: string | null,
    user_id: int,
}

export interface Call {
    start_at: Date,
    end_at: Date
}

export interface User {
    id: number;
    name: string;
    email: string;
    email_verified_at?: string;
}

export type PageProps<T extends Record<string, unknown> = Record<string, unknown>> = T & {
    auth: {
        user: User;
        servers: Server[] | null;
    };
    ziggy: Config & { location: string };
};
