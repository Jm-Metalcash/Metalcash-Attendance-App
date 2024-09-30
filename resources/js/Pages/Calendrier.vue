<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head } from "@inertiajs/vue3";


</script>

<template>
    <Head title="Calendrier" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-white bg-gray-800 leading-tight">
                Calendrier
            </h2>
        </template>

        <div class="text-center text-4xl mt-44">
            Calendrier en cours de développement...
        </div>
    </AuthenticatedLayout>
</template>

<style scoped>
</style>
