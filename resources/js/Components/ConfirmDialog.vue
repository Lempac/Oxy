<script lang="ts" setup>
import {ref} from "vue";

const isModalOpen = ref(false);

withDefaults(defineProps<{
    title: string,
    description: string,
    cancel?: (event: MouseEvent) => void,
    confirm?: (event: MouseEvent) => void,
    text?: string,
    className?: string,
    id?: string,
}>(), {
    cancel: () => {
    },
    confirm: () => {
    }
});
</script>

<template>
    <button :id="id" :class="className" @click.prevent="isModalOpen = true">
        <span v-if="text">{{ text }}</span>
        <slot v-else/>
    </button>
    <Teleport to="body">
        <Transition
            enter-active-class="transition duration-200 ease-out"
            enter-from-class="opacity-0"
            enter-to-class="opacity-100"
            leave-active-class="transition duration-200 ease-in"
            leave-from-class="opacity-100"
            leave-to-class="opacity-0"
        >
            <div v-if="isModalOpen" class="modal modal-open">
                <div class="modal-box relative z-10">
                    <h2 class="text-lg font-bold">{{ title }}</h2>
                    <p class="py-4">{{ description }}</p>
                    <div class="modal-action">
                        <button class="btn btn-error" @click="(e: MouseEvent) => {cancel(e); isModalOpen = false}">
                            Cancel
                        </button>
                        <button class="btn btn-success" @click="(e: MouseEvent) => {confirm(e); isModalOpen = false}">
                            Yes
                        </button>
                    </div>
                </div>
                <div class="modal-backdrop bg-neutral/30 fixed inset-0" @click="(e) => {cancel(e); isModalOpen = false}"></div>
            </div>
        </Transition>
    </Teleport>
</template>
