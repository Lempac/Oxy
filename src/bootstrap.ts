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

export const bigIntToPerms = (newPrem: string[]): Perms => {
    const permSet = new Set<string>(newPrem || []);
    return {
        perms: Array.from(permSet),
        add(addPerm) {
            const permsToAdd = Array.isArray(addPerm) ? addPerm : [addPerm];
            for (const p of permsToAdd) {
                permSet.add(p);
            }
            this.perms = Array.from(permSet);
        },
        has(otherPerm) {
            if (permSet.has('ADMINISTRATOR')) return true;
            const permsToCheck = Array.isArray(otherPerm) ? otherPerm : [otherPerm];
            return permsToCheck.every(p => permSet.has(p));
        },
        hasAny(otherPerm) {
            if (permSet.has('ADMINISTRATOR')) return true;
            const permsToCheck = Array.isArray(otherPerm) ? otherPerm : [otherPerm];
            return permsToCheck.some(p => permSet.has(p));
        },
        remove(removePerm) {
            const permsToRemove = Array.isArray(removePerm) ? removePerm : [removePerm];
            for (const p of permsToRemove) {
                permSet.delete(p);
            }
            this.perms = Array.from(permSet);
        }
    };
};

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

export const usePerms = (server?: Server | null, user?: User | null) => {
    return computed(() => {
        const currentUser = user || (pb.authStore.model as unknown as User);
        const currentServer = server;

        if (!currentServer || !currentUser) {
            return bigIntToPerms(['ADMINISTRATOR']);
        }

        if ((currentServer as unknown as { owner?: string }).owner === currentUser.id) {
            return bigIntToPerms(['ADMINISTRATOR']);
        }

        const userRoles = currentUser.rolesWithServer || currentUser.roles || [];
        const serverRoles = currentServer.roles || [];

        const matchingRoles = serverRoles.filter(sRole =>
            userRoles.some(uRole => String(uRole.id) === String(sRole.id))
        );

        if (matchingRoles.length === 0) {
            return bigIntToPerms(['ADMINISTRATOR']);
        }

        const allPerms = new Set<string>();
        for (const r of matchingRoles) {
            if (r.perms) {
                for (const p of r.perms) {
                    allPerms.add(p);
                }
            }
        }

        return bigIntToPerms(Array.from(allPerms));
    });
};
