<script lang="ts" setup>
import {ref} from "vue";

const modal = ref<HTMLDialogElement>();

withDefaults(defineProps<{
    title: string,
    description: string,
    cancel?: (event: MouseEvent) => void,
    confirm?: (event: MouseEvent) => void,
    text?: string,
    className: string,
}>(), {
    cancel: () => {
    },
    confirm: () => {
    }
});
</script>

<template>
    <div>
        <button :class="className" @click="modal?.showModal">
            <span v-if="text">{{ text }}</span>
            <slot v-else/>
        </button>
        <Teleport to="#teleported">
            <dialog ref="modal" class="modal">
                <div class="modal-box">
                    <h2 class="text-lg font-bold">{{ title }}</h2>
                    <p class="py-4">{{ description }}</p>
                    <div class="modal-action">
                        <button class="btn btn-error" @click="(e: MouseEvent) => {cancel(e); modal?.close()}">
                            Cancel
                        </button>
                        <button class="btn btn-success" @click="(e: MouseEvent) => {confirm(e); modal?.close()}">
                            Yes
                        </button>
                    </div>
                </div>
            </dialog>
        </Teleport>
    </div>
</template>
