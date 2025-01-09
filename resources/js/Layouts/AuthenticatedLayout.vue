<script setup>
import { Bars3Icon, XMarkIcon, ChevronDoubleLeftIcon, ChevronDoubleRightIcon } from '@heroicons/vue/24/outline';
import { Link } from '@inertiajs/vue3';
import { navigationItems, adminItems, userMenuItems } from '../constants/navigation';
import { useSidebar } from '../composables/useSidebar';
import SidebarLink from '@/Components/SidebarLink.vue';
import '../../css/sidebar.css';

const { isOpen, isCollapsed, toggle, close, toggleCollapse } = useSidebar();
</script>

<template>
    <div class="min-h-screen bg-gray-100">
        <!-- Mobile sidebar backdrop -->
        <div 
            v-show="isOpen" 
            class="fixed inset-0 z-20 bg-black/50 lg:hidden"
            @click="close"
        ></div>

        <!-- Sidebar -->
        <aside
            :class="[
                'fixed inset-y-0 left-0 z-30 bg-gray-900 text-white transform transition-all duration-300 ease-in-out lg:translate-x-0',
                isOpen ? 'translate-x-0' : '-translate-x-full',
                isCollapsed ? 'w-20' : 'w-64'
            ]"
        >
            <!-- Sidebar header -->
            <div class="flex items-center justify-between h-16 px-4 bg-gray-800">
                <Link :href="route('users.index')" class="flex items-center space-x-2" :class="{ 'justify-center': isCollapsed }">
                    <img src="/img/logo-desa.png" class="w-8 h-8" alt="Logo" />
                    <span class="text-lg font-semibold" v-show="!isCollapsed">SIPAS DESA</span>
                </Link>
            </div>

            <!-- Toggle collapse button -->
            <button
                @click="toggleCollapse"
                class="hidden lg:flex absolute -right-3 top-10 bg-gray-800 rounded-full p-1 text-gray-400 hover:text-white focus:outline-none"
            >
                <ChevronDoubleLeftIcon v-if="!isCollapsed" class="w-5 h-5" />
                <ChevronDoubleRightIcon v-else class="w-5 h-5" />
            </button>

            <!-- Sidebar content -->
            <nav class="flex flex-col h-[calc(100vh-4rem)] sidebar-content">
                <div class="flex-1 space-y-1 px-2 py-4 overflow-y-auto">
                    <!-- Main navigation -->
                    <div
                        v-for="item in navigationItems"
                        :key="item.name"
                        class="relative group"
                    >
                        <SidebarLink
                            :item="item"
                            :isCollapsed="isCollapsed"
                            @click="close"
                        />
                        <!-- Tooltip for collapsed state -->
                        <div
                            v-if="isCollapsed"
                            class="absolute left-full top-0 ml-2 px-2 py-1 bg-gray-800 rounded-md text-sm whitespace-nowrap opacity-0 group-hover:opacity-100 transition-opacity z-50"
                        >
                            {{ item.name }}
                        </div>
                    </div>

                    <!-- Admin section -->
                    <template v-if="$page.props.auth.user.level === 'admin'">
                        <div class="border-t border-gray-700 my-4"></div>
                        <div
                            v-for="item in adminItems"
                            :key="item.name"
                            class="relative group"
                        >
                            <SidebarLink
                                :item="item"
                                :isCollapsed="isCollapsed"
                                @click="close"
                            />
                            <!-- Tooltip for collapsed state -->
                            <div
                                v-if="isCollapsed"
                                class="absolute left-full top-0 ml-2 px-2 py-1 bg-gray-800 rounded-md text-sm whitespace-nowrap opacity-0 group-hover:opacity-100 transition-opacity z-50"
                            >
                                {{ item.name }}
                            </div>
                        </div>
                    </template>
                </div>

                <!-- User section -->
                <div class="border-t border-gray-700 px-2 py-4">
                    <div class="px-4 py-2" v-if="!isCollapsed">
                        <p class="text-sm font-medium text-white">
                            {{ $page.props.auth.user.nama_lengkap }}
                        </p>
                        <p class="text-xs text-gray-400">
                            {{ $page.props.auth.user.level }}
                        </p>
                    </div>
                    <div
                        v-for="item in userMenuItems"
                        :key="item.name"
                        class="relative group"
                    >
                        <SidebarLink
                            :item="item"
                            :isCollapsed="isCollapsed"
                            @click="close"
                        />
                        <!-- Tooltip for collapsed state -->
                        <div
                            v-if="isCollapsed"
                            class="absolute left-full top-0 ml-2 px-2 py-1 bg-gray-800 rounded-md text-sm whitespace-nowrap opacity-0 group-hover:opacity-100 transition-opacity z-50"
                        >
                            {{ item.name }}
                        </div>
                    </div>
                </div>
            </nav>
        </aside>

        <!-- Main content -->
        <div :class="[
            'transition-all duration-300 ease-in-out',
            isCollapsed ? 'lg:pl-20' : 'lg:pl-64'
        ]">
            <!-- Mobile header -->
            <div class="sticky top-0 z-10 flex h-16 bg-white shadow lg:hidden">
                <button
                    @click="toggle"
                    class="px-4 text-gray-500 hover:text-gray-700 focus:outline-none"
                >
                    <Bars3Icon class="w-6 h-6" />
                </button>
                <div class="flex items-center px-4">
                    <span class="text-lg font-semibold">SIPAS DESA</span>
                </div>
            </div>

            <!-- Page content -->
            <main class="py-6">
                <header class="px-4 sm:px-6 lg:px-8" v-if="$slots.header">
                    <slot name="header" />
                </header>
                <div class="px-4 sm:px-6 lg:px-8">
                    <slot />
                </div>
            </main>
        </div>
    </div>
</template>