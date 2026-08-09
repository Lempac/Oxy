<script lang="ts" setup>
import { update } from '@/routes/profile';
import { useForm, usePage } from '@inertiajs/vue3';
import ErrorAlert from "@/Components/ErrorAlert.vue";
import { ref } from "vue";
import { baseUrl } from "@/bootstrap";
import { Themes, ThemeType } from "@/types";
import { Io5AddOutline } from 'vue-icons-plus/io5';
import { RiUser3Line } from 'vue-icons-plus/ri';
import ImageEditorModal from '@/Components/ImageEditorModal.vue';

defineProps<{
    status?: string;
}>();

const user = usePage().props.user!;

const icon = ref<string | null>(user.icon ? baseUrl + user.icon : null);
const inputFile = ref<File | null>();
const isEditorOpen = ref(false);
const editorImageSource = ref<File | null>(null);
const isDraggingOver = ref(false);

const form = useForm<{ nickname: string, about_me: string, icon: File | null, light_theme: ThemeType, dark_theme: ThemeType }>({
    nickname: user.nickname,
    about_me: user.about_me || '',
    icon: null,
    light_theme: user.light_theme || Themes.OXY,
    dark_theme: user.dark_theme || Themes.DARK,
});

form.defaults();

const onFileSelected = (val: File) => {
    if (!val) return;
    editorImageSource.value = val;
    isEditorOpen.value = true;
};

const onDropAvatar = (e: DragEvent) => {
    isDraggingOver.value = false;
    const file = e.dataTransfer?.files?.[0];
    if (file && file.type.startsWith('image/')) {
        onFileSelected(file);
    }
};

const handleEditorSave = (editedFile: File) => {
    inputFile.value = editedFile;
    form.icon = editedFile;
    icon.value = URL.createObjectURL(editedFile);
    form.recentlySuccessful = false;
};

const submit = () => {
    form.post(update.url(), {
        method: 'put',
        preserveScroll: true,
        onSuccess: () => {
            form.defaults();
        },
    });
};

</script>

<template>
    <section>
        <p class="mt-1 text-sm text-base-content/70">
            Update your account's nickname and profile picture.
        </p>

        <form class="mt-6 space-y-6" @submit.prevent="submit">
            <!-- Profile Picture Upload -->
            <div
                class="form-control flex flex-row items-center gap-4 group"
                @dragover.prevent="isDraggingOver = true"
                @dragleave.prevent="isDraggingOver = false"
                @drop.prevent="onDropAvatar"
            >
                <label
                    :class="{'ring-2 ring-primary ring-offset-2 scale-105': isDraggingOver}"
                    class="cursor-pointer rounded-full bg-base-200 transition-all duration-300 ease-in-out hover:bg-transparent group-hover:bg-transparent"
                    for="profilePicture">
                    <img v-if="icon !== null" :src="icon" alt="" class="size-16 rounded-full object-cover"/>
                    <Io5AddOutline v-else scale="3.333"/>
                </label>
                <label class="cursor-pointer text-sm font-medium" for="profilePicture">
                    <span>Upload profile picture</span>
                    <span class="block text-xs text-base-content/50">Click or drag & drop image</span>
                </label>
                <input
                    id="profilePicture"
                    ref="inputFile"
                    accept="image/png, image/jpeg, image/webp"
                    autocomplete="off"
                    class="hidden"
                    data-bwignore="true"
                    type="file"
                    @input="onFileSelected((<HTMLInputElement>$event.target).files![0])"
                />
            </div>
            <ErrorAlert v-if="form.errors.icon" :message="form.errors.icon" class="mt-2"/>

            <ImageEditorModal
                v-model="isEditorOpen"
                :image-source="editorImageSource"
                title="Edit Profile Picture"
                :aspect-ratio-lock="1"
                :circle-mask="true"
                @save="handleEditorSave"
            />

            <div class="form-control">
                <label class="block font-medium text-sm text-base-content/90" for="nickname">Nickname</label>
                <label class="input input-bordered flex items-center gap-2">
                    <RiUser3Line class="h-4 w-4 opacity-70"/>
                    <input
                        id="profile-nickname"
                        v-model="form.nickname"
                        autocomplete="username"
                        autofocus
                        class="mt-1 block w-full"
                        name="nickname"
                        required
                        type="text"
                    />
                </label>
                <ErrorAlert :message="form.errors.nickname" class="mt-2"/>
            </div>

            <div class="form-control">
                <label class="block font-medium text-sm text-base-content/90" for="about_me">About Me</label>
                <textarea
                    id="about_me"
                    v-model="form.about_me"
                    class="textarea textarea-bordered mt-1 block w-full resize-none"
                    rows="3"
                    placeholder="Tell others a little about yourself..."
                />
                <ErrorAlert :message="form.errors.about_me" class="mt-2"/>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="form-control">
                    <label class="block font-medium text-sm text-base-content/90" for="light_theme">Light Theme</label>
                    <select
                        id="light_theme"
                        v-model="form.light_theme"
                        class="select select-bordered mt-1 block w-full"
                    >
                        <option v-for="theme in Themes" :key="theme" :value="theme">
                            {{ theme.charAt(0).toUpperCase() + theme.slice(1) }}
                        </option>
                    </select>
                    <ErrorAlert :message="form.errors.light_theme" class="mt-2"/>
                </div>

                <div class="form-control">
                    <label class="block font-medium text-sm text-base-content/90" for="dark_theme">Dark Theme</label>
                    <select
                        id="dark_theme"
                        v-model="form.dark_theme"
                        class="select select-bordered mt-1 block w-full"
                    >
                        <option v-for="theme in Themes" :key="theme" :value="theme">
                            {{ theme.charAt(0).toUpperCase() + theme.slice(1) }}
                        </option>
                    </select>
                    <ErrorAlert :message="form.errors.dark_theme" class="mt-2"/>
                </div>
            </div>

            <div class="flex items-center gap-4">
                <button :disabled="form.processing" class="btn">Save</button>

                <Transition
                    enter-active-class="transition ease-in-out"
                    enter-from-class="opacity-0"
                    leave-active-class="transition ease-in-out"
                    leave-to-class="opacity-0"
                >
                    <p v-if="form.recentlySuccessful && !form.isDirty" class="text-sm text-base-content/70">Saved.</p>
                </Transition>
            </div>
        </form>
    </section>
</template>
