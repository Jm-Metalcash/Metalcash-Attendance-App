<script setup>
import { ref, computed, watch } from "vue";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head, Link } from "@inertiajs/vue3";
import PrimaryButton from "@/Components/PrimaryButton.vue";

// Les données des jours sont passées à partir du contrôleur Laravel via Inertia
const props = defineProps(["days"]);

// Filtre sélectionné pour l'année et le mois
const selectedYear = ref(new Date().getFullYear());
const selectedMonth = ref(0); // 0 pour "Tous les mois"

// Filtrer les jours en fonction de l'année et du mois sélectionnés
const filteredDays = ref([]);
const totalMinutesWorked = ref(0); // Stocke le total des minutes

// Fonction pour calculer la différence entre l'heure d'arrivée et l'heure de départ
const calculateDailyTotal = (arrival, departure) => {
    if (!arrival || !departure) return "0h00"; // Si l'une des heures est manquante

    const [arrivalHours, arrivalMinutes] = arrival.split(":").map(Number);
    const [departureHours, departureMinutes] = departure.split(":").map(Number);

    const arrivalDate = new Date();
    arrivalDate.setHours(arrivalHours, arrivalMinutes, 0);

    const departureDate = new Date();
    departureDate.setHours(departureHours, departureMinutes, 0);

    // Calcul de la différence en minutes
    const differenceInMinutes = (departureDate - arrivalDate) / 1000 / 60;

    if (differenceInMinutes < 0) {
        return "0h00"; // Retourne 0 si l'heure de départ est avant l'arrivée
    }

    const hours = Math.floor(differenceInMinutes / 60);
    const minutes = differenceInMinutes % 60;
    return `${hours}h${minutes.toString().padStart(2, "0")}`;
};

// Fonction pour filtrer les jours en fonction de l'année et du mois sélectionnés
const filterDays = () => {
    filteredDays.value = props.days.map((day) => {
        // Calculer le total dynamique en fonction des heures d'arrivée et de départ
        const total = calculateDailyTotal(day.arrival, day.departure);

        return {
            ...day,
            total, // Mettre à jour le champ total avec le calcul dynamique
        };
    }).filter((day) => {
        const dayDate = new Date(day.date);
        const isSameYear = dayDate.getFullYear() === selectedYear.value;
        const isSameMonth =
            selectedMonth.value === 0 ||
            dayDate.getMonth() + 1 === selectedMonth.value;
        return isSameYear && isSameMonth;
    });
};

// Fonction pour calculer le total des minutes travaillées
const calculateTotalMinutes = () => {
    totalMinutesWorked.value = filteredDays.value.reduce((total, day) => {
        if (day.total) {
            const [hours, minutes] = day.total.split("h").map(Number);
            total += hours * 60 + minutes;
        }
        return total;
    }, 0);
};

// Watch sur les filtres pour recalculer les jours filtrés et les heures
watch([selectedYear, selectedMonth, () => props.days], () => {
    filterDays();
    calculateTotalMinutes();
}, { immediate: true });

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

// Filter les jours pour le mois sélectionné
function filteredDaysForMonth(monthIndex) {
    return props.days.filter((day) => {
        const dayDate = new Date(day.date);
        return (
            dayDate.getFullYear() === selectedYear.value &&
            dayDate.getMonth() + 1 === monthIndex
        );
    });
}

// Calcul du total des heures travaillées pour un mois donné
function formattedTotalHoursForMonth(monthIndex) {
    const totalMinutesForMonth = filteredDaysForMonth(monthIndex).reduce(
        (total, day) => {
            const calculatedTotal = calculateDailyTotal(day.arrival, day.departure);
            const [hours, minutes] = calculatedTotal.split("h").map(Number);
            total += hours * 60 + minutes;
            return total;
        },
        0
    );

    const hours = Math.floor(totalMinutesForMonth / 60);
    const minutes = totalMinutesForMonth % 60;
    return `${hours}h${minutes.toString().padStart(2, "0")}`;
}
</script>


<template>
    <Head title="Historique des pointages" />

    <AuthenticatedLayout>
        <template #header>
            <h2
                class="font-semibold text-xl text-white bg-gray-800 leading-tight"
            >
                Historique des pointages
            </h2>
        </template>

        <section
            class="attendance-section w-full max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 bg-white pt-16 md:pt-24 pb-20"
        >
            <!-- Statistiques et actions -->
            <div class="w-full max-w-4xl mx-auto mb-8">
                <div
                    class="flex flex-col md:flex-row justify-between items-start md:items-center bg-gray-100 p-6 rounded-lg shadow-md space-y-6 md:space-y-0"
                >
                    <!-- Section informations sur le total des heures et des jours enregistrés -->
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

                    <!-- Filtres par année et mois sur la même ligne que les informations pour tablette et desktop -->
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
                <!-- Si selectedMonth === 0, on boucle sur les mois et répète la table -->
                <template v-if="selectedMonth === 0">
                    <div v-for="monthIndex in 12" :key="monthIndex">
                        <!-- Filtrer les jours du mois actuel -->
                        <div
                            v-if="filteredDaysForMonth(monthIndex).length > 0"
                            class="month-table max-w-4xl pt-0 border border-gray-800 overflow-x-auto"
                        >
                            <div class="bg-[rgb(0,85,150)] w-[200%] md:w-full">
                                <h3
                                    class="py-4 text-lg font-bold px-6 text-left md:text-center text-gray-100"
                                >
                                    {{ months[monthIndex].name }}
                                    {{ selectedYear }}
                                </h3>
                            </div>

                            <table
                                class="min-w-full divide-y divide-gray-200 mb-8"
                            >
                                <thead>
                                    <tr>
                                        <th
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-800 uppercase tracking-wider"
                                        >
                                            Jour
                                        </th>
                                        <th
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-800 uppercase tracking-wider"
                                        >
                                            Date
                                        </th>
                                        <th
                                            class="px-6 py-3 text-center text-xs font-medium text-gray-800 uppercase tracking-wider"
                                        >
                                            Arrivée
                                        </th>
                                        <th
                                            class="px-6 py-3 text-center text-xs font-medium text-gray-800 uppercase tracking-wider"
                                        >
                                            Départ
                                        </th>
                                        <th
                                            class="px-6 py-3 text-right text-xs font-medium text-gray-800 uppercase tracking-wider"
                                        >
                                            Total
                                        </th>
                                    </tr>
                                </thead>
                                <tbody
                                    class="bg-white divide-y divide-gray-200"
                                >
                                    <tr
                                        v-if="
                                            filteredDaysForMonth(monthIndex)
                                                .length === 0
                                        "
                                    >
                                        <td
                                            colspan="5"
                                            class="px-6 py-4 text-center text-sm md:text-base"
                                        >
                                            Aucun pointage trouvé pour
                                            {{ months[monthIndex].name }}.
                                        </td>
                                    </tr>
                                    <template
                                        v-for="day in filteredDaysForMonth(
                                            monthIndex
                                        )"
                                        :key="day.id"
                                    >
                                        <tr
                                            class="hover:bg-gray-50 transition-colors"
                                        >
                                            <td
                                                class="px-6 py-4 whitespace-nowrap text-lg md:text-base"
                                            >
                                                {{ day.day }}
                                            </td>
                                            <td
                                                class="px-6 py-4 whitespace-nowrap text-sm md:text-base"
                                            >
                                                {{
                                                    new Date(
                                                        day.date
                                                    ).toLocaleDateString(
                                                        "fr-FR"
                                                    )
                                                }}
                                            </td>
                                            <td
                                                class="px-6 py-4 text-center whitespace-nowrap text-sm md:text-base"
                                            >
                                                {{ formatTime(day.arrival) }}
                                            </td>
                                            <td
                                                class="px-6 py-4 text-center whitespace-nowrap text-sm md:text-base"
                                            >
                                                {{ formatTime(day.departure) }}
                                            </td>
                                            <td
                                                class="px-6 py-4 text-right whitespace-nowrap text-sm md:text-base"
                                            >
                                            {{ calculateDailyTotal(day.arrival, day.departure) }}
                                            </td>
                                        </tr>
                                    </template>
                                </tbody>
                            </table>

                            <!-- Affichage du total des heures et du nombre de jours prestés pour chaque mois -->
                            <div
                                class="bg-gray-50 px-6 py-4 text-gray-800 border-t border-gray-300 rounded-b-lg w-[200%] md:w-full"
                            >
                                <div
                                    class="flex flex-col sm:flex-row justify-between items-start sm:items-center space-y-4 sm:space-y-0"
                                >
                                    <p class="text-sm font-medium">
                                        <i
                                            class="fas fa-clock text-blue-500 mr-2"
                                        ></i>
                                        Total des heures pour
                                        <span class="font-bold"
                                            >{{ months[monthIndex].name }}
                                            {{ selectedYear }}</span
                                        >
                                        :
                                        <span
                                            class="font-semibold text-[rgb(0,85,150)]"
                                        >
                                            {{
                                                formattedTotalHoursForMonth(
                                                    monthIndex
                                                )
                                            }}
                                        </span>
                                    </p>
                                    <p class="text-sm font-medium">
                                        <i
                                            class="fas fa-calendar-alt text-green-500 mr-2"
                                        ></i>
                                        Nombre de jours prestés :
                                        <span
                                            class="font-semibold text-gray-900"
                                        >
                                            {{
                                                filteredDaysForMonth(monthIndex)
                                                    .length
                                            }}
                                        </span>
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </template>

                <!-- Si selectedMonth !== 0, on garde l'affichage actuel -->
                <template v-else>
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
                                    Date
                                </th>
                                <th
                                    class="px-6 py-3 text-center text-xs font-medium text-gray-100 uppercase tracking-wider"
                                >
                                    Arrivée
                                </th>
                                <th
                                    class="px-6 py-3 text-center text-xs font-medium text-gray-100 uppercase tracking-wider"
                                >
                                    Départ
                                </th>
                                <th
                                    class="px-6 py-3 text-right text-xs font-medium text-gray-100 uppercase tracking-wider"
                                >
                                    Total
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <tr v-if="filteredDays.length === 0">
                                <td
                                    colspan="5"
                                    class="px-6 py-4 text-center text-sm md:text-base"
                                >
                                    Aucun pointage trouvé pour la période
                                    sélectionnée.
                                </td>
                            </tr>
                            <template v-for="day in filteredDays" :key="day.id">
                                <tr class="hover:bg-gray-50 transition-colors">
                                    <td
                                        class="px-6 py-4 whitespace-nowrap text-sm md:text-base"
                                    >
                                        {{ day.day }}
                                    </td>
                                    <td
                                        class="px-6 py-4 whitespace-nowrap text-sm md:text-base"
                                    >
                                        {{
                                            new Date(
                                                day.date
                                            ).toLocaleDateString("fr-FR")
                                        }}
                                    </td>
                                    <td
                                        class="px-6 py-4 text-center whitespace-nowrap text-sm md:text-base"
                                    >
                                        {{ formatTime(day.arrival) }}
                                    </td>
                                    <td
                                        class="px-6 py-4 text-center whitespace-nowrap text-sm md:text-base"
                                    >
                                        {{ formatTime(day.departure) }}
                                    </td>
                                    <td
                                        class="px-6 py-4 text-right whitespace-nowrap text-sm md:text-base"
                                    >
                                    {{ calculateDailyTotal(day.arrival, day.departure) }}
                                    </td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </template>
            </div>

            <div
                class="totalHour max-w-4xl mx-auto px-2 sm:px-4 py-3 bg-gray-800 text-center sm:text-right"
            >
                <h3
                    class="text-white text-base flex justify-center sm:justify-end items-center"
                >
                    <i
                        class="fas fa-calendar-week text-gray-100 mr-2 text-sm"
                    ></i>
                    Total des heures :
                    <span class="font-semibold text-gray-100 ml-2 text-base">{{
                        formattedTotalHours
                    }}</span>
                </h3>
            </div>

            <!-- Retour au Dashboard -->
            <div class="pt-16 flex justify-center">
                <Link :href="route('dashboard')">
                    <PrimaryButton>
                        <i class="fas fa-arrow-left mr-2"></i> Retour à
                        l'accueil
                    </PrimaryButton>
                </Link>
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
</style>
