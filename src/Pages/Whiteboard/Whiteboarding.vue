<script lang="ts" setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import ChannelSidebar from "@/Components/ChannelSidebar.vue";
import {Channel, Message, PermType, Server, Whiteboard} from "@/types";
import WhiteboardBoard from "./WhiteboardBoard.vue";
import {computed, nextTick, onMounted, onUnmounted, ref, watch} from "vue";
import {router, useForm} from "@inertiajs/vue3";
import {defaultIcon, getMemberRoleColor, resolveUrl, usePerms} from "@/bootstrap";
import {Filter} from 'bad-words';
import {FaRegPaperPlane} from 'vue-icons-plus/fa';
import {MdOutlineDeleteForever, MdOutlineFileUpload, MdOutlineModeEdit, MdDragIndicator, MdClose, MdArrowDownward} from 'vue-icons-plus/md';
import ConfirmDialog from "@/Components/ConfirmDialog.vue";
import {create, deleteMethod, edit} from "@/routes/message";
import {usePaneDrag} from "@/composables/usePaneDrag";
import {useRecentUploads} from "@/composables/useRecentUploads";
import FilePreviewCard from "@/Components/FilePreviewCard.vue";
import FileAttachmentDisplay from "@/Components/FileAttachmentDisplay.vue";
import RecentUploadsDropdown from "@/Components/RecentUploadsDropdown.vue";
import ImageEditorModal from "@/Components/ImageEditorModal.vue";
import {validateFilesBatch, validateMessageContent} from "@/utils/fileValidation";

const filter = new Filter({placeHolder: '#'});
filter.addWords();

const perms = usePerms();
const {
    draggedPaneId,
    dragHoverPaneId,
    getOrderedPanes,
    getPaneStyle,
    startPaneSwapDrag,
    endPaneSwapDrag,
    setDragHoverPane,
    dropOnPane,
    startGutterResize
} = usePaneDrag();
const { addRecentUpload } = useRecentUploads();

const isMaximized = ref(false);
const isFileDragging = ref(false);
let dragCounter = 0;

const props = defineProps<{
    servers: Server[],
    selectedServer?: Server,
    channels?: Channel[],
    selectedChannel?: Channel & { whiteboard: Whiteboard },
    messages?: Message[],
    inviteCode?: string,
}>();

const availablePanes = computed(() =>
    isMaximized.value
        ? ['whiteboard']
        : (props.selectedServer ? ['sidebar', 'chat', 'whiteboard'] : ['chat', 'whiteboard'])
);
const activePanes = computed(() => getOrderedPanes(availablePanes.value));

const fileInput = ref<HTMLInputElement | null>(null);
const messageModal = ref<HTMLDialogElement>();
const messageIdToEdit = ref<string | null>(null);
const stagedFiles = ref<File[]>([]);
const editingFileIndex = ref<number | null>(null);
const isEditorOpen = ref(false);
const editorImageSource = ref<File | null>(null);
const validationError = ref<string | null>(null);
const loading = ref(false);

const form = useForm<{ content: string; attachments: File[] }>({
    content: '',
    attachments: []
});

const editForm = useForm<{ content: string }>({
    content: ''
});

const clearValidation = () => {
    validationError.value = null;
};

const showValidationError = (msg: string) => {
    validationError.value = msg;
    setTimeout(() => {
        if (validationError.value === msg) {
            validationError.value = null;
        }
    }, 6000);
};

const getInputElement = (): HTMLInputElement | null => {
    if (fileInput.value) {
        if (typeof (fileInput.value as HTMLInputElement).click === 'function') {
            return fileInput.value as HTMLInputElement;
        }
        if (Array.isArray(fileInput.value) && fileInput.value[0] && typeof fileInput.value[0].click === 'function') {
            return fileInput.value[0] as HTMLInputElement;
        }
    }
    if (typeof document !== 'undefined') {
        return document.getElementById('whiteboard-file-input') as HTMLInputElement | null;
    }
    return null;
};

const clearAllFiles = () => {
    const el = getInputElement();
    if (el) el.value = '';
    stagedFiles.value = [];
    form.attachments = [];
};

const removeStagedFile = (index: number) => {
    stagedFiles.value.splice(index, 1);
    form.attachments = stagedFiles.value;
    if (stagedFiles.value.length === 0) {
        const el = getInputElement();
        if (el) el.value = '';
    }
};

const stageFiles = (files: File[] | FileList | File) => {
    const filesArray = files instanceof FileList ? Array.from(files) : Array.isArray(files) ? files : [files];
    if (!filesArray.length) return;

    if (!perms.value?.has([PermType.CAM_CREATE_ATTACHMENTS])) {
        showValidationError('You do not have permission to attach files.');
        return;
    }

    const result = validateFilesBatch(stagedFiles.value, filesArray);

    if (result.errors.length > 0) {
        showValidationError(result.errors.join(' '));
    }

    stagedFiles.value = result.validFiles;
    form.attachments = result.validFiles;

    for (const f of filesArray) {
        if (result.validFiles.includes(f)) {
            addRecentUpload(f);
        }
    }
};

const triggerFileInput = () => {
    const el = getInputElement();
    el?.click();
};

const onFileInputChange = (e: Event) => {
    const files = (e.target as HTMLInputElement).files;
    if (files && files.length > 0) {
        stageFiles(files);
    }
};

const handlePaste = (e: ClipboardEvent) => {
    const items = e.clipboardData?.items;
    if (!items) return;
    const pastedFiles: File[] = [];
    for (let i = 0; i < items.length; i++) {
        const item = items[i];
        if (item.kind === 'file') {
            const file = item.getAsFile();
            if (file) {
                pastedFiles.push(file);
            }
        }
    }
    if (pastedFiles.length > 0) {
        e.preventDefault();
        stageFiles(pastedFiles);
    }
};

const onDragEnter = (e: DragEvent) => {
    if (e.dataTransfer?.types?.includes('Files')) {
        e.preventDefault();
        dragCounter++;
        isFileDragging.value = true;
    }
};

const onDragLeave = (e: DragEvent) => {
    if (e.dataTransfer?.types?.includes('Files')) {
        e.preventDefault();
        dragCounter--;
        if (dragCounter <= 0) {
            dragCounter = 0;
            isFileDragging.value = false;
        }
    }
};

const onDragOver = (e: DragEvent) => {
    if (e.dataTransfer?.types?.includes('Files')) {
        e.preventDefault();
    }
};

const onDrop = (e: DragEvent) => {
    if (draggedPaneId.value) {
        dropOnPane('chat');
        return;
    }
    if (e.dataTransfer?.types?.includes('Files')) {
        e.preventDefault();
        dragCounter = 0;
        isFileDragging.value = false;
        const files = e.dataTransfer.files;
        if (files && files.length > 0) {
            stageFiles(files);
        }
    }
};

const onPaneDragLeave = (e: DragEvent, paneId: string) => {
    const currentTarget = e.currentTarget as HTMLElement | null;
    const relatedTarget = e.relatedTarget as Node | null;
    if (!currentTarget || !relatedTarget || !currentTarget.contains(relatedTarget)) {
        if (dragHoverPaneId.value === paneId) {
            setDragHoverPane(null);
        }
    }
};

const onChatDragLeave = (e: DragEvent) => {
    onPaneDragLeave(e, 'chat');
    onDragLeave(e);
};

const messageContainer = ref<HTMLDivElement | null>(null);
const isScrolledUp = ref(false);
let isPinnedToBottom = true;
let isRestoring = true;

const getScrollStorageKey = (): string => {
    const ch = props.selectedChannel;
    return `oxy_scroll_${ch?.id || ch?.slug || 'default'}`;
};

const getMessagesContainer = (): HTMLElement | null => {
    return (messageContainer.value as HTMLElement | null) || (document.querySelector('div.overflow-y-auto.grow') as HTMLElement | null);
};

const handleScroll = (e?: Event) => {
    const el = (e?.target as HTMLElement) || getMessagesContainer();
    if (!el || isRestoring) return;
    const { scrollTop, scrollHeight, clientHeight } = el;
    const maxScroll = scrollHeight - clientHeight;

    if (maxScroll <= 30) {
        isScrolledUp.value = false;
        isPinnedToBottom = true;
        return;
    }

    const distanceFromBottom = maxScroll - scrollTop;
    const atBottom = distanceFromBottom <= 40;

    if (atBottom) {
        isScrolledUp.value = false;
        isPinnedToBottom = true;
    } else {
        isScrolledUp.value = distanceFromBottom > 50;
        isPinnedToBottom = false;
    }

    const key = getScrollStorageKey();
    if (typeof localStorage !== 'undefined') {
        if (atBottom) {
            localStorage.setItem(key, 'BOTTOM');
        } else if (!isNaN(scrollTop) && scrollTop > 0) {
            localStorage.setItem(key, String(scrollTop));
        }
    }
};

const scrollToBottomSmooth = () => {
    const el = getMessagesContainer();
    if (el) {
        isPinnedToBottom = true;
        isScrolledUp.value = false;
        el.scrollTo({
            top: el.scrollHeight,
            behavior: 'smooth'
        });
        const key = getScrollStorageKey();
        if (typeof localStorage !== 'undefined') {
            localStorage.setItem(key, 'BOTTOM');
        }
    }
};

const scrollToBottom = () => {
    const el = getMessagesContainer();
    if (el) {
        el.scrollTop = el.scrollHeight;
        isPinnedToBottom = true;
        isScrolledUp.value = false;
    }
};

const restoreScrollOrBottom = () => {
    const key = getScrollStorageKey();
    const saved = typeof localStorage !== 'undefined' ? localStorage.getItem(key) : null;
    const shouldRestoreNumber = saved && saved !== 'BOTTOM' && saved !== 'undefined' && saved !== 'null';
    const savedTop = shouldRestoreNumber ? parseFloat(saved) : null;

    isRestoring = true;

    const el = getMessagesContainer();
    if (!el) return;

    if (savedTop !== null && !isNaN(savedTop) && savedTop >= 0) {
        isPinnedToBottom = false;
        el.scrollTop = savedTop;
        const maxScroll = el.scrollHeight - el.clientHeight;
        isScrolledUp.value = maxScroll - el.scrollTop > 50;
    } else {
        isPinnedToBottom = true;
        isScrolledUp.value = false;
        el.scrollTop = el.scrollHeight;
    }

    setTimeout(() => {
        isRestoring = false;
    }, 150);
};

onMounted(() => {
    restoreScrollOrBottom();
    nextTick(() => { restoreScrollOrBottom(); });
    setTimeout(restoreScrollOrBottom, 50);
    setTimeout(restoreScrollOrBottom, 150);
    setTimeout(restoreScrollOrBottom, 350);
    setTimeout(restoreScrollOrBottom, 600);

    router.on('finish', () => {
        setTimeout(restoreScrollOrBottom, 50);
    });

    window.addEventListener('paste', handlePaste);
});

onUnmounted(() => {
    window.removeEventListener('paste', handlePaste);
});

watch(
    () => props.selectedChannel?.id,
    () => {
        nextTick(() => { restoreScrollOrBottom(); });
        setTimeout(restoreScrollOrBottom, 100);
        setTimeout(restoreScrollOrBottom, 300);
    }
);

watch(
    () => props.messages?.length,
    (newLen, oldLen) => {
        if (oldLen !== undefined && newLen && newLen > oldLen) {
            if (isPinnedToBottom) {
                nextTick(() => {
                    scrollToBottom();
                });
            }
        }
    }
);

watch(
    () => stagedFiles.value.length,
    () => {
        if (isPinnedToBottom) {
            nextTick(() => {
                scrollToBottom();
            });
        }
    }
);

const openImageEditor = (file: File, index: number) => {
    editingFileIndex.value = index;
    editorImageSource.value = file;
    isEditorOpen.value = true;
};

const handleEditorSave = (editedFile: File) => {
    if (editingFileIndex.value !== null && editingFileIndex.value < stagedFiles.value.length) {
        stagedFiles.value[editingFileIndex.value] = editedFile;
        form.attachments = stagedFiles.value;
    } else {
        stageFiles([editedFile]);
    }
};

const createMessage = async () => {
    if (!props.selectedChannel || !props.selectedServer || loading.value) return;

    const validation = validateMessageContent(form.content, stagedFiles.value.length);
    if (!validation.valid) {
        showValidationError(validation.error || 'Please enter a message or attach a file.');
        return;
    }

    if (form.content && !perms.value?.has([PermType.CAN_CREATE_MESSAGE])) {
        showValidationError('You do not have permission to send messages.');
        return;
    }

    if (stagedFiles.value.length > 0 && !perms.value?.has([PermType.CAM_CREATE_ATTACHMENTS])) {
        showValidationError('You do not have permission to upload attachments.');
        return;
    }

    loading.value = true;
    form.attachments = stagedFiles.value;

    form.post(create.url({server: props.selectedServer.route_key, channel: props.selectedChannel.route_key}), {
        preserveScroll: true,
        onSuccess: () => {
            clearAllFiles();
            form.reset();
            clearValidation();
        },
        onError: (errors) => {
            const errList = Object.values(errors).join(' ');
            showValidationError(errList);
        },
        onFinish: () => {
            loading.value = false;
        }
    });
};

const deleteMessage = async (messageId: string) => {
    router.delete(deleteMethod.url(messageId), {preserveScroll: true});
};

const editMessage = async () => {
    if (messageIdToEdit.value !== null) {
        editForm.patch(edit.url(messageIdToEdit.value), {
            preserveScroll: true,
            onSuccess: () => {
                messageModal.value?.close();
                editForm.reset();
                messageIdToEdit.value = null;
            }
        });
    }
};

const openEditModal = (messageId: string, currentContent: string | null) => {
    messageIdToEdit.value = messageId;
    editForm.content = currentContent || '';
    messageModal.value?.showModal();
};

function formatDate(dateString: string): string {
    const date = new Date(dateString);
    const day = String(date.getDate()).padStart(2, '0');
    const month = String(date.getMonth() + 1).padStart(2, '0');
    const year = String(date.getFullYear());
    const hours = String(date.getHours()).padStart(2, '0');
    const minutes = String(date.getMinutes()).padStart(2, '0');
    return `${day}-${month}-${year} ${hours}:${minutes}`;
}
</script>

<template>
    <AuthenticatedLayout :invite-code="inviteCode" :selected-server="selectedServer" :servers="servers" :channels="channels">
        <template v-for="(paneId, idx) in activePanes" :key="paneId">
            <!-- 1. Sidebar Pane -->
            <div
                v-if="paneId === 'sidebar' && selectedServer"
                :style="getPaneStyle('sidebar', activePanes)"
                class="flex flex-col overflow-hidden relative transition-all duration-75"
                @dragenter.prevent="draggedPaneId ? setDragHoverPane('sidebar') : null"
                @dragover.prevent="draggedPaneId ? setDragHoverPane('sidebar') : null"
                @dragleave="onPaneDragLeave($event, 'sidebar')"
                @drop="dropOnPane('sidebar')"
            >
                <ChannelSidebar :channels="channels" :selected-server="selectedServer" />

                <!-- Pane Swap Drag Hover Overlay -->
                <div
                    v-if="dragHoverPaneId === 'sidebar' && draggedPaneId && draggedPaneId !== 'sidebar'"
                    class="absolute inset-0 z-40 pointer-events-none border-2 border-dashed border-primary bg-primary/15 rounded-xl transition-all animate-fadeIn"
                />
            </div>

            <!-- 2. Chat Pane -->
            <div
                v-else-if="paneId === 'chat' && selectedChannel"
                :style="getPaneStyle('chat', activePanes)"
                class="bg-base-100 flex flex-col overflow-hidden relative transition-all duration-75 min-w-[250px]"
                @dragenter.prevent="draggedPaneId ? setDragHoverPane('chat') : onDragEnter($event)"
                @dragover.prevent="draggedPaneId ? setDragHoverPane('chat') : onDragOver($event)"
                @dragleave="onChatDragLeave"
                @drop="onDrop"
            >
                <!-- Pane Swap Drag Hover Overlay -->
                <div
                    v-if="dragHoverPaneId === 'chat' && draggedPaneId && draggedPaneId !== 'chat'"
                    class="absolute inset-0 z-40 pointer-events-none border-2 border-dashed border-primary bg-primary/15 rounded-xl transition-all animate-fadeIn"
                />
                <!-- File Drag & Drop Overlay -->
                <div
                    v-if="isFileDragging"
                    class="absolute inset-0 bg-base-100/90 backdrop-blur-xs border-2 border-dashed border-primary z-40 flex flex-col items-center justify-center pointer-events-none m-2 rounded-2xl animate-fadeIn"
                >
                    <div class="p-4 bg-base-200/80 rounded-2xl shadow-xl flex flex-col items-center gap-2 border border-base-300 text-center">
                        <MdOutlineFileUpload class="size-10 text-primary animate-bounce" />
                        <p class="font-bold text-sm text-base-content">Drop files here</p>
                        <p class="text-[11px] text-base-content/60">Images, docs, archives, media</p>
                    </div>
                </div>

                <div class="h-12 px-4 bg-base-200/50 border-b border-base-300 flex items-center justify-between shrink-0">
                    <div class="font-bold text-sm text-base-content flex items-center gap-1.5">
                        <span>#</span>
                        <span>{{ selectedChannel.name }}</span>
                    </div>
                    <div
                        draggable="true"
                        class="cursor-grab active:cursor-grabbing text-base-content/70 hover:text-primary p-1 rounded hover:bg-base-300 transition-colors"
                        title="Drag handle: Drag to swap chat pane position"
                        @dragstart="startPaneSwapDrag('chat')"
                        @dragend="endPaneSwapDrag"
                    >
                        <MdDragIndicator class="size-4" />
                    </div>
                </div>

                <!-- Messages Stream -->
                <div ref="messageContainer" class="overflow-y-auto grow p-3 space-y-2 pb-10 relative" @scroll="handleScroll">
                    <div v-if="messages && messages.length > 0">
                        <div
                            v-for="message in messages" :key="message.id"
                            :class="{'chat chat-start': message.user_id !== $page.props.user?.id, 'chat chat-end': message.user_id === $page.props.user?.id}"
                        >
                            <div class="chat-image avatar">
                                <div class="w-8 rounded-full">
                                    <img :src="resolveUrl(message.sender?.icon) || defaultIcon" @error="(e) => (e.target as HTMLImageElement).src = defaultIcon" alt="User Avatar"/>
                                </div>
                            </div>
                            <div class="chat-header text-xs">
                                <span class="font-semibold" :style="{ color: getMemberRoleColor(message.sender, selectedServer?.roles) }">{{ message.sender?.nickname || message.sender?.name }}</span>
                                <time class="opacity-50 ml-1">{{ formatDate(message.created_at) }}</time>
                            </div>
                            <div class="chat-bubble group bg-base-200 text-base-content text-xs flex flex-col gap-1 relative">
                                <!-- Message Text -->
                                <div v-if="message.content" class="text-wrap break-words whitespace-pre-wrap">
                                    {{ filter.clean(message.content) }}
                                </div>

                                <!-- Message Attachments -->
                                <div v-if="message.attachments && message.attachments.length > 0" class="flex flex-col gap-1 mt-0.5">
                                    <FileAttachmentDisplay
                                        v-for="attachment in message.attachments"
                                        :key="attachment.id"
                                        :attachment="attachment"
                                    />
                                </div>

                                <!-- Message Actions -->
                                <div class="absolute right-1 top-1 hidden group-hover:flex items-center gap-1 bg-base-300/80 rounded-md p-0.5">
                                    <button
                                        v-if="message.user_id === $page.props.user?.id"
                                        class="btn btn-ghost btn-xs btn-circle p-0"
                                        title="Edit text"
                                        @click="openEditModal(message.id, message.content)"
                                    >
                                        <MdOutlineModeEdit class="size-3 text-warning" />
                                    </button>
                                    <ConfirmDialog
                                        v-if="message.user_id === $page.props.user?.id || perms.has([PermType.CAN_DELETE_MESSAGE])"
                                        :confirm="() => deleteMessage(message.id)"
                                        class-name="btn btn-ghost btn-xs btn-circle p-0"
                                        description="Are you sure you want to delete this message?"
                                        title="Delete Message"
                                    >
                                        <MdOutlineDeleteForever class="size-3 text-error" />
                                    </ConfirmDialog>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div v-else class="text-xs text-base-content/50 p-2">
                        <span>No messages yet in this channel.</span>
                    </div>
                </div>

                <!-- Floating Scroll to Bottom Button -->
                <Transition
                    enter-active-class="transition duration-150 ease-out"
                    enter-from-class="opacity-0 translate-y-2"
                    enter-to-class="opacity-100 translate-y-0"
                    leave-active-class="transition duration-100 ease-in"
                    leave-from-class="opacity-100 translate-y-0"
                    leave-to-class="opacity-0 translate-y-2"
                >
                    <button
                        v-if="isScrolledUp"
                        type="button"
                        class="btn btn-sm btn-circle btn-primary absolute bottom-20 right-6 shadow-2xl z-50 hover:scale-110 transition-transform"
                        title="Jump to bottom"
                        @click="scrollToBottomSmooth"
                    >
                        <MdArrowDownward class="size-4" />
                    </button>
                </Transition>

                <!-- Validation Error Toast -->
                <div v-if="validationError" class="px-3 py-1.5 bg-error/15 text-error text-[11px] border-t border-error/30 flex items-center justify-between">
                    <span>{{ validationError }}</span>
                    <button class="btn btn-ghost btn-xs btn-circle" @click="clearValidation">
                        <MdClose class="size-3" />
                    </button>
                </div>

                <!-- Staged Attachments Container (Supports Multiple Files) -->
                <div v-if="stagedFiles.length > 0" class="px-3 pt-2 pb-2 bg-base-100 border-t border-base-300 flex flex-wrap gap-1.5 max-h-28 overflow-y-auto shrink-0 z-10">
                    <FilePreviewCard
                        v-for="(file, index) in stagedFiles"
                        :key="index + file.name"
                        :file="file"
                        @edit="openImageEditor(file, index)"
                        @remove="removeStagedFile(index)"
                    />
                </div>

                <!-- Chat Input Form -->
                    <form
                        :class="{'border-t-0': stagedFiles.length > 0, 'border-t border-base-300': stagedFiles.length === 0}"
                        class="flex items-center gap-2 p-2 bg-base-100 shrink-0 z-10"
                        @submit.prevent="createMessage"
                    >
                        <RecentUploadsDropdown
                            :disabled="!perms.has([PermType.CAM_CREATE_ATTACHMENTS])"
                            @select-file="stageFiles"
                            @open-file-picker="triggerFileInput"
                        />
                        <input
                            id="whiteboard-file-input"
                            ref="fileInput"
                            :disabled="!perms.has([PermType.CAM_CREATE_ATTACHMENTS])"
                            autocomplete="off"
                            class="hidden"
                            data-bwignore="true"
                            type="file"
                            multiple
                            @change="onFileInputChange"
                        />

                    <input
                        v-model="form.content"
                        class="input input-sm input-bordered flex-1 focus:outline-none"
                        :placeholder="stagedFiles.length > 0 ? `Message with ${stagedFiles.length} file${stagedFiles.length > 1 ? 's' : ''}...` : 'Type message...'"
                        type="text"
                        @keydown.enter.prevent="createMessage"
                    />
                    <button
                        :disabled="loading || (!form.content.trim() && stagedFiles.length === 0)"
                        class="btn btn-sm btn-square btn-primary shrink-0"
                        type="submit"
                    >
                        <FaRegPaperPlane class="size-3.5" />
                    </button>
                </form>
            </div>

            <!-- 3. Whiteboard Canvas Pane -->
            <div
                v-else-if="paneId === 'whiteboard' && selectedChannel"
                :style="getPaneStyle('whiteboard', activePanes)"
                class="bg-base-100 flex flex-col overflow-hidden min-w-[300px] transition-all duration-75 relative"
                @dragenter.prevent="draggedPaneId ? setDragHoverPane('whiteboard') : null"
                @dragover.prevent="draggedPaneId ? setDragHoverPane('whiteboard') : null"
                @dragleave="onPaneDragLeave($event, 'whiteboard')"
                @drop="dropOnPane('whiteboard')"
            >
                <WhiteboardBoard
                    :selected-channel="selectedChannel"
                    :selected-server="selectedServer"
                    :whiteboard="selectedChannel.whiteboard"
                    @toggle-maximize="isMaximized = !isMaximized"
                />

                <!-- Pane Swap Drag Hover Overlay -->
                <div
                    v-if="dragHoverPaneId === 'whiteboard' && draggedPaneId && draggedPaneId !== 'whiteboard'"
                    class="absolute inset-0 z-40 pointer-events-none border-2 border-dashed border-primary bg-primary/15 rounded-xl transition-all animate-fadeIn"
                />
            </div>

            <!-- Gutter between adjacent panes (strictly 1 gutter between each pair) -->
            <div
                v-if="idx < activePanes.length - 1"
                class="w-1.5 hover:w-2 hover:bg-primary active:bg-primary cursor-col-resize z-30 transition-all bg-base-300/80 flex-shrink-0 self-stretch select-none"
                :title="`Drag to resize`"
                @pointerdown.prevent="startGutterResize($event, activePanes[idx], activePanes[idx + 1], activePanes)"
            ></div>
        </template>
    </AuthenticatedLayout>

    <ImageEditorModal
        v-model="isEditorOpen"
        :image-source="editorImageSource"
        title="Annotate & Edit Image"
        @save="handleEditorSave"
    />

    <!-- Edit Message Modal -->
    <dialog ref="messageModal" class="modal">
        <div class="modal-box">
            <form @submit.prevent="editMessage">
                <div class="form-control mb-4">
                    <label class="label">
                        <span class="label-text font-bold">Edit Message Content</span>
                    </label>
                    <textarea
                        v-model="editForm.content"
                        autocomplete="off"
                        class="textarea textarea-bordered w-full h-28 focus:outline-none"
                        data-bwignore="true"
                        placeholder="Edit message..."
                    ></textarea>
                </div>
                <div class="modal-action">
                    <button class="btn btn-primary w-full" type="submit" :disabled="editForm.processing">
                        Save Changes
                    </button>
                </div>
                <button
                    class="btn btn-sm btn-circle btn-ghost absolute right-2 top-2"
                    type="button"
                    @click="() => { messageModal?.close(); editForm.reset(); messageIdToEdit = null; }">✕
                </button>
            </form>
        </div>
    </dialog>
</template>
