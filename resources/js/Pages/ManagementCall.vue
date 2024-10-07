<script setup>
import { ref, computed } from "vue";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import { Head, useForm } from "@inertiajs/vue3";

// Générer des données fictives avec noms, prénoms français, pays et numéros de téléphone adaptés
const users = ref([
    {
        nom: "Dupont",
        prenom: "Jean",
        country: "France",
        telephone: "+33 6 12 34 56 78",
    },
    {
        nom: "Martin",
        prenom: "Marie",
        country: "Belgique",
        telephone: "+32 495 12 34 56",
    },
    {
        nom: "Schmidt",
        prenom: "Hans",
        country: "Allemagne",
        telephone: "+49 151 12345678",
    },
    {
        nom: "Bakker",
        prenom: "Sophie",
        country: "Pays-Bas",
        telephone: "+31 6 12345678",
    },
    {
        nom: "Smith",
        prenom: "John",
        country: "Angleterre",
        telephone: "+44 7700 900123",
    },
    {
        nom: "Durand",
        prenom: "Luc",
        country: "France",
        telephone: "+33 7 56 43 21 09",
    },
    {
        nom: "Moreau",
        prenom: "Claire",
        country: "Belgique",
        telephone: "+32 493 65 43 21",
    },
    {
        nom: "Keller",
        prenom: "Friedrich",
        country: "Allemagne",
        telephone: "+49 171 6543210",
    },
    {
        nom: "Jansen",
        prenom: "Emma",
        country: "Pays-Bas",
        telephone: "+31 6 98765432",
    },
    {
        nom: "Wilson",
        prenom: "Emily",
        country: "Angleterre",
        telephone: "+44 7700 123456",
    },
]);

// Terme de recherche
const searchTerm = ref("");

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
</script>

<template>
    <Head title="Gestion des appels téléphoniques" />

    <AuthenticatedLayout>
        <template #header>
            <h2
                class="font-semibold text-xl text-white bg-gray-800 leading-tight"
            >
                Gestion des appels téléphoniques
            </h2>
        </template>

        <div
            class="container mx-auto flex flex-col items-center justify-center px-4 sm:px-8 max-w-7xl mt-8"
        >
            <div class="w-full max-w-7xl mt-8 mx-auto px-6">
                <div class="flex justify-center p-4 px-3 py-10">
                    <div class="w-full max-w-4xl">
                        <div
                            class="bg-white shadow-md rounded-lg px-3 py-2 mb-4"
                        >
                            <div
                                class="block text-gray-700 text-lg font-semibold py-2 px-2"
                            >
                                Rechercher un utilisateur
                            </div>
                            <!-- BARRE DE RECHERCHE -->
                            <div
                                class="flex items-center bg-[rgb(237,242,247)] rounded-md mb-4"
                            >
                                <div class="pl-2">
                                    <svg
                                        class="fill-current text-gray-400 w-6 h-6"
                                        xmlns="http://www.w3.org/2000/svg"
                                        viewBox="0 0 24 24"
                                    >
                                        <path
                                            class="heroicon-ui"
                                            d="M16.32 14.9l5.39 5.4a1 1 0 0 1-1.42 1.4l-5.38-5.38a8 8 0 1 1 1.41-1.41zM10 16a6 6 0 1 0 0-12 6 6 0 0 0 0 12z"
                                        />
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
                            <!-- END BARRE DE RECHERCHE -->

                            <!-- LISTE DES UTILISATEURS FILTRÉE : AFFICHÉE SEULEMENT SI TERM DANS L'INPUT -->
                            <div v-if="searchTerm.length > 0" class="py-3 text-sm pt-1">
                                <div
                                    v-for="(user, index) in filteredUsers"
                                    :key="index"
                                    class="flex flex-wrap justify-between items-center cursor-pointer text-gray-700 hover:text-blue-400 hover:bg-blue-100 rounded-md px-2 py-2 my-2 w-full"
                                >
                                    <!-- Colonne Nom et Prénom (avec indicateur de statut à gauche) -->
                                    <div
                                        class="flex w-full sm:w-1/6 items-center mb-2 sm:mb-0"
                                    >
                                        <span
                                            class="bg-green-400 h-2 w-2 mr-4 rounded-full"
                                        ></span>
                                        <div class="font-medium text-left">
                                            {{ user.prenom }} {{ user.nom }}
                                        </div>
                                    </div>

                                    <!-- Colonne Numéro de Téléphone -->
                                    <div
                                        class="flex w-full sm:w-1/6 mb-2 sm:mb-0"
                                    >
                                        <div
                                            class="font-medium text-left w-full"
                                        >
                                            {{ user.telephone }}
                                        </div>
                                    </div>

                                    <!-- Colonne Pays -->
                                    <div class="flex w-full sm:w-1/6">
                                        <div
                                            class="text-sm font-normal text-left text-gray-500"
                                        >
                                            {{ user.country }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!-- END LISTE DES UTILISATEURS FILTRÉE -->
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<style scoped></style>
