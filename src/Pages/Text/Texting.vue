<script lang="ts" setup>
import {defaultIcon, getMemberRoleColor, resolveUrl, usePerms} from '@/bootstrap';
import pb from "@/pocketbase";
import {Channel, Message, PermType, Server} from "@/types";
import {computed, nextTick, onMounted, onUnmounted, reactive, ref, watch} from "vue";
import ConfirmDialog from "@/Components/ConfirmDialog.vue";
import {Filter} from 'bad-words';
import {FaRegPaperPlane} from 'vue-icons-plus/fa';
import {
    MdArrowDownward,
    MdClose,
    MdDragIndicator,
    MdOutlineDeleteForever,
    MdOutlineFileUpload,
    MdOutlineModeEdit
} from 'vue-icons-plus/md';
import {useMessageEvents} from "@/composables/useMessageEvents";
import {usePaneDrag} from "@/composables/usePaneDrag";
import {useRecentUploads} from "@/composables/useRecentUploads";
import FilePreviewCard from "@/Components/FilePreviewCard.vue";
import FileAttachmentDisplay from "@/Components/FileAttachmentDisplay.vue";
import RecentUploadsDropdown from "@/Components/RecentUploadsDropdown.vue";
import ImageEditorModal from "@/Components/ImageEditorModal.vue";
import {validateFilesBatch, validateMessageContent} from "@/utils/fileValidation";


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
const {addRecentUpload} = useRecentUploads();

const filter = new Filter({placeHolder: '#'});
filter.addWords();

const perms = usePerms();
const props = defineProps<{
    servers: Server[],
    selectedServer?: Server,
    channels?: Channel[],
    selectedChannel?: Channel,
    messages?: Message[],
    inviteCode?: string,
}>();

const availablePanes = computed(() => props.selectedServer ? ['sidebar', 'chat'] : ['chat']);
const activePanes = computed(() => getOrderedPanes(availablePanes.value));

const fileInput = ref<HTMLInputElement | null>(null);
const messageContainer = ref<HTMLElement>();
const messageModal = ref<HTMLDialogElement>();
const messageIdToEdit = ref<string | null>(null);
const stagedFiles = ref<File[]>([]);
const editingFileIndex = ref<number | null>(null);
const isEditorOpen = ref(false);
const editorImageSource = ref<File | null>(null);
const isFileDragging = ref(false);
const validationError = ref<string | null>(null);
let dragCounter = 0;

function formatDate(dateString: string): string {
    const date = new Date(dateString);
    const year = String(date.getFullYear());
    const month = String(date.getMonth() + 1).padStart(2, '0');
    const day = String(date.getDate()).padStart(2, '0');
    const hours = String(date.getHours()).padStart(2, '0');
    const minutes = String(date.getMinutes()).padStart(2, '0');
    return `${day}-${month}-${year} ${hours}:${minutes}`;
}

const form = reactive({
    content: '',
    attachments: [] as File[],
    processing: false,
    reset() {
        this.content = '';
        this.attachments = [];
    },
    post(_url?: string, _opts?: unknown) {
        this.processing = false;
    }
});

const editForm = reactive({
    content: '',
    processing: false,
    reset() {
        this.content = '';
    },
});

const localMessages = ref<Message[]>([]);

const fetchChannelMessages = async () => {
    if (!props.selectedChannel?.id) {
        localMessages.value = [];
        return;
    }
    try {
        const records = await pb.collection('messages').getFullList({
            filter: `channel = "${props.selectedChannel.id}"`,
            sort: '+id',
            expand: 'user',
            requestKey: null
        });
        localMessages.value = records.map(r => ({
            id: r.id,
            channel_id: r.channel,
            user_id: r.user,
            content: r.content,
            status: r.status,
            created_at: r.created,
            updated_at: r.updated,
            sender: r.expand?.user ? {
                id: r.expand.user.id,
                nickname: r.expand.user.name || r.expand.user.username || 'User',
                icon: r.expand.user.avatar || null,
                status: r.expand.user.status || 'online'
            } : null
        })) as unknown as Message[];
    } catch (err) {
        console.error('Error fetching messages:', err);
    }
};

watch(() => props.selectedChannel?.id, () => {
    fetchChannelMessages();
}, { immediate: true });

useMessageEvents(
    props.selectedChannel?.id,
    () => { fetchChannelMessages(); },
    () => { fetchChannelMessages(); },
    () => { fetchChannelMessages(); }
);

const activeMessages = computed(() => (props.messages && props.messages.length > 0) ? props.messages : localMessages.value);

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
        return document.getElementById('message-file-input') as HTMLInputElement | null;
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

const loading = ref(false);

const createMessage = async () => {
    if (loading.value) return;

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
    try {
        if (stagedFiles.value.length > 0) {
            const formData = new FormData();
            formData.append('channel', props.selectedChannel!.id);
            formData.append('user', pb.authStore.model!.id);
            formData.append('content', form.content);
            formData.append('status', 'sent');

            stagedFiles.value.forEach(file => {
                formData.append('attachments', file);
            });

            await pb.collection('messages').create(formData, { requestKey: null });
        } else {
            await pb.collection('messages').create({
                channel: props.selectedChannel!.id,
                user: pb.authStore.model!.id,
                content: form.content,
                status: 'sent'
            }, { requestKey: null });
        }

        clearAllFiles();
        form.content = '';
        clearValidation();
        await fetchChannelMessages();
        const key = getScrollStorageKey();
        if (key && typeof sessionStorage !== 'undefined') {
            sessionStorage.removeItem(key);
        }
        nextTick(() => scrollToBottom());
    } catch (err: unknown) {
        showValidationError((err as { message?: string })?.message || 'Failed to send message');
    } finally {
        loading.value = false;
    }
};

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
    const {scrollTop, scrollHeight, clientHeight} = el;
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
    nextTick(() => {
        restoreScrollOrBottom();
    });
    setTimeout(restoreScrollOrBottom, 50);
    setTimeout(restoreScrollOrBottom, 150);
    setTimeout(restoreScrollOrBottom, 350);
    setTimeout(restoreScrollOrBottom, 600);

    window.addEventListener('paste', handlePaste);
});

onUnmounted(() => {
    window.removeEventListener('paste', handlePaste);
});

watch(
    () => props.selectedChannel?.id,
    () => {
        nextTick(() => {
            restoreScrollOrBottom();
        });
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

const deleteMessage = async (messageId: string) => {
    try {
        await pb.collection('messages').delete(messageId, { requestKey: null });
        await fetchChannelMessages();
    } catch (err: unknown) {
        console.error('Error deleting message:', err);
    }
};

const editMessage = async () => {
    if (messageIdToEdit.value !== null) {
        try {
            await pb.collection('messages').update(messageIdToEdit.value, {
                content: editForm.content,
                status: 'edited',
            }, { requestKey: null });
            messageIdToEdit.value = null;
            editForm.content = '';
            await fetchChannelMessages();
        } catch (err: unknown) {
            console.error('Error editing message:', err);
        }
    }
};

const openEditModal = (messageId: string, currentContent: string | null) => {
    messageIdToEdit.value = messageId;
    editForm.content = currentContent || '';
    messageModal.value?.showModal();
};

</script>

<template>
    <div class="h-full flex flex-col w-full relative">
        <!-- Chat Stream & Input Pane -->
        <div
            class="bg-base-100 flex-1 flex flex-col overflow-hidden relative min-w-[250px] h-full"
            @dragleave="onChatDragLeave"
            @drop="onDrop"
            @paste="handlePaste"
            @dragenter.prevent="onDragEnter($event)"
            @dragover.prevent="onDragOver($event)"
        >
            <!-- Pane Swap Drag Hover Overlay -->
            <div
                v-if="dragHoverPaneId === 'chat' && draggedPaneId && draggedPaneId !== 'chat'"
                class="absolute inset-0 z-40 pointer-events-none border-2 border-dashed border-primary bg-primary/15 rounded-xl transition-all animate-fadeIn"
            />
            <template v-if="selectedChannel">
                <!-- File Drag & Drop Overlay -->
                <div
                    v-if="isFileDragging"
                    class="absolute inset-0 bg-base-100/90 backdrop-blur-xs border-2 border-dashed border-primary z-40 flex flex-col items-center justify-center pointer-events-none m-2 rounded-2xl animate-fadeIn"
                >
                    <div
                        class="p-6 bg-base-200/80 rounded-2xl shadow-xl flex flex-col items-center gap-2 border border-base-300 text-center">
                        <MdOutlineFileUpload class="size-12 text-primary animate-bounce"/>
                        <p class="font-bold text-base text-base-content">Drop files here to upload</p>
                        <p class="text-xs text-base-content/60">Images, PDFs, documents, archives, audio, video &
                            software</p>
                    </div>
                </div>

                <!-- Channel Header Bar with Drag Handle -->
                <div
                    class="h-12 px-4 bg-base-200/50 border-b border-base-300 flex items-center justify-between shrink-0">
                    <div class="flex items-center gap-2 font-bold text-sm text-base-content">
                        <span>#</span>
                        <span>{{ selectedChannel.name }}</span>
                    </div>
                    <div
                        class="cursor-grab active:cursor-grabbing text-base-content/70 hover:text-primary p-1 rounded hover:bg-base-300 transition-colors"
                        draggable="true"
                        title="Drag handle: Drag to swap pane position"
                        @dragend="endPaneSwapDrag"
                        @dragstart="startPaneSwapDrag('chat')"
                    >
                        <MdDragIndicator class="size-4"/>
                    </div>
                </div>

                <!-- Messages Stream -->
                <div
ref="messageContainer" class="overflow-y-auto grow p-3 mx-5 mt-5 pb-10 relative"
                     @scroll="handleScroll">
                    <div v-if="activeMessages && activeMessages.length > 0" class="space-y-4">
                        <div
                            v-for="message in activeMessages" :key="message.id"
                            :class="{'chat chat-start': message.user_id !== pb.authStore.model?.id, 'chat chat-end': message.user_id === pb.authStore.model?.id}"
                        >
                            <div class="chat-image avatar">
                                <div class="w-10 rounded-full">
                                    <img
                                        :src="resolveUrl(message.sender?.icon) || defaultIcon"
                                        alt="User Avatar"
                                        @error="(e) => (e.target as HTMLImageElement).src = defaultIcon"/>
                                </div>
                            </div>
                            <div class="chat-header">
                                <span
:style="{ color: getMemberRoleColor(message.sender, selectedServer?.roles) }"
                                      class="font-semibold">{{
                                        message.sender?.nickname || message.sender?.name
                                    }}</span>
                                <time class="text-xs opacity-50 ml-1">{{ formatDate(message.created_at) }}</time>
                            </div>

                            <div class="indicator">
                                <div
                                    class="chat-bubble group max-w-full bg-base-200 text-base-content flex flex-col gap-1">
                                    <!-- Message Text Body -->
                                    <div
v-if="message.content"
                                         class="text-wrap break-words max-w-[40vw] whitespace-pre-wrap text-sm">
                                        {{ filter.clean(message.content) }}
                                    </div>

                                    <!-- Message Attachments List -->
                                    <div
v-if="message.attachments && message.attachments.length > 0"
                                         class="flex flex-col gap-1.5 mt-0.5">
                                        <FileAttachmentDisplay
                                            v-for="attachment in message.attachments"
                                            :key="attachment.id"
                                            :attachment="attachment"
                                        />
                                    </div>

                                    <!-- Message Actions Hover Toolbar -->
                                    <div
                                        v-if="message.user_id === pb.authStore.model?.id || perms.has([PermType.CAN_DELETE_MESSAGE])"
                                        class="absolute right-1 top-1 hidden group-hover:flex items-center gap-1 bg-base-300/90 backdrop-blur-xs rounded-md p-0.5 shadow-sm z-20"
                                    >
                                        <button
                                            v-if="message.user_id === pb.authStore.model?.id"
                                            class="btn btn-ghost btn-xs btn-circle p-0"
                                            title="Edit text"
                                            @click="openEditModal(message.id, message.content)"
                                        >
                                            <MdOutlineModeEdit class="size-3.5 text-warning"/>
                                        </button>
                                        <ConfirmDialog
                                            v-if="message.user_id === pb.authStore.model?.id || perms.has([PermType.CAN_DELETE_MESSAGE])"
                                            :confirm="() => deleteMessage(message.id)"
                                            class-name="btn btn-ghost btn-xs btn-circle p-0"
                                            description="Are you sure you want to delete this message?"
                                            title="Delete Message"
                                        >
                                            <MdOutlineDeleteForever class="size-3.5 text-error"/>
                                        </ConfirmDialog>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div v-else class="text-center text-base-content/50 py-8">
                        <p>No messages yet in this channel.</p>
                    </div>
                </div>

                <!-- Floating Scroll to Bottom Button -->
                <Transition
                    enter-active-class="transition duration-150 ease-out"
                    enter-from-class="opacity-0 translate-y-3 scale-90"
                    enter-to-class="opacity-100 translate-y-0 scale-100"
                    leave-active-class="transition duration-100 ease-in"
                    leave-from-class="opacity-100 translate-y-0 scale-100"
                    leave-to-class="opacity-0 translate-y-3 scale-90"
                >
                    <button
                        v-if="isScrolledUp"
                        class="btn btn-sm btn-circle btn-primary absolute bottom-20 right-8 shadow-2xl z-50 hover:scale-110 transition-transform"
                        title="Jump to bottom"
                        type="button"
                        @click="scrollToBottomSmooth"
                    >
                        <MdArrowDownward class="size-5"/>
                    </button>
                </Transition>

                <!-- Validation Error Toast / Banner -->
                <div
v-if="validationError"
                     class="px-4 py-2 bg-error/15 text-error text-xs border-t border-error/30 flex items-center justify-between">
                    <span>{{ validationError }}</span>
                    <button class="btn btn-ghost btn-xs btn-circle" @click="clearValidation">
                        <MdClose class="size-3.5"/>
                    </button>
                </div>

                <!-- Staged File Previews Container (Supports Multiple Files) -->
                <div
v-if="stagedFiles.length > 0"
                     class="px-3 pt-2 pb-2 bg-base-100 border-t border-base-300 flex flex-wrap gap-2 max-h-36 overflow-y-auto shrink-0 z-10">
                    <FilePreviewCard
                        v-for="(file, index) in stagedFiles"
                        :key="index + file.name"
                        :file="file"
                        @edit="openImageEditor(file, index)"
                        @remove="removeStagedFile(index)"
                    />
                </div>

                <!-- Send Message Form -->
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
                        id="message-file-input"
                        ref="fileInput"
                        :disabled="!perms.has([PermType.CAM_CREATE_ATTACHMENTS])"
                        autocomplete="off"
                        class="hidden"
                        data-bwignore="true"
                        multiple
                        type="file"
                        @change="onFileInputChange"
                    />

                    <input
                        v-model="form.content"
                        :class="`input input-sm input-bordered flex-1 focus:outline-none ${validationError ? 'input-error' : ''}`"
                        :disabled="loading || (!perms.has([PermType.CAN_CREATE_MESSAGE]) && stagedFiles.length === 0)"
                        :placeholder="stagedFiles.length > 0 ? `Add a message to ${stagedFiles.length} attachment${stagedFiles.length > 1 ? 's' : ''}... (optional)` : 'Type a message...'"
                        autocomplete="off"
                        data-bwignore="true"
                        type="text"
                        @keydown.enter.prevent="createMessage"
                    />
                    <button
                        :disabled="loading || (!form.content.trim() && stagedFiles.length === 0) || !perms.hasAny([PermType.CAN_CREATE_MESSAGE, PermType.CAM_CREATE_ATTACHMENTS])"
                        class="btn btn-sm btn-square btn-primary shrink-0"
                        title="Send Message"
                        type="submit"
                    >
                        <FaRegPaperPlane class="size-3.5"/>
                    </button>
                </form>
            </template>
            <div v-else class="flex-grow flex items-center justify-center text-base-content/50 w-full h-full">
                <p>Select a channel to start texting!</p>
            </div>
        </div>
    </div>

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
                    <button :disabled="editForm.processing" class="btn btn-primary w-full" type="submit">
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
