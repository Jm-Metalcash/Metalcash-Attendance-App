<script setup>
import { ref, watch, onMounted, onUnmounted, computed } from "vue";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import { Head, Link } from "@inertiajs/vue3";
import ModalDashboard from "@/Components/ModalDashboard.vue";
import axios from "axios";

// Tableau des jours de la semaine
const defaultDays = [
    {
        day: "Lundi",
        date: getWeekDate(1),
        arrival: "",
        departure: "",
        total: "",
    },
    {
        day: "Mardi",
        date: getWeekDate(2),
        arrival: "",
        departure: "",
        total: "",
    },
    {
        day: "Mercredi",
        date: getWeekDate(3),
        arrival: "",
        departure: "",
        total: "",
    },
    {
        day: "Jeudi",
        date: getWeekDate(4),
        arrival: "",
        departure: "",
        total: "",
    },
    {
        day: "Vendredi",
        date: getWeekDate(5),
        arrival: "",
        departure: "",
        total: "",
    },
];

// Créer une référence pour les jours de la semaine
const days = ref([...defaultDays]);

// Reformater les heures de la base de données pour qu'elles soient au format HH:mm
function formatDatabaseTime(time) {
    if (!time) return null; // Si l'heure est null, retourner null
    const [hour, minute] = time.split(":"); // Prendre uniquement les heures et minutes
    return `${hour}:${minute}`; // Retourner l'heure au format HH:mm
}

// Charger les données depuis la base de données
onMounted(() => {
    axios
        .get("/dashboard/days")
        .then((response) => {
            const data = response.data;

            // Combiner les jours de la semaine avec les données de la base
            days.value = defaultDays.map((defaultDay) => {
                const dbDay = data.find((d) => d.day === defaultDay.day);
                if (dbDay) {
                    // Reformater les heures récupérées pour ne pas avoir de secondes
                    dbDay.arrival = formatDatabaseTime(dbDay.arrival);
                    dbDay.departure = formatDatabaseTime(dbDay.departure);
                    return { ...defaultDay, ...dbDay };
                } else {
                    return defaultDay;
                }
            });
        })
        .catch((error) => {
            console.error(
                "Erreur lors de la récupération des données : ",
                error
            );
        });
});

const arrivalButtonRef = ref(null);
const departureButtonRef = ref(null);

// Variable qui stocke le total des heures de travail de la semaine
const weeklyTotal = ref("00h00");

// Variables pour le modal
const showModal = ref(false);
const actionType = ref("");
let currentTime = "";
// Variable réactive pour stocker l'heure actuelle
const currentTimeReactive = ref(
    new Date().toLocaleTimeString("fr-FR", {
        hour: "2-digit",
        minute: "2-digit",
    })
);

// Ouvrir le modal
function openModal(type) {
    actionType.value = type;
    currentTime = new Date().toLocaleTimeString("fr-FR", {
        hour: "2-digit",
        minute: "2-digit",
    });
    showModal.value = true;
}

// Fonction pour calculer la différence entre l'heure d'arrivée et de départ
function calculateTotal(arrival, departure) {
    if (!arrival || !departure) return "00:00";

    const [arrivalHour, arrivalMinute] = arrival.split(":").map(Number);
    const [departureHour, departureMinute] = departure.split(":").map(Number);

    let totalMinutes =
        departureHour * 60 +
        departureMinute -
        (arrivalHour * 60 + arrivalMinute);

    // Gérer les cas où le départ est le lendemain
    if (totalMinutes < 0) totalMinutes += 24 * 60;

    const hours = Math.floor(totalMinutes / 60);
    const minutes = totalMinutes % 60;

    return `${hours}h${minutes.toString().padStart(2, "0")}`;
}

// Fonction pour calculer le total de la semaine
function calculateWeeklyTotal() {
    let totalMinutes = 0;

    days.value.forEach((day) => {
        if (day.total) {
            const [hours, minutes] = day.total.split("h").map(Number);
            totalMinutes += hours * 60 + minutes;
        }
    });

    const totalHours = Math.floor(totalMinutes / 60);
    const remainingMinutes = totalMinutes % 60;

    return `${totalHours}h${remainingMinutes.toString().padStart(2, "0")}`;
}

function formatDateForBackend(date) {
    const [day, month, year] = date.split("/");
    return `${year}-${month}-${day}`; // Retourne la date au format Y-m-d
}

// Fonction pour reformater l'heure au format HH:mm
function formatTimeToHoursMinutes(time) {
    if (!time) return null;
    const [hour, minute] = time.split(":");
    return `${hour}:${minute}`; // Retourne l'heure au format HH:mm
}

// Confirmer l'action et enregistrer l'heure
function confirmAction() {
    const today = new Date().toLocaleDateString("fr-FR");
    const dayInfo = days.value.find((day) => day.date === today);

    if (actionType.value === "arrival") {
        // Reformater l'heure avant de l'enregistrer
        dayInfo.arrival = formatTimeToHoursMinutes(currentTime);
    } else if (actionType.value === "departure") {
        // Reformater l'heure avant de l'enregistrer
        dayInfo.departure = formatTimeToHoursMinutes(currentTime);
        dayInfo.total = calculateTotal(dayInfo.arrival, dayInfo.departure);
    }

    // Conversion de la date au format 'Y-m-d' avant de l'envoyer à la base de données
    const formattedDate = formatDateForBackend(dayInfo.date);

    axios
        .post("/days/store", {
            day: dayInfo.day,
            date: formattedDate, // Utiliser la date au format 'Y-m-d'
            arrival: dayInfo.arrival,
            departure: dayInfo.departure,
            total: dayInfo.total,
        })
        .then((response) => {
            console.log(response.data.message);
        })
        .catch((error) => {
            if (error.response && error.response.status === 422) {
                console.error(
                    "Erreur de validation : ",
                    error.response.data.errors
                );
            } else {
                console.error(
                    "Erreur lors de l'enregistrement des données :",
                    error
                );
            }
        });

    showModal.value = false;
}

// Annuler l'action
function cancelAction() {
    showModal.value = false;
}

// Met à jour le total de la semaine chaque fois que les heures d'un jour sont modifiées
watch(
    days,
    () => {
        weeklyTotal.value = calculateWeeklyTotal();
    },
    { deep: true }
);

// Fonction pour obtenir la date de la semaine
function getWeekDate(dayIndex) {
    const today = new Date();
    const currentDayOfWeek = today.getDay();
    const distanceFromMonday =
        currentDayOfWeek === 0 ? 6 : currentDayOfWeek - 1;
    const firstDayOfWeek = new Date(
        today.setDate(today.getDate() - distanceFromMonday)
    );
    const date = new Date(firstDayOfWeek);
    date.setDate(firstDayOfWeek.getDate() + (dayIndex - 1));
    return date.toLocaleDateString("fr-FR");
}

// Fonction pour obtenir la date au format "jj mois année"
function formatDateVerbose(date) {
    const options = {
        day: "numeric",
        month: "long",
        year: "numeric",
    };
    return date.toLocaleDateString("fr-FR", options);
}

// Fonction pour vérifier si l'arrivée a déjà été enregistrée pour aujourd'hui
const today = new Date().toLocaleDateString("fr-FR");
// Vérifier si une arrivée est enregistrée pour aujourd'hui
const isArrivalRecordedForToday = computed(() => {
    const dayInfo = days.value.find((day) => day.date === today);
    return dayInfo ? dayInfo.arrival !== null && dayInfo.arrival !== "" : false;
});

// Fonction pour vérifier si le départ a déjà été enregistré pour aujourd'hui
const isDepartureRecordedForToday = computed(() => {
    const dayInfo = days.value.find((day) => day.date === today);
    return dayInfo
        ? dayInfo.departure !== null && dayInfo.departure !== ""
        : false;
});

// Mettre à jour l'heure actuelle chaque seconde
onMounted(() => {
    const intervalId = setInterval(() => {
        currentTimeReactive.value = new Date().toLocaleTimeString("fr-FR", {
            hour: "2-digit",
            minute: "2-digit",
        });
    }, 1000);

    onUnmounted(() => {
        clearInterval(intervalId);
    });
});
</script>

<template>
    <Head title="Aujourd'hui" />

    <AuthenticatedLayout>
        <template #header>
            <h2
                class="font-semibold text-xl text-white bg-gray-800 leading-tight"
            >
                Aujourd'hui
            </h2>
        </template>

        <section
            class="attendance-section w-full max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 bg-white pt-8 pb-20"
        >
            <div class="mt-8 flex flex-col items-center space-y-8">
                <!-- Bloc de date, heure, et citation -->
                <div
                    class="p-6 rounded-lg text-center w-full bg-white shadow-md"
                >
                    <h2 class="text-gray-800 text-xl sm:text-2xl font-semibold">
                        <i class="fas fa-clock text-blue-600 mr-2"></i> Suivi du
                        temps de travail
                    </h2>
                    <p class="text-gray-600 mt-4 text-sm sm:text-base">
                        Bonjour
                        <span class="font-bold">{{
                            $page.props.auth.user.name
                        }}</span
                        >, nous sommes le :
                        <span class="font-semibold text-gray-900">
                            {{ formatDateVerbose(new Date()) }}
                        </span>
                    </p>
                    <p class="text-gray-600 mt-2 text-sm sm:text-base">
                        <i class="fas fa-hourglass-half mr-1"></i> Heure
                        actuelle :
                        <span class="font-semibold text-gray-900">{{
                            currentTimeReactive
                        }}</span>
                    </p>
                </div>

                <!-- Boutons d'arrivée et de départ -->
                <div
                    class="grid grid-cols-1 sm:grid-cols-2 gap-4 w-full max-w-lg"
                >
                    <!-- Heure d'arrivée -->
                    <div
                        class="p-6 bg-white rounded-lg shadow-md hover:shadow-lg transition-shadow duration-300 text-center"
                    >
                        <h3 class="text-gray-800 text-lg font-semibold">
                            <i
                                class="fas fa-sign-in-alt text-green-600 mr-2"
                            ></i>
                            Heure d'arrivée
                        </h3>
                        <button
                            :disabled="isArrivalRecordedForToday"
                            ref="arrivalButtonRef"
                            id="arrivalButton"
                            @click="openModal('arrival')"
                            :class="{
                                'bg-green-700 text-white cursor-not-allowed':
                                    isArrivalRecordedForToday,
                                'text-green-700 border border-green-700 bg-white hover:text-white hover:bg-green-800 cursor-pointer':
                                    !isArrivalRecordedForToday,
                            }"
                            class="mt-4 transition-colors duration-300 focus:ring-4 focus:outline-none font-medium rounded-full text-sm px-6 py-3"
                        >
                            <i class="fas fa-arrow-right"></i> Enregistrer
                        </button>
                        <p class="mt-2 text-gray-600 text-sm">
                            Heure d'arrivée enregistrée pour aujourd'hui
                        </p>
                        <span
                            id="hourArrivalToday"
                            class="block text-lg mt-2 font-semibold text-gray-600"
                        >
                            {{
                                days.find((day) => day.date === today)
                                    ?.arrival || "Non enregistré"
                            }}
                        </span>
                    </div>

                    <!-- Heure de départ -->
                    <div
                        class="p-6 bg-white rounded-lg shadow-md hover:shadow-lg transition-shadow duration-300 text-center"
                    >
                        <h3 class="text-gray-800 text-lg font-semibold">
                            <i
                                class="fas fa-sign-out-alt text-red-600 mr-2"
                            ></i>
                            Heure de départ
                        </h3>
                        <button
                            :disabled="isDepartureRecordedForToday"
                            ref="departureButtonRef"
                            id="departureButton"
                            @click="openModal('departure')"
                            :class="{
                                'bg-red-700 text-white cursor-not-allowed':
                                    isDepartureRecordedForToday,
                                'text-red-700 border border-red-700 bg-white hover:text-white hover:bg-red-800 cursor-pointer':
                                    !isDepartureRecordedForToday,
                            }"
                            class="mt-4 transition-colors duration-300 focus:ring-4 focus:outline-none font-medium rounded-full text-sm px-6 py-3"
                        >
                            <i class="fas fa-arrow-left"></i> Enregistrer
                        </button>
                        <p class="mt-2 text-gray-600 text-sm">
                            Heure de départ enregistrée pour aujourd'hui
                        </p>
                        <span
                            id="hourDepartureToday"
                            class="block text-lg mt-2 font-semibold text-gray-600"
                            >{{
                                days.find((day) => day.date === today)
                                    ?.departure || "Non enregistré"
                            }}</span
                        >
                    </div>
                </div>

                <h3
                    class="bg-[rgb(0,85,150)] w-full max-w-4xl mx-auto text-gray-100 p-4 text-left font-bold mt-12 mb-4"
                >
                    Cette semaine :
                </h3>
                <!-- Table des heures de la semaine -->
                <div class="w-full max-w-4xl mx-auto overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead>
                            <tr>
                                <th
                                    class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                                >
                                    Jour
                                </th>
                                <th
                                    class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                                >
                                    Date
                                </th>
                                <th
                                    class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider"
                                >
                                    Arrivée
                                </th>
                                <th
                                    class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider"
                                >
                                    Départ
                                </th>
                                <th
                                    class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider"
                                >
                                    Total
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <tr v-for="(day, index) in days" :key="index">
                                <td class="px-4 py-4 whitespace-nowrap">
                                    {{ day.day }}
                                </td>
                                <td class="px-4 py-4 whitespace-nowrap">
                                    {{ day.date }}
                                </td>
                                <td
                                    class="px-4 py-4 whitespace-nowrap text-center"
                                >
                                    {{ day.arrival || "--:--" }}
                                </td>
                                <td
                                    class="px-4 py-4 whitespace-nowrap text-center"
                                >
                                    {{ day.departure || "--:--" }}
                                </td>
                                <td
                                    class="px-4 py-4 whitespace-nowrap text-right"
                                >
                                    {{ day.total || "--:--" }}
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Total des heures de la semaine -->
                <div
                    class="totalHour w-full max-w-4xl mx-auto px-2 sm:px-4 py-2 bg-[rgb(0,85,150)] text-center sm:text-right"
                >
                    <h3
                        class="text-white text-lg font-semibold flex justify-center sm:justify-end items-center"
                    >
                        <i class="fas fa-calendar-week text-white mr-2"></i>
                        Total de la semaine
                    </h3>
                    <p class="text-white mt-2 sm:mt-4 text-base">
                        <strong>Total :</strong>
                        <span class="font-semibold text-white ml-2 text-lg">{{
                            weeklyTotal
                        }}</span>
                    </p>
                </div>

                <div class="pt-4">
                    <Link :href="route('historique')">
                        <PrimaryButton>Voir historique complet</PrimaryButton>
                    </Link>
                </div>
            </div>
        </section>

        <!-- Modal -->
        <ModalDashboard
            :showModal="showModal"
            :currentTime="currentTime"
            :actionType="actionType"
            @confirm="confirmAction"
            @cancel="cancelAction"
        />
    </AuthenticatedLayout>
</template>

<style scoped>
.totalHour {
    margin-top: 0px !important;
}
</style>
