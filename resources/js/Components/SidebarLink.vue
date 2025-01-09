<script setup>
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';

const props = defineProps({
    item: {
        type: Object,
        required: true
    },
    isCollapsed: {
        type: Boolean,
        default: false
    },
    onClick: {
        type: Function,
        default: () => {}
    }
});

const classes = computed(() => {
    return [
        route().current(props.item.href) 
            ? 'bg-gray-800 text-white' 
            : 'text-gray-300 hover:bg-gray-800 hover:text-white',
        'group flex items-center px-4 py-2 text-sm font-medium rounded-md transition-colors duration-150',
        props.isCollapsed ? 'justify-center' : ''
    ];
});
</script>

<template>
    <Link
        :href="route(item.href)"
        :method="item.method || 'get'"
        :class="classes"
        @click="onClick"
    >
        <component 
            :is="item.icon" 
            class="w-5 h-5 flex-shrink-0"
            :class="isCollapsed ? '' : 'mr-3'"
        />
        <span v-show="!isCollapsed">{{ item.name }}</span>
    </Link>
</template>