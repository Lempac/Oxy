<script setup lang="ts">
import {ref, defineProps} from "vue";

const modal = ref<HTMLDialogElement>();

withDefaults(defineProps<{
    title: String,
    description: String,
    cancel?: (event: MouseEvent) => void,
    confirm?: (event: MouseEvent) => void
    text?: string,
    className: string,
}>(), {
    cancel: (_: MouseEvent) => {},
    confirm: (_: MouseEvent) => {},
});

</script>

<template>
    <button @click="modal?.showModal" :class="className">
        <span v-if="text" v-text="text"></span>
        <slot v-else/>
    </button>
    <Teleport to="#teleported">
        <dialog ref="modal" class="modal">
            <div class="modal-box">
                <h2 class="text-lg font-bold" v-text="title"></h2>
                <p class="py-4" v-text="description"></p>
                <div class="modal-action">
                    <button @click="(e: MouseEvent) => {cancel(e); modal?.close()}" class="btn btn-error">
                        Cancel
                    </button>
                    <button @click="(e: MouseEvent) => {confirm(e); modal?.close()}" class="btn btn-success">
                        Yes
                    </button>
                </div>
            </div>
        </dialog>
    </Teleport>
</template>
