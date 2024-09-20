<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head } from "@inertiajs/vue3";


</script>

<template>
    <Head title="Liste des employés" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-white bg-gray-800 leading-tight">
                Liste des employés
            </h2>
        </template>

        <div class="text-center text-4xl mt-44">
            En cours de construction...
        </div>
    </AuthenticatedLayout>
</template>

<style scoped>
</style>
