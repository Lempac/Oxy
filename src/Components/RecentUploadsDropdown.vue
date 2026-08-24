<script lang="ts" setup>
import { ref, onMounted, onUnmounted } from 'vue';
import { useRecentUploads, RecentUpload } from '@/composables/useRecentUploads';
import {
    MdOutlineFileUpload,
    MdHistory,
    MdClose,
} from 'vue-icons-plus/md';
import {
    FaFilePdf,
    FaFileArchive,
    FaFileAudio,
    FaFileVideo,
    FaFileCode,
    FaFileWord,
    FaFileExcel,
    FaRegFile,
    FaWindows,
} from 'vue-icons-plus/fa';
import { SiAndroid, SiApple, SiLinux } from 'vue-icons-plus/si';

const props = withDefaults(
    defineProps<{
        disabled?: boolean;
    }>(),
    {
        disabled: false,
    }
);

const emit = defineEmits<{
    (e: 'select-file', file: File): void;
    (e: 'open-file-picker'): void;
}>();

const isOpen = ref(false);
const dropdownRef = ref<HTMLDivElement | null>(null);

const { recentUploads, removeRecentUpload, clearAllRecentUploads, recentToFile } = useRecentUploads();

const toggleDropdown = () => {
    if (props.disabled) return;
    isOpen.value = !isOpen.value;
};

const closeDropdown = () => {
    isOpen.value = false;
};

const handlePickRecent = (item: RecentUpload) => {
    const file = recentToFile(item);
    emit('select-file', file);
    closeDropdown();
};

const handleTriggerUpload = () => {
    emit('open-file-picker');
    closeDropdown();
};

const formatSize = (bytes: number): string => {
    if (bytes === 0) return '0 B';
    const k = 1024;
    const sizes = ['B', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return parseFloat((bytes / Math.pow(k, i)).toFixed(1)) + ' ' + sizes[i];
};

const formatTimeAgo = (timestamp: number): string => {
    const elapsed = Date.now() - timestamp;
    const mins = Math.floor(elapsed / 60000);
    if (mins < 1) return 'Just now';
    if (mins < 60) return `${mins}m ago`;
    const hours = Math.floor(mins / 60);
    if (hours < 24) return `${hours}h ago`;
    const days = Math.floor(hours / 24);
    return `${days}d ago`;
};

const getFileExtension = (name: string) => {
    return name.split('.').pop()?.toLowerCase() || '';
};

const handleClickOutside = (e: MouseEvent) => {
    if (dropdownRef.value && !dropdownRef.value.contains(e.target as Node)) {
        closeDropdown();
    }
};

onMounted(() => {
    window.addEventListener('click', handleClickOutside);
});

onUnmounted(() => {
    window.removeEventListener('click', handleClickOutside);
});
</script>

<template>
    <div ref="dropdownRef" class="relative inline-block">
        <!-- Main Trigger Button -->
        <button
            type="button"
            :disabled="disabled"
            :class="{'btn-disabled opacity-50': disabled, 'bg-base-300': isOpen}"
            class="btn btn-sm btn-square btn-ghost shrink-0"
            title="Upload file or pick recent"
            @click.stop="toggleDropdown"
        >
            <MdOutlineFileUpload class="size-4" />
        </button>

        <!-- Dropdown Menu -->
        <div
            v-if="isOpen"
            class="absolute bottom-full left-0 mb-2 w-72 bg-base-100 border border-base-300 rounded-2xl shadow-2xl z-40 overflow-hidden flex flex-col animate-scaleIn"
            @click.stop
        >
            <!-- Header / Upload Option -->
            <div class="p-2 border-b border-base-300 bg-base-200/50 flex flex-col gap-1">
                <button
                    type="button"
                    class="btn btn-sm btn-primary w-full gap-2 justify-start font-medium"
                    @click="handleTriggerUpload"
                >
                    <MdOutlineFileUpload class="size-4" />
                    <span>Upload from Device</span>
                </button>
            </div>

            <!-- Recent Files Section Header -->
            <div class="px-3 py-1.5 bg-base-200/30 flex items-center justify-between border-b border-base-300 text-[11px] font-semibold text-base-content/70">
                <div class="flex items-center gap-1.5">
                    <MdHistory class="size-3.5" />
                    <span>Recent Uploads</span>
                    <span v-if="recentUploads.length > 0" class="badge badge-xs badge-neutral">{{ recentUploads.length }}</span>
                </div>
                <button
                    v-if="recentUploads.length > 0"
                    type="button"
                    class="text-[10px] text-error hover:underline cursor-pointer"
                    title="Clear history"
                    @click="clearAllRecentUploads"
                >
                    Clear
                </button>
            </div>

            <!-- Recent Files List -->
            <div class="max-h-60 overflow-y-auto divide-y divide-base-300/60 p-1">
                <div
                    v-for="item in recentUploads"
                    :key="item.id"
                    class="group flex items-center gap-2.5 p-2 hover:bg-base-200/80 rounded-xl cursor-pointer transition-colors"
                    @click="handlePickRecent(item)"
                >
                    <!-- Thumbnail or Icon -->
                    <div class="size-9 rounded-lg bg-base-300 flex items-center justify-center shrink-0 overflow-hidden border border-base-300">
                        <img
                            v-if="item.dataUrl"
                            :src="item.dataUrl"
                            :alt="item.name"
                            class="w-full h-full object-cover"
                        />
                        <template v-else>
                            <FaWindows v-if="['exe', 'msi', 'bat', 'cmd', 'com'].includes(getFileExtension(item.name))" class="size-4 text-primary" />
                            <SiAndroid v-else-if="['apk'].includes(getFileExtension(item.name))" class="size-4 text-success" />
                            <SiApple v-else-if="['dmg', 'pkg', 'app'].includes(getFileExtension(item.name))" class="size-4 text-base-content/80" />
                            <SiLinux v-else-if="['deb', 'rpm', 'appimage'].includes(getFileExtension(item.name))" class="size-4 text-warning" />
                            <FaFilePdf v-else-if="['pdf'].includes(getFileExtension(item.name))" class="size-4 text-error" />
                            <FaFileArchive v-else-if="['zip', 'rar', '7z', 'tar', 'gz', 'bz2', 'xz', 'iso'].includes(getFileExtension(item.name))" class="size-4 text-warning" />
                            <FaFileAudio v-else-if="['mp3', 'wav', 'ogg', 'flac'].includes(getFileExtension(item.name))" class="size-4 text-accent" />
                            <FaFileVideo v-else-if="['mp4', 'webm', 'mkv', 'mov'].includes(getFileExtension(item.name))" class="size-4 text-secondary" />
                            <FaFileCode v-else-if="['ts', 'js', 'vue', 'json', 'php', 'html', 'css', 'py'].includes(getFileExtension(item.name))" class="size-4 text-success" />
                            <FaFileWord v-else-if="['doc', 'docx', 'txt', 'md'].includes(getFileExtension(item.name))" class="size-4 text-info" />
                            <FaFileExcel v-else-if="['xls', 'xlsx', 'csv'].includes(getFileExtension(item.name))" class="size-4 text-success" />
                            <FaRegFile v-else class="size-4 text-base-content/60" />
                        </template>
                    </div>

                    <!-- File Info -->
                    <div class="flex-1 min-w-0">
                        <p class="text-xs font-medium text-base-content truncate group-hover:text-primary transition-colors" :title="item.name">
                            {{ item.name }}
                        </p>
                        <div class="flex items-center gap-1.5 text-[10px] text-base-content/50 mt-0.5">
                            <span>{{ formatSize(item.size) }}</span>
                            <span>•</span>
                            <span>{{ formatTimeAgo(item.timestamp) }}</span>
                        </div>
                    </div>

                    <!-- Remove from recent -->
                    <button
                        type="button"
                        class="btn btn-xs btn-ghost btn-circle opacity-0 group-hover:opacity-100 hover:text-error transition-opacity shrink-0"
                        title="Remove from recents"
                        @click.stop="removeRecentUpload(item.id)"
                    >
                        <MdClose class="size-3" />
                    </button>
                </div>

                <!-- Empty State -->
                <div v-if="recentUploads.length === 0" class="py-6 px-4 text-center text-xs text-base-content/50">
                    <p>No recent files uploaded yet.</p>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
@keyframes scaleIn {
    from {
        opacity: 0;
        transform: translateY(6px) scale(0.97);
    }
    to {
        opacity: 1;
        transform: translateY(0) scale(1);
    }
}
.animate-scaleIn {
    animation: scaleIn 0.12s cubic-bezier(0.16, 1, 0.3, 1) forwards;
}
</style>
