<script lang="ts" setup>
import { computed, onUnmounted, ref, watch } from 'vue';
import {
    FaFilePdf,
    FaFileArchive,
    FaFileAudio,
    FaFileVideo,
    FaFileCode,
    FaFileWord,
    FaFileExcel,
    FaFilePowerpoint,
    FaRegFile,
    FaWindows,
} from 'vue-icons-plus/fa';
import { SiAndroid, SiApple, SiLinux } from 'vue-icons-plus/si';
import { MdOutlineModeEdit, MdClose } from 'vue-icons-plus/md';

const props = withDefaults(
    defineProps<{
        file: File | null;
        canEdit?: boolean;
    }>(),
    {
        canEdit: true,
    }
);

const emit = defineEmits<{
    (e: 'edit'): void;
    (e: 'remove'): void;
}>();

const previewUrl = ref<string | null>(null);

const isImage = computed(() => {
    if (!props.file) return false;
    if (props.file.type.startsWith('image/')) return true;
    const ext = props.file.name.split('.').pop()?.toLowerCase();
    return ['png', 'jpg', 'jpeg', 'webp', 'gif', 'svg', 'bmp'].includes(ext || '');
});

const fileExtension = computed(() => {
    if (!props.file) return '';
    return props.file.name.split('.').pop()?.toLowerCase() || '';
});

const formatSize = (bytes: number): string => {
    if (bytes === 0) return '0 B';
    const k = 1024;
    const sizes = ['B', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return parseFloat((bytes / Math.pow(k, i)).toFixed(1)) + ' ' + sizes[i];
};

const updatePreview = () => {
    if (previewUrl.value) {
        URL.revokeObjectURL(previewUrl.value);
        previewUrl.value = null;
    }
    if (props.file && isImage.value) {
        previewUrl.value = URL.createObjectURL(props.file);
    }
};

watch(
    () => props.file,
    () => {
        updatePreview();
    },
    { immediate: true }
);

onUnmounted(() => {
    if (previewUrl.value) {
        URL.revokeObjectURL(previewUrl.value);
    }
});
</script>

<template>
    <div
        v-if="file"
        class="flex items-center gap-3 p-2 bg-base-200/90 hover:bg-base-200 border border-base-300 rounded-xl max-w-md shadow-sm transition-all animate-fadeIn relative group shrink-0"
    >
        <!-- Image Thumbnail Mode -->
        <div v-if="isImage" class="relative size-14 rounded-lg overflow-hidden bg-base-300 shrink-0 group/thumb">
            <img
                v-if="previewUrl"
                :src="previewUrl"
                :alt="file.name"
                class="w-full h-full object-cover"
            />
            <div
                v-if="canEdit"
                class="absolute inset-0 bg-black/40 opacity-0 group-hover/thumb:opacity-100 flex items-center justify-center transition-opacity cursor-pointer"
                title="Edit / Annotate Image"
                @click.prevent="emit('edit')"
            >
                <MdOutlineModeEdit class="text-white size-5" />
            </div>
        </div>

        <!-- Non-Image File Icon Mode -->
        <div
            v-else
            class="size-14 rounded-lg bg-base-300/80 flex items-center justify-center shrink-0 border border-base-content/10"
        >
            <FaWindows v-if="['exe', 'msi', 'bat', 'cmd', 'com'].includes(fileExtension)" class="size-6 text-primary" />
            <SiAndroid v-else-if="['apk'].includes(fileExtension)" class="size-6 text-success" />
            <SiApple v-else-if="['dmg', 'pkg', 'app'].includes(fileExtension)" class="size-6 text-base-content/80" />
            <SiLinux v-else-if="['deb', 'rpm', 'appimage'].includes(fileExtension)" class="size-6 text-warning" />
            <FaFilePdf v-else-if="['pdf'].includes(fileExtension)" class="size-6 text-error" />
            <FaFileArchive v-else-if="['zip', 'rar', '7z', 'tar', 'gz', 'bz2', 'xz', 'iso'].includes(fileExtension)" class="size-6 text-warning" />
            <FaFileAudio v-else-if="['mp3', 'wav', 'ogg', 'flac', 'm4a', 'aac', 'opus'].includes(fileExtension)" class="size-6 text-accent" />
            <FaFileVideo v-else-if="['mp4', 'webm', 'mkv', 'mov', 'avi', 'wmv'].includes(fileExtension)" class="size-6 text-secondary" />
            <FaFileCode v-else-if="['ts', 'js', 'vue', 'json', 'php', 'html', 'css', 'py', 'rs', 'go', 'sh', 'sql', 'yaml', 'yml', 'toml', 'xml'].includes(fileExtension)" class="size-6 text-success" />
            <FaFileWord v-else-if="['doc', 'docx', 'odt', 'rtf'].includes(fileExtension)" class="size-6 text-info" />
            <FaFileExcel v-else-if="['xls', 'xlsx', 'csv', 'ods'].includes(fileExtension)" class="size-6 text-success" />
            <FaFilePowerpoint v-else-if="['ppt', 'pptx', 'odp'].includes(fileExtension)" class="size-6 text-warning" />
            <FaRegFile v-else class="size-6 text-base-content/60" />
        </div>

        <!-- File Info -->
        <div class="flex-1 min-w-0 pr-2">
            <div class="flex items-center gap-1.5">
                <span class="font-medium text-xs text-base-content truncate block" :title="file.name">
                    {{ file.name }}
                </span>
            </div>
            <div class="flex items-center gap-2 mt-0.5">
                <span class="text-[11px] text-base-content/60 font-mono">
                    {{ formatSize(file.size) }}
                </span>
                <span v-if="isImage" class="badge badge-xs badge-ghost text-[10px]">Image</span>
                <span v-else class="badge badge-xs badge-ghost text-[10px] uppercase">{{ fileExtension || 'FILE' }}</span>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="flex items-center gap-1">
            <button
                v-if="isImage && canEdit"
                class="btn btn-xs btn-circle btn-ghost text-primary hover:bg-primary/10"
                title="Edit / Crop / Draw Image"
                @click.prevent="emit('edit')"
            >
                <MdOutlineModeEdit class="size-4" />
            </button>
            <button
                class="btn btn-xs btn-circle btn-ghost text-base-content/70 hover:text-error hover:bg-error/10"
                title="Remove attachment"
                @click.prevent="emit('remove')"
            >
                <MdClose class="size-4" />
            </button>
        </div>
    </div>
</template>

<style scoped>
@keyframes fadeIn {
    from {
        opacity: 0;
        transform: translateY(4px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}
.animate-fadeIn {
    animation: fadeIn 0.15s cubic-bezier(0.16, 1, 0.3, 1) forwards;
}
</style>
