<script setup lang="ts">
import Checkbox from '@/Components/Checkbox.vue';
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import { ref } from 'vue';
import service from '@/Components/Toast/service';
import Snowfall from 'vue3-snowfall';

defineProps<{
    canResetPassword?: boolean;
    status?: string;
    isError?: boolean;
}>();

const page = usePage();
const form = useForm({
    email: '',
    password: '',
    remember: false,
});
const formErrors = ref<string[]>([]);
let toast = service();

const showPassword = ref(false);
const isDecember = new Date().getMonth() === 11;

const submit = () => {
    form.post(route('login'), {
        preserveScroll: true,
        onSuccess: () => {
            toast.success('Signed in successfully');
        },
        onError: (errors) => {
            formErrors.value = Object.values(errors || {});
            if (Object.values(errors).includes('User InActive')) {
                toast.error('User InActive');
            }
        },
        onFinish: () => {
            form.reset('password');
        },
    });
};
</script>

<template>
    <div class="min-h-screen flex items-center justify-center relative overflow-hidden login-page">
        <!-- Background image -->
        <div class="absolute inset-0">
            <div class="absolute inset-0" style="background-image: url('/img/bg.jpg'); background-size: cover; background-position: center;"></div>
            <!-- Optional dark overlay for contrast -->
            <div class="absolute inset-0"></div>
        </div>

        <!-- Login form container -->
        <div class="relative z-10 w-full max-w-md">
            <div class="bg-white/95 backdrop-blur-sm shadow-2xl rounded-2xl p-8 border border-white/20">
                <!-- Logo and title -->
                <div class="text-center mb-8">
                    <div class="mx-auto w-16 h-16 bg-gradient-to-r from-blue-600 to-indigo-600 rounded-full flex items-center justify-center mb-4">
                        <svg class="w-8 h-8 " fill="currentColor" viewBox="0 0 24 24">
                            <path d="M12 2L2 7v10c0 5.55 3.84 9.739 9 11 5.16-1.261 9-5.45 9-11V7l-10-5z"/>
                        </svg>
                    </div>
                    <h2 class="text-2xl font-bold text-gray-800 mb-2">Welcome Back</h2>
                </div>

                <Head title="Log in" />

                <div v-if="status" :class="isError ? 'mb-4 text-sm text-red-600 bg-red-50 p-3 rounded-lg border border-red-200' : 'mb-4 text-sm font-medium text-green-600 bg-green-50 p-3 rounded-lg border border-green-200'">
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


            </div>

            <div>
                <InputLabel for="password" value="Password" class="text-gray-700 font-medium" />

                <div class="relative mt-2">
                    <span class="absolute inset-y-0 left-3 flex items-center pointer-events-none text-gray-400">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M12 17a2 2 0 100-4 2 2 0 000 4zm6-7h-1V7a5 5 0 10-10 0v3H6a2 2 0 00-2 2v8a2 2 0 002 2h12a2 2 0 002-2v-8a2 2 0 00-2-2zm-9-3a3 3 0 116 0v3H9V7z" />
                        </svg>
                    </span>

                    <TextInput
                        id="password"
                        :type="showPassword ? 'text' : 'password'"
                        class="block w-full pl-10 pr-10 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200"
                        v-model="form.password"
                        required
                        autocomplete="current-password"
                        placeholder="Enter your password"
                    />

                    <button type="button" @click="showPassword = !showPassword" class="absolute inset-y-0 right-3 flex items-center text-gray-500 hover:text-gray-700">
                        <svg v-if="!showPassword" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M1 12s4-7 11-7 11 7 11 7-4 7-11 7S1 12 1 12z" />
                            <circle cx="12" cy="12" r="3" />
                        </svg>
                        <svg v-else xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M17.94 17.94A10.94 10.94 0 0112 20c-7 0-11-8-11-8a21.83 21.83 0 015.01-6.42" />
                            <path d="M1 1l22 22" />
                        </svg>
                    </button>
                </div>

                <InputError class="mt-2" :message="form.errors.password" />
            </div>

            <div class="flex items-center justify-between">
                <label class="flex items-center">
                    <Checkbox name="remember" v-model:checked="form.remember" class="rounded border-gray-300 text-blue-600 focus:ring-blue-500" />
                    <span class="ml-2 text-sm text-gray-600">Remember me</span>
                </label>

                <Link
                    v-if="canResetPassword"
                    :href="route('password.request')"
                    class="text-sm text-blue-600 hover:text-blue-800 underline transition-colors duration-200"
                >
                    Forgot password?
                </Link>
            </div>

            <PrimaryButton
                class="w-full bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white font-semibold py-3 px-4 rounded-lg transition-all duration-200 transform hover:scale-[1.02] focus:ring-4 focus:ring-blue-300"
                :class="{ 'opacity-50 cursor-not-allowed': form.processing }"
                :disabled="form.processing"
            >
                <span v-if="!form.processing">Sign In</span>
                <span v-else class="flex items-center justify-center">
                    <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    Signing in...
                </span>
            </PrimaryButton>


        </form>
            </div>
        </div>
        <Snowfall v-if="isDecember" />
    </div>
</template>

<style scoped>
/* Force light mode for login page - override dark mode styles */
[data-theme="dark"] .login-page,
[data-theme="dark"] .login-page * {
  background-color: initial !important;
  color: initial !important;
  border-color: initial !important;
}

[data-theme="dark"] .login-page {
  background: #ffffff !important;
}

[data-theme="dark"] .login-page .bg-white\/95 {
  background-color: rgba(255, 255, 255, 0.95) !important;
  color: #1f2937 !important;
  border-color: rgba(255, 255, 255, 0.2) !important;
}

[data-theme="dark"] .login-page h2 {
  color: #1f2937 !important;
}

[data-theme="dark"] .login-page input,
[data-theme="dark"] .login-page .border {
  background-color: #ffffff !important;
  border-color: #d1d5db !important;
  color: #1f2937 !important;
}

[data-theme="dark"] .login-page .text-gray-700,
[data-theme="dark"] .login-page .text-gray-800 {
  color: #1f2937 !important;
}

[data-theme="dark"] .login-page .text-gray-400 {
  color: #6b7280 !important;
}

[data-theme="dark"] .login-page .bg-red-50 {
  background-color: #fee2e2 !important;
}

[data-theme="dark"] .login-page .bg-green-50 {
  background-color: #ecfdf5 !important;
}
</style>
