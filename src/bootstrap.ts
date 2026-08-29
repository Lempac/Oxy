import pb from '@/pocketbase';
import { Perms, Role, Server, User } from "@/types";
import { computed } from 'vue';

export const defaultIcon = "images/icon.svg";

export const baseUrl = pb.baseUrl;

export const resolveUrl = (pathOrUrl?: string | null): string => {
    if (!pathOrUrl) return '';
    if (pathOrUrl.startsWith('http://') || pathOrUrl.startsWith('https://') || pathOrUrl.startsWith('blob:') || pathOrUrl.startsWith('data:')) {
        return pathOrUrl;
    }
    const base = pb.baseUrl || '';
    return `${base}${pathOrUrl.startsWith('/') ? '' : '/'}${pathOrUrl}`;
};

export const bigIntToPerms = (newPrem: string[]): Perms => ({
    perms: [...newPrem],
    add(addPerm) {
        const permsToAdd = Array.isArray(addPerm) ? addPerm : [addPerm];
        for (const p of permsToAdd) {
            if (!this.perms.includes(p)) {
                this.perms.push(p);
            }
        }
    },
    has(otherPerm) {
        if (this.perms.includes('ADMINISTRATOR')) return true;
        const permsToCheck = Array.isArray(otherPerm) ? otherPerm : [otherPerm];
        return permsToCheck.every(p => this.perms.includes(p));
    },
    hasAny(otherPerm) {
        if (this.perms.includes('ADMINISTRATOR')) return true;
        const permsToCheck = Array.isArray(otherPerm) ? otherPerm : [otherPerm];
        return permsToCheck.some(p => this.perms.includes(p));
    },
    remove(removePerm) {
        const permsToRemove = Array.isArray(removePerm) ? removePerm : [removePerm];
        this.perms = this.perms.filter(p => !permsToRemove.includes(p));
    }
});

export const fetchJson = async (url: string, options: RequestInit = {}) => {
    const token = pb.authStore.token;
    const headers: Record<string, string> = {
        'Accept': 'application/json',
        ...((options.headers as Record<string, string>) || {})
    };

    if (token) {
        headers['Authorization'] = `Bearer ${token}`;
    }

    const response = await fetch(url, {
        ...options,
        headers
    });

    const isJson = response.headers.get('content-type')?.includes('application/json');
    const data = isJson ? await response.json() : null;

    if (!response.ok) {
        const error = new Error(response.statusText) as Error & { response: { status: number, data: unknown } };
        error.response = { status: response.status, data };
        throw error;
    }

    return { status: response.status, data };
};

export const joinServer = async (code: string): Promise<[number, string?]> => {
    try {
        const res = await pb.send('/api/invites/join', {
            method: 'POST',
            body: JSON.stringify({ code })
        });
        return [200, res?.[1] || 'Successfully joined server.'];
    } catch (err: unknown) {
        const error = err as { status?: number; message?: string };
        return [error.status || 500, error.message || 'Failed to join server.'];
    }
};

export const getMemberRoleColor = (
    user?: (User & { rolesWithServer?: Role[] | null }) | null,
    serverRoles?: Role[] | null
): string | undefined => {
    if (!user || !serverRoles || serverRoles.length === 0) return undefined;

    const userRoles = user.rolesWithServer || user.roles || [];
    if (userRoles.length === 0) return undefined;

    const memberServerRoles = serverRoles.filter(sRole =>
        userRoles.some(uRole => String(uRole.id) === String(sRole.id))
    );

    if (memberServerRoles.length === 0) return undefined;

    memberServerRoles.sort((a, b) => a.importance - b.importance);

    return memberServerRoles[0]?.color || undefined;
};

const ALL_PERMISSIONS_LIST = [
    'ADMINISTRATOR',
    'CAN_DELETE_SERVER',
    'CAN_EDIT_SERVER',
    'CAN_CREATE_CHANNEL',
    'CAN_DELETE_CHANNEL',
    'CAN_EDIT_CHANNEL',
    'CAN_CREATE_MESSAGE',
    'CAM_CREATE_ATTACHMENTS',
    'CAN_DELETE_MESSAGE',
    'CAN_MANAGE_CHANNEL',
    'CAN_CREATE_ROLE',
    'CAN_DELETE_ROLE',
    'CAN_EDIT_ROLE',
    'CAN_MANAGE_MEMBERS',
    'CAN_MANAGE_ROLE',
    'CAN_MANAGE_SERVER',
    'CAN_SEE_CHANNEL',
    'CAN_INVITE',
    'CAN_KICK',
    'CAN_EDIT_MEMBER_ROLES',
    'SEND_MESSAGES',
    'ATTACH_FILES',
    'CONNECT_VOICE',
    'SPEAK_VOICE'
];

export const usePerms = (server?: Server | null, user?: User | null) => {
    return computed(() => {
        const currentUser = user || (pb.authStore.model as unknown as User);
        const currentServer = server;

        if (!currentServer || !currentUser) {
            return bigIntToPerms(ALL_PERMISSIONS_LIST);
        }

        if ((currentServer as unknown as { owner?: string }).owner === currentUser.id) {
            return bigIntToPerms(ALL_PERMISSIONS_LIST);
        }

        const userRoles = currentUser.rolesWithServer || currentUser.roles || [];
        const serverRoles = currentServer.roles || [];

        const matchingRoles = serverRoles.filter(sRole =>
            userRoles.some(uRole => String(uRole.id) === String(sRole.id))
        );

        const allPerms = new Set<string>();
        for (const r of matchingRoles) {
            if (r.perms) {
                for (const p of r.perms) {
                    allPerms.add(p);
                }
            }
        }

        if (allPerms.has('ADMINISTRATOR') || matchingRoles.length === 0) {
            return bigIntToPerms(ALL_PERMISSIONS_LIST);
        }

        return bigIntToPerms(Array.from(allPerms));
    });
};
