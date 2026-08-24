export interface PocketBaseRecord {
  id: string;
  get(key: string): unknown;
  getString(key: string): string;
  getInt(key: string): number;
  getDateTime(key: string): { time(): { unix(): number } };
  set(key: string, value: unknown): void;
}

export interface PocketBaseAuthRecord extends PocketBaseRecord {
  email: string;
}

export interface PocketBaseRequestInfo {
  authRecord: PocketBaseAuthRecord | null;
  data: Record<string, unknown>;
}

export interface PocketBaseEvent {
  request: {
    pathValue(key: string): string;
  };
  json(status: number, data: unknown): unknown;
}

export interface PocketBaseApp {
  findFirstRecordByData(collection: string, field: string, value: unknown): PocketBaseRecord;
  findRecordById(collection: string, id: string): PocketBaseRecord;
  findRecordsByFilter(collection: string, filter: string): PocketBaseRecord[];
  findCollectionByNameOrId(nameOrId: string): unknown;
  fileUrl(record: PocketBaseRecord, fileName: string): string;
  save(record: unknown): void;
  delete(record: unknown): void;
}

export interface PocketBaseApis {
  requestInfo(event: unknown): PocketBaseRequestInfo;
}

export interface PocketBaseSecurity {
  base64UrlEncode(str: string): string;
  hs256(input: string, secret: string): string;
}

declare global {
  const $app: PocketBaseApp;
  const $apis: PocketBaseApis;
  const $security: PocketBaseSecurity;
  const Record: new (collection: unknown, data?: Record<string, unknown>) => PocketBaseRecord;
  function routerAdd(method: string, path: string, handler: (e: PocketBaseEvent) => unknown): void;
}
