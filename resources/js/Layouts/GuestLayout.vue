<script setup lang="ts">
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import { Link } from '@inertiajs/vue3';
</script>

<template>
    <div class="flex min-h-screen flex-col items-center bg-brand-navy pt-8 sm:justify-center sm:pt-0">
        <!-- Logo -->
        <div class="mb-6">
            <Link href="/">
                <ApplicationLogo :white="true" class="h-12 w-auto" />
            </Link>
        </div>

        <!-- Card -->
        <div class="w-full overflow-hidden bg-white px-6 py-6 shadow-xl sm:max-w-md sm:rounded-lg">
            <slot />
        </div>

        <!-- Footer -->
        <p class="mt-6 text-xs text-gray-500">
            Assessment submission by Adriaan van Niekerk for Axiomatic Consultants
        </p>
    </div>
</template>
