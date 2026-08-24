import { createRouter, createWebHistory } from 'vue-router';
import pb from '@/pocketbase';

import Welcome from '@/Pages/Welcome.vue';
import Home from '@/Pages/Home.vue';
import Login from '@/Pages/Auth/Login.vue';
import Register from '@/Pages/Auth/Register.vue';
import Texting from '@/Pages/Text/Texting.vue';
import WhiteboardBoard from '@/Pages/Whiteboard/WhiteboardBoard.vue';
import Manual from '@/Pages/Manual.vue';
import ProfileEdit from '@/Pages/Profile/Edit.vue';
import ServerSettings from '@/Pages/Settings/Server.vue';
import MembersSettings from '@/Pages/Settings/Members.vue';
import RoleSettings from '@/Pages/Settings/Role.vue';

const routes = [
    { path: '/', name: 'welcome', component: Welcome },
    { path: '/login', name: 'login', component: Login },
    { path: '/register', name: 'register', component: Register },
    { path: '/home', name: 'home', component: Home, meta: { requiresAuth: true } },
    { path: '/channels/:serverId/:channelId', name: 'channel', component: Home, meta: { requiresAuth: true } },
    { path: '/manual', name: 'manual', component: Manual },
    { path: '/profile', name: 'profile', component: ProfileEdit, meta: { requiresAuth: true } },
    { path: '/settings/server/:serverId', name: 'settings.server', component: ServerSettings, meta: { requiresAuth: true } },
    { path: '/settings/members/:serverId', name: 'settings.members', component: MembersSettings, meta: { requiresAuth: true } },
    { path: '/settings/roles/:serverId', name: 'settings.roles', component: RoleSettings, meta: { requiresAuth: true } },
];

export const router = createRouter({
    history: createWebHistory(),
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
