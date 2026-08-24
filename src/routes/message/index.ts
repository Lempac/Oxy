export const create = { url: (serverId: string, channelId: string) => `/api/message/${serverId}/${channelId}` };
export const edit = { url: (messageId: string) => `/api/message/${messageId}` };
export const deleteMethod = { url: (messageId: string) => `/api/message/${messageId}` };
export default { create, edit, deleteMethod };
