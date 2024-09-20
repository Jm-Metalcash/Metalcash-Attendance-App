<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head, router, usePage } from "@inertiajs/vue3";
import { defineProps, onMounted } from "vue";

// Props pour recevoir les utilisateurs
const props = defineProps({
    users: Array,
});

// Récupérer les informations de la page
const page = usePage();

// Vérifier les rôles et rediriger si l'utilisateur n'est pas admin
onMounted(() => {
    if (!page.props.auth.roles.includes("Admin")) {
        router.get(route("dashboard"));
    }
});
</script>

<template>
    <Head title="Liste des employés" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-white bg-gray-800 leading-tight">
                Liste des employés
            </h2>
        </template>

        <div class="container mx-auto px-4 sm:px-8 max-w-7xl">
            <div class="py-8">
                <!-- Titre de la page -->
                <div>
                    <h2 class="text-2xl font-semibold leading-tight text-gray-800">
                        Liste des employés
                    </h2>
                </div>

                <!-- Table responsive -->
                <div class="-mx-4 sm:-mx-8 px-4 sm:px-8 py-4 overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead>
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Nom
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    E-mail
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Rôle
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Statut
                                </th>
                                <th class="text-center px-6 py-3 text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Action
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <!-- Boucle sur les utilisateurs pour afficher leurs infos -->
                            <tr v-for="user in users" :key="user.id">
                                <td class="px-6 py-4 whitespace-nowrap">{{ user.name }}</td>
                                <td class="px-6 py-4 whitespace-nowrap">{{ user.email }}</td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <!-- Afficher les rôles de l'utilisateur -->
                                    <span v-for="role in user.roles" :key="role.id">
                                        {{ role.name }}<span v-if="user.roles.length > 1 && role !== user.roles[user.roles.length - 1]">, </span>
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                        Active
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-center">
                                    <button
                                        class="px-4 py-2 text-sm text-white bg-gray-800 rounded-md hover:bg-gray-600 focus:outline-none focus:shadow-outline-blue active:bg-gray-600 transition duration-150 ease-in-out"
                                    >
                                        <i class="fa-regular fa-eye"></i> Voir profil
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<style scoped></style>
