import pb from '@/pocketbase';
import { Perms, Role, Server, User } from "@/types";
import { computed } from 'vue';

export const defaultIcon = "/images/icon.svg";

export const baseUrl = typeof window !== 'undefined' ? window.location.origin : '';

export const resolveUrl = (pathOrUrl?: string | null): string => {
    if (!pathOrUrl) return '';
    if (pathOrUrl.startsWith('http://') || pathOrUrl.startsWith('https://') || pathOrUrl.startsWith('blob:') || pathOrUrl.startsWith('data:')) {
        return pathOrUrl;
    }
    return `${baseUrl}${pathOrUrl.startsWith('/') ? '' : '/'}${pathOrUrl}`;
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
        const permsToCheck = Array.isArray(otherPerm) ? otherPerm : [otherPerm];
        return permsToCheck.every(p => this.perms.includes(p));
    },
    hasAny(otherPerm) {
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

export const usePerms = (server?: Server | null, user?: User | null) => {
    return computed(() => {
        if (server && server.roles !== null && server.roles !== undefined) {
            return bigIntToPerms(server.roles.filter((role: Role) => user?.roles?.some(roleObj => roleObj.id === role.id)).reduce((acc: string[], curr: Role) => [...new Set([...acc, ...curr.perms])], []));
        }
        return bigIntToPerms([]);
    });
};
