<script setup>
import { Link } from '@inertiajs/vue3';
</script>

<template>
    <div class="min-h-screen relative overflow-hidden">
        <!-- Background Pattern -->
        <div class="absolute inset-0 bg-gradient-to-br from-blue-50 via-white to-blue-50">
            <!-- Decorative circles - Adjusted sizes -->
            <div class="absolute -top-8 -right-8 w-32 h-32 bg-blue-100 rounded-full opacity-50"></div>
            <div class="absolute top-1/4 -left-8 w-24 h-24 bg-blue-100 rounded-full opacity-30"></div>
            <div class="absolute bottom-1/3 right-0 w-28 h-28 bg-blue-100 rounded-full opacity-40"></div>
        </div>

        <!-- Main Content Container - Adjusted max-width -->
        <div class="min-h-screen flex flex-col items-center justify-center relative z-10 px-4 sm:px-6 lg:px-8">
            <!-- Content Card - Reduced max-width -->
            <div class="w-full max-w-sm">
                <div class="bg-white shadow-xl rounded-xl overflow-hidden transition-all duration-300 hover:shadow-2xl">
                    <!-- Card Border Accent -->
                    <div class="h-0.5 bg-gradient-to-r from-blue-500 to-blue-600"></div>
                    
                    <!-- Content Area - Reduced padding -->
                    <div class="px-4 py-6 sm:px-6">
                        <slot />
                    </div>
                </div>

                <!-- Footer - Adjusted spacing -->
                <div class="mt-6 text-center text-xs text-gray-500">
                    <p>© {{ new Date().getFullYear() }} Desa. All rights reserved.</p>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
.shadow-xl {
    transition: box-shadow 0.3s ease-in-out;
}

@keyframes float {
    0% { transform: translateY(0px); }
    50% { transform: translateY(-10px); }
    100% { transform: translateY(0px); }
}

.animate-float {
    animation: float 6s ease-in-out infinite;
}
</style>