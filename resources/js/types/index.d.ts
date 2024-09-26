import { Config } from 'ziggy-js';

// export interface Messages

export interface Server {
    id: number;
    name: string;
    description: string;
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
