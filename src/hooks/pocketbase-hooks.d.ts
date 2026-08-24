declare global {
  const $app: unknown;
  const $apis: unknown;
  const $security: unknown;
  const Record: unknown;
  function routerAdd(method: string, path: string, handler: (e: unknown) => unknown): void;
}

export {};
