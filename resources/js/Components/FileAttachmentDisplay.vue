<script lang="ts" setup>
import { computed, onUnmounted, ref, watch } from 'vue';
import { MessageAttachment } from '@/types';
import { baseUrl } from '@/bootstrap';
import {
    formatFileSize,
    getFileExtension,
    isAudioFile,
    isImageFile,
    isVideoFile,
} from '@/utils/fileValidation';
import {
    FaFilePdf,
    FaFileArchive,
    FaFileAudio,
    FaFileVideo,
    FaFileCode,
    FaFileWord,
    FaFileExcel,
    FaFilePowerpoint,
    FaFileAlt,
    FaRegFile,
    FaWindows,
} from 'vue-icons-plus/fa';
import { SiAndroid, SiApple, SiLinux } from 'vue-icons-plus/si';
import { MdClose, MdFullscreen, MdOutlineFileDownload, MdOutlineOpenInNew } from 'vue-icons-plus/md';

const props = defineProps<{
    attachment: MessageAttachment;
}>();

const isFullscreenOpen = ref(false);

const openFullscreen = () => {
    isFullscreenOpen.value = true;
};

const closeFullscreen = () => {
    isFullscreenOpen.value = false;
};

const onKeyDown = (e: KeyboardEvent) => {
    if (e.key === 'Escape' && isFullscreenOpen.value) {
        closeFullscreen();
    }
};

watch(isFullscreenOpen, (open) => {
    if (typeof window === 'undefined') return;
    if (open) {
        window.addEventListener('keydown', onKeyDown);
    } else {
        window.removeEventListener('keydown', onKeyDown);
    }
});

onUnmounted(() => {
    if (typeof window !== 'undefined') {
        window.removeEventListener('keydown', onKeyDown);
    }
});

const ext = computed(() => getFileExtension(props.attachment.filename));

const isImage = computed(() => {
    return isImageFile(props.attachment.mime_type || props.attachment.filename);
});

const isVideo = computed(() => {
    return isVideoFile(props.attachment.mime_type || props.attachment.filename);
});

const isAudio = computed(() => {
    return isAudioFile(props.attachment.mime_type || props.attachment.filename);
});

const isMedia = computed(() => isImage.value || isVideo.value || isAudio.value);

const resolvedUrl = computed(() => {
    const raw = props.attachment.url || props.attachment.path || '';
    if (!raw || raw === '0') return '';
    if (raw.startsWith('http://') || raw.startsWith('https://') || raw.startsWith('blob:')) return raw;
    return `${baseUrl}${raw.startsWith('/') ? '' : '/'}${raw}`;
});

const fileIconComponent = computed(() => {
    const e = ext.value;
    if (['exe', 'msi', 'bat', 'cmd', 'com'].includes(e)) return FaWindows;
    if (['apk'].includes(e)) return SiAndroid;
    if (['dmg', 'pkg', 'app'].includes(e)) return SiApple;
    if (['deb', 'rpm', 'appimage'].includes(e)) return SiLinux;
    if (['pdf'].includes(e)) return FaFilePdf;
    if (['zip', 'rar', '7z', 'tar', 'gz', 'bz2', 'xz', 'iso'].includes(e)) return FaFileArchive;
    if (['mp3', 'wav', 'ogg', 'flac', 'm4a', 'aac', 'opus'].includes(e)) return FaFileAudio;
    if (['mp4', 'webm', 'mkv', 'mov', 'avi', 'wmv'].includes(e)) return FaFileVideo;
    if (['doc', 'docx', 'odt', 'rtf'].includes(e)) return FaFileWord;
    if (['xls', 'xlsx', 'csv', 'ods'].includes(e)) return FaFileExcel;
    if (['ppt', 'pptx', 'odp'].includes(e)) return FaFilePowerpoint;
    if (['ts', 'js', 'vue', 'json', 'php', 'html', 'css', 'py', 'rs', 'go', 'c', 'cpp', 'cs', 'java', 'sql', 'yaml', 'yml', 'toml', 'xml', 'sh'].includes(e)) return FaFileCode;
    if (['txt', 'md', 'log', 'ini', 'cfg', 'conf'].includes(e)) return FaFileAlt;
    return FaRegFile;
});

const fileIconColorClass = computed(() => {
    const e = ext.value;
    if (['exe', 'msi', 'bat', 'cmd', 'com'].includes(e)) return 'text-primary';
    if (['apk'].includes(e)) return 'text-success';
    if (['dmg', 'pkg', 'app'].includes(e)) return 'text-neutral-content';
    if (['deb', 'rpm', 'appimage'].includes(e)) return 'text-warning';
    if (['pdf'].includes(e)) return 'text-error';
    if (['zip', 'rar', '7z', 'tar', 'gz', 'bz2', 'xz', 'iso'].includes(e)) return 'text-warning';
    if (['mp3', 'wav', 'ogg', 'flac', 'm4a', 'aac', 'opus'].includes(e)) return 'text-accent';
    if (['mp4', 'webm', 'mkv', 'mov', 'avi', 'wmv'].includes(e)) return 'text-secondary';
    if (['doc', 'docx', 'odt', 'rtf'].includes(e)) return 'text-info';
    if (['xls', 'xlsx', 'csv', 'ods'].includes(e)) return 'text-success';
    if (['ppt', 'pptx', 'odp'].includes(e)) return 'text-warning';
    if (['ts', 'js', 'vue', 'json', 'php', 'html', 'css', 'py', 'rs', 'go', 'c', 'cpp', 'cs', 'java', 'sql'].includes(e)) return 'text-success';
    return 'text-base-content/70';
});
</script>

<template>
    <!-- Image Attachment -->
    <div v-if="isImage" class="relative group/img my-1 max-w-sm rounded-xl overflow-hidden border border-base-content/10 shadow-sm bg-base-300/40">
        <img
            :src="resolvedUrl"
            :alt="attachment.filename"
            class="max-w-full max-h-72 object-contain rounded-xl block cursor-pointer hover:opacity-95 transition-opacity"
            loading="lazy"
            @click="openFullscreen"
        />
        <!-- Image Hover Overlay with Action Buttons at Bottom -->
        <div class="absolute inset-x-0 bottom-0 bg-gradient-to-t from-black/80 via-black/50 to-transparent opacity-0 group-hover/img:opacity-100 transition-opacity flex items-center justify-between p-2.5 pt-6 pointer-events-none">
            <span class="text-white text-xs font-medium truncate max-w-[150px]" :title="attachment.filename">
                {{ attachment.filename }}
            </span>
            <div class="flex items-center gap-1.5 shrink-0 pointer-events-auto">
                <button
                    type="button"
                    class="btn btn-xs btn-circle btn-ghost text-white hover:bg-white/20"
                    title="Fullscreen"
                    @click.stop="openFullscreen"
                >
                    <MdFullscreen class="size-4" />
                </button>
                <a
                    :href="resolvedUrl"
                    target="_blank"
                    rel="noopener noreferrer"
                    class="btn btn-xs btn-circle btn-ghost text-white hover:bg-white/20"
                    title="Open full size in new tab"
                >
                    <MdOutlineOpenInNew class="size-4" />
                </a>
                <a
                    :href="resolvedUrl"
                    :download="attachment.filename"
                    class="btn btn-xs btn-circle btn-primary shadow"
                    :title="`Download ${attachment.filename}`"
                >
                    <MdOutlineFileDownload class="size-4 text-primary-content" />
                </a>
            </div>
        </div>
    </div>

    <!-- Video Attachment -->
    <div
        v-else-if="isVideo"
        class="relative group/media my-1 max-w-sm rounded-xl overflow-hidden border border-base-content/10 shadow-sm bg-base-300/40"
    >
        <video
            :src="resolvedUrl"
            controls
            preload="metadata"
            class="max-w-full max-h-72 object-contain rounded-xl block bg-black/40"
        />
        <!-- Video Hover Overlay with Action Buttons at Bottom -->
        <div class="absolute inset-x-0 bottom-0 bg-gradient-to-t from-black/80 via-black/50 to-transparent opacity-0 group-hover/media:opacity-100 transition-opacity flex items-center justify-between p-2.5 pt-6 pointer-events-none">
            <span class="text-white text-xs font-medium truncate max-w-[150px]" :title="attachment.filename">
                {{ attachment.filename }}
            </span>
            <div class="flex items-center gap-1.5 shrink-0 pointer-events-auto">
                <button
                    type="button"
                    class="btn btn-xs btn-circle btn-ghost text-white hover:bg-white/20"
                    title="Fullscreen"
                    @click.stop="openFullscreen"
                >
                    <MdFullscreen class="size-4" />
                </button>
                <a
                    :href="resolvedUrl"
                    target="_blank"
                    rel="noopener noreferrer"
                    class="btn btn-xs btn-circle btn-ghost text-white hover:bg-white/20"
                    title="Open full size in new tab"
                >
                    <MdOutlineOpenInNew class="size-4" />
                </a>
                <a
                    :href="resolvedUrl"
                    :download="attachment.filename"
                    class="btn btn-xs btn-circle btn-primary shadow"
                    :title="`Download ${attachment.filename}`"
                >
                    <MdOutlineFileDownload class="size-4 text-primary-content" />
                </a>
            </div>
        </div>
    </div>

    <!-- Audio Attachment -->
    <div
        v-else-if="isAudio"
        class="my-1 max-w-sm rounded-xl overflow-hidden border border-base-content/10 shadow-sm bg-base-300/60 hover:bg-base-300/90 p-3 space-y-2 transition-colors group/audio"
    >
        <div class="flex items-center justify-between gap-2">
            <div class="flex items-center gap-2 min-w-0">
                <div class="size-7 rounded-lg bg-base-100/70 flex items-center justify-center shrink-0">
                    <FaFileAudio class="size-4 text-accent" />
                </div>
                <div class="min-w-0">
                    <span class="text-xs font-medium text-base-content truncate block max-w-[180px]" :title="attachment.filename">
                        {{ attachment.filename }}
                    </span>
                    <span class="text-[10px] text-base-content/60 font-mono block">
                        {{ formatFileSize(attachment.size) }}
                    </span>
                </div>
            </div>
            <div class="flex items-center gap-1 shrink-0">
                <button
                    type="button"
                    class="btn btn-xs btn-circle btn-ghost text-base-content/70 hover:btn-primary hover:text-primary-content transition-colors"
                    title="Open in player modal"
                    @click.stop="openFullscreen"
                >
                    <MdFullscreen class="size-4" />
                </button>
                <a
                    :href="resolvedUrl"
                    target="_blank"
                    rel="noopener noreferrer"
                    class="btn btn-xs btn-circle btn-ghost text-base-content/70 hover:btn-primary hover:text-primary-content transition-colors"
                    title="Open in new tab"
                >
                    <MdOutlineOpenInNew class="size-4" />
                </a>
                <a
                    :href="resolvedUrl"
                    :download="attachment.filename"
                    class="btn btn-xs btn-circle btn-ghost text-base-content/70 hover:btn-primary hover:text-primary-content transition-colors"
                    :title="`Download ${attachment.filename}`"
                >
                    <MdOutlineFileDownload class="size-4" />
                </a>
            </div>
        </div>
        <audio
            :src="resolvedUrl"
            controls
            preload="metadata"
            class="w-full h-8 block"
        />
    </div>

    <!-- Non-Media File Attachment (Icon on same line as name + download button) -->
    <div
        v-else
        class="flex items-center gap-2.5 px-3 py-2 my-1 bg-base-300/60 hover:bg-base-300/90 border border-base-content/10 rounded-xl max-w-sm transition-colors group/file shadow-xs"
    >
        <!-- Icon on the same line as filename -->
        <div class="size-7 rounded-lg bg-base-100/70 flex items-center justify-center shrink-0">
            <component :is="fileIconComponent" :class="`size-4 ${fileIconColorClass}`" />
        </div>

        <!-- File Name and Size -->
        <div class="flex-1 min-w-0">
            <span class="text-xs font-medium text-base-content truncate block" :title="attachment.filename">
                {{ attachment.filename }}
            </span>
            <span class="text-[10px] text-base-content/60 font-mono block">
                {{ formatFileSize(attachment.size) }}
            </span>
        </div>

        <!-- Download Button -->
        <a
            :href="resolvedUrl"
            :download="attachment.filename"
            class="btn btn-xs btn-circle btn-ghost hover:btn-primary text-base-content/70 hover:text-primary-content shrink-0 transition-colors"
            :title="`Download ${attachment.filename}`"
        >
            <MdOutlineFileDownload class="size-4" />
        </a>
    </div>

    <!-- Fullscreen Media Lightbox / Player Modal -->
    <Teleport to="body">
        <Transition
            enter-active-class="transition duration-150 ease-out"
            enter-from-class="opacity-0"
            enter-to-class="opacity-100"
            leave-active-class="transition duration-100 ease-in"
            leave-from-class="opacity-100"
            leave-to-class="opacity-0"
        >
            <div
                v-if="isMedia && isFullscreenOpen"
                class="fixed inset-0 z-50 flex flex-col items-center justify-between bg-black/90 backdrop-blur-xs p-4 select-none"
                role="dialog"
                aria-modal="true"
                :aria-label="attachment.filename"
                @click.self="closeFullscreen"
            >
                <!-- Top Action Header -->
                <div class="w-full flex items-center justify-between text-white px-2 py-1 max-w-7xl">
                    <div class="flex items-center gap-2 min-w-0">
                        <span class="text-sm font-semibold truncate" :title="attachment.filename">
                            {{ attachment.filename }}
                        </span>
                        <span class="text-xs text-white/60 font-mono shrink-0">
                            ({{ formatFileSize(attachment.size) }}<span v-if="attachment.width && attachment.height">, {{ attachment.width }}×{{ attachment.height }}px</span>)
                        </span>
                    </div>
                    <div class="flex items-center gap-2 shrink-0">
                        <a
                            :href="resolvedUrl"
                            target="_blank"
                            rel="noopener noreferrer"
                            class="btn btn-sm btn-circle btn-ghost text-white hover:bg-white/20"
                            title="Open original in new tab"
                        >
                            <MdOutlineOpenInNew class="size-5" />
                        </a>
                        <a
                            :href="resolvedUrl"
                            :download="attachment.filename"
                            class="btn btn-sm btn-circle btn-ghost text-white hover:bg-white/20"
                            :title="`Download ${attachment.filename}`"
                        >
                            <MdOutlineFileDownload class="size-5" />
                        </a>
                        <button
                            type="button"
                            class="btn btn-sm btn-circle btn-ghost text-white hover:bg-white/20"
                            title="Close (Esc)"
                            @click="closeFullscreen"
                        >
                            <MdClose class="size-5" />
                        </button>
                    </div>
                </div>

                <!-- Media Viewer Container -->
                <div class="flex-1 flex items-center justify-center w-full max-w-7xl overflow-hidden p-2" @click.self="closeFullscreen">
                    <!-- Image Viewer -->
                    <img
                        v-if="isImage"
                        :src="resolvedUrl"
                        :alt="attachment.filename"
                        class="max-w-full max-h-[82vh] object-contain rounded-lg shadow-2xl transition-transform"
                    />

                    <!-- Video Player -->
                    <video
                        v-else-if="isVideo"
                        :src="resolvedUrl"
                        controls
                        autoplay
                        class="max-w-full max-h-[82vh] object-contain rounded-lg shadow-2xl bg-black/70"
                    />

                    <!-- Audio Player -->
                    <div
                        v-else-if="isAudio"
                        class="flex flex-col items-center justify-center p-8 bg-base-200/40 rounded-2xl border border-white/10 backdrop-blur-md max-w-lg w-full shadow-2xl space-y-5"
                    >
                        <div class="size-28 rounded-2xl bg-accent/20 border border-accent/40 flex items-center justify-center shadow-lg text-accent">
                            <FaFileAudio class="size-14 animate-pulse" />
                        </div>
                        <div class="text-center w-full min-w-0">
                            <p class="text-base font-semibold text-white truncate" :title="attachment.filename">{{ attachment.filename }}</p>
                            <p class="text-xs text-white/60 font-mono mt-0.5">{{ formatFileSize(attachment.size) }}</p>
                        </div>
                        <audio :src="resolvedUrl" controls autoplay class="w-full shadow-md rounded-xl" />
                    </div>
                </div>

                <!-- Footer Hint -->
                <div class="text-[11px] text-white/50 text-center pb-1">
                    Press <kbd class="kbd kbd-xs bg-white/20 text-white border-0">Esc</kbd> or click outside to close
                </div>
            </div>
        </Transition>
    </Teleport>
</template>
