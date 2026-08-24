declare global {
  const $app: any;
  const $apis: any;
  const $security: any;
  const Record: any;
  function routerAdd(method: string, path: string, handler: (e: any) => any): void;
}

export {};
