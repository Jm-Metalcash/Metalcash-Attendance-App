<script setup>
import { ref, computed, watch, defineProps, onMounted } from "vue";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head, Link, usePage } from "@inertiajs/vue3";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import axios from "axios";

const props = defineProps(["days", "isShow", "user"]);

const page = usePage();

// Filtre sélectionné pour l'année et le mois
const selectedYear = ref(new Date().getFullYear());
const selectedMonth = ref(0); // 0 pour "Tous les mois"

// Filtrer les jours en fonction de l'année et du mois sélectionnés
const filteredDays = ref([]);
const totalMinutesWorked = ref(0); // Stocke le total des minutes

// Variables pour le déploiement des détails
const expandedDays = ref([]);

// Fonction pour calculer le total des minutes travaillées d'une journée
const calculateDailyTotal = (arrivals, departures) => {
    let totalMinutes = 0;

    if (
        !arrivals ||
        !departures ||
        arrivals.length === 0 ||
        departures.length === 0
    ) {
        return "0h00"; // Si aucune arrivée ou départ, retourner 0h00
    }

    // Parcourir les paires d'arrivées et départs
    for (let i = 0; i < Math.min(arrivals.length, departures.length); i++) {
        const arrivalTime = arrivals[i];
        const departureTime = departures[i];

        if (
            typeof arrivalTime !== "string" ||
            typeof departureTime !== "string"
        ) {
            continue; // Ignorer les entrées invalides
        }

        const [arrivalHours, arrivalMinutes] = arrivalTime
            .split(":")
            .map(Number);
        const [departureHours, departureMinutes] = departureTime
            .split(":")
            .map(Number);

        const arrivalTotalMinutes = arrivalHours * 60 + arrivalMinutes;
        const departureTotalMinutes = departureHours * 60 + departureMinutes;

        let durationMinutes = departureTotalMinutes - arrivalTotalMinutes;

        // Gérer les cas où le départ est après minuit
        if (durationMinutes < 0) {
            durationMinutes += 24 * 60; // Ajouter 24 heures en minutes
        }

        totalMinutes += durationMinutes;
    }

    // Empêcher un total négatif
    if (totalMinutes < 0) {
        totalMinutes = 0;
    }

    const hours = Math.floor(totalMinutes / 60);
    const minutes = totalMinutes % 60;

    return `${hours.toString().padStart(2, "0")}h${minutes
        .toString()
        .padStart(2, "0")}`;
};

// Fonction pour formater les minutes en heures
const formatMinutesToHours = (minutes) => {
    const hours = Math.floor(minutes / 60);
    const remainingMinutes = minutes % 60;
    return `${hours.toString().padStart(2, "0")}h${remainingMinutes
        .toString()
        .padStart(2, "0")}`;
};

// Fonction pour formater l'heure sans les secondes
function formatTimeWithoutSeconds(time) {
    if (!time) return "--:--";
    return time.split(":").slice(0, 2).join(":"); // Retourne HH:mm
}

// Fonction pour déplier/replier les détails d'un jour
function toggleExpand(index) {
    const idx = expandedDays.value.indexOf(index);
    if (idx > -1) {
        expandedDays.value.splice(idx, 1);
    } else {
        expandedDays.value.push(index);
    }
}

// Fonction pour filtrer les jours en fonction des filtres d'année et de mois
function filterDays() {
    filteredDays.value = props.days
        .map((day) => {
            const arrivals = Array.isArray(day.time_entries)
                ? day.time_entries
                      .filter((entry) => entry.type === "arrival")
                      .map((entry) => entry.time)
                : [];

            const departures = Array.isArray(day.time_entries)
                ? day.time_entries
                      .filter((entry) => entry.type === "departure")
                      .map((entry) => entry.time)
                : [];

            return {
                ...day,
                arrivals,
                departures,
                total: calculateDailyTotal(arrivals, departures),
            };
        })
        .filter((day) => {
            const dayDate = new Date(day.date);
            const isSameYear = dayDate.getFullYear() === selectedYear.value;
            const isSameMonth =
                selectedMonth.value === 0 ||
                dayDate.getMonth() + 1 === selectedMonth.value;
            return isSameYear && isSameMonth;
        });
}

// Fonction pour calculer le total des minutes travaillées
const calculateTotalMinutes = () => {
    totalMinutesWorked.value = filteredDays.value.reduce((total, day) => {
        const [hours, minutes] = day.total
            .replace("h", ":")
            .split(":")
            .map(Number);
        return total + hours * 60 + minutes;
    }, 0);
};

// Watch sur les filtres pour recalculer les jours filtrés et les heures
watch(
    [selectedYear, selectedMonth, () => props.days],
    () => {
        filterDays();
        calculateTotalMinutes();
    },
    { immediate: true }
);

// Formater le total des heures et minutes
const formattedTotalHours = computed(() => {
    const totalMinutes = totalMinutesWorked.value;
    const hours = Math.floor(totalMinutes / 60);
    const minutes = totalMinutes % 60;
    return `${hours}h${minutes.toString().padStart(2, "0")}`;
});

// Nombre de jours enregistrés
const totalDaysRecorded = computed(() => filteredDays.value.length);

// Liste des années disponibles (2023 à 2025)
const years = ref([2023, 2024, 2025]);

// Liste des mois, avec l'option "Tous les mois"
const months = [
    { value: 0, name: "Tous les mois" },
    { value: 1, name: "Janvier" },
    { value: 2, name: "Février" },
    { value: 3, name: "Mars" },
    { value: 4, name: "Avril" },
    { value: 5, name: "Mai" },
    { value: 6, name: "Juin" },
    { value: 7, name: "Juillet" },
    { value: 8, name: "Août" },
    { value: 9, name: "Septembre" },
    { value: 10, name: "Octobre" },
    { value: 11, name: "Novembre" },
    { value: 12, name: "Décembre" },
];

// Variables pour le modal
const isModalOpen = ref(false);
const selectedDay = ref(null);

// Ouvrir le modal pour modifier un jour
const openModal = (day) => {
    // Vérifier si l'utilisateur a le rôle "Admin" ou "Informatique"
    if (
        page.props.auth.roles.includes("Admin") ||
        page.props.auth.roles.includes("Informatique")
    ) {
        // Si l'utilisateur est "Admin" ou "Informatique", on peut ouvrir le modal
        selectedDay.value = { ...day };
        isModalOpen.value = true;
    } else {
        // Sinon, empêcher l'ouverture et afficher un message ou prendre une autre action
        console.log(
            "Accès refusé : vous n'avez pas les autorisations nécessaires."
        );
    }
};

// Fermer le modal
const closeModal = () => {
    isModalOpen.value = false;
    selectedDay.value = null;
};

// Sauvegarder les modifications du jour
const saveDayChanges = async () => {
    if (!selectedDay.value || !selectedDay.value.id) {
        console.error("ID du jour manquant !");
        return;
    }

    try {
        // Formater les heures pour correspondre au format 'HH:mm'
        const formattedArrivals = selectedDay.value.arrivals.map((time) =>
            time ? time.substring(0, 5) : null
        );
        const formattedDepartures = selectedDay.value.departures.map((time) =>
            time ? time.substring(0, 5) : null
        );

        // Envoyer les arrivées et départs formatées
        await axios.post(`/update-day/${selectedDay.value.id}`, {
            arrivals: formattedArrivals,
            departures: formattedDepartures,
        });

        // Recharger la page après la mise à jour
        window.location.reload();
    } catch (error) {
        console.error(
            "Erreur lors de la sauvegarde des modifications :",
            error
        );
    }
};

// Variables pour le modal d'ajout
const isAddDayModalOpen = ref(false);
const newDay = ref({
    day: "Lundi", // Par défaut Lundi
    date: "",
    arrivals: [],
    departures: [],
});

// Ouvrir le modal pour ajouter un jour
const openAddDayModal = () => {
    isAddDayModalOpen.value = true;
};

// Fermer le modal d'ajout
const closeAddDayModal = () => {
    isAddDayModalOpen.value = false;
    newDay.value = { day: "Lundi", date: "", arrivals: [], departures: [] }; // Réinitialiser les champs
};

// Fonction pour ajouter un nouveau jour
const addDay = async () => {
    try {
        // Formater les heures pour correspondre au format 'HH:mm'
        const formattedArrivals = newDay.value.arrivals.map((time) =>
            time ? time.substring(0, 5) : null
        );
        const formattedDepartures = newDay.value.departures.map((time) =>
            time ? time.substring(0, 5) : null
        );

        await axios.post("/add-day", {
            user_id: props.user.id,
            day: newDay.value.day,
            date: newDay.value.date,
            arrivals: formattedArrivals,
            departures: formattedDepartures,
        });

        // Fermer le modal après succès
        closeAddDayModal();
        window.location.reload(); // Recharger la page pour refléter les changements
    } catch (error) {
        console.error("Erreur lors de l'ajout du jour :", error);
    }
};

// Fonction pour ajouter une heure d'arrivée dans le modal
const addArrivalTime = () => {
    newDay.value.arrivals.push("");
};

// Fonction pour supprimer une heure d'arrivée dans le modal
const removeArrivalTime = (index) => {
    newDay.value.arrivals.splice(index, 1);
};

// Fonction pour ajouter une heure de départ dans le modal
const addDepartureTime = () => {
    newDay.value.departures.push("");
};

// Fonction pour supprimer une heure de départ dans le modal
const removeDepartureTime = (index) => {
    newDay.value.departures.splice(index, 1);
};

// Fonction pour ajouter une heure d'arrivée dans le modal de modification
const addArrivalTimeEdit = () => {
    selectedDay.value.arrivals.push("");
};

// Fonction pour supprimer une heure d'arrivée dans le modal de modification
const removeArrivalTimeEdit = (index) => {
    selectedDay.value.arrivals.splice(index, 1);
};

// Fonction pour ajouter une heure de départ dans le modal de modification
const addDepartureTimeEdit = () => {
    selectedDay.value.departures.push("");
};

// Fonction pour supprimer une heure de départ dans le modal de modification
const removeDepartureTimeEdit = (index) => {
    selectedDay.value.departures.splice(index, 1);
};
</script>

<template>
    <Head title="Historique des pointages" />

    <AuthenticatedLayout>
        <template #header>
            <h2
                class="font-semibold text-xl text-white bg-gray-800 leading-tight"
            >
                <!-- Si isShow est vrai, afficher "Historique des pointages du user cliqué" -->
                <span v-if="isShow"
                    >Historique des pointages de {{ user.name }}</span
                >
                <!-- Sinon, garder le texte par défaut -->
                <span v-else>Historique des pointages</span>
            </h2>
        </template>

        <section
            class="attendance-section flex-grow w-full max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 bg-white pt-16 md:pt-24 pb-20 rounded-lg shadow-lg min-h-[800px]"
        >
            <!-- Statistiques et filtres -->
            <div class="w-full max-w-6xl mx-auto mb-8">
                <div
                    class="flex flex-col md:flex-row justify-between items-start md:items-center bg-gray-100 p-6 rounded-lg shadow-md space-y-6 md:space-y-0"
                >
                    <!-- Informations sur le total des heures et des jours enregistrés -->
                    <div
                        class="flex flex-col items-start space-y-2 text-gray-700 w-full lg:w-auto"
                    >
                        <div class="flex items-center text-sm">
                            <i class="fas fa-clock text-blue-500 mr-2"></i>
                            <span
                                ><strong>Total des heures :</strong>
                                {{ formattedTotalHours }}</span
                            >
                        </div>
                        <div class="flex items-center text-sm">
                            <i
                                class="fas fa-calendar-alt text-green-500 mr-2"
                            ></i>
                            <span
                                ><strong>Jours enregistrés :</strong>
                                {{ totalDaysRecorded }}</span
                            >
                        </div>
                    </div>

                    <!-- Filtres par année et mois -->
                    <div
                        class="flex flex-col sm:flex-row md:flex-row items-start md:items-center space-y-4 sm:space-y-0 sm:space-x-4 w-full md:w-auto lg:ml-auto"
                    >
                        <div class="flex items-center w-full sm:w-auto">
                            <label
                                for="year"
                                class="text-gray-600 font-semibold text-sm mr-2"
                                >Année</label
                            >
                            <select
                                id="year"
                                v-model="selectedYear"
                                class="w-full sm:w-48 md:w-40 border-gray-300 text-sm rounded-md shadow-sm focus:border-indigo-500 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                            >
                                <option
                                    v-for="year in years"
                                    :key="year"
                                    :value="year"
                                >
                                    {{ year }}
                                </option>
                            </select>
                        </div>

                        <div class="flex items-center w-full sm:w-auto">
                            <label
                                for="month"
                                class="text-gray-600 font-semibold text-sm mr-4 md:mr-2"
                                >Mois</label
                            >
                            <select
                                id="month"
                                v-model="selectedMonth"
                                class="w-full sm:w-48 md:w-40 border-gray-300 text-sm rounded-md shadow-sm focus:border-indigo-500 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                            >
                                <option
                                    v-for="month in months"
                                    :key="month.value"
                                    :value="month.value"
                                >
                                    {{ month.name }}
                                </option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Bouton pour ajouter un jour -->
            <div
                v-if="
                    isShow &&
                    page.props.auth.roles &&
                    (page.props.auth.roles.includes('Admin') ||
                        page.props.auth.roles.includes('Informatique'))
                "
                class="pt-6 flex justify-end mb-4 mr-8"
            >
                <PrimaryButton @click="openAddDayModal">
                    <i class="fas fa-plus mr-2"></i> Ajouter un jour
                </PrimaryButton>
            </div>

            <!-- Table -->
            <div class="w-full max-w-6xl mx-auto overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-[rgb(0,85,150)]">
                        <tr>
                            <th
                                class="px-6 py-3 text-left text-xs font-medium text-gray-100 uppercase tracking-wider"
                            >
                                Jour
                            </th>
                            <th
                                class="px-6 py-3 text-left text-xs font-medium text-gray-100 uppercase tracking-wider"
                            >
                                Arrivées
                            </th>
                            <th
                                class="px-6 py-3 text-left text-xs font-medium text-gray-100 uppercase tracking-wider"
                            >
                                Départs
                            </th>
                            <th
                                class="px-6 py-3 text-right text-xs font-medium text-gray-100 uppercase tracking-wider"
                            >
                                Total
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Lignes principales -->
                        <tr v-if="filteredDays.length === 0">
                            <td
                                colspan="4"
                                class="px-4 sm:px-6 py-4 text-center text-sm md:text-base"
                            >
                                Aucun pointage trouvé pour la période
                                sélectionnée.
                            </td>
                        </tr>
                        <template
                            v-for="(day, index) in filteredDays"
                            :key="index"
                        >
                            <tr
                                :class="[
                                    'hover:bg-gray-50 transition-colors',
                                    isShow ? 'cursor-pointer' : '',
                                ]"
                                @click="isShow && openModal(day)"
                            >
                                <td class="px-6 py-4">
                                    <strong class="capitalize">{{
                                        day.day
                                    }}</strong>
                                    <br />
                                    <span class="text-sm text-gray-500">
                                        {{
                                            new Date(
                                                day.date
                                            ).toLocaleDateString("fr-FR")
                                        }}
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    {{
                                        formatTimeWithoutSeconds(
                                            day.arrivals &&
                                                day.arrivals.length > 0
                                                ? day.arrivals[0]
                                                : null
                                        ) || "--:--"
                                    }}
                                    <button
                                        v-if="
                                            day.arrivals &&
                                            day.arrivals.length > 1
                                        "
                                        class="text-gray-500 text-sm ml-2"
                                        @click.stop="toggleExpand(index)"
                                    >
                                        (+{{ day.arrivals.length - 1 }})
                                        <i
                                            :class="
                                                expandedDays.includes(index)
                                                    ? 'fa-solid fa-chevron-up'
                                                    : 'fa-solid fa-chevron-down'
                                            "
                                            class="ml-1"
                                        ></i>
                                    </button>
                                </td>
                                <td class="px-6 py-4">
                                    {{
                                        formatTimeWithoutSeconds(
                                            day.departures &&
                                                day.departures.length > 0
                                                ? day.departures[0]
                                                : null
                                        ) || "--:--"
                                    }}
                                    <button
                                        v-if="
                                            day.departures &&
                                            day.departures.length > 1
                                        "
                                        class="text-gray-500 text-sm ml-2"
                                        @click.stop="toggleExpand(index)"
                                    >
                                        (+{{ day.departures.length - 1 }})
                                        <i
                                            :class="
                                                expandedDays.includes(index)
                                                    ? 'fa-solid fa-chevron-up'
                                                    : 'fa-solid fa-chevron-down'
                                            "
                                            class="ml-1"
                                        ></i>
                                    </button>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    {{ day.total || "--:--" }}
                                </td>
                            </tr>

                            <!-- Lignes détaillées -->
                            <tr v-if="expandedDays.includes(index)">
                                <td colspan="4" class="px-0 py-0">
                                    <transition name="slide-expand">
                                        <div
                                            class="bg-gray-50 border rounded-lg overflow-hidden"
                                        >
                                            <!-- Détails -->
                                            <div
                                                class="p-4"
                                                :class="[
                                                    'hover:bg-gray-50 transition-colors',
                                                    isShow
                                                        ? 'cursor-pointer'
                                                        : '',
                                                ]"
                                                @click="
                                                    isShow && openModal(day)
                                                "
                                            >
                                                <h4
                                                    class="text-sm font-semibold text-gray-800 mb-2"
                                                >
                                                    Détails des heures :
                                                </h4>
                                                <div
                                                    class="flex flex-col space-y-2"
                                                >
                                                    <div>
                                                        <strong
                                                            class="text-green-600"
                                                            >Arrivées :</strong
                                                        >
                                                        <div
                                                            class="flex flex-wrap gap-2 mt-1"
                                                        >
                                                            <span
                                                                v-for="(
                                                                    arrival, i
                                                                ) in day.arrivals"
                                                                :key="`arrival-${index}-${i}`"
                                                                class="bg-green-100 text-green-600 px-3 py-1 rounded-md text-sm"
                                                            >
                                                                {{
                                                                    formatTimeWithoutSeconds(
                                                                        arrival
                                                                    )
                                                                }}
                                                            </span>
                                                        </div>
                                                    </div>
                                                    <div>
                                                        <strong
                                                            class="text-red-600"
                                                            >Départs :</strong
                                                        >
                                                        <div
                                                            class="flex flex-wrap gap-2 mt-1"
                                                        >
                                                            <span
                                                                v-for="(
                                                                    departure, i
                                                                ) in day.departures"
                                                                :key="`departure-${index}-${i}`"
                                                                class="bg-red-100 text-red-600 px-3 py-1 rounded-md text-sm"
                                                            >
                                                                {{
                                                                    formatTimeWithoutSeconds(
                                                                        departure
                                                                    )
                                                                }}
                                                            </span>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </transition>
                                </td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>

            <!-- Retour au Dashboard ou à la gestion des employés -->
            <div class="pt-16 flex justify-center">
                <!-- Si isShow est vrai, afficher le bouton avec la route "employes" -->
                <Link v-if="isShow" :href="route('employes')">
                    <PrimaryButton>
                        <i class="fas fa-arrow-left mr-2"></i> Retour à la
                        gestion des employés
                    </PrimaryButton>
                </Link>
                <!-- Sinon, afficher le bouton pour retourner au dashboard -->
                <Link v-else :href="route('dashboard')">
                    <PrimaryButton>
                        <i class="fas fa-arrow-left mr-2"></i> Retour à
                        l'accueil
                    </PrimaryButton>
                </Link>
            </div>

            <!-- Modal pour modifier les jours -->
            <div
                v-if="isModalOpen"
                class="fixed z-50 inset-0 flex items-center justify-center bg-gray-800 bg-opacity-75"
            >
                <div class="bg-white rounded-lg shadow-lg p-6 w-full max-w-md">
                    <!-- Affichage du jour sélectionné -->
                    <h2 class="text-xl font-semibold mb-4">
                        Modifier le jour :
                        {{
                            new Date(selectedDay.date).toLocaleDateString(
                                "fr-FR"
                            )
                        }}
                    </h2>

                    <!-- Champs pour modifier les heures d'arrivée -->
                    <div class="mb-4">
                        <label
                            class="block text-sm font-medium text-gray-700 mb-2"
                        >
                            Heures d'arrivée
                        </label>
                        <div class="space-y-2">
                            <div
                                v-for="(arrival, index) in selectedDay.arrivals"
                                :key="`arrival-edit-${index}`"
                                class="flex items-center space-x-2"
                            >
                                <input
                                    type="time"
                                    v-model="selectedDay.arrivals[index]"
                                    class="block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                                />
                                <button
                                    @click="removeArrivalTimeEdit(index)"
                                    class="text-red-500 hover:text-red-700"
                                >
                                    <i class="fas fa-trash-alt"></i>
                                </button>
                            </div>
                        </div>
                        <button
                            @click="addArrivalTimeEdit"
                            class="mt-2 text-blue-500 hover:text-blue-700 text-sm"
                        >
                            <i class="fas fa-plus-circle"></i> Ajouter une heure
                        </button>
                    </div>

                    <!-- Champs pour modifier les heures de départ -->
                    <div class="mb-4">
                        <label
                            class="block text-sm font-medium text-gray-700 mb-2"
                        >
                            Heures de départ
                        </label>
                        <div class="space-y-2">
                            <div
                                v-for="(
                                    departure, index
                                ) in selectedDay.departures"
                                :key="`departure-edit-${index}`"
                                class="flex items-center space-x-2"
                            >
                                <input
                                    type="time"
                                    v-model="selectedDay.departures[index]"
                                    class="block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                                />
                                <button
                                    @click="removeDepartureTimeEdit(index)"
                                    class="text-red-500 hover:text-red-700"
                                >
                                    <i class="fas fa-trash-alt"></i>
                                </button>
                            </div>
                        </div>
                        <button
                            @click="addDepartureTimeEdit"
                            class="mt-2 text-blue-500 hover:text-blue-700 text-sm"
                        >
                            <i class="fas fa-plus-circle"></i> Ajouter une heure
                        </button>
                    </div>

                    <!-- Boutons pour annuler ou sauvegarder -->
                    <div class="flex justify-end space-x-4">
                        <button
                            @click="closeModal"
                            class="bg-gray-100 text-gray-600 px-4 rounded-md font-bold hover:bg-gray-50 hover:text-gray-500"
                        >
                            Annuler
                        </button>
                        <PrimaryButton @click="saveDayChanges">
                            Enregistrer
                        </PrimaryButton>
                    </div>
                </div>
            </div>

            <!-- Modal pour ajouter un nouveau jour -->
            <div
                v-if="isAddDayModalOpen"
                class="fixed z-50 inset-0 flex items-center justify-center bg-gray-800 bg-opacity-75"
            >
                <div class="bg-white rounded-lg shadow-lg p-6 w-full max-w-md">
                    <!-- Formulaire pour ajouter un jour -->
                    <h2 class="text-xl font-semibold mb-4">Ajouter un jour</h2>

                    <!-- Champ pour sélectionner le jour de la semaine -->
                    <div class="mb-4">
                        <label
                            for="day"
                            class="block text-sm font-medium text-gray-700"
                            >Jour</label
                        >
                        <select
                            id="day"
                            v-model="newDay.day"
                            class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                        >
                            <option value="Lundi">Lundi</option>
                            <option value="Mardi">Mardi</option>
                            <option value="Mercredi">Mercredi</option>
                            <option value="Jeudi">Jeudi</option>
                            <option value="Vendredi">Vendredi</option>
                        </select>
                    </div>

                    <!-- Champ pour sélectionner la date -->
                    <div class="mb-4">
                        <label
                            for="date"
                            class="block text-sm font-medium text-gray-700"
                            >Date</label
                        >
                        <input
                            id="date"
                            type="date"
                            v-model="newDay.date"
                            class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                        />
                    </div>

                    <!-- Champs pour les heures d'arrivée -->
                    <div class="mb-4">
                        <label
                            class="block text-sm font-medium text-gray-700 mb-2"
                        >
                            Heures d'arrivée
                        </label>
                        <div class="space-y-2">
                            <div
                                v-for="(arrival, index) in newDay.arrivals"
                                :key="`arrival-${index}`"
                                class="flex items-center space-x-2"
                            >
                                <input
                                    type="time"
                                    v-model="newDay.arrivals[index]"
                                    class="block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                                />
                                <button
                                    @click="removeArrivalTime(index)"
                                    class="text-red-500 hover:text-red-700"
                                >
                                    <i class="fas fa-trash-alt"></i>
                                </button>
                            </div>
                        </div>
                        <button
                            @click="addArrivalTime"
                            class="mt-2 text-blue-500 hover:text-blue-700 text-sm"
                        >
                            <i class="fas fa-plus-circle"></i> Ajouter une heure
                        </button>
                    </div>

                    <!-- Champs pour les heures de départ -->
                    <div class="mb-4">
                        <label
                            class="block text-sm font-medium text-gray-700 mb-2"
                        >
                            Heures de départ
                        </label>
                        <div class="space-y-2">
                            <div
                                v-for="(departure, index) in newDay.departures"
                                :key="`departure-${index}`"
                                class="flex items-center space-x-2"
                            >
                                <input
                                    type="time"
                                    v-model="newDay.departures[index]"
                                    class="block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                                />
                                <button
                                    @click="removeDepartureTime(index)"
                                    class="text-red-500 hover:text-red-700"
                                >
                                    <i class="fas fa-trash-alt"></i>
                                </button>
                            </div>
                        </div>
                        <button
                            @click="addDepartureTime"
                            class="mt-2 text-blue-500 hover:text-blue-700 text-sm"
                        >
                            <i class="fas fa-plus-circle"></i> Ajouter une heure
                        </button>
                    </div>

                    <!-- Boutons pour annuler ou ajouter -->
                    <div class="flex justify-end space-x-4">
                        <button
                            @click="closeAddDayModal"
                            class="bg-gray-100 text-gray-600 px-4 rounded-md font-bold hover:bg-gray-50 hover:text-gray-500"
                        >
                            Annuler
                        </button>
                        <PrimaryButton @click="addDay"> Ajouter </PrimaryButton>
                    </div>
                </div>
            </div>
        </section>
    </AuthenticatedLayout>
</template>

<style scoped>
/* Ajout d'une petite animation de survol sur les lignes de la table */
.transition-colors:hover {
    background-color: #edf6ff;
    transition: background-color 0.3s ease;
}

.badge {
    display: inline-block;
    padding: 0.25rem 0.5rem;
    font-size: 0.875rem;
    font-weight: 500;
    border-radius: 0.375rem;
    background-color: #f3f4f6;
    color: #374151;
    margin-right: 0.5rem;
    margin-bottom: 0.5rem;
}

.badge.green {
    background-color: #d1fae5;
    color: #047857;
}
.badge.red {
    background-color: #fee2e2;
    color: #b91c1c;
}

/* Transition personnalisée pour le slide */
.slide-expand-enter-active,
.slide-expand-leave-active {
    transition: max-height 0.4s ease-in-out, opacity 0.3s ease-in-out;
    overflow: hidden;
}

.slide-expand-enter-from,
.slide-expand-leave-to {
    max-height: 0;
    opacity: 0;
}

.slide-expand-enter-to,
.slide-expand-leave-from {
    max-height: 500px; /* Ajustez selon vos besoins */
    opacity: 1;
}

/* Style pour le modal */
.fixed {
    background-color: rgba(0, 0, 0, 0.5);
}

/* Style pour les boutons */
button:disabled {
    cursor: not-allowed;
    opacity: 0.5;
}
</style>
