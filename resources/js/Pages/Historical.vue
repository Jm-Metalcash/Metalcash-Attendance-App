<script setup>
import { ref, computed, watch, defineProps } from "vue";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head, Link, usePage } from "@inertiajs/vue3";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import Header from "@/Components/Header.vue";
import axios from "axios";

const props = defineProps(["days", "isShow", "user"]);

const page = usePage();
const showFlash = ref(false);
const flashMessage = ref('');
const flashType = ref('success');

// Fonction pour afficher le flash message
const displayFlash = (message, type = 'success') => {
    flashMessage.value = message;
    flashType.value = type;
    showFlash.value = true;
    setTimeout(() => {
        showFlash.value = false;
    }, 8000);
};

// Calculer dynamiquement le titre
const pageTitle = computed(() =>
    props.isShow
        ? `Historique des pointages de ${props.user.name}`
        : "Historique des pointages"
);

// Filtre sélectionné pour l'année et le mois
const selectedYear = ref(new Date().getFullYear());
const selectedMonth = ref(0); // 0 pour "Tous les mois"

// Filtrer les jours en fonction de l'année et du mois sélectionnés
const filteredDays = ref([]);
const totalMinutesWorked = ref(0); // Stocke le total des minutes

// Variables pour le déploiement des détails
const expandedDays = ref([]); // Contiendra des identifiants uniques pour chaque jour


// Fonction pour calculer le total des minutes travaillées d'une journée
const calculateDailyTotal = (arrivals, departures) => {
    let totalMinutes = 0;

    if (
        !arrivals ||
        !departures ||
        arrivals.length === 0 ||
        departures.length === 0
    ) {
        return "0:00"; // Si aucune arrivée ou départ, retourner 0:00
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

    return `${hours.toString()}:${minutes.toString().padStart(2, "0")}`;
};

// Fonction pour formater l'heure sans les secondes
function formatTimeWithoutSeconds(time) {
    if (!time) return "--:--";
    return time.split(":").slice(0, 2).join(":"); // Retourne HH:mm
}

// Fonction pour déplier/replier les détails d'un jour
function toggleExpand(monthIndex, dayIndex) {
    const dayId = `${monthIndex}-${dayIndex}`;
    const idx = expandedDays.value.indexOf(dayId);
    if (idx > -1) {
        expandedDays.value.splice(idx, 1);
    } else {
        expandedDays.value.push(dayId);
    }
}

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

// Fonction pour filtrer les jours en fonction des filtres d'année et de mois
function filterDays() {
    // Récupérer les jours filtrés par année
    const daysFilteredByYear = props.days.filter((day) => {
        const dayDate = new Date(day.date);
        return dayDate.getFullYear() === selectedYear.value;
    });

    if (selectedMonth.value === 0) {
        // Grouper les jours par mois
        const groupedByMonth = {};

        daysFilteredByYear.forEach((day) => {
            const dayDate = new Date(day.date);
            const month = dayDate.getMonth() + 1; // Les mois commencent à 0 en JS
            const monthName = months.find((m) => m.value === month).name;

            if (!groupedByMonth[month]) {
                groupedByMonth[month] = {
                    monthName: monthName,
                    days: [],
                };
            }

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

            groupedByMonth[month].days.push({
                ...day,
                arrivals,
                departures,
                total: calculateDailyTotal(arrivals, departures),
            });
        });

        // Convertir l'objet en tableau et trier par mois
        filteredDays.value = Object.values(groupedByMonth).sort(
            (a, b) =>
                months.findIndex((m) => m.name === a.monthName) -
                months.findIndex((m) => m.name === b.monthName)
        );
    } else {
        // Filtrer par mois sélectionné
        filteredDays.value = [
            {
                monthName: months.find((m) => m.value === selectedMonth.value)
                    .name,
                days: daysFilteredByYear
                    .filter((day) => {
                        const dayDate = new Date(day.date);
                        return dayDate.getMonth() + 1 === selectedMonth.value;
                    })
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
                    }),
            },
        ];
    }
}

// Fonction pour calculer le total des minutes travaillées
const calculateTotalMinutes = () => {
    totalMinutesWorked.value = filteredDays.value.reduce(
        (totalMinutes, monthGroup) => {
            const monthTotal = monthGroup.days.reduce((monthTotal, day) => {
                const [hours, minutes] = day.total.split(":").map(Number);
                return monthTotal + hours * 60 + minutes;
            }, 0);
            return totalMinutes + monthTotal;
        },
        0
    );
};

// Nombre de jours enregistrés
const totalDaysRecorded = computed(() => {
    return filteredDays.value.reduce((totalDays, monthGroup) => {
        return totalDays + monthGroup.days.length;
    }, 0);
});

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

// Liste des années disponibles (2023 à 2025)
const years = ref([2023, 2024, 2025]);

// Variables pour le modal
const isModalOpen = ref(false);
const selectedDay = ref(null);
const originalDayData = ref(null);

// Ouvrir le modal pour modifier un jour
const openModal = (day) => {
    // Vérifier si l'utilisateur a le rôle "Admin" ou "Informatique"
    if (
        page.props.auth.roles.includes("Admin") ||
        page.props.auth.roles.includes("Informatique")
    ) {
        // Créer une copie profonde des données du jour
        selectedDay.value = {
            ...day,
            arrivals: [...(day.arrivals || [])],
            departures: [...(day.departures || [])]
        };
        // Sauvegarder les données originales
        originalDayData.value = {
            arrivals: [...(day.arrivals || [])],
            departures: [...(day.departures || [])]
        };
        isModalOpen.value = true;
    } else {
        console.log(
            "Accès refusé : vous n'avez pas les autorisations nécessaires."
        );
    }
};

// Fermer le modal
const closeModal = () => {
    if (originalDayData.value && selectedDay.value) {
        // Restaurer les données originales avant de fermer
        selectedDay.value.arrivals = [...originalDayData.value.arrivals];
        selectedDay.value.departures = [...originalDayData.value.departures];
    }
    isModalOpen.value = false;
    selectedDay.value = null;
    originalDayData.value = null;
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

        displayFlash('Le jour a été modifié avec succès !', 'success');
        // Recharger la page après la mise à jour
        window.location.reload();
    } catch (error) {
        displayFlash('Erreur lors de la modification du jour.', 'error');
        console.error(
            "Erreur lors de la sauvegarde des modifications :",
            error
        );
    }
};

// Fonction pour supprimer un jour
const deleteDay = async () => {
    if (!selectedDay.value || !selectedDay.value.id) {
        console.error("ID du jour manquant !");
        return;
    }

    if (!confirm('Êtes-vous sûr de vouloir supprimer ce jour ?')) {
        return;
    }

    try {
        await axios.delete(`/delete-day/${selectedDay.value.id}`);
        displayFlash('Le jour a été supprimé avec succès !', 'success');
        closeModal();
        window.location.reload();
    } catch (error) {
        displayFlash('Erreur lors de la suppression du jour.', 'error');
        console.error("Erreur lors de la suppression du jour :", error);
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

        displayFlash('Le jour a été ajouté avec succès !', 'success');
        // Fermer le modal après succès
        closeAddDayModal();
        window.location.reload(); // Recharger la page pour refléter les changements
    } catch (error) {
        displayFlash('Erreur lors de l\'ajout du jour.', 'error');
        console.error("Erreur lors de l'ajout du jour :", error);
    }
};

// Fonctions pour gérer les heures d'arrivée et de départ dans les modals
const addArrivalTime = () => {
    newDay.value.arrivals.push("");
};
const removeArrivalTime = (index) => {
    newDay.value.arrivals.splice(index, 1);
};
const addDepartureTime = () => {
    newDay.value.departures.push("");
};
const removeDepartureTime = (index) => {
    newDay.value.departures.splice(index, 1);
};
const addArrivalTimeEdit = () => {
    selectedDay.value.arrivals.push("");
};
const removeArrivalTimeEdit = (index) => {
    selectedDay.value.arrivals.splice(index, 1);
};
const addDepartureTimeEdit = () => {
    selectedDay.value.departures.push("");
};
const removeDepartureTimeEdit = (index) => {
    selectedDay.value.departures.splice(index, 1);
};

</script>

<template>

    <Head title="Historique des pointages" />

    <AuthenticatedLayout>
        <template #header>
            <Header :pageTitle="pageTitle" />
        </template>

        <section
            class="attendance-section flex-grow w-full max-w-[1700px] mt-16 mx-auto px-4 sm:px-6 lg:px-8 bg-white pt-16 md:pt-14 pb-20 rounded-lg shadow-lg min-h-[800px]">
            <!-- Statistiques et filtres -->
            <div class="w-full mx-auto mb-2">
                <transition
                    enter-active-class="transition ease-out duration-300 transform"
                    enter-from-class="opacity-0 -translate-y-2"
                    enter-to-class="opacity-100 translate-y-0"
                    leave-active-class="transition ease-in duration-300 transform"
                    leave-from-class="opacity-100 translate-y-0"
                    leave-to-class="opacity-0 -translate-y-2"
                >
                    <div v-if="showFlash" 
                        :class="[
                            'fixed top-5 right-5 px-4 py-3 rounded-lg shadow-lg z-50 flex items-center',
                            flashType === 'success' ? 'bg-green-600 text-white' : 'bg-red-600 text-white'
                        ]"
                    >
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <i :class="[
                                    'fas mr-3',
                                    flashType === 'success' ? 'text-white' : 'text-white'
                                ]"></i>
                            </div>
                            <div>
                                <p class="font-medium">{{ flashMessage }}</p>
                            </div>
                            <button @click="showFlash = false" class="ml-4 text-white hover:text-gray-200">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                    </div>
                </transition>
                <div class="p-6 rounded-lg text-center w-full bg-white shadow-md mb-8">
                    <h2 class="text-gray-800 text-xl sm:text-2xl font-semibold">
                        <i class="fa-solid fa-clock-rotate-left text-[#005692] mr-2"></i> Historique des pointages
                        <span v-if="isShow">de {{ user.name }}</span>
                    </h2>
                </div>
                <div
                    class="bg-white border border-gray-200 shadow-sm rounded-lg p-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                    <!-- Bouton pour ajouter un jour -->
                    <div v-if="
                        isShow &&
                        page.props.auth.roles &&
                        (page.props.auth.roles.includes('Admin') ||
                            page.props.auth.roles.includes('Informatique'))
                    " class="flex items-center">
                        <PrimaryButton @click="openAddDayModal">
                            <i class="fas fa-plus mr-2"></i> Ajouter un jour
                        </PrimaryButton>
                    </div>

                    <!-- Filtres -->
                    <div class="flex flex-col sm:flex-row sm:items-center gap-4">
                        <!-- Filtre Année -->
                        <div class="flex items-center gap-2">
                            <label for="year" class="text-gray-600 font-medium text-sm">
                                Année
                            </label>
                            <select id="year" v-model="selectedYear"
                                class="border border-gray-300 rounded-md text-sm shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                                <option v-for="year in years" :key="year" :value="year">
                                    {{ year }}
                                </option>
                            </select>
                        </div>

                        <!-- Filtre Mois -->
                        <div class="flex items-center gap-2">
                            <label for="month" class="text-gray-600 font-medium text-sm">
                                Mois
                            </label>
                            <select id="month" v-model="selectedMonth"
                                class="border border-gray-300 rounded-md text-sm shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                                <option v-for="month in months" :key="month.value" :value="month.value">
                                    {{ month.name }}
                                </option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Table -->
            <div class="w-full mx-auto overflow-x-auto">
                <div v-if="selectedMonth === 0"
                    class="flex justify-between items-center p-4 bg-gray-100 border-t border-gray-300 text-gray-800">
                    <!-- Total des heures -->
                    <div class="flex items-center space-x-2">
                        <i class="fas fa-calendar-alt text-green-500"></i>
                        <span class="text-sm font-medium">
                            <span>Jours enregistrés: </span>
                            <span class="font-bold">{{
                                totalDaysRecorded
                                }}</span>
                        </span>
                    </div>
                    <!-- Jours enregistrés -->
                    <div class="flex items-center space-x-2">
                        <i class="fas fa-clock text-blue-500"></i>
                        <span class="text-sm font-medium">
                            <span>Total des heures: </span>
                            <span class="font-bold">{{
                                formattedTotalHours
                                }}</span>
                        </span>
                    </div>
                </div>
                <template v-for="(monthGroup, monthIndex) in filteredDays" :key="monthIndex">
                    <!-- En-tête du mois -->
                    <h3 class="text-lg font-semibold text-gray-700 mt-12 mb-4">
                        {{ monthGroup.monthName }}
                    </h3>

                    <table class="min-w-full divide-y divide-gray-200 border">
                        <thead class="bg-[rgb(0,85,150)]">
                            <tr>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-100 uppercase tracking-wider">
                                    Jour
                                </th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-100 uppercase tracking-wider">
                                    Arrivées
                                </th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-100 uppercase tracking-wider">
                                    Départs
                                </th>
                                <th
                                    class="px-7 py-3 text-right text-xs font-medium text-gray-100 uppercase tracking-wider">
                                    Total
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- Lignes principales -->
                            <tr v-if="monthGroup.days.length === 0">
                                <td colspan="4" class="px-4 sm:px-6 py-4 text-center text-sm md:text-base">
                                    Aucun pointage trouvé pour ce mois.
                                </td>
                            </tr>
                            <template v-for="(day, dayIndex) in monthGroup.days" :key="`${monthIndex}-${dayIndex}`">
                                <tr :class="[
                                    'hover:bg-gray-50 transition-colors',
                                    isShow ? 'cursor-pointer' : '',
                                ]" @click="isShow && openModal(day)">
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
                                    <td class="px-6 py-4 text-emerald-700">
                                        {{
                                            formatTimeWithoutSeconds(
                                                day.arrivals &&
                                                    day.arrivals.length > 0
                                                    ? day.arrivals[0]
                                                    : null
                                            ) || "--:--"
                                        }}
                                        <button v-if="
                                            day.arrivals &&
                                            day.arrivals.length > 1
                                        " class="text-gray-500 text-sm ml-2" @click.stop="
                                                toggleExpand(
                                                    monthIndex,
                                                    dayIndex
                                                )
                                                ">
                                            (+{{ day.arrivals.length - 1 }})
                                            <i :class="expandedDays.includes(
                                                `${monthIndex}-${dayIndex}`
                                            )
                                                    ? 'fa-solid fa-chevron-up'
                                                    : 'fa-solid fa-chevron-down'
                                                " class="ml-1"></i>
                                        </button>
                                    </td>
                                    <td class="px-6 py-4 text-red-700">
                                        {{
                                            formatTimeWithoutSeconds(
                                                day.departures &&
                                                    day.departures.length > 0
                                                    ? day.departures[0]
                                                    : null
                                            ) || "--:--"
                                        }}
                                        <button v-if="
                                            day.departures &&
                                            day.departures.length > 1
                                        " class="text-gray-500 text-sm ml-2" @click.stop="
                                                toggleExpand(
                                                    monthIndex,
                                                    dayIndex
                                                )
                                                ">
                                            (+{{ day.departures.length - 1 }})
                                            <i :class="expandedDays.includes(
                                                `${monthIndex}-${dayIndex}`
                                            )
                                                    ? 'fa-solid fa-chevron-up'
                                                    : 'fa-solid fa-chevron-down'
                                                " class="ml-1"></i>
                                        </button>
                                    </td>
                                    <td class="px-6 py-4 text-right font-semibold opacity-70">
                                        {{ day.total || "--:--" }}
                                    </td>
                                </tr>

                                <!-- Lignes détaillées -->
                                <tr v-if="
                                    expandedDays.includes(
                                        `${monthIndex}-${dayIndex}`
                                    )
                                ">
                                    <td colspan="4" class="px-0 py-0">
                                        <transition name="slide-expand">
                                            <div class="bg-gray-50 border rounded-lg overflow-hidden">
                                                <!-- Détails -->
                                                <div class="p-4" :class="[
                                                    'hover:bg-gray-50 transition-colors',
                                                    isShow
                                                        ? 'cursor-pointer'
                                                        : '',
                                                ]" @click="
                                                        isShow && openModal(day)
                                                        ">
                                                    <h4 class="text-sm font-semibold text-gray-800 mb-2">
                                                        Détails des heures :
                                                    </h4>
                                                    <div class="flex flex-col space-y-2">
                                                        <div>
                                                            <strong class="text-green-600">Arrivées
                                                                :</strong>
                                                            <div class="flex flex-wrap gap-2 mt-1">
                                                                <span v-for="(
                                                                        arrival,
                                                                            i
                                                                    ) in day.arrivals"
                                                                    :key="`arrival-${dayIndex}-${i}`"
                                                                    class="bg-green-100 text-green-600 px-3 py-1 rounded-md text-sm">
                                                                    {{
                                                                        formatTimeWithoutSeconds(
                                                                            arrival
                                                                        )
                                                                    }}
                                                                </span>
                                                            </div>
                                                        </div>
                                                        <div>
                                                            <strong class="text-red-600">Départs
                                                                :</strong>
                                                            <div class="flex flex-wrap gap-2 mt-1">
                                                                <span v-for="(
                                                                        departure,
                                                                            i
                                                                    ) in day.departures"
                                                                    :key="`departure-${dayIndex}-${i}`"
                                                                    class="bg-red-100 text-red-600 px-3 py-1 rounded-md text-sm">
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
                    <div v-if="selectedMonth > 0"
                        class="flex justify-between items-center p-4 bg-gray-100 border-t border-gray-300 rounded-b-lg text-gray-800">
                        <!-- Total des heures -->
                        <div class="flex items-center space-x-2">
                            <i class="fas fa-calendar-alt text-green-500"></i>
                            <span class="text-sm font-medium">
                                <span>Jours enregistrés: </span>
                                <span class="font-bold">{{
                                    totalDaysRecorded
                                    }}</span>
                            </span>
                        </div>
                        <!-- Jours enregistrés -->
                        <div class="flex items-center space-x-2">
                            <i class="fas fa-clock text-blue-500"></i>
                            <span class="text-sm font-medium">
                                <span>Total des heures: </span>
                                <span class="font-bold">{{
                                    formattedTotalHours
                                    }}</span>
                            </span>
                        </div>
                    </div>
                </template>
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
                <Link v-else :href="route('home')">
                <PrimaryButton>
                    <i class="fas fa-arrow-left mr-2"></i> Retour à
                    l'accueil
                </PrimaryButton>
                </Link>
            </div>

            <!-- Modal pour modifier les jours -->
            <div v-if="isModalOpen"
                class="fixed z-50 inset-0 flex items-center justify-center bg-gray-800 bg-opacity-75">
                <div class="bg-white rounded-xl shadow-xl p-8 w-full max-w-lg mx-4">
                    <!-- En-tête du modal -->
                    <div class="mb-6">
                        <h2 class="text-2xl font-bold text-gray-800">
                            Modifier le jour : {{ selectedDay?.date }}
                        </h2>
                    </div>

                    <!-- Champs pour modifier les heures d'arrivée -->
                    <div class="mb-6">
                        <label class="block text-sm font-semibold text-gray-700 mb-3">
                            Heures d'arrivée
                        </label>
                        <div class="space-y-3">
                            <div v-for="(arrival, index) in selectedDay.arrivals" :key="`arrival-edit-${index}`"
                                class="flex items-center gap-2">
                                <input type="time" v-model="selectedDay.arrivals[index]"
                                    class="flex-1 rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200 focus:ring-opacity-50" />
                                <button @click="removeArrivalTimeEdit(index)" 
                                    class="p-2 text-red-500 hover:text-red-700 transition-colors">
                                    <i class="fas fa-trash-alt"></i>
                                </button>
                            </div>
                        </div>
                        <button @click="addArrivalTimeEdit" 
                            class="mt-3 inline-flex items-center text-blue-600 hover:text-blue-700 transition-colors">
                            <i class="fas fa-plus-circle mr-2"></i>
                            Ajouter une heure
                        </button>
                    </div>

                    <!-- Champs pour modifier les heures de départ -->
                    <div class="mb-8">
                        <label class="block text-sm font-semibold text-gray-700 mb-3">
                            Heures de départ
                        </label>
                        <div class="space-y-3">
                            <div v-for="(departure, index) in selectedDay.departures" :key="`departure-edit-${index}`"
                                class="flex items-center gap-2">
                                <input type="time" v-model="selectedDay.departures[index]"
                                    class="flex-1 rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200 focus:ring-opacity-50" />
                                <button @click="removeDepartureTimeEdit(index)"
                                    class="p-2 text-red-500 hover:text-red-700 transition-colors">
                                    <i class="fas fa-trash-alt"></i>
                                </button>
                            </div>
                        </div>
                        <button @click="addDepartureTimeEdit"
                            class="mt-3 inline-flex items-center text-blue-600 hover:text-blue-700 transition-colors">
                            <i class="fas fa-plus-circle mr-2"></i>
                            Ajouter une heure
                        </button>
                    </div>

                    <!-- Actions -->
                    <div class="flex justify-between items-center">
                        <button @click="deleteDay"
                            class="inline-flex items-center px-4 py-2 bg-red-500 text-white font-medium rounded-lg hover:bg-red-400">
                            <i class="fas fa-trash-alt mr-2"></i>
                            Supprimer
                        </button>
                        <div class="flex items-center gap-3">
                            <button @click="closeModal"
                                class="px-4 py-2 bg-gray-100 text-gray-700 font-medium rounded-lg hover:bg-gray-200">
                                Annuler
                            </button>
                            <button @click="saveDayChanges"
                                class="px-4 py-2 bg-gray-900 text-white font-medium rounded-lg hover:bg-gray-600">
                                Sauvegarder
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Modal pour ajouter un nouveau jour -->
            <div v-if="isAddDayModalOpen"
                class="fixed z-50 inset-0 flex items-center justify-center bg-gray-800 bg-opacity-75">
                <div class="bg-white rounded-xl shadow-xl p-8 w-full max-w-lg mx-4">
                    <!-- En-tête du modal -->
                    <div class="mb-6">
                        <h2 class="text-2xl font-bold text-gray-800">
                            Ajouter un jour
                        </h2>
                    </div>

                    <!-- Sélection du jour -->
                    <div class="mb-6">
                        <label class="block text-sm font-semibold text-gray-700 mb-3">
                            Jour
                        </label>
                        <select v-model="newDay.day"
                            class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200 focus:ring-opacity-50">
                            <option value="Lundi">Lundi</option>
                            <option value="Mardi">Mardi</option>
                            <option value="Mercredi">Mercredi</option>
                            <option value="Jeudi">Jeudi</option>
                            <option value="Vendredi">Vendredi</option>
                        </select>
                    </div>

                    <!-- Sélection de la date -->
                    <div class="mb-6">
                        <label class="block text-sm font-semibold text-gray-700 mb-3">
                            Date
                        </label>
                        <input type="date" v-model="newDay.date"
                            class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200 focus:ring-opacity-50" />
                    </div>

                    <!-- Heures d'arrivée -->
                    <div class="mb-6">
                        <label class="block text-sm font-semibold text-gray-700 mb-3">
                            Heures d'arrivée
                        </label>
                        <div class="space-y-3">
                            <div v-for="(arrival, index) in newDay.arrivals" :key="`arrival-${index}`"
                                class="flex items-center gap-2">
                                <input type="time" v-model="newDay.arrivals[index]"
                                    class="flex-1 rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200 focus:ring-opacity-50" />
                                <button @click="removeArrivalTime(index)"
                                    class="p-2 text-red-500 hover:text-red-700 transition-colors">
                                    <i class="fas fa-trash-alt"></i>
                                </button>
                            </div>
                        </div>
                        <button @click="addArrivalTime"
                            class="mt-3 inline-flex items-center text-blue-600 hover:text-blue-700 transition-colors">
                            <i class="fas fa-plus-circle mr-2"></i>
                            Ajouter une heure
                        </button>
                    </div>

                    <!-- Heures de départ -->
                    <div class="mb-8">
                        <label class="block text-sm font-semibold text-gray-700 mb-3">
                            Heures de départ
                        </label>
                        <div class="space-y-3">
                            <div v-for="(departure, index) in newDay.departures" :key="`departure-${index}`"
                                class="flex items-center gap-2">
                                <input type="time" v-model="newDay.departures[index]"
                                    class="flex-1 rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200 focus:ring-opacity-50" />
                                <button @click="removeDepartureTime(index)"
                                    class="p-2 text-red-500 hover:text-red-700 transition-colors">
                                    <i class="fas fa-trash-alt"></i>
                                </button>
                            </div>
                        </div>
                        <button @click="addDepartureTime"
                            class="mt-3 inline-flex items-center text-blue-600 hover:text-blue-700 transition-colors">
                            <i class="fas fa-plus-circle mr-2"></i>
                            Ajouter une heure
                        </button>
                    </div>

                    <!-- Actions -->
                    <div class="flex justify-end items-center gap-3">
                        <button @click="closeAddDayModal"
                            class="px-4 py-2 bg-gray-100 text-gray-700 font-medium rounded-lg hover:bg-gray-200">
                            Annuler
                        </button>
                        <button @click="addDay"
                            class="px-4 py-2 bg-gray-900 text-white font-medium rounded-lg hover:bg-gray-600">
                            Ajouter
                        </button>
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
    max-height: 500px;
    /* Ajustez selon vos besoins */
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
