export const index = { url: (serverId: string) => `/api/roles/${serverId}` };
export const addUser = { url: (roleId: string, userId: string) => `/api/roles/${roleId}/add-user/${userId}` };
export const removeUser = { url: (roleId: string, userId: string) => `/api/roles/${roleId}/remove-user/${userId}` };
export const create = { url: (serverId: string) => `/api/roles/${serverId}` };
export const edit = { url: (roleId: string) => `/api/roles/${roleId}` };
export const deleteMethod = { url: (roleId: string) => `/api/roles/${roleId}` };
export default { index, addUser, removeUser, create, edit, deleteMethod };
