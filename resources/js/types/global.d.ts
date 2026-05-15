import {AxiosInstance} from 'axios';
import Pusher from "pusher-js";

declare global {
    interface Window {
        axios: AxiosInstance;
        Pusher: typeof Pusher;
    }
}

declare module "@inertiajs/core" {
    export interface InertiaConfig {
        flashDataType: {
            toast?: { type: "success" | "error"; message: string };
        };
        errorValueType: string[];
        layoutProps: {
            title: string;
            showSidebar: boolean;
        };
        namedLayoutProps: {
            app: { title: string; theme: "light" | "dark" };
            content: { padding: string; maxWidth: string };
        };
    }
}
