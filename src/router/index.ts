import { createRouter, createWebHashHistory } from 'vue-router';
import pb from '@/pocketbase';

const routes = [
    { path: '/', name: 'welcome', component: () => import('@/Pages/Welcome.vue') },
    { path: '/login', name: 'login', component: () => import('@/Pages/Auth/Login.vue') },
    { path: '/register', name: 'register', component: () => import('@/Pages/Auth/Register.vue') },
    { path: '/home', name: 'home', component: () => import('@/Pages/Home.vue'), meta: { requiresAuth: true } },
    { path: '/channels/:serverId/:channelId', name: 'channel', component: () => import('@/Pages/Home.vue'), meta: { requiresAuth: true } },
    { path: '/manual', name: 'manual', component: () => import('@/Pages/Manual.vue') },
    { path: '/profile', name: 'profile', component: () => import('@/Pages/Profile/Edit.vue'), meta: { requiresAuth: true } },
    { path: '/settings/server/:serverId', name: 'settings.server', component: () => import('@/Pages/Settings/Server.vue'), meta: { requiresAuth: true } },
    { path: '/settings/members/:serverId', name: 'settings.members', component: () => import('@/Pages/Settings/Members.vue'), meta: { requiresAuth: true } },
    { path: '/settings/roles/:serverId', name: 'settings.roles', component: () => import('@/Pages/Settings/Role.vue'), meta: { requiresAuth: true } },
];

export const router = createRouter({
    history: createWebHashHistory(),
    routes,
});

router.beforeEach((to, _from, next) => {
    const isAuthenticated = pb.authStore.isValid;
    if (to.meta.requiresAuth && !isAuthenticated) {
        next('/login');
    } else if ((to.path === '/login' || to.path === '/register') && isAuthenticated) {
        next('/home');
    } else {
        next();
    }
});

export default router;
