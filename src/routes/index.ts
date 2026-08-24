export const route = (path: string) => path;
export const welcome = { url: () => '/' };
export const home = { url: () => '/home' };
export const login = { url: () => '/login' };
export const logout = { url: () => '/login' };
export const register = { url: () => '/register' };
export const manual = { url: () => '/manual' };
export default { url: () => '/', welcome, home, login, logout, register, manual };
