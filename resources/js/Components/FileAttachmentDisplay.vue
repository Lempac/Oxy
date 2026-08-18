<script lang="ts" setup>
import { computed } from 'vue';
import { MessageAttachment } from '@/types';
import { baseUrl } from '@/bootstrap';
import { formatFileSize, getFileExtension, isImageFile } from '@/utils/fileValidation';
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
import { MdOutlineFileDownload, MdOutlineOpenInNew } from 'vue-icons-plus/md';

const props = defineProps<{
    attachment: MessageAttachment;
}>();

const ext = computed(() => getFileExtension(props.attachment.filename));

const isImage = computed(() => {
    return isImageFile(props.attachment.mime_type || props.attachment.filename);
});

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
            class="max-w-full max-h-72 object-contain rounded-xl block"
            loading="lazy"
        />
        <!-- Image Hover Overlay with Action Buttons -->
        <div class="absolute inset-0 bg-black/50 opacity-0 group-hover/img:opacity-100 transition-opacity flex items-center justify-between p-3">
            <span class="text-white text-xs font-medium truncate max-w-[180px]" :title="attachment.filename">
                {{ attachment.filename }}
            </span>
            <div class="flex items-center gap-1.5 shrink-0">
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

    <!-- Non-Image File Attachment (Icon on same line as name + download button) -->
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
</template>
