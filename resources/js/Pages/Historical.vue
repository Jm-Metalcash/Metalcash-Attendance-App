<script setup>
import { ref, computed, watch, defineProps } from "vue";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head, Link } from "@inertiajs/vue3";
import PrimaryButton from "@/Components/PrimaryButton.vue";

// Les données des jours sont passées à partir du contrôleur Laravel via Inertia
const props = defineProps(["days", "isShow", "user"]);

// Filtre sélectionné pour l'année et le mois
const selectedYear = ref(new Date().getFullYear());
const selectedMonth = ref(0); // 0 pour "Tous les mois"

// Filtrer les jours en fonction de l'année et du mois sélectionnés
const filteredDays = ref([]);
const totalMinutesWorked = ref(0); // Stocke le total des minutes

// Fonction pour calculer la différence entre l'heure d'arrivée et de départ
const calculateDailyTotal = (arrival, departure) => {
    if (!arrival || !departure) return "0h00"; // Si l'une des heures est manquante

    const [arrivalHours, arrivalMinutes] = arrival.split(":").map(Number);
    const [departureHours, departureMinutes] = departure.split(":").map(Number);

    const arrivalDate = new Date();
    arrivalDate.setHours(arrivalHours, arrivalMinutes, 0);

    const departureDate = new Date();
    departureDate.setHours(departureHours, departureMinutes, 0);

    const differenceInMinutes = (departureDate - arrivalDate) / 1000 / 60;

    if (differenceInMinutes < 0) {
        return "0h00"; // Retourne 0 si l'heure de départ est avant l'arrivée
    }

    return formatMinutesToHours(differenceInMinutes); // Utiliser la fonction pour formater le total
};

//Fonction pour calculer le total des heures de la semaine du mois
const calculateWeeklyTotals = (weeks, days) => {
    return weeks.map((week) => {
        const totalMinutes = week.reduce((total, day) => {
            // Utiliser toLocaleDateString pour éviter les problèmes de fuseau horaire
            const dayString = day.toLocaleDateString("fr-CA"); // 'YYYY-MM-DD'
            const dayData = days.find((d) => d.date === dayString);

            if (dayData && dayData.arrival && dayData.departure) {
                const [arrivalHours, arrivalMinutes] = dayData.arrival
                    .split(":")
                    .map(Number);
                const [departureHours, departureMinutes] = dayData.departure
                    .split(":")
                    .map(Number);

                if (
                    !isNaN(arrivalHours) &&
                    !isNaN(departureHours) &&
                    !isNaN(arrivalMinutes) &&
                    !isNaN(departureMinutes)
                ) {
                    const arrivalDate = new Date();
                    arrivalDate.setHours(arrivalHours, arrivalMinutes, 0);

                    const departureDate = new Date();
                    departureDate.setHours(departureHours, departureMinutes, 0);

                    const dailyTotalMinutes =
                        (departureDate - arrivalDate) / 1000 / 60; // Différence en minutes

                    if (dailyTotalMinutes > 0) {
                        total += dailyTotalMinutes;
                    }
                }
            }
            return total;
        }, 0);

        const hours = Math.floor(totalMinutes / 60);
        const minutes = totalMinutes % 60;

        return {
            week,
            totalHours: `${hours}h${minutes.toString().padStart(2, "0")}`,
            dateRange: `${formatDate(week[0])} au ${formatDate(
                week[week.length - 1]
            )}`,
        };
    });
};

// Fonction pour filtrer les jours en fonction de l'année et du mois sélectionnés
const filterDays = () => {
    filteredDays.value = props.days
        .map((day) => {
            // Calculer le total dynamique en fonction des heures d'arrivée et de départ
            const total = calculateDailyTotal(day.arrival, day.departure);

            return {
                ...day,
                total, // Mettre à jour le champ total avec le calcul dynamique
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
};

// Sauvegarder les filtres dans le localStorage
const saveFiltersToLocalStorage = () => {
    localStorage.setItem("selectedYear", selectedYear.value);
    localStorage.setItem("selectedMonth", selectedMonth.value);
};

// Charger les filtres depuis le localStorage
const loadFiltersFromLocalStorage = () => {
    const savedYear = localStorage.getItem("selectedYear");
    const savedMonth = localStorage.getItem("selectedMonth");

    if (savedYear) {
        selectedYear.value = parseInt(savedYear);
    }
    if (savedMonth) {
        selectedMonth.value = parseInt(savedMonth);
    }
};
// Appliquer les filtres du localStorage au démarrage
loadFiltersFromLocalStorage();

// Fonction pour calculer le total des minutes travaillées
const calculateTotalMinutes = () => {
    totalMinutesWorked.value = filteredDays.value.reduce((total, day) => {
        if (day.total && typeof day.total === "string") {
            const [hours, minutes] = day.total.split("h").map(Number);
            if (!isNaN(hours) && !isNaN(minutes)) {
                total += hours * 60 + minutes;
            }
        }
        return total;
    }, 0);
};

const formatMinutesToHours = (totalMinutes) => {
    const hours = Math.floor(totalMinutes / 60);
    const minutes = totalMinutes % 60;
    return `${hours}h${minutes.toString().padStart(2, "0")}`;
};

// Watch sur les filtres pour recalculer les jours filtrés et les heures
watch(
    [saveFiltersToLocalStorage, selectedYear, selectedMonth, () => props.days],
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

// Fonction pour reformater l'heure et retirer les secondes
function formatTime(time) {
    if (!time) return "--:--"; // Si l'heure est vide ou nulle
    const [hours, minutes] = time.split(":");
    return `${hours}:${minutes}`; // Retourne HH:mm
}

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

// Fonction pour récupérer toutes les semaines d'un mois
const getAllWeeksInMonth = (year, monthIndex) => {
    const weeks = [];
    const firstDay = new Date(year, monthIndex - 1, 1);
    const lastDay = new Date(year, monthIndex, 0); // Dernier jour du mois

    let currentDay = new Date(firstDay);

    // Semaine 1 : Commence le premier jour du mois et se termine le premier vendredi
    let week1 = [];
    while (currentDay <= lastDay && currentDay.getDay() !== 6) {
        // Ajouter le jour au tableau tant qu'on est avant ou sur vendredi (getDay() === 5)
        week1.push(new Date(currentDay)); // Stocker en tant qu'objet Date
        currentDay.setDate(currentDay.getDate() + 1);
    }
    // Ajouter le vendredi si nécessaire (getDay() === 5)
    if (currentDay.getDay() === 5) {
        week1.push(new Date(currentDay));
        currentDay.setDate(currentDay.getDate() + 1); // Passer au jour suivant
    }
    weeks.push(week1);

    // Semaines suivantes : du lundi au vendredi
    while (currentDay <= lastDay) {
        // Passer au lundi si ce n'est pas un lundi
        if (currentDay.getDay() !== 1) {
            const daysToMonday = (1 - currentDay.getDay() + 7) % 7;
            currentDay.setDate(currentDay.getDate() + daysToMonday);
            if (currentDay > lastDay) break;
        }

        let week = [];
        for (let i = 0; i < 5; i++) {
            // Lundi à vendredi (assurer qu'on inclut bien vendredi)
            if (currentDay > lastDay) break;
            week.push(new Date(currentDay)); // Stocker les objets Date
            currentDay.setDate(currentDay.getDate() + 1);
        }
        weeks.push(week);
    }

    return weeks;
};

// Fonction pour formater les dates en format DD/MM/YYYY
const formatDate = (dateObj) => {
    if (!dateObj) return "Invalid Date";
    return dateObj.toLocaleDateString("fr-FR", {
        year: "numeric",
        month: "2-digit",
        day: "2-digit",
    });
};

// Calcul du nombre total de jours prestés pour un mois donné
const getTotalDaysForMonth = (monthIndex, year) => {
    const filteredDays = props.days.filter((day) => {
        const dayDate = new Date(day.date);
        return (
            dayDate.getFullYear() === year &&
            dayDate.getMonth() + 1 === monthIndex
        );
    });
    return filteredDays.length; // Retourne le nombre de jours prestés
};

// Calcul du total des heures prestées pour un mois donné
const getTotalHoursForMonth = (monthIndex, year) => {
    const filteredDays = props.days.filter((day) => {
        const dayDate = new Date(day.date);
        return (
            dayDate.getFullYear() === year &&
            dayDate.getMonth() + 1 === monthIndex
        );
    });

    // Calcul du total des minutes travaillées pour le mois
    const totalMinutes = filteredDays.reduce((total, day) => {
        if (day.arrival && day.departure) {
            const [arrivalHours, arrivalMinutes] = day.arrival
                .split(":")
                .map(Number);
            const [departureHours, departureMinutes] = day.departure
                .split(":")
                .map(Number);

            const arrivalDate = new Date();
            arrivalDate.setHours(arrivalHours, arrivalMinutes, 0);

            const departureDate = new Date();
            departureDate.setHours(departureHours, departureMinutes, 0);

            const dailyTotalMinutes = (departureDate - arrivalDate) / 1000 / 60; // Différence en minutes

            if (dailyTotalMinutes > 0) {
                total += dailyTotalMinutes;
            }
        }
        return total;
    }, 0);

    const hours = Math.floor(totalMinutes / 60);
    const minutes = totalMinutes % 60;
    return `${hours}h${minutes.toString().padStart(2, "0")}`; // Retourne les heures et minutes formatées
};

// Variables pour le modal
const selectedDay = ref(null);
const isModalOpen = ref(false);

// Ouvrir le modal pour modifier un jour
const openModal = (day) => {
    selectedDay.value = { ...day };
    isModalOpen.value = true;
};


// Fermer le modal
const closeModal = () => {
    isModalOpen.value = false;
    selectedDay.value = null;
};

const showSuccessToast = ref(false);

// Sauvegarder les modifications du jour
const saveDayChanges = async () => {
    if (!selectedDay.value || !selectedDay.value.id) {
        console.error("ID du jour manquant !");
        return;
    }

    // S'assurer que les heures ont le bon format 'HH:MM:SS'
    const formattedArrival = selectedDay.value.arrival.length === 5 ? `${selectedDay.value.arrival}:00` : selectedDay.value.arrival;
    const formattedDeparture = selectedDay.value.departure.length === 5 ? `${selectedDay.value.departure}:00` : selectedDay.value.departure;

    try {
        // Envoi des modifications avec les heures formatées
        await axios.post(`/update-day/${selectedDay.value.id}`, {
            arrival: formattedArrival,
            departure: formattedDeparture,
        });

        // Mettre à jour directement les heures dans le tableau filteredDays
        const dayToUpdate = filteredDays.value.find(day => day.id === selectedDay.value.id);
        if (dayToUpdate) {
            dayToUpdate.arrival = formattedArrival;
            dayToUpdate.departure = formattedDeparture;
            dayToUpdate.total = calculateDailyTotal(formattedArrival, formattedDeparture); // Met à jour le total pour ce jour
        }

        // Recalculer le total des heures pour le mois
        calculateTotalMinutes();

        // Afficher le toast de succès
        showSuccessToast.value = true;
        setTimeout(() => {
            showSuccessToast.value = false;
        }, 5000);

        // Fermer le modal après la mise à jour
        closeModal();
    } catch (error) {
        console.error("Erreur lors de la sauvegarde des modifications :", error);
    }
};

// Variables pour le modal d'ajout
const isAddDayModalOpen = ref(false);
const newDay = ref({
    day: 'Lundi', // Par défaut Lundi
    date: '',
    arrival: '',
    departure: ''
});

// Ouvrir le modal pour ajouter un jour
const openAddDayModal = () => {
    isAddDayModalOpen.value = true;
};

// Fermer le modal d'ajout
const closeAddDayModal = () => {
    isAddDayModalOpen.value = false;
    newDay.value = { day: 'Lundi', date: '', arrival: '', departure: '' }; // Réinitialiser les champs
};

// Fonction pour ajouter un nouveau jour
const addDay = async () => {
    try {
        await axios.post('/add-day', {
            user_id: props.user.id, // Récupérer l'ID de l'utilisateur cliqué
            day: newDay.value.day,
            date: newDay.value.date,
            arrival: `${newDay.value.arrival}:00`,
            departure: `${newDay.value.departure}:00`,
        });

        // Fermer le modal après succès
        closeAddDayModal();
        // Rafraîchir la liste des jours après l'ajout
        window.location.reload(); // Pour l'instant, recharger la page
    } catch (error) {
        console.error('Erreur lors de l\'ajout du jour :', error);
    }
};

</script>

<template>
    <Head title="Historique des pointages" />

    <AuthenticatedLayout>
        <template #header>
    <h2 class="font-semibold text-xl text-white bg-gray-800 leading-tight">
        <!-- Si isShow est vrai, afficher "Historique des pointages du user cliqué" -->
        <span v-if="isShow">Historique des pointages de {{ user.name }}</span>
        <!-- Sinon, garder le texte par défaut -->
        <span v-else>Historique des pointages</span>
    </h2>
</template>



        <section
            class="attendance-section w-full max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 bg-white pt-16 md:pt-24 pb-20"
        >

            <!-- Flashmessage pour l'enregistrement des jours -->
            <div v-if="showSuccessToast" 
                class="fixed top-4 right-4 bg-green-800 text-white p-4 rounded-lg shadow-lg transition-opacity duration-500 opacity-100">
                L'heure a bien été sauvegardée avec succès !
            </div>


            <!-- Statistiques et actions -->
            <div class="w-full max-w-4xl mx-auto mb-8">
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

            <!-- Table -->
            <div
                class="w-full max-w-4xl mx-auto overflow-x-auto bg-white shadow-md rounded-lg"
            >
                <!-- Si selectedMonth === 0, afficher toutes les semaines de tous les mois -->
                <template v-if="selectedMonth === 0">
                    <!-- Bouton pour ajouter un jour -->
                    <div v-if="isShow" class="pt-6 flex justify-start mb-4">
                        <PrimaryButton @click="openAddDayModal">
                            <i class="fas fa-plus mr-2"></i> Ajouter un jour
                        </PrimaryButton>
                    </div>
                    <div
                        v-for="monthIndex in 12"
                        :key="monthIndex"
                        class="mb-12"
                    >
                        <div
                            v-if="
                                getAllWeeksInMonth(selectedYear, monthIndex)
                                    .length > 0
                            "
                            class="min-w-full pt-0 border border-gray-800"
                        >
                            <!-- Conteneur avec overflow-x-auto pour le défilement horizontal -->
                            <div class="overflow-x-auto">
                                <table
                                    class="min-w-full divide-y divide-gray-200 mb-8"
                                >
                                    <thead class="bg-[rgb(0,85,150)]">
                                        <tr>
                                            <th
                                                class="py-4 text-sm sm:text-base font-bold px-4 sm:px-6 text-left md:text-center text-gray-100"
                                                colspan="3"
                                            >
                                                {{ months[monthIndex].name }}
                                                {{ selectedYear }}
                                            </th>
                                        </tr>
                                    </thead>
                                    <thead>
                                        <tr>
                                            <th
                                                class="px-4 sm:px-6 py-2 sm:py-3 text-left text-xs font-medium text-gray-800 uppercase tracking-wider"
                                            >
                                                Semaine
                                            </th>
                                            <th
                                                class="px-4 sm:px-6 py-2 sm:py-3 text-center text-xs font-medium text-gray-800 uppercase tracking-wider"
                                            >
                                                Date
                                            </th>
                                            <th
                                                class="px-4 sm:px-6 py-2 sm:py-3 text-center text-xs font-medium text-gray-800 uppercase tracking-wider"
                                            >
                                                Total des heures
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody
                                        class="bg-white divide-y divide-gray-200"
                                    >
                                        <template
                                            v-for="(
                                                week, index
                                            ) in calculateWeeklyTotals(
                                                getAllWeeksInMonth(
                                                    selectedYear,
                                                    monthIndex
                                                ),
                                                props.days
                                            )"
                                            :key="index"
                                        >
                                            <tr class="transition-colors">
                                                <td
                                                    class="px-4 sm:px-6 py-2 sm:py-4 whitespace-nowrap text-sm md:text-base"
                                                >
                                                    Semaine {{ index + 1 }}
                                                </td>
                                                <td
                                                    class="px-4 sm:px-6 text-center py-2 sm:py-4 whitespace-nowrap text-sm md:text-base"
                                                >
                                                    {{ week.dateRange }}
                                                </td>
                                                <td
                                                    class="px-4 sm:px-6 py-2 sm:py-4 text-center whitespace-nowrap text-sm md:text-base"
                                                >
                                                    {{ week.totalHours }}
                                                </td>
                                            </tr>
                                        </template>
                                    </tbody>
                                </table>
                            </div>

                            <!-- Affichage du total des jours et des heures pour chaque mois -->
                            <div
                                class="bg-gray-100 text-gray-800 p-3 sm:p-4 flex flex-col sm:flex-row justify-between items-start sm:items-center space-y-2 sm:space-y-0"
                            >
                                <div class="flex items-center space-x-4">
                                    <i
                                        class="fas fa-calendar-alt text-green-500 mr-0 text-xs sm:text-sm"
                                    ></i>
                                    <span
                                        class="font-semibold text-xs sm:text-sm"
                                    >
                                        Jours enregistrés :
                                        {{
                                            getTotalDaysForMonth(
                                                monthIndex,
                                                selectedYear
                                            )
                                        }}
                                    </span>
                                </div>
                                <div class="flex items-center space-x-4">
                                    <i
                                        class="fas fa-clock text-blue-500 mr-0 text-xs sm:text-sm"
                                    ></i>
                                    <span
                                        class="font-semibold text-xs sm:text-sm"
                                    >
                                        Total des heures :
                                        {{
                                            getTotalHoursForMonth(
                                                monthIndex,
                                                selectedYear
                                            )
                                        }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </template>

                <!-- Si selectedMonth !== 0, afficher les jours filtrés -->
                <template v-else>
                    <!-- Bouton pour ajouter un jour -->
                    <div v-if="isShow" class="pt-6 flex justify-start mb-4">
                        <PrimaryButton @click="openAddDayModal">
                            <i class="fas fa-plus mr-2"></i> Ajouter un jour
                        </PrimaryButton>
                    </div>
                    
                    <!-- Conteneur pour gérer le défilement horizontal -->
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-[rgb(0,85,150)]">
                                <tr>
                                    <th
                                        class="px-4 sm:px-6 py-2 sm:py-3 text-left text-xs font-medium text-gray-100 uppercase tracking-wider"
                                    >
                                        Jour
                                    </th>
                                    <th
                                        class="px-4 sm:px-6 py-2 sm:py-3 text-left text-xs font-medium text-gray-100 uppercase tracking-wider"
                                    >
                                        Date
                                    </th>
                                    <th
                                        class="px-4 sm:px-6 py-2 sm:py-3 text-center text-xs font-medium text-gray-100 uppercase tracking-wider"
                                    >
                                        Arrivée
                                    </th>
                                    <th
                                        class="px-4 sm:px-6 py-2 sm:py-3 text-center text-xs font-medium text-gray-100 uppercase tracking-wider"
                                    >
                                        Départ
                                    </th>
                                    <th
                                        class="px-4 sm:px-6 py-2 sm:py-3 text-right text-xs font-medium text-gray-100 uppercase tracking-wider"
                                    >
                                        Total
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <tr v-if="filteredDays.length === 0">
                                    <td
                                        colspan="5"
                                        class="px-4 sm:px-6 py-4 text-center text-sm md:text-base"
                                    >
                                        Aucun pointage trouvé pour la période
                                        sélectionnée.
                                    </td>
                                </tr>
                                <template
                                    v-for="day in filteredDays"
                                    :key="day.id"
                                >
                                    <tr
                                        :class="['hover:bg-gray-50 transition-colors', isShow ? 'cursor-pointer' : '']"
                                        @click="isShow && openModal(day)"
                                    >
                                        <td
                                            class="px-4 sm:px-6 py-2 sm:py-4 whitespace-nowrap text-sm md:text-base"
                                        >
                                            {{ day.day }}
                                        </td>
                                        <td
                                            class="px-4 sm:px-6 py-2 sm:py-4 whitespace-nowrap text-sm md:text-base"
                                        >
                                            {{
                                                new Date(
                                                    day.date
                                                ).toLocaleDateString("fr-FR")
                                            }}
                                        </td>
                                        <td
                                            class="px-4 sm:px-6 py-2 sm:py-4 text-center whitespace-nowrap text-sm md:text-base"
                                        >
                                            {{ formatTime(day.arrival) }}
                                        </td>
                                        <td
                                            class="px-4 sm:px-6 py-2 sm:py-4 text-center whitespace-nowrap text-sm md:text-base"
                                        >
                                            {{ formatTime(day.departure) }}
                                        </td>
                                        <td
                                            class="px-4 sm:px-6 py-2 sm:py-4 text-right whitespace-nowrap text-sm md:text-base"
                                        >
                                            {{
                                                calculateDailyTotal(
                                                    day.arrival,
                                                    day.departure
                                                )
                                            }}
                                        </td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                    </div>

                    <!-- Affichage du total des jours et des heures pour chaque mois -->
                    <div
                        class="bg-gray-100 text-gray-800 p-3 sm:p-4 flex flex-col sm:flex-row justify-between items-start sm:items-center space-y-2 sm:space-y-0 mt-4"
                    >
                        <div class="flex items-center space-x-4">
                            <i
                                class="fas fa-calendar-alt text-green-500 mr-0 text-xs sm:text-sm"
                            ></i>
                            <span class="font-semibold text-xs sm:text-sm">
                                Jours enregistrés :
                                {{
                                   totalDaysRecorded
                                }}
                            </span>
                        </div>
                        <div class="flex items-center space-x-4">
                            <i
                                class="fas fa-clock text-blue-500 mr-0 text-xs sm:text-sm"
                            ></i>
                            <span class="font-semibold text-xs sm:text-sm">
                                Total des heures :
                                {{
                                    formattedTotalHours
                                }}
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
                        <i class="fas fa-arrow-left mr-2"></i> Retour à la gestion des employés
                    </PrimaryButton>
                </Link>
                <!-- Sinon, afficher le bouton pour retourner au dashboard -->
                <Link v-else :href="route('dashboard')">
                    <PrimaryButton>
                        <i class="fas fa-arrow-left mr-2"></i> Retour à l'accueil
                    </PrimaryButton>
                </Link>
            </div>


            <!-- Modal pour modifier les jours -->
            <div v-if="isModalOpen" class="fixed z-50 inset-0 flex items-center justify-center">
                <div class="bg-white rounded-lg shadow-lg p-6 w-full max-w-md">
                    <!-- Affichage du jour sélectionné -->
                    <h2 class="text-xl font-semibold mb-4">
                        Modifier le jour : {{ new Date(selectedDay.date).toLocaleDateString("fr-FR") }}
                    </h2>

                    <!-- Champ pour modifier l'heure d'arrivée -->
                    <div class="mb-4">
                        <label for="arrival" class="block text-sm font-medium text-gray-700">
                            Heure d'arrivée
                        </label>
                        <input
                            id="arrival"
                            type="time"
                            v-model="selectedDay.arrival"
                            class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                        />
                    </div>

                    <!-- Champ pour modifier l'heure de départ -->
                    <div class="mb-4">
                        <label for="departure" class="block text-sm font-medium text-gray-700">
                            Heure de départ
                        </label>
                        <input
                            id="departure"
                            type="time"
                            v-model="selectedDay.departure"
                            class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                        />
                    </div>

                    <!-- Boutons pour annuler ou sauvegarder -->
                    <div class="flex justify-end space-x-4">
                        <button @click="closeModal" class="bg-gray-100 text-gray-600 px-4 rounded-md font-bold hover:bg-gray-50 hover:text-gray-500">
                            Annuler
                        </button>
                        <PrimaryButton @click="saveDayChanges">
                            Enregistrer
                        </PrimaryButton>
                    </div>
                </div>
            </div>

            <!-- Modal pour ajouter un nouveau jour -->
            <div v-if="isAddDayModalOpen" class="fixed z-50 inset-0 flex items-center justify-center">
                <div class="bg-white rounded-lg shadow-lg p-6 w-full max-w-md">
                    <!-- Formulaire pour ajouter un jour -->
                    <h2 class="text-xl font-semibold mb-4">Ajouter un jour</h2>

                    <!-- Champ pour sélectionner le jour de la semaine -->
                    <div class="mb-4">
                        <label for="day" class="block text-sm font-medium text-gray-700">Jour</label>
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
                        <label for="date" class="block text-sm font-medium text-gray-700">Date</label>
                        <input
                            id="date"
                            type="date"
                            v-model="newDay.date"
                            class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                        />
                    </div>

                    <!-- Champ pour l'heure d'arrivée -->
                    <div class="mb-4">
                        <label for="arrival" class="block text-sm font-medium text-gray-700">Heure d'arrivée</label>
                        <input
                            id="arrival"
                            type="time"
                            v-model="newDay.arrival"
                            class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                        />
                    </div>

                    <!-- Champ pour l'heure de départ -->
                    <div class="mb-4">
                        <label for="departure" class="block text-sm font-medium text-gray-700">Heure de départ</label>
                        <input
                            id="departure"
                            type="time"
                            v-model="newDay.departure"
                            class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                        />
                    </div>

                    <!-- Boutons pour annuler ou ajouter -->
                    <div class="flex justify-end space-x-4">
                        <button @click="closeAddDayModal" class="bg-gray-100 text-gray-600 px-4 rounded-md font-bold hover:bg-gray-50 hover:text-gray-500">Annuler</button>
                        <PrimaryButton @click="addDay">
                            Ajouter
                        </PrimaryButton>
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

.month-table {
    margin-top: 4px;
    margin-bottom: 12px;
}

.fixed {
    background-color: rgba(0, 0, 0, 0.5);
}
</style
