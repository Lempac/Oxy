export const addUser = { url: () => '/api/invites/join' };
export const removeUser = { url: (id: string) => `/api/server/${id}/remove-user` };
export const leave = { url: (id: string) => `/api/server/${id}/leave` };
export const edit = { url: (id: string) => `/api/server/${id}` };
export const update = { url: (id: string) => `/api/server/${id}` };
export const destroy = { url: (id: string) => `/api/server/${id}` };
export const create = { url: () => '/api/server' };
export const server = { url: (id: string) => `/channels/${id}` };
export default { addUser, removeUser, leave, edit, update, destroy, create, server };
