export const server = { url: (id: string) => `/channels/${id}` };
export const text = { url: (id?: string) => id ? `/channels/${id}` : '/home' };
export const voice = { url: (id?: string) => id ? `/channels/${id}` : '/home' };
export const whiteboard = { url: (id?: string) => id ? `/channels/${id}` : '/home' };
export default { server, text, voice, whiteboard };
