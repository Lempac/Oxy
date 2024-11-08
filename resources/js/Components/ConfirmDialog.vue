<script setup lang="ts">
import {ref, defineProps, Ref} from "vue";

const modal = ref<HTMLDialogElement>();

defineProps<{
    title: String,
    description: String,
    cancel: (event: MouseEvent) => void,
    confirm: (event: MouseEvent) => void
}>();

defineExpose<{

}>()

</script>

<template>
    <Teleport to="body">
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
