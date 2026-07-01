import {addUser} from '@/routes/server';
import './echo';
import {router, usePage} from "@inertiajs/vue3";
import {Perms, Role, Server} from "@/types";
import {computed} from 'vue';

export const defaultIcon = "https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcS78CXwhRL-71jDHotN6WOTp9dC1RWPQEAJUA&s";

export const baseUrl = typeof window !== 'undefined' ? window.location.origin : '';

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

export const usePerms = () => {
    return computed(() => {
        const page = usePage();
        const server = page.props.selectedServer as Server | undefined;
        const user = page.props.user;
        if (server && server.roles !== null && server.roles !== undefined) {
            return bigIntToPerms(server.roles.filter((role: Role) => user?.roles?.some(roleObj => roleObj.id === role.id)).reduce((acc: string[], curr: Role) => [...new Set([...acc, ...curr.perms])], []));
        }
        return bigIntToPerms([]);
    });
};

export const fetchJson = async (url: string, options: RequestInit = {}) => {
    const xsrfTokenMatch = document.cookie.match(new RegExp('(^| )XSRF-TOKEN=([^;]+)'));
    const xsrfToken = xsrfTokenMatch ? decodeURIComponent(xsrfTokenMatch[2]) : '';

    const headers: Record<string, string> = {
        'Accept': 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
        ...((options.headers as Record<string, string>) || {})
    };

    if (xsrfToken) {
        headers['X-XSRF-TOKEN'] = xsrfToken;
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
        await fetchJson(addUser.url(), {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ code })
        });
        
        router.reload({only: ['servers', 'user']});
        return [200, 'Successfully joined to server.'];
    } catch (err: unknown) {
        const error = err as { response?: { status?: number, data?: { message?: string } }, message?: string };
        return [error.response?.status || 500, error.response?.data?.message || error.message];
    }
}

