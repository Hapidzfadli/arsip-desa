<script setup>
import { Head, Link } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

defineProps({
    suratMasukCount: Number,
    suratKeluarCount: Number,
});
</script>

<template>
    <Head title="Beranda" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Beranda</h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <!-- Welcome Card -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                    <div class="p-6">
                        <h1 class="text-2xl font-bold text-gray-800">
                            Selamat Datang di SIPAS DESA
                        </h1>
                        <p class="mt-2 text-gray-600">
                            Sistem Informasi Pengarsipan Surat Desa
                        </p>
                    </div>
                </div>

                <!-- Statistics Grid -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
                    <!-- Surat Masuk Card -->
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-sm font-medium text-gray-600">Surat Masuk</p>
                                    <p class="text-3xl font-bold text-gray-900">{{ suratMasukCount }}</p>
                                </div>
                                <div class="p-3 bg-blue-100 rounded-full">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                    </svg>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Surat Keluar Card -->
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-sm font-medium text-gray-600">Surat Keluar</p>
                                    <p class="text-3xl font-bold text-gray-900">{{ suratKeluarCount }}</p>
                                </div>
                                <div class="p-3 bg-green-100 rounded-full">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8" />
                                    </svg>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Quick Actions -->
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg col-span-2">
                        <div class="p-6">
                            <h3 class="text-lg font-medium text-gray-900 mb-4">Aksi Cepat</h3>
                            <div class="grid grid-cols-2 gap-4">
                                <Link
                                    :href="route('surat-masuk.create')"
                                    class="inline-flex items-center justify-center px-4 py-2 bg-blue-50 border border-transparent rounded-md font-semibold text-sm text-blue-700 hover:bg-blue-100 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                                >
                                    Buat Surat Masuk
                                </Link>
                                <Link
                                    :href="route('surat-keluar.create')"
                                    class="inline-flex items-center justify-center px-4 py-2 bg-green-50 border border-transparent rounded-md font-semibold text-sm text-green-700 hover:bg-green-100 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500"
                                >
                                    Buat Surat Keluar
                                </Link>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>