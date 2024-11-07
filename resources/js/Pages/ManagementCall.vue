<script setup>
import { ref, computed, watch, onMounted } from "vue";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head } from "@inertiajs/vue3";
import UserDetails from "./ManagementCall/Partials/UserDetails.vue";
import UserList from "./ManagementCall/Partials/UserList.vue";
import AddUserModal from "./ManagementCall/Partials/AddUserModal.vue";
import FlashMessage from "@/Components/FlashMessage.vue";

// Les clients sont passés via Inertia
const props = defineProps(["clients", "currentUser"]);

// Les données des clients à partir des props
const users = ref(props.clients);

// Terme de recherche
const searchTerm = ref("");

// Utilisateur sélectionné
const selectedUser = ref(null);

// Nouvel utilisateur à ajouter
const newUser = ref({
    firstName: "",
    familyName: "",
    phone: "",
    email: "",
    company: "",
    companyvat: "",
    address: "",
    postalCode: "",
    locality: "",
    country: "",
});

// Variable pour indiquer si un nouvel utilisateur a été ajouté
const isNewUserAdded = ref(false);

// Liste pour stocker les utilisateurs filtrés par la recherche (limité à 5)
const filteredUsers = computed(() => {
    const searchLower = searchTerm.value.toLowerCase().replace(/\s+/g, "");

    if (!searchLower) {
        return [];
    }

    return users.value
        .filter((user) => {
            const normalize = (str) =>
                str ? str.toLowerCase().replace(/\s+/g, "") : "";

            const combinedName1 = normalize(user.firstName + user.familyName);
            const combinedName2 = normalize(user.familyName + user.firstName);

            const nameMatches =
                combinedName1.includes(searchLower) ||
                combinedName2.includes(searchLower);

            return (
                nameMatches ||
                (user.address
                    ? normalize(user.address).includes(searchLower)
                    : false) ||
                (user.locality
                    ? normalize(user.locality).includes(searchLower)
                    : false) ||
                (user.postalCode
                    ? normalize(user.postalCode).includes(searchLower)
                    : false) ||
                (user.country
                    ? normalize(user.country).includes(searchLower)
                    : false) ||
                (user.email
                    ? normalize(user.email).includes(searchLower)
                    : false) ||
                (user.phone
                    ? normalize(user.phone).includes(searchLower)
                    : false) ||
                (user.company
                    ? normalize(user.company).includes(searchLower)
                    : false)
            );
        })
        .slice(0, 5); // Limiter à 5 résultats maximum
});

// Liste pour stocker les 5 derniers utilisateurs consultés (à sauvegarder dans localStorage)
const recentUsers = ref([]);

// Charger les derniers utilisateurs consultés depuis localStorage lors du montage
onMounted(() => {
    const storedRecentUsers = localStorage.getItem("recentUsers");
    if (storedRecentUsers) {
        const parsedUsers = JSON.parse(storedRecentUsers);

        // Filtrer `recentUsers` pour conserver uniquement les utilisateurs existants
        recentUsers.value = parsedUsers.filter((user) =>
            users.value.some((dbUser) => dbUser.id === user.id)
        );

        // Mettre à jour le localStorage pour garder la liste synchronisée
        saveRecentUsersToLocalStorage();
    }
});

// Fonction pour sauvegarder recentUsers dans localStorage
const saveRecentUsersToLocalStorage = () => {
    localStorage.setItem("recentUsers", JSON.stringify(recentUsers.value));
};

// Fonction pour sélectionner un utilisateur et mettre à jour la liste des 5 derniers utilisateurs consultés
const selectUser = (user) => {
    axios
        .get(`/clients/${user.id}`)
        .then((response) => {
            selectedUser.value = response.data.user;

            // Ajouter l'utilisateur dans recentUsers si non présent
            if (!recentUsers.value.find((u) => u.id === user.id)) {
                recentUsers.value.unshift(user);
            }
            // Limiter recentUsers à 5 éléments
            if (recentUsers.value.length > 5) {
                recentUsers.value.pop();
            }
            // Sauvegarder recentUsers dans localStorage
            saveRecentUsersToLocalStorage();
        })
        .catch((error) => {
            console.error(
                "Erreur lors de la récupération des données de l'utilisateur :",
                error
            );
        });
};

// Réinitialiser selectedUser si searchTerm change
watch(searchTerm, () => {
    selectedUser.value = null;
});

// État du modal pour ajouter un utilisateur
const showModal = ref(false);

// Fonction pour ouvrir et fermer le modal
const toggleModal = () => {
    showModal.value = !showModal.value;
};

// Variable pour stocker l'ID du fournisseur récemment ajouté
const newUserId = ref(null);

// État pour afficher le message de confirmation de l'ajout d'un utilisateur
const showAddConfirmation = ref(false);

const handleAddUser = (newUser) => {
    users.value.push(newUser);
    selectedUser.value = newUser;
    newUserId.value = newUser.id;
    showAddConfirmation.value = true; 
    setTimeout(() => {
        showAddConfirmation.value = false; // Cache le message après 4 secondes
    }, 4000);
    toggleModal();
};

// Fonction pour mettre à jour l'utilisateur dans la liste
const updateUserInList = (updatedUser) => {
    const index = users.value.findIndex((user) => user.id === updatedUser.id);

    if (index !== -1) {
        users.value[index] = { ...updatedUser };
    }

    if (selectedUser.value && selectedUser.value.id === updatedUser.id) {
        selectedUser.value = users.value[index];
    }
};

const showDeleteConfirmation = ref(false);

// Fonction qui permet de supprimer un client
const handleUserDeleted = (deletedUserId) => {
    users.value = users.value.filter((user) => user.id !== deletedUserId);

    if (selectedUser.value && selectedUser.value.id === deletedUserId) {
        selectedUser.value = null;
    }

    recentUsers.value = recentUsers.value.filter(
        (user) => user.id !== deletedUserId
    );
    saveRecentUsersToLocalStorage();

    // Afficher le flash message
    showDeleteConfirmation.value = true;
};
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

        <FlashMessage
            v-if="showDeleteConfirmation"
            message="Le fournisseur a bien été supprimé."
            @close="showDeleteConfirmation = false"
        />

        <FlashMessage
            v-if="showAddConfirmation"
            message="Le fournisseur a bien été ajouté."
            @close="showAddConfirmation = false"
        />

        <div
            class="container mx-auto flex flex-col flex-grow items-center px-0 md:px-4 sm:px-8 max-w-7xl mt-2 md:mt-8 bg-white min-h-[800px]"
        >
            <div class="w-full max-w-7xl mt-0 mx-auto px-0">
                <div class="flex justify-center p-4 px-3 py-10">
                    <div class="w-full">
                        <div
                            class="bg-white shadow-md rounded-lg px-3 py-4 pb-6 mb-4"
                        >
                            <!-- Conteneur pour le texte "Rechercher un client" et le bouton -->
                            <div
                                class="flex flex-row justify-between items-center mb-1 md:mb-6"
                            >
                                <!-- Rechercher un client -->
                                <div
                                    class="text-gray-700 pt-4 md:pt-0 text-sm md:text-lg font-semibold mb-4 sm:mb-0"
                                >
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
                                    class="w-full rounded-md text-sm md:text-base bg-[rgb(237,242,247)] text-gray-500 leading-tight focus:outline-none focus:ring-0 border-none py-3 px-2 placeholder-gray-500 placeholder-opacity-60"
                                    id="search"
                                    type="text"
                                    placeholder="Tapez le numéro de téléphone, nom, e-mail ou pays pour chercher"
                                />
                            </div>

                            <!-- Affichage de la liste des utilisateurs filtrés (max 5) -->
                            <UserList
                                v-if="
                                    !selectedUser &&
                                    searchTerm &&
                                    filteredUsers.length > 0
                                "
                                :filteredUsers="filteredUsers"
                                :selectUser="selectUser"
                                :newUserId="newUserId"
                            />

                            <!-- Affichage des 5 derniers utilisateurs consultés -->
                            <div
                                v-if="!selectedUser && recentUsers.length > 0"
                                class="mt-8"
                            >
                                <h3 class="text-lg font-semibold text-gray-700">
                                    Derniers fournisseurs consultés
                                </h3>
                                <UserList
                                    :filteredUsers="recentUsers"
                                    :selectUser="selectUser"
                                    :isNewUserAdded="isNewUserAdded"
                                />
                            </div>

                            <!-- Détails de l'utilisateur sélectionné -->
                            <UserDetails
                                v-if="selectedUser"
                                :user="selectedUser"
                                @user-updated="updateUserInList"
                                @user-deleted="handleUserDeleted"
                            />

                            <!-- Modal d'ajout de client -->
                            <AddUserModal
                                :showModal="showModal"
                                :newUser="newUser"
                                @toggleModal="toggleModal"
                                @addUser="handleAddUser"
                            />
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
