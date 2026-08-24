/// <reference types="vite/client" />

declare module '*.vue' {
  import type { DefineComponent } from 'vue';
  const component: DefineComponent<Record<string, unknown>, Record<string, unknown>, unknown>;
  export default component;
}

declare module '@inertiajs/vue3' {
  export const Head: unknown;
  export const Link: unknown;
  export const useForm: <T extends Record<string, unknown>>(values: T) => T & {
    processing: boolean;
    errors: Record<string, string>;
    isDirty: boolean;
    post: (url: string, options?: unknown) => void;
    put: (url: string, options?: unknown) => void;
    delete: (url: string, options?: unknown) => void;
    reset: (...fields: string[]) => void;
    clearErrors: () => void;
    setError: (field: string | Record<string, string>, value?: string) => void;
  };
  export const router: {
    visit: (url: string) => void;
    reload: (options?: unknown) => void;
    post: (url: string, data?: unknown, options?: unknown) => void;
    patch: (url: string, data?: unknown, options?: unknown) => void;
    delete: (url: string, options?: unknown) => void;
    on: (event: string, callback: unknown) => () => void;
  };
  export const usePage: () => { props: Record<string, unknown> };
  export const createInertiaApp: unknown;
}

declare module '@inertiajs/core' {
  export type PageProps<T = Record<string, unknown>> = T;
}

declare module '@vue/runtime-core' {
  export interface ComponentCustomProperties {
    $page: { props: Record<string, unknown> };
    $router: unknown;
  }
}
