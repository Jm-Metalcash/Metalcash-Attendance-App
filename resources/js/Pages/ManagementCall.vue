<script setup>
import { ref, computed, watch, onMounted } from "vue";
import { router as Inertia } from "@inertiajs/vue3";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head, usePage, Link } from "@inertiajs/vue3";
import ProspectDetails from "./ManagementCall/Partials/ProspectDetails.vue";
import ClientDetails from "./ManagementCall/Partials/ClientDetails.vue";
import RecentAddedUser from "./ManagementCall/Partials/RecentAddedUser.vue";
import RecentModifiedUser from "./ManagementCall/Partials/RecentModifiedUser.vue";
import Legend from "@/Components/Legend.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import Header from "@/Components/Header.vue";
import AddUserModal from "./ManagementCall/Partials/AddUserModal.vue";
import FilteredUserList from "./ManagementCall/Partials/FilteredUserList.vue";
import FlashMessage from "@/Components/FlashMessage.vue";

const page = usePage();

// Les prospects sont passés via Inertia
const props = defineProps([
    "prospects",
    "clients",
    "currentUser",
    "selectedProspect",
    "selectedClient",
    "recentModifiedItems",
]);

// Les données des prospects à partir des props
const prospects = ref(props.prospects);

// Données des clients à partir des props
const clients = ref(props.clients);

// Liste des dernières modifications
const recentModifiedProspects = ref(props.recentModifiedItems);

// Terme de recherche
const searchTerm = ref("");

// Prospect sélectionné
const selectedProspect = ref(null);

// Prospect ou client sélectionné
const selectedItem = ref(null);

// Déterminer si l'élément sélectionné est un prospect ou un client
const isSelectedClient = computed(() => selectedItem.value?.type === "client");

// Nouveau prospect à ajouter
const newProspect = ref({
    firstName: "",
    familyName: "",
    phone: "",
    locality: "",
    country: "",
});

// Liste pour stocker les résultats filtrés (prospects et clients)
const filteredResults = computed(() => {
    const searchLower = searchTerm.value.toLowerCase().replace(/\s+/g, "");
    if (!searchLower) {
        return [];
    }

    const normalize = (str) =>
        str ? str.toLowerCase().replace(/\s+/g, "") : "";
    const normalizePhone = (phone) =>
        phone
            ? phone.replace(/^\+/, "00").replace(/\s+/g, "").toLowerCase()
            : "";

    const filterFunction = (list, type) =>
        list
            .filter((item) => {
                const combinedName1 = normalize(
                    item.firstName + item.familyName
                );
                const combinedName2 = normalize(
                    item.familyName + item.firstName
                );

                const nameMatches =
                    combinedName1.includes(searchLower) ||
                    combinedName2.includes(searchLower);

                const phoneMatches = item.phone
                    ? normalizePhone(item.phone).includes(
                        normalizePhone(searchLower)
                    )
                    : false;

                return (
                    nameMatches ||
                    phoneMatches ||
                    (item.country
                        ? normalize(item.country).includes(searchLower)
                        : false)
                );
            })
            .map((item) => ({
                ...item,
                type,
                last_important_note: item.last_important_note,
            }));

    const prospectsResults = filterFunction(prospects.value, "prospect");
    const clientsResults = filterFunction(clients.value, "client");

    return [...prospectsResults, ...clientsResults].slice(0, 5); // Limite à 5 résultats
});

// Charger les derniers prospects consultés lors du montage
onMounted(() => {
    if (props.selectedProspect) {
        selectedItem.value = props.selectedProspect;
    } else if (props.selectedClient) {
        selectedItem.value = props.selectedClient;
    }
});

// Watch pour mettre à jour selectedProspect lorsque props.selectedProspect change
watch(
    () => props.selectedProspect,
    (newSelectedProspect) => {
        selectedProspect.value = newSelectedProspect;
    }
);

// Fonction pour sélectionner un prospect
const selectItem = (item) => {
    if (!item || !item.id) {
        console.error("Élément invalide ou ID manquant.", item);
        return;
    }

    const routeName =
        item.type === "client" ? "management-call.client" : "management-call";

    Inertia.visit(route(routeName, { id: item.id }), {
        method: "get",
        preserveScroll: true,
    });
};

const closeProspectDetails = () => {
    selectedItem.value = null;

    // Mettre à jour l'URL pour revenir à /gestion-appels-telephoniques
    Inertia.visit(route("management-call"), {
        method: "get",
        preserveScroll: true,
    });
};

// Réinitialiser selectedProspect si searchTerm change
watch(searchTerm, () => {
    selectedProspect.value = null;
});

// État du modal pour ajouter un prospect
const showModal = ref(false);

// Fonction pour ouvrir et fermer le modal
const toggleModal = () => {
    showModal.value = !showModal.value;
};

// Variable pour stocker l'ID du prospect récemment ajouté
const newProspectId = ref(null);

// État pour afficher le message de confirmation de l'ajout d'un prospect
const showAddConfirmation = ref(false);

const handleAddProspect = (newProspect) => {
    prospects.value.push(newProspect);
    selectedProspect.value = newProspect;
    newProspectId.value = newProspect.id;
    showAddConfirmation.value = true;
    setTimeout(() => {
        showAddConfirmation.value = false; // Cache le message après 4 secondes
    }, 4000);
    toggleModal();
};

// Fonction pour mettre à jour le prospect dans la liste
const updateProspectInList = (updatedProspect) => {
    const index = prospects.value.findIndex(
        (prospect) => prospect.id === updatedProspect.id
    );

    if (index !== -1) {
        prospects.value[index] = { ...updatedProspect };
    }

    if (
        selectedProspect.value &&
        selectedProspect.value.id === updatedProspect.id
    ) {
        selectedProspect.value = prospects.value[index];
    }
};

// Les 10 derniers prospects ajoutés
const recentAddedProspects = computed(() => {
    return prospects.value
        .filter((prospect) => prospect.created_at)
        .map((prospect) => ({
            ...prospect,
            type: "prospect", // Ajouter le type prospect
            created_by_name: prospect.created_by
                ? prospect.created_by.name
                : null,
        }))
        .sort((a, b) => new Date(b.created_at) - new Date(a.created_at))
        .slice(0, 10); // Limiter à 10 résultats
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

watch(filteredResults, (newValue) => {
    console.log("Résultats filtrés :", newValue);
});
</script>

<template>

    <Head title="Gestion des appels téléphoniques" />

    <AuthenticatedLayout>
        <template #header>
            <Header :pageTitle="'Gestion des appels téléphones'" />
        </template>

        <FlashMessage v-if="showAddConfirmation" message="Prospect ajouté avec succès."
            @close="showAddConfirmation = false" />

        <div
            class="container max-w-[1700px] mx-auto flex flex-col flex-grow items-center px-0 md:px-4 sm:px-8 md:mt-16 rounded-lg shadow-lg bg-white min-h-[800px]">
            <div class="w-full mt-0 mx-auto px-0">
                <div class="p-6 rounded-lg text-center w-full bg-white">
                    <h1 class="text-gray-800 text-xl sm:text-2xl font-semibold shadow-md py-8">
                        <i class="fa-solid fa-phone-volume text-[#005692] mr-2"></i> Gestion des appels téléphoniques
                    </h1>
                </div>
                <div class="flex justify-center p-4 px-3 py-10">
                    <div class="w-full">
                        <div class="bg-white px-3 py-4 pb-6 mb-4">
                            <!-- Conteneur pour "Rechercher un prospect" et la barre de recherche -->
                            <div class="flex flex-row justify-between items-center mb-1 md:mb-6">
                                <!-- Rechercher un prospect -->
                                <div class="text-gray-700 pt-4 md:pt-0 text-sm md:text-lg font-semibold mb-4 sm:mb-0">
                                    Rechercher un Prospect ou Fournisseur
                                    existant
                                </div>
                            </div>

                            <!-- BARRE DE RECHERCHE -->
                            <div
                                class="flex items-center bg-[rgb(237,242,247)] rounded-md mb-14 border-b-2 border-gray-300">
                                <div class="pl-2">
                                    <svg class="fill-current text-gray-400 w-6 h-6" xmlns="http://www.w3.org/2000/svg"
                                        viewBox="0 0 24 24">
                                        <path class="heroicon-ui"
                                            d="M16.32 14.9l5.39 5.4a1 1 0 0 1-1.42 1.4l-5.38-5.38a8 8 0 1 1 1.41-1.41zM10 16a6 6 0 1 0 0-12 6 6 0 0 0 0 12z" />
                                    </svg>
                                </div>
                                <input v-model="searchTerm"
                                    class="w-full rounded-md text-sm md:text-base bg-[rgb(237,242,247)] text-gray-500 leading-tight focus:outline-none focus:ring-0 border-none py-3 px-2 placeholder-gray-500 placeholder-opacity-60"
                                    id="search" type="text" placeholder="Numéro de téléphone, Nom ou Prénom" />
                            </div>

                            <!-- Affichage de la liste des prospects filtrés -->
                            <FilteredUserList v-if="filteredResults.length > 0" :filteredProspects="filteredResults"
                                :selectProspect="selectItem" />

                            <!-- Aucun résultat -->
                            <div v-else-if="searchTerm" class="text-center mt-8 text-gray-600 text-base">
                                Aucun résultat trouvé pour "{{ searchTerm }}"
                                <button @click="toggleModal"
                                    class="text-xs md:text-sm bg-gray-50 hover:bg-gray-200 text-gray-700 border border-gray-300 font-bold py-2 px-4 rounded block mx-auto mt-3">
                                    <i class="fa-solid fa-plus text-xs"></i> Ajouter un prospect
                                </button>
                            </div>

                            <!-- Détails de l'élément sélectionné -->
                            <div v-if="selectedItem && !searchTerm">
                                <ProspectDetails v-if="!isSelectedClient" :prospect="selectedItem"
                                    @prospect-updated="updateProspectInList" @closeUserDetails="closeProspectDetails" />
                                <ClientDetails v-else :client="selectedItem" @client-updated="updateClientInList"
                                    @closeUserDetails="closeProspectDetails" />
                            </div>
                            

                            <!-- Autres composants uniquement si aucun prospect n'est sélectionné -->
                            <div v-else>
                                <!-- Historique des modifications des prospects -->
                                <div v-if="!searchTerm" class="client-manage-panel">
                                    <!-- PANEL ADMIN -->
                                    <div class="admin-panel-clients">
                                        <!-- Affiche les 10 derniers prospects modifiés -->
                                        <RecentModifiedUser :recentModifiedProspects="recentModifiedProspects
                                            " :selectProspect="selectItem" :formatDate="formatDate" />

                                        <!-- LEGENDE -->
                                        <Legend />


                                        <!-- Affiche les 20 derniers prospects ajoutés -->
                                        <RecentAddedUser class="mt-12" :recentAddedProspects="recentAddedProspects
                                            " :selectProspect="selectItem" :formatDate="formatDate" />
                                    </div>
                                </div>
                            </div>

                            <!-- Modal d'ajout de prospect -->
                            <AddUserModal :showModal="showModal" :newProspect="newProspect" @toggleModal="toggleModal"
                                @addProspect="handleAddProspect" />
                        </div>
                    </div>
                </div>
            </div>

            <div class="mb-12 flex justify-center">
                <Link :href="route('managementCall')">
                <PrimaryButton>
                    <i class="fas fa-arrow-left mr-2"></i> Retour à la gestion des appels
                </PrimaryButton>
                </Link>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
