import { defineComponent, h, reactive } from 'vue';
import { useRouter } from 'vue-router';
import pb from '@/pocketbase';

export const Head = defineComponent({
  name: 'HeadComponent',
  props: {
    title: { type: String, default: '' }
  },
  setup(props, { slots }) {
    if (props.title && typeof document !== 'undefined') {
      document.title = `${props.title} - Oxy`;
    }
    return () => slots.default ? slots.default() : null;
  }
});

export const Link = defineComponent({
  name: 'LinkComponent',
  props: {
    href: { type: String, default: '#' },
    to: { type: String, default: '' },
    method: { type: String, default: 'get' },
    as: { type: String, default: 'a' }
  },
  setup(props, { slots }) {
    const routerInstance = useRouter();
    const handleClick = (e: MouseEvent) => {
      e.preventDefault();
      const target = props.to || props.href;
      if (target && routerInstance) {
        routerInstance.push(target);
      }
    };
    return () => h('a', { href: props.href || props.to, onClick: handleClick }, slots.default ? slots.default() : []);
  }
});

export function useForm<T extends Record<string, unknown>>(initialValues: T) {
  const form = reactive({
    ...initialValues,
    processing: false,
    errors: {} as Record<string, string>,
    isDirty: false,
    post(_url: string, _options?: Record<string, unknown>) {
      this.processing = false;
    },
    put(_url: string, _options?: Record<string, unknown>) {
      this.processing = false;
    },
    delete(_url: string, _options?: Record<string, unknown>) {
      this.processing = false;
    },
    reset(...fields: string[]) {
      if (fields.length === 0) {
        Object.assign(this, initialValues);
      } else {
        fields.forEach((f) => {
          (this as Record<string, unknown>)[f] = initialValues[f];
        });
      }
    },
    clearErrors() {
      this.errors = {};
    },
    setError(field: string | Record<string, string>, value?: string) {
      if (typeof field === 'string' && value) {
        this.errors[field] = value;
      } else if (typeof field === 'object') {
        Object.assign(this.errors, field);
      }
    }
  });
  return form;
}

export const router = {
  visit(url: string) {
    if (typeof window !== 'undefined') {
      window.location.href = url;
    }
  },
  reload(_options?: Record<string, unknown>) {
    // SPA reactive reload trigger
  },
  post(_url: string, _data?: unknown, _options?: Record<string, unknown>) {},
  patch(_url: string, _data?: unknown, _options?: Record<string, unknown>) {},
  delete(_url: string, _options?: Record<string, unknown>) {},
  on(_event: string, _callback: unknown) {
    return () => {};
  }
};

export function usePage() {
  const user = pb.authStore.model ? {
    id: pb.authStore.model.id,
    nickname: pb.authStore.model.name || pb.authStore.model.email || 'User',
    icon: pb.authStore.model.avatar || null,
    status: 'online',
    light_theme: 'oxy',
    dark_theme: 'dark',
    roles: [],
    servers: []
  } : null;

  return reactive({
    props: {
      user,
      selectedServer: undefined,
      servers: [],
      channels: []
    }
  });
}
