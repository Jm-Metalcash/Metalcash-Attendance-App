<script setup>
import { ref, computed, watch } from "vue";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head } from "@inertiajs/vue3";
import UserDetails from "./ManagementCall/Partials/UserDetails.vue";
import UserList from "./ManagementCall/Partials/UserList.vue";
import AddUserModal from "./ManagementCall/Partials/AddUserModal.vue"; 

// Générer des données fictives avec noms, prénoms, pays, et adresses adaptés
const users = ref([
    {
        nom: "Dupont",
        prenom: "Jean",
        country: "France",
        telephone: "+33 6 12 34 56 78",
        email: "jean.dupont@example.com",
        adresse: {
            rue: "123 Rue de Paris",
            codePostal: "75001",
            localite: "Paris",
            pays: "France",
        },
        historique: [
            { date: "2024-01-10", type: "Or", poids: "2.5" },
            { date: "2024-02-15", type: "Argent", poids: "1.2" },
            { date: "2024-03-05", type: "Platine", poids: "0.8" },
        ],
    },
    {
        nom: "Martin",
        prenom: "Marie",
        country: "Belgique",
        telephone: "+32 495 12 34 56",
        email: "marie.martin@example.com",
        adresse: {
            rue: "12 Rue Royale",
            codePostal: "1000",
            localite: "Bruxelles",
            pays: "Belgique",
        },
        historique: [
            { date: "2024-02-20", type: "Or", poids: "3.1" },
            { date: "2024-04-12", type: "Argent", poids: "2.0" },
        ],
    },
    {
        nom: "Schneider",
        prenom: "Franz",
        country: "Allemagne",
        telephone: "+49 151 23456789",
        email: "franz.schneider@example.com",
        adresse: {
            rue: "10 Hauptstraße",
            codePostal: "10115",
            localite: "Berlin",
            pays: "Allemagne",
        },
        historique: [
            { date: "2024-03-18", type: "Or", poids: "1.8" },
            { date: "2024-05-22", type: "Argent", poids: "3.4" },
        ],
    },
    {
        nom: "Jansen",
        prenom: "Emma",
        country: "Pays-Bas",
        telephone: "+31 6 12345678",
        email: "emma.jansen@example.com",
        adresse: {
            rue: "34 Nieuwezijds Voorburgwal",
            codePostal: "1012",
            localite: "Amsterdam",
            pays: "Pays-Bas",
        },
        historique: [
            { date: "2024-02-10", type: "Platine", poids: "0.7" },
            { date: "2024-04-17", type: "Or", poids: "2.2" },
        ],
    },
    {
        nom: "Smith",
        prenom: "John",
        country: "Angleterre",
        telephone: "+44 7700 900123",
        email: "john.smith@example.com",
        adresse: {
            rue: "45 Oxford Street",
            codePostal: "W1D 1BS",
            localite: "Londres",
            pays: "Angleterre",
        },
        historique: [
            { date: "2024-03-12", type: "Or", poids: "4.0" },
            { date: "2024-05-14", type: "Argent", poids: "1.7" },
        ],
    },
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
            user.telephone.toLowerCase().includes(searchLower) ||
            user.email.toLowerCase().includes(searchLower) ||
            user.adresse.rue.toLowerCase().includes(searchLower) ||
            user.adresse.codePostal.toLowerCase().includes(searchLower) ||
            user.adresse.localite.toLowerCase().includes(searchLower) ||
            user.adresse.pays.toLowerCase().includes(searchLower)
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

// État du modal
const showModal = ref(false);

// Fonction pour ouvrir et fermer le modal
const toggleModal = () => {
    showModal.value = !showModal.value;
};

// Nouvel utilisateur à ajouter
const newUser = ref({
    nom: "",
    prenom: "",
    telephone: "",
    email: "",
    adresse: {
        rue: "",
        codePostal: "",
        localite: "",
        pays: "",
    },
    historique: [],
});

// Fonction pour ajouter un nouvel utilisateur
const addUser = () => {
    users.value.push({ ...newUser.value });
    newUser.value = {
        nom: "",
        prenom: "",
        telephone: "",
        email: "",
        adresse: {
            rue: "",
            codePostal: "",
            localite: "",
            pays: "",
        },
        historique: [],
    };
    toggleModal();
};
</script>

<template>
    <Head title="Gestion des appels téléphoniques" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-white bg-gray-800 leading-tight">
                Gestion des appels téléphoniques
            </h2>
        </template>

        <div class="container mx-auto flex flex-col items-center justify-center px-0 md:px-4 sm:px-8 max-w-7xl mt-2 md:mt-8">
            <div class="w-full max-w-7xl mt-0 mx-auto px-0">
                <div class="flex justify-center p-4 px-3 py-10">
                    <div class="w-full">
                        <div class="bg-white shadow-md rounded-lg px-3 py-4 pb-6 mb-4">
                            <!-- Conteneur pour le texte "Rechercher un client" et le bouton -->
                            <div class="flex flex-row justify-between items-center mb-1 md:mb-6">
                                <!-- Rechercher un client -->
                                <div class="text-gray-700 pt-4 md:pt-0 text-sm md:text-lg font-semibold mb-4 sm:mb-0">
                                    Rechercher un fournisseur
                                </div>
                                <!-- Bouton Ajouter un client -->
                                <button
                                    @click="toggleModal"
                                    class="text-xs md:text-sm bg-gray-900 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded"
                                >
                                    Encoder un appel
                                </button>
                            </div>

                            <!-- BARRE DE RECHERCHE -->
                            <div class="flex items-center bg-[rgb(237,242,247)] rounded-md mb-4">
                                <div class="pl-2">
                                    <svg class="fill-current text-gray-400 w-6 h-6" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
                                        <path class="heroicon-ui" d="M16.32 14.9l5.39 5.4a1 1 0 0 1-1.42 1.4l-5.38-5.38a8 8 0 1 1 1.41-1.41zM10 16a6 6 0 1 0 0-12 6 6 0 0 0 0 12z" />
                                    </svg>
                                </div>
                                <input
                                    v-model="searchTerm"
                                    class="w-full rounded-md text-sm md:text-base bg-[rgb(237,242,247)] text-gray-500 leading-tight focus:outline-none focus:ring-0 border-none py-3 px-2 placeholder-gray-500 placeholder-opacity-60"
                                    id="search"
                                    type="text"
                                    placeholder="Tapez le numéro de téléphone, nom, e-mail ou pays pour chercher"
                                />
                            </div>

                            <!-- Affichage de la liste d'utilisateurs -->
                            <UserList v-if="!selectedUser && searchTerm.length > 0" :filteredUsers="filteredUsers" :selectUser="selectUser" />

                            <!-- Détails de l'utilisateur sélectionné -->
                            <UserDetails v-if="selectedUser" :user="selectedUser" />

                            <!-- Modal d'ajout de client -->
                            <AddUserModal :showModal="showModal" :newUser="newUser" @toggleModal="toggleModal" @addUser="addUser" />
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<style scoped>
/* Pour s'assurer que le bouton reste à droite en version mobile également */
@media (max-width: 640px) {
    .flex-row {
        flex-direction: row;
    }
}
</style>


<style scoped></style>
