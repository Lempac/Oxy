import { describe, expect, it } from 'vitest';
import { getMemberRoleColor } from '@/bootstrap';
import { Role, User } from '@/types';

describe('getMemberRoleColor', () => {
    const ownerRole: Role = { id: 'r1', name: 'Owner', importance: 0, color: '#FF0000', perms: [], created_at: '', users: null, server: null, update_at: '' };
    const adminRole: Role = { id: 'r2', name: 'Admin', importance: 1, color: '#00FF00', perms: [], created_at: '', users: null, server: null, update_at: '' };
    const memberRole: Role = { id: 'r3', name: 'Member', importance: 2, color: '#0000FF', perms: [], created_at: '', users: null, server: null, update_at: '' };

    const serverRoles = [memberRole, ownerRole, adminRole];

    it('returns undefined when user or serverRoles are null/empty', () => {
        expect(getMemberRoleColor(null, serverRoles)).toBeUndefined();
        expect(getMemberRoleColor({ id: 'u1', nickname: 'test', status: 'online', light_theme: 'oxy', dark_theme: 'dark', icon: null, roles: [], servers: [] }, [])).toBeUndefined();
    });

    it('returns highest order role color (minimum importance) when user has multiple roles', () => {
        const user: User = {
            id: 'u1',
            nickname: 'John',
            status: 'online',
            light_theme: 'oxy',
            dark_theme: 'dark',
            icon: null,
            roles: [adminRole, memberRole],
            servers: [],
        };

        expect(getMemberRoleColor(user, serverRoles)).toBe('#00FF00');
    });

    it('returns owner color when user has owner and member roles', () => {
        const user: User = {
            id: 'u2',
            nickname: 'Boss',
            status: 'online',
            light_theme: 'oxy',
            dark_theme: 'dark',
            icon: null,
            rolesWithServer: [memberRole, ownerRole],
            roles: [],
            servers: [],
        };

        expect(getMemberRoleColor(user, serverRoles)).toBe('#FF0000');
    });
});
