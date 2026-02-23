<script setup lang="ts">
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import { ref } from 'vue';
import service from '@/Components/Toast/service';

const page = usePage();
const form = useForm({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
});
const formErrors = ref<string[]>([]);
let toast = service();

const submit = () => {
    form.post(route('register'), {
        preserveScroll: true,
        onSuccess: () => {
            toast.success('Registration successful');
        },
        onError: (errors) => {
            formErrors.value = Object.values(errors || {});
            toast.error('Registration failed');
        },
        onFinish: () => {
            form.reset('password', 'password_confirmation');
        },
    });
};
</script>

<template>
    <div class="min-h-screen flex items-center justify-center relative overflow-hidden">
        <!-- Warehouse-themed background -->
        <div class="absolute inset-0 bg-gradient-to-br from-gray-800 via-gray-700 to-slate-900">
            <!-- Warehouse structure -->
            <div class="absolute inset-0 opacity-15">
                <svg class="w-full h-full" viewBox="0 0 1200 800" preserveAspectRatio="xMidYMid slice">
                    <!-- Warehouse building silhouette -->
                    <rect x="100" y="300" width="300" height="200" fill="white" opacity="0.3"/>
                    <polygon points="100,300 250,200 400,300" fill="white" opacity="0.2"/>
                    
                    <rect x="500" y="250" width="400" height="250" fill="white" opacity="0.3"/>
                    <polygon points="500,250 700,150 900,250" fill="white" opacity="0.2"/>
                    
                    <rect x="950" y="320" width="200" height="180" fill="white" opacity="0.3"/>
                    <polygon points="950,320 1050,240 1150,320" fill="white" opacity="0.2"/>
                    
                    <!-- Warehouse doors -->
                    <rect x="150" y="400" width="40" height="100" fill="white" opacity="0.4"/>
                    <rect x="220" y="400" width="40" height="100" fill="white" opacity="0.4"/>
                    <rect x="290" y="400" width="40" height="100" fill="white" opacity="0.4"/>
                    
                    <rect x="550" y="350" width="50" height="150" fill="white" opacity="0.4"/>
                    <rect x="650" y="350" width="50" height="150" fill="white" opacity="0.4"/>
                    <rect x="750" y="350" width="50" height="150" fill="white" opacity="0.4"/>
                    
                    <!-- Loading docks -->
                    <rect x="120" y="480" width="20" height="20" fill="yellow" opacity="0.6"/>
                    <rect x="240" y="480" width="20" height="20" fill="yellow" opacity="0.6"/>
                    <rect x="570" y="480" width="20" height="20" fill="yellow" opacity="0.6"/>
                    <rect x="770" y="480" width="20" height="20" fill="yellow" opacity="0.6"/>
                    
                    <!-- Forklift silhouettes -->
                    <g opacity="0.2">
                        <rect x="450" y="460" width="30" height="15" fill="orange"/>
                        <rect x="445" y="450" width="10" height="25" fill="orange"/>
                        <circle cx="455" cy="480" r="5" fill="orange"/>
                        <circle cx="470" cy="480" r="5" fill="orange"/>
                    </g>
                    
                    <!-- Shipping containers -->
                    <rect x="50" y="520" width="80" height="40" fill="blue" opacity="0.3"/>
                    <rect x="350" y="520" width="80" height="40" fill="red" opacity="0.3"/>
                    <rect x="800" y="520" width="80" height="40" fill="green" opacity="0.3"/>
                </svg>
            </div>
            
            <!-- Floating warehouse elements -->
            <div class="absolute inset-0">
                <div class="animate-bounce absolute top-32 left-24 w-3 h-3 bg-yellow-400 rounded-sm opacity-40" style="animation-delay: 0s;"></div>
                <div class="animate-bounce absolute top-48 right-36 w-2 h-2 bg-orange-400 rounded-sm opacity-35" style="animation-delay: 1.5s;"></div>
                <div class="animate-bounce absolute bottom-40 left-1/4 w-2.5 h-2.5 bg-blue-400 rounded-sm opacity-30" style="animation-delay: 3s;"></div>
                <div class="animate-bounce absolute top-2/3 right-24 w-2 h-2 bg-green-400 rounded-sm opacity-40" style="animation-delay: 0.8s;"></div>
            </div>
        </div>

        <!-- Register form container -->
        <div class="relative z-10 w-full max-w-md">
            <div class="bg-white/95 backdrop-blur-sm shadow-2xl rounded-2xl p-8 border border-white/20">
                <!-- Logo and title -->
                <div class="text-center mb-8">
                    <div class="mx-auto w-16 h-16 bg-gradient-to-r from-orange-600 to-red-600 rounded-full flex items-center justify-center mb-4">
                        <svg class="w-8 h-8 text-white" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
                        </svg>
                    </div>
                    <h2 class="text-2xl font-bold text-gray-800 mb-2">Join Our Network</h2>
                    <p class="text-gray-600">Start managing your warehouse operations</p>
                </div>

                <Head title="Register" />

                <form @submit.prevent="submit" class="space-y-5">
            <div v-if="formErrors.length" class="mb-4 text-sm text-red-600 bg-red-50 p-3 rounded-lg border border-red-200">
                <div v-for="(e,i) in formErrors" :key="i">{{ e }}</div>
            </div>
            <div>
                <InputLabel for="name" value="Full Name" class="text-gray-700 font-medium" />

                <TextInput
                    id="name"
                    type="text"
                    class="mt-2 block w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-transparent transition-all duration-200"
                    v-model="form.name"
                    required
                    autofocus
                    autocomplete="name"
                    placeholder="Enter your full name"
                />

                <InputError class="mt-2" :message="form.errors.name" />
            </div>

            <div>
                <InputLabel for="email" value="Email Address" class="text-gray-700 font-medium" />

                <TextInput
                    id="email"
                    type="email"
                    class="mt-2 block w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-transparent transition-all duration-200"
                    v-model="form.email"
                    required
                    autocomplete="username"
                    placeholder="Enter your email address"
                />

                <InputError class="mt-2" :message="form.errors.email" />
            </div>

            <div>
                <InputLabel for="password" value="Password" class="text-gray-700 font-medium" />

                <TextInput
                    id="password"
                    type="password"
                    class="mt-2 block w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-transparent transition-all duration-200"
                    v-model="form.password"
                    required
                    autocomplete="new-password"
                    placeholder="Create a strong password"
                />

                <InputError class="mt-2" :message="form.errors.password" />
            </div>

            <div>
                <InputLabel
                    for="password_confirmation"
                    value="Confirm Password"
                    class="text-gray-700 font-medium"
                />

                <TextInput
                    id="password_confirmation"
                    type="password"
                    class="mt-2 block w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-transparent transition-all duration-200"
                    v-model="form.password_confirmation"
                    required
                    autocomplete="new-password"
                    placeholder="Confirm your password"
                />

                <InputError
                    class="mt-2"
                    :message="form.errors.password_confirmation"
                />
            </div>

            <PrimaryButton
                class="w-full bg-gradient-to-r from-orange-600 to-red-600 hover:from-orange-700 hover:to-red-700 text-white font-semibold py-3 px-4 rounded-lg transition-all duration-200 transform hover:scale-[1.02] focus:ring-4 focus:ring-orange-300"
                :class="{ 'opacity-50 cursor-not-allowed': form.processing }"
                :disabled="form.processing"
            >
                <span v-if="!form.processing">Create Account</span>
                <span v-else class="flex items-center justify-center">
                    <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    Creating account...
                </span>
            </PrimaryButton>

            <!-- Login link -->
            <div class="text-center pt-4 border-t border-gray-200">
                <p class="text-sm text-gray-600">
                    Already have an account? 
                    <Link :href="route('login')" class="text-orange-600 hover:text-orange-800 font-medium underline transition-colors duration-200">
                        Sign in here
                    </Link>
                </p>
            </div>
        </form>
            </div>
        </div>
    </div>
</template>
