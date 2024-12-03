<script setup>
import { ref, computed, watch, onMounted } from "vue";
import { router as Inertia } from "@inertiajs/vue3";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head, usePage } from "@inertiajs/vue3";
import UserDetails from "./ManagementCall/Partials/UserDetails.vue";
import RecentUser from "./ManagementCall/Partials/RecentUser.vue";
import RecentAddedUser from "./ManagementCall/Partials/RecentAddedUser.vue";
import RecentModifiedUser from "./ManagementCall/Partials/RecentModifiedUser.vue";
import AddUserModal from "./ManagementCall/Partials/AddUserModal.vue";
import FilteredUserList from "./ManagementCall/Partials/FilteredUserList.vue";
import FlashMessage from "@/Components/FlashMessage.vue";

const page = usePage();

// Les prospects sont passés via Inertia
const props = defineProps(["prospects", "currentUser", "selectedProspect"]);

// Les données des prospects à partir des props
const prospects = ref(props.prospects);

// Terme de recherche
const searchTerm = ref("");

// Prospect sélectionné
const selectedProspect = ref(null);

// Nouveau prospect à ajouter
const newProspect = ref({
    firstName: "",
    familyName: "",
    phone: "",
    locality: "",
    country: "",
});

// Liste pour stocker les prospects filtrés par la recherche (limité à 5)
const filteredProspects = computed(() => {
    const searchLower = searchTerm.value.toLowerCase().replace(/\s+/g, "");

    if (!searchLower) {
        return [];
    }

    return prospects.value
        .filter((prospect) => {
            const normalize = (str) =>
                str ? str.toLowerCase().replace(/\s+/g, "") : "";

            const normalizePhone = (phone) => {
                if (!phone) return "";
                // Remplace les préfixes internationaux en les unifiant au format international "+XX"
                return phone
                    .replace(/^\+/, "00") // Remplace le "+" par "00"
                    .replace(/\s+/g, "") // Supprime les espaces
                    .toLowerCase();
            };

            const combinedName1 = normalize(
                prospect.firstName + prospect.familyName
            );
            const combinedName2 = normalize(
                prospect.familyName + prospect.firstName
            );

            const nameMatches =
                combinedName1.includes(searchLower) ||
                combinedName2.includes(searchLower);

            const phoneMatches = prospect.phone
                ? normalizePhone(prospect.phone).includes(
                      normalizePhone(searchLower)
                  )
                : false;

            return (
                nameMatches ||
                phoneMatches ||
                (prospect.country
                    ? normalize(prospect.country).includes(searchLower)
                    : false)
            );
        })
        .slice(0, 5); // Limiter à 5 résultats maximum
});

// Liste pour stocker les 5 derniers prospects consultés
const recentProspects = ref([]);

// Charger les derniers prospects consultés lors du montage
onMounted(() => {
    axios.get("/recent-views").then((response) => {
        recentProspects.value = response.data.map((view) => ({
            prospect: view.prospect,
            viewedBy: view.viewedBy || "Inconnu",
            viewedAt: view.viewedAt || "Date inconnue",
        }));
    });

    if (props.selectedProspect) {
        selectedProspect.value = props.selectedProspect;
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
const selectProspect = (prospect) => {
    if (!prospect || !prospect.id) {
        console.error("Prospect invalide ou ID manquant.", prospect);
        return;
    }

    Inertia.visit(route("management-call", { prospect: prospect.id }), {
        method: "get",
        preserveScroll: true,
    });
};

const closeProspectDetails = () => {
    selectedProspect.value = null;

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

// Les 20 derniers prospects ajoutés
const recentAddedProspects = computed(() => {
    return prospects.value
        .filter((prospect) => prospect.created_at)
        .sort((a, b) => new Date(b.created_at) - new Date(a.created_at))
        .slice(0, 20);
});

// Les 10 derniers prospects modifiés
const recentModifiedProspects = computed(() => {
    return prospects.value
        .filter(
            (prospect) =>
                prospect.updated_at &&
                prospect.created_at &&
                new Date(prospect.updated_at) > new Date(prospect.created_at)
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
            <h2 class="font-semibold text-xl text-white bg-gray-800 leading-tight">
                Gestion des appels téléphoniques
            </h2>
        </template>

        <FlashMessage
            v-if="showAddConfirmation"
            message="Prospect ajouté avec succès."
            @close="showAddConfirmation = false"
        />

        <div
            class="container mx-auto flex flex-col flex-grow items-center px-0 md:px-4 sm:px-8 max-w-7xl mt-2 md:mt-8 bg-white min-h-[800px]"
        >
            <div class="w-full max-w-7xl mt-0 mx-auto px-0">
                <div class="flex justify-center p-4 px-3 py-10">
                    <div class="w-full">
                        <div class="bg-white shadow-md rounded-lg px-3 py-4 pb-6 mb-4">
                            <!-- Conteneur pour "Rechercher un prospect" et la barre de recherche -->
                            <div
                                class="flex flex-row justify-between items-center mb-1 md:mb-6"
                            >
                                <!-- Rechercher un prospect -->
                                <div
                                    class="text-gray-700 pt-4 md:pt-0 text-sm md:text-lg font-semibold mb-4 sm:mb-0"
                                >
                                    Rechercher un Prospect ou Fournisseur existant
                                </div>
                                <!-- Bouton Ajouter un prospect -->
                                <button
                                    @click="toggleModal"
                                    class="text-xs md:text-sm bg-gray-900 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded"
                                >
                                    Ajouter un prospect
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
                                    placeholder="Numéro de téléphone, Nom ou Prénom"
                                />
                            </div>

                            <!-- Détails du prospect sélectionné -->
                            <UserDetails
                                v-if="selectedProspect"
                                :prospect="selectedProspect"
                                @prospect-updated="updateProspectInList"
                                @closeUserDetails="closeProspectDetails"
                            />

                            <!-- Autres composants uniquement si aucun prospect n'est sélectionné -->
                            <div v-else>
                                <!-- Affichage de la liste des prospects filtrés -->
                                <FilteredUserList
                                    v-if="searchTerm && filteredProspects.length > 0"
                                    :filteredProspects="filteredProspects"
                                    :selectProspect="selectProspect"
                                    :newProspectId="newProspectId"
                                />

                                <!-- Historique des modifications des prospects -->
                                <div v-if="!searchTerm" class="client-manage-panel">
                                    <!-- Affichage des derniers prospects consultés -->
                                    <RecentUser
                                        v-if="recentProspects.length > 0"
                                        :recentProspects="recentProspects"
                                        :selectProspect="selectProspect"
                                    />

                                    <!-- PANEL ADMIN -->
                                    <div
                                        v-if="
                                            recentAddedProspects.length > 0 &&
                                            page.props.auth.roles &&
                                            (page.props.auth.roles.includes('Admin') ||
                                                page.props.auth.roles.includes('Informatique'))
                                        "
                                        class="admin-panel-clients"
                                    >
                                        <h2
                                            class="mt-20 text-xl p-2 font-bold w-full bg-[rgb(0,86,146)] text-white"
                                        >
                                            <i class="fa-solid fa-lock pl-2 pr-1"></i>
                                            Administration
                                        </h2>

                                        <!-- Affiche les 20 derniers prospects ajoutés -->
                                        <RecentAddedUser
                                            :recentAddedProspects="recentAddedProspects"
                                            :selectProspect="selectProspect"
                                            :formatDate="formatDate"
                                        />

                                        <!-- Affiche les 10 derniers prospects modifiés -->
                                        <RecentModifiedUser
                                            :recentModifiedProspects="recentModifiedProspects"
                                            :selectProspect="selectProspect"
                                            :formatDate="formatDate"
                                        />
                                    </div>
                                </div>
                            </div>

                            <!-- Modal d'ajout de prospect -->
                            <AddUserModal
                                :showModal="showModal"
                                :newProspect="newProspect"
                                @toggleModal="toggleModal"
                                @addProspect="handleAddProspect"
                            />
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
