import {PageProps as InertiaPageProps} from '@inertiajs/core';
import {PageProps as AppPageProps} from './';
import Pusher from "pusher-js";

declare global {
    interface Window {
        Pusher: typeof Pusher;
    }
}

declare module '@inertiajs/core' {
    interface PageProps extends InertiaPageProps, AppPageProps {}
}
