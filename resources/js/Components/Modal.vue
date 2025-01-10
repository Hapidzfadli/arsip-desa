<script setup>
import { onMounted, onUnmounted, watch, computed } from 'vue';
import { XMarkIcon } from '@heroicons/vue/24/outline';

const props = defineProps({
    show: Boolean,
    title: String,
    maxWidth: {
        type: String,
        default: '2xl'
    }
});

const emit = defineEmits(['close']);

// Handle escape key to close modal
const closeOnEscape = (e) => {
    if (e.key === 'Escape' && props.show) {
        emit('close');
    }
};

// Prevent body scroll when modal is open
watch(() => props.show, (value) => {
    if (value) {
        document.body.style.overflow = 'hidden';
    } else {
        document.body.style.overflow = 'auto';
    }
});

onMounted(() => {
    document.addEventListener('keydown', closeOnEscape);
});

onUnmounted(() => {
    document.removeEventListener('keydown', closeOnEscape);
    document.body.style.overflow = 'auto';
});

const maxWidthClass = computed(() => {
    return {
        'sm': 'sm:max-w-sm',
        'md': 'sm:max-w-md',
        'lg': 'sm:max-w-lg',
        'xl': 'sm:max-w-xl',
        '2xl': 'sm:max-w-2xl',
    }[props.maxWidth];
});
</script>

<template>
    <Teleport to="body">
        <transition
            enter-active-class="ease-out duration-300"
            enter-from-class="opacity-0"
            enter-to-class="opacity-100"
            leave-active-class="ease-in duration-200"
            leave-from-class="opacity-100"
            leave-to-class="opacity-0"
        >
            <div v-show="show" class="fixed inset-0 z-50 overflow-y-auto" @click="$emit('close')">
                <!-- Backdrop -->
                <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity"></div>

                <!-- Modal Panel -->
                <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
                    <transition
                        enter-active-class="ease-out duration-300"
                        enter-from-class="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                        enter-to-class="opacity-100 translate-y-0 sm:scale-100"
                        leave-active-class="ease-in duration-200"
                        leave-from-class="opacity-100 translate-y-0 sm:scale-100"
                        leave-to-class="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                    >
                        <div
                            v-show="show"
                            class="relative transform rounded-lg bg-white text-left shadow-xl transition-all sm:my-8 w-full"
                            :class="[maxWidthClass]"
                            @click.stop
                        >
                            <!-- Modal Header -->
                            <div class="border-b border-gray-200 px-4 py-3 sm:px-6">
                                <div class="flex items-center justify-between">
                                    <h3 class="text-lg font-medium text-gray-900">
                                        {{ title }}
                                    </h3>
                                    <button
                                        type="button"
                                        class="rounded-md text-gray-400 hover:text-gray-500 focus:outline-none"
                                        @click="$emit('close')"
                                    >
                                        <XMarkIcon class="h-6 w-6" />
                                    </button>
                                </div>
                            </div>

                            <!-- Modal Content -->
                            <div class="px-4 py-5 sm:p-6">
                                <slot></slot>
                            </div>

                            <!-- Modal Footer -->
                            <div v-if="$slots.footer" class="bg-gray-50 px-4 py-3 sm:px-6 flex justify-end space-x-3 rounded-b-lg">
                                <slot name="footer"></slot>
                            </div>
                        </div>
                    </transition>
                </div>
            </div>
        </transition>
    </Teleport>
</template>