<script lang="ts" setup>
import { computed, ref, watch } from 'vue';
import { router, usePage } from '@inertiajs/vue3';
import { defaultIcon, getMemberRoleColor, resolveUrl, usePerms } from '@/bootstrap';
import { PermType, Role, Server, User } from '@/types';
import {
    HiCalendar,
    HiCheck,
    HiInformationCircle,
    HiPencil,
    HiPlus,
    HiServer,
    HiShieldCheck,
    HiUserRemove
} from 'vue-icons-plus/hi';
import { addUser, removeUser as roles_removeUser } from '@/routes/roles';
import { removeUser as server_removeUser } from '@/routes/server';
import { update as updateProfile } from '@/routes/profile';

const props = defineProps<{
    user: User | null;
    selectedServer?: Server;
}>();

const emit = defineEmits<{
    (e: 'close'): void;
}>();

const page = usePage();
const perms = usePerms();

const localUser = ref<User | null>(null);
const showKickConfirm = ref(false);

const isEditingAboutMe = ref(false);
const aboutMeInput = ref('');
const isSavingAboutMe = ref(false);

watch(
    () => props.user,
    (newUser) => {
        if (newUser) {
            localUser.value = {
                ...newUser,
                rolesWithServer: newUser.rolesWithServer ? [...newUser.rolesWithServer] : (newUser.roles ? [...newUser.roles] : [])
            };
            isEditingAboutMe.value = false;
            aboutMeInput.value = newUser.about_me || '';
        } else {
            localUser.value = null;
            showKickConfirm.value = false;
            isEditingAboutMe.value = false;
        }
    },
    { immediate: true, deep: true }
);

const closeProfile = () => {
    emit('close');
};

const currentUser = computed(() => page.props.user as User | null);

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

const getUserServerRoles = (user?: User | null): Role[] => {
    if (!user || !props.selectedServer?.roles) return [];
    const userRoles = user.rolesWithServer || user.roles || [];
    return props.selectedServer.roles
        .filter((sRole) => userRoles.some((uRole) => String(uRole.id) === String(sRole.id)))
        .sort((a, b) => a.importance - b.importance);
};

const formattedCreatedAt = computed(() => {
    if (!localUser.value?.created_at) return null;
    try {
        const date = new Date(localUser.value.created_at);
        return date.toLocaleDateString('en-US', {
            year: 'numeric',
            month: 'short',
            day: 'numeric'
        });
    } catch {
        return localUser.value.created_at;
    }
});

const canManageRoles = computed(() =>
    perms.value.hasAny([PermType.CAN_EDIT_MEMBER_ROLES, PermType.CAN_MANAGE_ROLE, PermType.CAN_MANAGE_MEMBERS])
);

const isRoleAssigned = (roleId: string | number, user?: User | null) => {
    if (!user) return false;
    const userRoles = user.rolesWithServer || user.roles || [];
    return userRoles.some((r) => String(r.id) === String(roleId));
};

const getContrastColor = (hexColor?: string): string => {
    if (!hexColor) return '#ffffff';
    let hex = hexColor.replace('#', '');
    if (hex.length === 3) {
        hex = hex.split('').map((c) => c + c).join('');
    }
    if (hex.length !== 6) return '#ffffff';
    const r = parseInt(hex.substring(0, 2), 16);
    const g = parseInt(hex.substring(2, 4), 16);
    const b = parseInt(hex.substring(4, 6), 16);
    const yiq = (r * 299 + g * 587 + b * 114) / 1000;
    return yiq >= 128 ? '#000000' : '#ffffff';
};

const toggleRole = (roleId: string | number, userId: string | number, state: boolean) => {
    if (!localUser.value) return;

    const targetRole = props.selectedServer?.roles?.find((r) => String(r.id) === String(roleId));
    if (!targetRole) return;

    const currentRoles = localUser.value.rolesWithServer
        ? [...localUser.value.rolesWithServer]
        : (localUser.value.roles ? [...localUser.value.roles] : []);

    const previousRoles = [...currentRoles];

    let updatedRoles: Role[];
    if (state) {
        if (!currentRoles.some((r) => String(r.id) === String(roleId))) {
            updatedRoles = [...currentRoles, targetRole];
        } else {
            updatedRoles = currentRoles;
        }
    } else {
        updatedRoles = currentRoles.filter((r) => String(r.id) !== String(roleId));
    }

    localUser.value = {
        ...localUser.value,
        rolesWithServer: updatedRoles
    };

    if (props.selectedServer?.users) {
        const userInServer = props.selectedServer.users.find((u) => String(u.id) === String(userId));
        if (userInServer) {
            userInServer.rolesWithServer = [...updatedRoles];
        }
    }

    if (state) {
        router.post(
            addUser.url({ role: roleId, user: userId }),
            {},
            {
                preserveScroll: true,
                preserveState: true,
                onError: () => {
                    if (localUser.value) {
                        localUser.value = {
                            ...localUser.value,
                            rolesWithServer: previousRoles
                        };
                    }
                }
            }
        );
    } else {
        router.delete(
            roles_removeUser.url({ role: roleId, user: userId }),
            {
                preserveScroll: true,
                preserveState: true,
                onError: () => {
                    if (localUser.value) {
                        localUser.value = {
                            ...localUser.value,
                            rolesWithServer: previousRoles
                        };
                    }
                }
            }
        );
    }
};

const pageServers = computed(() => (page.props.servers as Server[]) || currentUser.value?.servers || []);

const commonServers = computed(() => {
    if (!localUser.value) return [];
    const targetServers = localUser.value.servers || [];

    if (targetServers.length > 0) {
        return pageServers.value.filter((s) => targetServers.some((ts) => ts.id === s.id));
    }

    if (props.selectedServer) {
        return [props.selectedServer];
    }
    return [];
});

const canKickUser = computed(() => {
    if (!localUser.value || !currentUser.value) return false;
    if (currentUser.value.id === localUser.value.id) return false;
    return perms.value.hasAny([PermType.CAN_KICK, PermType.CAN_MANAGE_MEMBERS, PermType.CAN_MANAGE_SERVER]);
});

const kickMember = () => {
    if (!props.selectedServer || !localUser.value) return;
    const userId = localUser.value.id;
    router.delete(server_removeUser.url(props.selectedServer.route_key), {
        data: { user_id: userId },
        onSuccess: () => {
            closeProfile();
            router.reload({ only: ['selected_server'] });
        }
    });
};

const canEditAboutMe = computed(() => {
    return currentUser.value?.id === localUser.value?.id;
});

const startEditAboutMe = () => {
    aboutMeInput.value = localUser.value?.about_me || '';
    isEditingAboutMe.value = true;
};

const saveAboutMe = () => {
    if (!localUser.value) return;
    isSavingAboutMe.value = true;
    router.post(
        updateProfile.url(),
        {
            nickname: localUser.value.nickname,
            about_me: aboutMeInput.value
        },
        {
            preserveScroll: true,
            preserveState: true,
            onSuccess: () => {
                if (localUser.value) {
                    localUser.value.about_me = aboutMeInput.value;
                }
                isEditingAboutMe.value = false;
                isSavingAboutMe.value = false;
            },
            onError: () => {
                isSavingAboutMe.value = false;
            }
        }
    );
};
</script>

<template>
    <!-- User Profile Modal Element -->
    <Teleport to="body">
        <Transition
            enter-active-class="transition duration-200 ease-out"
            enter-from-class="opacity-0 scale-95"
            enter-to-class="opacity-100 scale-100"
            leave-active-class="transition duration-150 ease-in"
            leave-from-class="opacity-100 scale-100"
            leave-to-class="opacity-0 scale-95"
        >
            <div
                v-if="localUser"
                class="modal modal-open z-50 flex items-center justify-center p-4"
            >
                <div
                    class="modal-box relative z-10 max-w-sm w-full bg-base-100 rounded-2xl shadow-2xl p-0 border border-base-300 max-h-[85vh] flex flex-col overflow-hidden"
                >
                    <!-- Non-scrolling Card Header Banner & Avatar -->
                    <div class="relative shrink-0 flex flex-col items-center text-center pb-2">
                        <!-- Decorative Banner -->
                        <div class="h-24 bg-gradient-to-r from-primary/30 to-secondary/30 w-full relative">
                            <button
                                class="btn btn-sm btn-circle btn-ghost absolute right-2 top-2 bg-base-100/60 hover:bg-base-100 z-20 cursor-pointer"
                                type="button"
                                @click="closeProfile"
                            >
                                ✕
                            </button>
                        </div>

                        <!-- Avatar overlapping banner cleanly -->
                        <div class="relative -mt-12 z-10">
                            <div :data-tip="formatStatusText(localUser.status)" class="tooltip tooltip-bottom">
                                <div class="indicator">
                                    <span
                                        v-if="localUser.status === 'online'"
                                        class="indicator-item indicator-bottom indicator-end p-0.5 bg-base-100 rounded-full"
                                    >
                                        <span class="loading loading-ring loading-sm text-success block" />
                                    </span>
                                    <span
                                        v-else
                                        :class="getStatusBadgeClass(localUser.status)"
                                        class="indicator-item indicator-bottom indicator-end badge badge-sm border-2 border-base-100"
                                    />
                                    <div class="avatar">
                                        <div class="w-20 rounded-full ring-4 ring-base-100 shadow-md bg-base-200">
                                            <img
                                                :alt="localUser.nickname"
                                                :src="resolveUrl(localUser.icon) || defaultIcon"
                                                @error="(e) => (e.target as HTMLImageElement).src = defaultIcon"
                                            />
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Nickname -->
                        <h3
                            :style="{ color: getMemberRoleColor(localUser, selectedServer?.roles) }"
                            class="text-xl font-bold mt-2 px-6"
                        >
                            {{ localUser.nickname }}
                        </h3>
                    </div>

                    <!-- Scrollable Body Content -->
                    <div class="px-6 pb-6 pt-2 flex flex-col items-center text-center overflow-y-auto gap-4">
                        <!-- About Me Section -->
                        <div class="w-full text-left bg-base-200/60 p-3 rounded-xl border border-base-300/50">
                            <div class="flex items-center justify-between mb-1">
                                <div class="flex items-center gap-1.5 text-xs font-bold uppercase tracking-wider opacity-60">
                                    <HiInformationCircle class="w-4 h-4 text-primary" />
                                    About Me
                                </div>
                                <button
                                    v-if="canEditAboutMe && !isEditingAboutMe"
                                    class="btn btn-ghost btn-xs text-primary gap-1 cursor-pointer"
                                    type="button"
                                    @click="startEditAboutMe"
                                >
                                    <HiPencil class="w-3.5 h-3.5" />
                                    <span>Edit</span>
                                </button>
                            </div>

                            <!-- Editable form -->
                            <div v-if="isEditingAboutMe" class="space-y-2 mt-2">
                                <textarea
                                    v-model="aboutMeInput"
                                    class="textarea textarea-bordered w-full text-xs resize-none focus:outline-none focus:ring-1 focus:ring-primary"
                                    placeholder="Tell others a little about yourself..."
                                    rows="3"
                                />
                                <div class="flex justify-end gap-2">
                                    <button
                                        :disabled="isSavingAboutMe"
                                        class="btn btn-xs btn-ghost cursor-pointer"
                                        type="button"
                                        @click="isEditingAboutMe = false"
                                    >
                                        Cancel
                                    </button>
                                    <button
                                        :disabled="isSavingAboutMe"
                                        class="btn btn-xs btn-primary cursor-pointer"
                                        type="button"
                                        @click="saveAboutMe"
                                    >
                                        {{ isSavingAboutMe ? 'Saving...' : 'Save' }}
                                    </button>
                                </div>
                            </div>

                            <!-- Display view -->
                            <p v-else class="text-xs text-base-content/80 leading-relaxed whitespace-pre-line">
                                {{ localUser.about_me || "No bio provided." }}
                            </p>
                        </div>

                        <!-- Member Since / Created At Section -->
                        <div v-if="formattedCreatedAt" class="w-full text-left bg-base-200/60 p-3 rounded-xl border border-base-300/50">
                            <div class="flex items-center gap-1.5 text-xs font-bold uppercase tracking-wider opacity-60 mb-1">
                                <HiCalendar class="w-4 h-4 text-primary" />
                                Joined / Created
                            </div>
                            <p class="text-xs text-base-content/80 font-medium">
                                {{ formattedCreatedAt }}
                            </p>
                        </div>

                        <!-- Roles Section -->
                        <div class="w-full text-left bg-base-200/60 p-3 rounded-xl border border-base-300/50">
                            <div class="flex items-center justify-between mb-2">
                                <div class="flex items-center gap-1.5 text-xs font-bold uppercase tracking-wider opacity-60">
                                    <HiShieldCheck class="w-4 h-4 text-primary" />
                                    Roles
                                </div>
                                <span v-if="canManageRoles" class="text-[10px] text-primary font-medium">
                                    (Click to toggle)
                                </span>
                            </div>

                            <!-- Manageable roles list -->
                            <div v-if="canManageRoles && (selectedServer?.roles?.length || 0) > 0" class="flex flex-wrap gap-1.5">
                                <button
                                    v-for="role in selectedServer?.roles"
                                    :key="role.id"
                                    :class="isRoleAssigned(role.id, localUser) ? 'shadow-xs font-semibold' : 'opacity-50 hover:opacity-100'"
                                    :style="isRoleAssigned(role.id, localUser) ? {
                                        backgroundColor: role.color || 'var(--p)',
                                        color: getContrastColor(role.color),
                                        borderColor: role.color || 'var(--p)'
                                    } : {
                                        backgroundColor: 'transparent',
                                        color: role.color || 'currentColor',
                                        borderColor: role.color || 'currentColor'
                                    }"
                                    class="badge badge-sm font-medium px-2.5 py-1 border gap-1 transition-all cursor-pointer hover:scale-105 active:scale-95"
                                    type="button"
                                    @click="toggleRole(role.id, localUser.id, !isRoleAssigned(role.id, localUser))"
                                >
                                    <HiCheck v-if="isRoleAssigned(role.id, localUser)" class="w-3 h-3" />
                                    <HiPlus v-else class="w-3 h-3" />
                                    <span>{{ role.name }}</span>
                                </button>
                            </div>

                            <!-- Read-only roles list -->
                            <div v-else-if="getUserServerRoles(localUser).length > 0" class="flex flex-wrap gap-1.5">
                                <span
                                    v-for="role in getUserServerRoles(localUser)"
                                    :key="role.id"
                                    :style="{
                                        backgroundColor: role.color || 'var(--p)',
                                        color: getContrastColor(role.color),
                                        borderColor: role.color || 'var(--p)'
                                    }"
                                    class="badge badge-sm font-semibold px-2.5 py-1 border"
                                >
                                    {{ role.name }}
                                </span>
                            </div>
                            <p v-else class="text-xs opacity-50 italic">No roles assigned</p>
                        </div>

                        <!-- Common Servers Section -->
                        <div class="w-full text-left bg-base-200/60 p-3 rounded-xl border border-base-300/50">
                            <div class="flex items-center gap-1.5 text-xs font-bold uppercase tracking-wider opacity-60 mb-2">
                                <HiServer class="w-4 h-4 text-primary" />
                                Common Servers ({{ commonServers.length }})
                            </div>
                            <div class="flex flex-wrap gap-1.5">
                                <div
                                    v-for="srv in commonServers"
                                    :key="srv.id"
                                    class="flex items-center gap-1.5 px-2.5 py-1 bg-base-100 rounded-lg text-xs font-medium border border-base-300 shadow-xs"
                                >
                                    <div class="avatar">
                                        <div class="w-4 rounded-full">
                                            <img :alt="srv.name" :src="resolveUrl(srv.icon) || defaultIcon" @error="(e) => (e.target as HTMLImageElement).src = defaultIcon" />
                                        </div>
                                    </div>
                                    <span>{{ srv.name }}</span>
                                </div>
                            </div>
                        </div>

                        <!-- Remove / Kick from Server Button -->
                        <button
                            v-if="canKickUser"
                            class="btn btn-error btn-outline btn-sm w-full gap-2 mt-2 font-semibold hover:bg-error hover:text-error-content transition-colors cursor-pointer"
                            type="button"
                            @click="showKickConfirm = true"
                        >
                            <HiUserRemove class="w-4 h-4" />
                            <span>Remove from Server</span>
                        </button>
                    </div>
                </div>

                <!-- Backdrop -->
                <div class="modal-backdrop bg-neutral/40 fixed inset-0" @click="closeProfile" />
            </div>
        </Transition>

        <!-- Remove Member Confirmation Modal -->
        <Transition
            enter-active-class="transition duration-200 ease-out"
            enter-from-class="opacity-0 scale-95"
            enter-to-class="opacity-100 scale-100"
            leave-active-class="transition duration-150 ease-in"
            leave-from-class="opacity-100 scale-100"
            leave-to-class="opacity-0 scale-95"
        >
            <div
                v-if="showKickConfirm"
                class="modal modal-open z-[60] flex items-center justify-center p-4"
            >
                <div class="modal-box relative z-10 max-w-sm w-full bg-base-100 rounded-2xl shadow-2xl p-6 border border-error/30 text-center">
                    <div class="w-12 h-12 rounded-full bg-error/10 text-error flex items-center justify-center mx-auto mb-3">
                        <HiUserRemove class="w-6 h-6" />
                    </div>
                    <h4 class="text-lg font-bold text-base-content mb-1">Remove Member?</h4>
                    <p class="text-xs text-base-content/70 mb-5">
                        Are you sure you want to remove <span class="font-bold text-base-content">{{ localUser?.nickname }}</span> from the server?
                    </p>
                    <div class="flex gap-2">
                        <button
                            class="btn btn-sm btn-ghost flex-1 cursor-pointer"
                            type="button"
                            @click="showKickConfirm = false"
                        >
                            Cancel
                        </button>
                        <button
                            class="btn btn-sm btn-error flex-1 font-semibold cursor-pointer"
                            type="button"
                            @click="kickMember"
                        >
                            Remove
                        </button>
                    </div>
                </div>
                <div class="modal-backdrop bg-neutral/50 fixed inset-0" @click="showKickConfirm = false" />
            </div>
        </Transition>
    </Teleport>
</template>
