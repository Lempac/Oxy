export const channel = {
  url: (serverIdOrObj: string | { server: string; channel: string }, channelId?: string) => {
    if (typeof serverIdOrObj === 'object') {
      return `/channels/${serverIdOrObj.server}/${serverIdOrObj.channel}`;
    }
    return `/channels/${serverIdOrObj}/${channelId || ''}`;
  }
};
export default { channel };
