<script setup lang="ts">
import AppLogoIcon from '@/components/AppLogoIcon.vue';
import { home } from '@/routes';
import { Link } from '@inertiajs/vue3';

defineProps<{
    title?: string;
    description?: string;
}>();
</script>

<template>
    <!-- Modern gradient background with animated mesh -->
    <div class="relative flex min-h-svh flex-col items-center justify-center overflow-hidden bg-gradient-to-br from-blue-50 via-indigo-50 to-purple-50 dark:from-gray-900 dark:via-blue-900/20 dark:to-purple-900/20 p-6 md:p-10">
        <!-- Animated background elements -->
        <div class="absolute inset-0 overflow-hidden">
            <div class="absolute -top-40 -right-40 h-80 w-80 rounded-full bg-gradient-to-br from-blue-400/30 to-purple-400/30 blur-3xl animate-pulse"></div>
            <div class="absolute -bottom-40 -left-40 h-80 w-80 rounded-full bg-gradient-to-br from-indigo-400/30 to-pink-400/30 blur-3xl animate-pulse" style="animation-delay: 1s;"></div>
        </div>

        <!-- Content -->
        <div class="relative w-full max-w-md">
            <!-- Logo and branding -->
            <div class="mb-8 flex flex-col items-center gap-4">
                <Link
                    :href="home()"
                    class="group flex flex-col items-center gap-3"
                >
                    <div class="flex h-16 w-16 items-center justify-center rounded-2xl bg-gradient-to-br from-blue-600 to-purple-600 shadow-lg shadow-blue-500/50 transition-transform group-hover:scale-105">
                        <AppLogoIcon class="size-10 fill-current text-white" />
                    </div>
                    <span class="text-2xl font-bold bg-gradient-to-r from-blue-600 to-purple-600 bg-clip-text text-transparent">
                        Attendance System
                    </span>
                </Link>
            </div>

            <!-- Card with glassmorphism effect -->
            <div class="rounded-2xl border border-white/20 bg-white/80 backdrop-blur-xl shadow-2xl dark:border-gray-700/50 dark:bg-gray-800/80 p-8">
                <div class="mb-6 space-y-2 text-center">
                    <h1 class="text-2xl font-bold text-gray-900 dark:text-white">
                        {{ title }}
                    </h1>
                    <p class="text-sm text-gray-600 dark:text-gray-400">
                        {{ description }}
                    </p>
                </div>
                
                <slot />
            </div>

            <!-- Footer -->
            <p class="mt-6 text-center text-sm text-gray-600 dark:text-gray-400">
                Powered by Laravel & Vue.js
            </p>
        </div>
    </div>
</template>
