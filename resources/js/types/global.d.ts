import {PageProps as InertiaPageProps} from '@inertiajs/core';
import {AxiosInstance} from 'axios';
import {route as ziggyRoute} from 'ziggy-js';
import {PageProps as AppPageProps} from './';
import Pusher from "pusher-js";
import {Ziggy} from "@/ziggy";

declare global {
    interface Window {
        axios: AxiosInstance;
        Pusher: typeof Pusher;
        Ziggy: typeof Ziggy;
    }
    let route: typeof ziggyRoute;
}

declare module 'vue' {
    interface ComponentCustomProperties {
        route: typeof ziggyRoute;
    }
}

declare module '@inertiajs/core' {
    interface PageProps extends InertiaPageProps, AppPageProps {}
}
