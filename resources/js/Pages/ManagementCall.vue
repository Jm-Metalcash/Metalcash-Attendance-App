<script setup>
import { ref, computed, watch } from "vue";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head } from "@inertiajs/vue3";

// Générer des données fictives avec noms, prénoms français, pays et numéros de téléphone adaptés
const users = ref([
    {
        nom: "Dupont",
        prenom: "Jean",
        country: "France",
        telephone: "+33 6 12 34 56 78",
        email: "jean.dupont@example.com",
        adresse: "123 Rue de Paris, Paris",
    },
    {
        nom: "Martin",
        prenom: "Marie",
        country: "Belgique",
        telephone: "+32 495 12 34 56",
        email: "marie.martin@example.com",
        adresse: "12 Rue Royale, Bruxelles",
    },
    // Ajout des autres utilisateurs...
]);

// Terme de recherche
const searchTerm = ref("");

// Utilisateur sélectionné
const selectedUser = ref(null);

// Propriété calculée pour filtrer les utilisateurs
const filteredUsers = computed(() => {
    return users.value.filter((user) => {
        const searchLower = searchTerm.value.toLowerCase();
        return (
            user.nom.toLowerCase().includes(searchLower) ||
            user.prenom.toLowerCase().includes(searchLower) ||
            user.country.toLowerCase().includes(searchLower) ||
            user.telephone.toLowerCase().includes(searchLower)
        );
    });
});

// Fonction pour sélectionner un utilisateur
const selectUser = (user) => {
    selectedUser.value = user;
};

// Réinitialiser selectedUser si searchTerm change
watch(searchTerm, () => {
    selectedUser.value = null;
});
</script>

<template>
    <Head title="Gestion des appels téléphoniques" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-white bg-gray-800 leading-tight">
                Gestion des appels téléphoniques
            </h2>
        </template>

        <div class="container mx-auto flex flex-col items-center justify-center px-4 sm:px-8 max-w-7xl mt-8">
            <div class="w-full max-w-7xl mt-0 mx-auto px-6">
                <div class="flex justify-center p-4 px-3 py-10">
                    <div class="w-full">
                        <div class="bg-white shadow-md rounded-lg px-3 py-4 pb-6 mb-4">
                            <div class="block text-gray-700 text-lg font-semibold py-2 px-2">
                                Rechercher un utilisateur
                            </div>

                            <!-- BARRE DE RECHERCHE -->
                            <div class="flex items-center bg-[rgb(237,242,247)] rounded-md">
                                <div class="pl-2">
                                    <svg class="fill-current text-gray-400 w-6 h-6" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
                                        <path class="heroicon-ui" d="M16.32 14.9l5.39 5.4a1 1 0 0 1-1.42 1.4l-5.38-5.38a8 8 0 1 1 1.41-1.41zM10 16a6 6 0 1 0 0-12 6 6 0 0 0 0 12z" />
                                    </svg>
                                </div>
                                <input
                                    v-model="searchTerm"
                                    class="w-full rounded-md bg-[rgb(237,242,247)] text-gray-500 leading-tight focus:outline-none focus:ring-0 border-none py-2 px-2 placeholder-gray-500 placeholder-opacity-60"
                                    id="search"
                                    type="text"
                                    placeholder="Tapez le nom, numéro ou pays de l'utilisateur"
                                />
                            </div>

                            <!-- LISTE DES UTILISATEURS FILTRÉE -->
                            <div v-if="searchTerm.length > 0" class="py-3 text-sm">
                                <div
                                    v-for="(user, index) in filteredUsers"
                                    :key="index"
                                    @click="selectUser(user)"
                                    class="flex flex-wrap justify-between items-center cursor-pointer text-gray-700 hover:text-blue-400 hover:bg-blue-100 rounded-md px-2 py-2 my-2 w-full"
                                >
                                    <!-- Colonne Nom et Prénom (avec indicateur de statut à gauche) -->
                                    <div class="flex w-full sm:w-1/6 items-center mb-2 sm:mb-0">
                                        <span class="bg-green-400 h-2 w-2 mr-4 rounded-full"></span>
                                        <div class="font-medium text-left">{{ user.prenom }} {{ user.nom }}</div>
                                    </div>

                                    <!-- Colonne Numéro de Téléphone -->
                                    <div class="flex w-full sm:w-1/6 mb-2 sm:mb-0">
                                        <div class="font-medium text-left w-full">{{ user.telephone }}</div>
                                    </div>

                                    <!-- Colonne Pays -->
                                    <div class="flex w-full sm:w-1/6">
                                        <div class="text-sm font-normal text-left text-gray-500">{{ user.country }}</div>
                                    </div>
                                </div>
                            </div>

                            <!-- Détails de l'utilisateur sélectionné -->
                            <div v-if="selectedUser" class="bg-white overflow-hidden shadow rounded-lg border mt-8">
                                <div class="px-4 py-5 sm:px-6">
                                    <p class="mt-1 max-w-2xl text-sm text-gray-500 font-bold">
                                        Informations générales sur l'utilisateur.
                                    </p>
                                </div>
                                <div class="border-t border-gray-200 px-4 py-5 sm:p-0">
                                    <dl class="sm:divide-y sm:divide-gray-200">
                                        <div class="py-3 sm:py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                                            <dt class="text-sm font-medium text-gray-500">
                                                Nom complet
                                            </dt>
                                            <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                                                {{ selectedUser.prenom }} {{ selectedUser.nom }}
                                            </dd>
                                        </div>
                                        <div class="py-3 sm:py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                                            <dt class="text-sm font-medium text-gray-500">
                                                E-mail
                                            </dt>
                                            <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                                                {{ selectedUser.email }}
                                            </dd>
                                        </div>
                                        <div class="py-3 sm:py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                                            <dt class="text-sm font-medium text-gray-500">
                                                Numéro de téléphone
                                            </dt>
                                            <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                                                {{ selectedUser.telephone }}
                                            </dd>
                                        </div>
                                        <div class="py-3 sm:py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                                            <dt class="text-sm font-medium text-gray-500">
                                                Adresse
                                            </dt>
                                            <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                                                {{ selectedUser.adresse }}
                                            </dd>
                                        </div>
                                    </dl>
                                </div>
                            </div>
                            <!-- END Détails utilisateur -->
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<style scoped></style>
