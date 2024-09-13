<script setup>
import { Link } from '@inertiajs/vue3';
import { computed } from 'vue';

const logoUrl = computed(() => '/images/logo-HD.png');
</script>

<template>
    <div class="min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0 bg-gray-50">
        <div>
            <Link href="/">
                <img :src="logoUrl" alt="Logo Metalcash" class="w-44 h-44">
            </Link>
        </div>

        <div
            class="w-full sm:max-w-md mt-6 px-6 py-4 bg-white shadow-md overflow-hidden sm:rounded-lg"
        >
            <slot />
        </div>
    </div>
</template>
