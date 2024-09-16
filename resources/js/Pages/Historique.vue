<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head } from "@inertiajs/vue3";


</script>

<template>
    <Head title="Historique" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-white bg-gray-800 leading-tight">
                Historique des heures de travail
            </h2>
        </template>
    </AuthenticatedLayout>
</template>

<style scoped>
</style>
