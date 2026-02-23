<script setup lang="ts">
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, useForm, Link } from '@inertiajs/vue3';
import { ref } from 'vue';
import service from '@/Components/Toast/service';

defineProps<{
    status?: string;
}>();

const form = useForm({
    email: '',
});

const formErrors = ref<string[]>([]);
let toast = service();

const submit = () => {
    form.post(route('password.email'), {
        preserveScroll: true,
        onSuccess: () => {
            toast.success('Password reset link sent');
        },
        onError: (errors) => {
            formErrors.value = Object.values(errors || {});
        },
    });
};
</script>

<template>
    <div class="min-h-screen flex items-center justify-center relative overflow-hidden">
        <!-- Background image -->
        <div class="absolute inset-0">
            <div class="absolute inset-0" style="background-image: url('/img/bg.jpg'); background-size: cover; background-position: center;"></div>
            <!-- Optional dark overlay for contrast -->
            <div class="absolute inset-0"></div>
        </div>

        <!-- Forgot Password form container -->
        <div class="relative z-10 w-full max-w-md">
            <div class="bg-white/95 backdrop-blur-sm shadow-2xl rounded-2xl p-8 border border-white/20">
                <!-- Logo and title -->
                <div class="text-center mb-8">
                    <div class="mx-auto w-16 h-16 bg-gradient-to-r from-blue-600 to-indigo-600 rounded-full flex items-center justify-center mb-4">
                        <svg class="w-8 h-8 text-white" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M12 2L2 7v10c0 5.55 3.84 9.739 9 11 5.16-1.261 9-5.45 9-11V7l-10-5z"/>
                        </svg>
                    </div>
                    <h2 class="text-2xl font-bold text-gray-800 mb-2">Forgot Password</h2>
                    <p class="text-sm text-gray-600 mt-2">
                        Forgot your password? No problem. Just let us know your email address and we will email you a password reset link.
                    </p>
                </div>

                <Head title="Forgot Password" />

                <div v-if="status" class="mb-4 text-sm font-medium text-green-600 bg-green-50 p-3 rounded-lg border border-green-200">
                    {{ status }}
                </div>

                <div v-if="formErrors.length" class="mb-4 text-sm text-red-600 bg-red-50 p-3 rounded-lg border border-red-200">
                    <div v-for="(e,i) in formErrors" :key="i">{{ e }}</div>
                </div>

                <form @submit.prevent="submit" class="space-y-6">
                    <div>
                        <InputLabel for="email" value="Email" class="text-gray-700 font-medium" />

                        <div class="relative mt-2">
                            <span class="absolute inset-y-0 left-3 flex items-center pointer-events-none text-gray-400">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="currentColor">
                                    <path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-3.33 0-10 1.67-10 5v1h20v-1c0-3.33-6.67-5-10-5z" />
                                </svg>
                            </span>
                            <TextInput
                                id="email"
                                type="email"
                                class="block w-full pl-10 pr-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200"
                                v-model="form.email"
                                required
                                autofocus
                                autocomplete="username"
                                placeholder="Enter your email"
                            />
                        </div>

                        <InputError class="mt-2" :message="form.errors.email" />
                    </div>

                    <PrimaryButton
                        class="w-full bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white font-semibold py-3 px-4 rounded-lg transition-all duration-200 transform hover:scale-[1.02] focus:ring-4 focus:ring-blue-300"
                        :class="{ 'opacity-50 cursor-not-allowed': form.processing }"
                        :disabled="form.processing"
                    >
                        <span v-if="!form.processing">Email Password Reset Link</span>
                        <span v-else class="flex items-center justify-center">
                            <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            Sending...
                        </span>
                    </PrimaryButton>

                    <div class="flex items-center justify-center mt-4">
                        <Link
                            :href="route('login')"
                            class="text-sm text-blue-600 hover:text-blue-800 underline transition-colors duration-200"
                        >
                            Back to Login
                        </Link>
                    </div>
                </form>
            </div>
        </div>
    </div>
</template>
