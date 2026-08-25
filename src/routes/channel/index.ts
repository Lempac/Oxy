export const create = { url: (serverId: string) => `/api/channel/${serverId}` };
export const deleteMethod = {
  url: (serverIdOrObj: string | { server: string; channel: string }, channelId?: string) => {
    if (typeof serverIdOrObj === 'object') {
      return `/api/channel/${serverIdOrObj.server}/${serverIdOrObj.channel}`;
    }
    return `/api/channel/${serverIdOrObj}/${channelId || ''}`;
  }
};
export const edit = {
  url: (serverIdOrObj: string | { server: string; channel: string }, channelId?: string) => {
    if (typeof serverIdOrObj === 'object') {
      return `/api/channel/${serverIdOrObj.server}/${serverIdOrObj.channel}`;
    }
    return `/api/channel/${serverIdOrObj}/${channelId || ''}`;
  }
};
export default { create, deleteMethod, edit };
