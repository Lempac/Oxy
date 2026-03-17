import {PageProps as InertiaPageProps} from '@inertiajs/core';
import {AxiosInstance} from 'axios';
import {PageProps as AppPageProps} from './';
import Pusher from "pusher-js";

declare global {
    interface Window {
        axios: AxiosInstance;
        Pusher: typeof Pusher;
    }
}

declare module '@inertiajs/core' {
    interface PageProps extends InertiaPageProps, AppPageProps {}
}
