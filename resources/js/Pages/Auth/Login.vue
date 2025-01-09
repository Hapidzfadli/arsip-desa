<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';

const form = useForm({
    username: '',
    password: '',
});

const submit = () => {
    form.post(route('login'), {
        onFinish: () => form.reset('password'),
        onError: (errors) => console.error('Login errors:', errors),
        onSuccess: () => console.log('Login successful')
    });
};
</script>

<template>
    <GuestLayout>
        <Head title="Login SIPAS DESA" />

        <div v-if="form.errors.username || form.errors.password" 
             class="mb-4 bg-red-50 border-l-4 border-red-500 p-4">
            <div class="text-sm text-red-700">
                <p v-if="form.errors.username">{{ form.errors.username }}</p>
                <p v-if="form.errors.password">{{ form.errors.password }}</p>
            </div>
        </div>

        <!-- Logo and Title Section - Reduced spacing -->
        <div class="flex flex-col items-center mb-6">
            <!-- Smaller logo size -->
            <img
                class="w-16 h-16 md:w-20 md:h-20 object-contain"
                src="/img/logo-desa.png"
                alt="Logo Desa"
            />
            <!-- Adjusted title spacing and size -->
            <h2 class="mt-4 text-xl md:text-2xl font-bold text-gray-900 text-center">
                SIPAS DESA
            </h2>
            <!-- Smaller subtitle with reduced padding -->
            <p class="mt-1 text-xs md:text-sm text-gray-600 text-center max-w-xs mx-auto">
                Sistem Informasi Pengarsipan Surat Desa
            </p>
        </div>

        <!-- Login Form - Reduced spacing between elements -->
        <form @submit.prevent="submit" class="space-y-4">
            <div class="space-y-1">
                <InputLabel for="username" value="Username" class="text-sm" />
                <TextInput
                    id="username"
                    type="text"
                    v-model="form.username"
                    class="block w-full text-sm"
                    required
                    autofocus
                    autocomplete="username"
                    placeholder="Masukkan username anda"
                />
                <InputError :message="form.errors.username" class="mt-1" />
            </div>

            <div class="space-y-1">
                <InputLabel for="password" value="Password" class="text-sm" />
                <TextInput
                    id="password"
                    type="password"
                    v-model="form.password"
                    class="block w-full text-sm"
                    required
                    autocomplete="current-password"
                    placeholder="Masukkan password anda"
                />
                <InputError :message="form.errors.password" class="mt-1" />
            </div>

            <!-- Modified button with loading state -->
            <div>
                <PrimaryButton
                    class="w-full justify-center py-2 text-sm"
                    :class="{ 'opacity-50 cursor-not-allowed': form.processing }"
                    :disabled="form.processing"
                >
                    <span v-if="form.processing">
                        <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white inline-block" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        Memproses...
                    </span>
                    <span v-else>Login</span>
                </PrimaryButton>
            </div>
        </form>

        <!-- Footer Text - Reduced margin -->
        <p class="mt-6 text-center text-xs text-gray-500">
            SIPAS DESA v1.0 - Sistem Informasi Desa
        </p>
    </GuestLayout>
</template>