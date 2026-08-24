export const create = { url: (serverId: string) => `/api/channel/${serverId}` };
export const deleteMethod = { url: (serverId: string, channelId: string) => `/api/channel/${serverId}/${channelId}` };
export const edit = { url: (serverId: string, channelId: string) => `/api/channel/${serverId}/${channelId}` };
export default { create, deleteMethod, edit };
