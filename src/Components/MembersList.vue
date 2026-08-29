<script lang="ts" setup>
import { computed, ref, watch } from "vue";
import { defaultIcon, getMemberRoleColor, resolveUrl } from "@/bootstrap";
import { Role, Server, User } from "@/types";
import { HiUsers } from "vue-icons-plus/hi";
import UserProfileModal from "@/Components/UserProfileModal.vue";

import pb from "@/pocketbase";

const props = defineProps<{
    selectedServer?: Server;
}>();

const selectedProfileUser = ref<User | null>(null);
const serverMembers = ref<User[]>([]);
const serverRoles = ref<Role[]>([]);

const fetchServerMembersAndRoles = async () => {
    const sId = props.selectedServer?.id;
    if (!sId) {
        serverMembers.value = [];
        serverRoles.value = [];
        return;
    }
    try {
        const roleRecs = await pb.collection('roles').getFullList({
            filter: `server = "${sId}"`,
            sort: 'importance',
            requestKey: null
        });

        serverRoles.value = roleRecs.map(r => ({
            id: r.id,
            server_id: r.server,
            name: r.name,
            color: r.color,
            importance: r.importance,
            perms: r.perms || []
        })) as unknown as Role[];

        const memberRecs = await pb.collection('members').getFullList({
            filter: `server = "${sId}"`,
            expand: 'user,role',
            requestKey: null
        });

        serverMembers.value = memberRecs.map(m => ({
            id: m.expand?.user?.id || m.user,
            nickname: m.expand?.user?.name || m.expand?.user?.username || 'User',
            icon: m.expand?.user?.avatar || null,
            status: m.expand?.user?.status || 'online',
            rolesWithServer: m.expand?.role ? [{
                id: m.expand.role.id,
                name: m.expand.role.name,
                color: m.expand.role.color,
                importance: m.expand.role.importance,
                perms: m.expand.role.perms || []
            }] : [],
            roles: m.expand?.role ? [{
                id: m.expand.role.id,
                name: m.expand.role.name,
                color: m.expand.role.color,
                importance: m.expand.role.importance,
                perms: m.expand.role.perms || []
            }] : []
        })) as unknown as User[];
    } catch (err) {
        console.error('Error fetching server members & roles for MembersList:', err);
    }
};

watch(() => props.selectedServer?.id, () => {
    fetchServerMembersAndRoles();
}, { immediate: true });

const displayUsers = computed(() => {
    if (props.selectedServer?.users && props.selectedServer.users.length > 0) {
        return props.selectedServer.users;
    }
    return serverMembers.value;
});

const activeRoles = computed(() => (props.selectedServer?.roles && props.selectedServer.roles.length > 0) ? props.selectedServer.roles : serverRoles.value);

const openProfile = (user: User) => {
    selectedProfileUser.value = {
        ...user,
        rolesWithServer: user.rolesWithServer ? [...user.rolesWithServer] : (user.roles ? [...user.roles] : [])
    };
};

const closeProfile = () => {
    selectedProfileUser.value = null;
};

// Keep selected profile user up to date if server data reloads
watch(
    () => props.selectedServer,
    (newServer) => {
        const usersList = displayUsers.value;
        if (selectedProfileUser.value && usersList.length > 0) {
            const updatedUser = usersList.find((u) => String(u.id) === String(selectedProfileUser.value?.id));
            if (updatedUser) {
                const currentRoles = selectedProfileUser.value.rolesWithServer || [];
                selectedProfileUser.value = {
                    ...updatedUser,
                    rolesWithServer: updatedUser.rolesWithServer && updatedUser.rolesWithServer.length > 0 ? updatedUser.rolesWithServer : currentRoles
                };
            } else {
                closeProfile();
            }
        }
    },
    {deep: true}
);

const totalMembersCount = computed(() => displayUsers.value.length);

const onlineMembersCount = computed(() => {
    return displayUsers.value.filter(
        (user) => user.status === 'online' || user.status === 'idle' || user.status === 'do_not_disturb'
    ).length;
});

const getStatusBadgeClass = (status?: string) => {
    switch (status) {
        case 'online':
            return 'badge-success';
        case 'idle':
            return 'badge-warning';
        case 'do_not_disturb':
            return 'badge-error';
        case 'offline':
        case 'invisible':
        default:
            return 'badge-neutral opacity-60';
    }
};

const formatStatusText = (status?: string) => {
    switch (status) {
        case 'online':
            return 'Online';
        case 'idle':
            return 'Idle';
        case 'do_not_disturb':
            return 'Do Not Disturb';
        case 'invisible':
            return 'Invisible';
        case 'offline':
        default:
            return 'Offline';
    }
};
</script>

<template>
    <div class="flex flex-row overflow-x-auto whitespace-nowrap h-20 items-center px-4 w-full gap-2">
        <!-- Bar Indicator: Total & Online Member Count -->
        <div class="indicator shrink-0 mr-2">
            <span
                class="indicator-item indicator-top indicator-end badge badge-primary font-bold text-[10px] shadow-sm">
                {{ onlineMembersCount }} / {{ totalMembersCount }}
            </span>
            <div
                class="flex items-center gap-1.5 px-3 py-2 bg-base-200 hover:bg-base-300 rounded-xl text-xs font-semibold border border-base-300 transition-colors shadow-xs">
                <HiUsers class="w-4 h-4 text-primary"/>
                <span>Members</span>
            </div>
        </div>

        <div class="divider divider-horizontal px-0 mx-0"/>

        <!-- User Buttons List -->
        <div
            v-for="(user, index) in displayUsers"
            :key="user.id"
            class="flex flex-row items-center shrink-0"
        >
            <button
                class="flex flex-col items-center px-3 py-1 rounded-xl hover:bg-base-200 active:bg-base-300 transition-all cursor-pointer group focus:outline-none focus:ring-2 focus:ring-primary/40"
                type="button"
                @click="openProfile(user)"
            >
                <div :data-tip="formatStatusText(user.status)" class="tooltip tooltip-bottom">
                    <div class="indicator">
                        <!-- Green loading ring for online status, normal indicator badge for others -->
                        <span
                            v-if="user.status === 'online'"
                            class="indicator-item indicator-bottom indicator-end p-[1px] bg-base-100 rounded-full"
                        >
                            <span class="loading loading-ring loading-xs text-success block"/>
                        </span>
                        <span
                            v-else
                            :class="getStatusBadgeClass(user.status)"
                            class="indicator-item indicator-bottom indicator-end badge badge-xs border-2 border-base-100"
                        />
                        <div class="avatar">
                            <div class="w-10 rounded-full group-hover:scale-105 transition-transform duration-150">
                                <img
                                    :alt="user.nickname"
                                    :src="resolveUrl(user.icon) || defaultIcon"
                                    @error="(e) => (e.target as HTMLImageElement).src = defaultIcon"
                                />
                            </div>
                        </div>
                    </div>
                </div>
                <div
                    :style="{ color: getMemberRoleColor(user, activeRoles) }"
                    class="text-xs mt-1 font-medium truncate max-w-[5.5rem]"
                >
                    {{ user.nickname }}
                </div>
            </button>

            <div
                v-if="index < (displayUsers.length - 1)"
                class="divider divider-horizontal px-0 mx-1"
            />
        </div>
    </div>

    <!-- User Profile Modal Element -->
    <UserProfileModal
        :selected-server="selectedServer"
        :user="selectedProfileUser"
        @close="closeProfile"
    />
</template>
