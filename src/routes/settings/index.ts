export const members = { url: (id: string) => `/settings/members/${id}` };
export const role = { url: (id: string) => `/settings/roles/${id}` };
export const server = { url: (id: string) => `/settings/server/${id}` };
export default { members, role, server };
