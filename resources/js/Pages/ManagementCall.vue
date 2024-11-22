<script setup>
import { ref, computed, watch, onMounted } from "vue";
import { router as Inertia } from "@inertiajs/vue3";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head, usePage } from "@inertiajs/vue3";
import UserDetails from "./ManagementCall/Partials/UserDetails.vue";
import UserList from "./ManagementCall/Partials/UserList.vue";
import AddUserModal from "./ManagementCall/Partials/AddUserModal.vue";
import FlashMessage from "@/Components/FlashMessage.vue";

const page = usePage();

// Les clients sont passés via Inertia
const props = defineProps(["clients", "currentUser", "selectedUser"]);

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
    axios.get("/recent-views").then((response) => {
        recentUsers.value = response.data.map((view) => ({
            client: view.client,
            viewedBy: view.viewedBy || "Inconnu",
            viewedAt: view.viewedAt || "Date inconnue",
        }));
    });

    if (props.selectedUser) {
        selectedUser.value = props.selectedUser;
    }
});

// Watch pour mettre à jour selectedUser lorsque props.selectedUser change
watch(
    () => props.selectedUser,
    (newSelectedUser) => {
        selectedUser.value = newSelectedUser;
    }
);

// Fonction pour sélectionner un utilisateur et mettre à jour la liste des 5 derniers utilisateurs consultés
const selectUser = (user) => {
    if (!user || !user.id) {
        console.error("Utilisateur invalide ou ID manquant.", user);
        return;
    }

    Inertia.visit(route("management-call", { user: user.id }), {
        method: "get",
        preserveScroll: true, // Retirer preserveState pour permettre la mise à jour des props
    });
};

const closeUserDetails = () => {
    selectedUser.value = null;

    // Mettre à jour l'URL pour revenir à /gestion-appels-telephoniques
    Inertia.visit(route("management-call"), {
        method: "get",
        preserveScroll: true,
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

// Les 20 derniers clients ajoutés
const recentAddedUsers = computed(() => {
    return users.value
        .filter((user) => user.created_at)
        .sort((a, b) => new Date(b.created_at) - new Date(a.created_at))
        .slice(0, 20);
});

// Les 10 derniers clients modifiés
const recentModifiedUsers = computed(() => {
    return users.value
        .filter(
            (user) =>
                user.updated_at &&
                user.created_at &&
                new Date(user.updated_at) > new Date(user.created_at)
        )
        .sort((a, b) => new Date(b.updated_at) - new Date(a.updated_at))
        .slice(0, 10);
});

// Fonction pour formater les dates
const formatDate = (date) => {
    if (!date) return "Non disponible";
    const d = new Date(date);
    return d.toLocaleString("fr-FR", {
        day: "2-digit",
        month: "2-digit",
        year: "numeric",
        hour: "2-digit",
        minute: "2-digit",
    });
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
                            <div
                                v-if="
                                    !selectedUser &&
                                    searchTerm &&
                                    filteredUsers.length > 0
                                "
                            >
                                <h3 class="text-lg font-semibold text-gray-700">
                                    Résultat de la recherche :
                                </h3>

                                <UserList
                                    :filteredUsers="filteredUsers"
                                    :selectUser="selectUser"
                                    :newUserId="newUserId"
                                />
                            </div>

                            <!-- Historique des modifications des fournisseurs -->
                            <div v-if="!searchTerm" class="client-manage-panel">
                                <!-- Affichage des 5 derniers utilisateurs consultés -->
                                <div
                                    v-if="
                                        !selectedUser && recentUsers.length > 0
                                    "
                                    class="mt-8 border p-4 bg-zinc-0"
                                >
                                    <h3
                                        class="text-lg font-semibold text-gray-700"
                                    >
                                        Derniers fournisseurs consultés
                                    </h3>

                                    <!-- Utilisation du composant UserList -->
                                    <UserList
                                        :filteredUsers="
                                            recentUsers.map((view) => ({
                                                ...view.client, // Inclure les détails du client
                                                viewedBy:
                                                    view.viewedBy || 'Inconnu', // Nom de l'utilisateur ayant consulté
                                                viewedAt:
                                                    view.viewedAt ||
                                                    'Date inconnue', // Date de consultation
                                            }))
                                        "
                                        :selectUser="selectUser"
                                    />
                                </div>

                                <!-- PANEL ADMIN -->
                                <div
                                    v-if="
                                        recentAddedUsers.length > 0 &&
                                        page.props.auth.roles &&
                                        (page.props.auth.roles.includes(
                                            'Admin'
                                        ) ||
                                            page.props.auth.roles.includes(
                                                'Informatique'
                                            ))
                                    "
                                    class="admin-panel-clients"
                                >
                                    <h2
                                        class="mt-20 text-xl p-2 font-bold w-full bg-[rgb(0,86,146)] text-white"
                                    >
                                        <i
                                            class="fa-solid fa-lock pl-2 pr-1"
                                        ></i>
                                        Administration
                                    </h2>

                                    <!-- Affiche les 20 derniers fournisseurs ajoutés -->
                                    <div
                                        v-if="!selectedUser"
                                        class="mt-0 border p-4 bg-zinc-50"
                                    >
                                        <h3
                                            class="text-lg font-semibold text-gray-700"
                                        >
                                            Nouveaux fournisseurs ajoutés
                                        </h3>
                                        <div
                                            v-for="(
                                                user, index
                                            ) in recentAddedUsers"
                                            :key="user.id || index"
                                            @click="selectUser(user)"
                                            class="text-sm flex flex-wrap justify-between items-center cursor-pointer rounded-md px-2 py-2 my-2 w-full bg-orange-50 text-gray-700 hover:bg-orange-100 hover:text-orange-900"
                                        >
                                            <!-- Nom complet -->
                                            <div
                                                class="flex w-full sm:w-1/6 items-center"
                                            >
                                                <span
                                                    :class="[
                                                        user.recently_added
                                                            ? 'bg-fuchsia-500'
                                                            : 'bg-green-400',
                                                        'h-2 w-2 mr-4 rounded-full',
                                                    ]"
                                                ></span>
                                                <div
                                                    class="font-medium text-left"
                                                >
                                                    {{ user.firstName }}
                                                    {{ user.familyName }}
                                                </div>
                                            </div>

                                            <!-- Numéro de téléphone -->
                                            <div class="flex w-full sm:w-1/6">
                                                <div
                                                    class="font-medium text-left ml-6 md:ml-0"
                                                >
                                                    {{ user.phone }}
                                                </div>
                                            </div>

                                            <!-- Pays -->
                                            <div class="flex w-full sm:w-1/6">
                                                <div
                                                    class="text-sm font-normal text-left ml-6 md:ml-0"
                                                >
                                                    {{ user.country }}
                                                </div>
                                            </div>

                                            <!-- Date d'ajout et utilisateur ayant ajouté -->
                                            <div
                                                class="text-xs w-full sm:w-1/6"
                                            >
                                                Ajouté par : <br />
                                                <span
                                                    class="font-bold text-xs mr-2"
                                                >
                                                    {{
                                                        user.created_by
                                                            ? user.created_by
                                                                  .name
                                                            : "Inconnu"
                                                    }}
                                                </span>
                                                <span class="font-bold text-xs">
                                                    {{
                                                        formatDate(
                                                            user.created_at
                                                        )
                                                    }}
                                                </span>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Affiche les 10 derniers fournisseurs modifiés -->
                                    <div
                                        v-if="!selectedUser"
                                        class="mt-8 border p-4 bg-zinc-50"
                                    >
                                        <h3
                                            class="text-lg font-semibold text-gray-700"
                                        >
                                            Derniers fournisseurs modifiés
                                        </h3>
                                        <div
                                            v-for="(
                                                user, index
                                            ) in recentModifiedUsers"
                                            :key="user.id || index"
                                            @click="selectUser(user)"
                                            class="text-sm flex flex-wrap justify-between items-center cursor-pointer rounded-md px-2 py-2 my-2 w-full bg-green-50 text-gray-700 hover:bg-green-100 hover:text-green-800"
                                        >
                                            <!-- Nom complet -->
                                            <div
                                                class="flex w-full sm:w-1/6 items-center"
                                            >
                                                <span
                                                    :class="[
                                                        user.recently_added
                                                            ? 'bg-fuchsia-500'
                                                            : 'bg-green-400',
                                                        'h-2 w-2 mr-4 rounded-full',
                                                    ]"
                                                ></span>
                                                <div
                                                    class="font-medium text-left"
                                                >
                                                    {{ user.firstName }}
                                                    {{ user.familyName }}
                                                </div>
                                            </div>

                                            <!-- Numéro de téléphone -->
                                            <div class="flex w-full sm:w-1/6">
                                                <div
                                                    class="font-medium text-left ml-6 md:ml-0"
                                                >
                                                    {{
                                                        user.phone
                                                    }}
                                                </div>
                                            </div>

                                            <!-- Pays -->
                                            <div class="flex w-full sm:w-1/6">
                                                <div
                                                    class="text-sm font-normal text-left ml-6 md:ml-0"
                                                >
                                                    {{
                                                        user.country
                                                    }}
                                                </div>
                                            </div>

                                            <!-- Date de modification et utilisateur ayant modifié -->
                                            <div
                                                class="text-xs w-full sm:w-1/6"
                                            >
                                                Modifié par : <br />
                                                <span
                                                    class="font-bold text-xs mr-2"
                                                >
                                                    {{
                                                        user.updated_by
                                                            ? user.updated_by
                                                                  .name
                                                            : "Inconnu"
                                                    }}
                                                </span>
                                                <span class="font-bold text-xs">
                                                    {{
                                                        formatDate(
                                                            user.updated_at
                                                        )
                                                    }}
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Détails de l'utilisateur sélectionné -->
                            <UserDetails
                                v-if="selectedUser"
                                :user="selectedUser"
                                @user-updated="updateUserInList"
                                @closeUserDetails="closeUserDetails"
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
