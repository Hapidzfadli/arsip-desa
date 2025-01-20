<script setup>
defineProps({
    links: {
        type: Array,
        required: true,
    },
});
</script>

<template>
    <div v-if="links.length > 3" class="flex justify-center rounded-md -space-x-px">
        <template v-for="(link, key) in links" :key="key">
            <div
                v-if="link.url === null"
                class="relative inline-flex items-center px-4 py-2 text-sm font-medium text-gray-500 bg-white border border-gray-300 cursor-default"
                v-html="link.label"
            />
            <Link
                v-else
                :href="link.url"
                class="relative inline-flex items-center px-4 py-2 text-sm font-medium border"
                :class="[
                    link.active
                        ? 'z-10 bg-blue-50 border-blue-500 text-blue-600'
                        : 'bg-white border-gray-300 text-gray-500 hover:bg-gray-50'
                ]"
                v-html="link.label"
            />
        </template>
    </div>
</template>