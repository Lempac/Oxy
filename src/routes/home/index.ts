export const server = { url: (id: string) => `/channels/${id}` };
export const text = { url: () => '/home' };
export const voice = { url: () => '/home' };
export const whiteboard = { url: () => '/home' };
export default { server, text, voice, whiteboard };
