<script setup>
import { ref, watch, onMounted, onUnmounted, computed } from "vue";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import { Head, Link } from "@inertiajs/vue3";
import ModalDashboard from "@/Components/ModalDashboard.vue";
import axios from "axios";

// Variables réactives
const onInitialPageLoaded = ref(false);
const days = ref([]);
const showModal = ref(false);
const actionType = ref(""); // Peut être "arrival", "departure", "break_start", "break_end"
let currentTime = "";
const weeklyTotal = ref("00h00");
const showBreakButtons = ref(false); // Pour afficher ou cacher les boutons de break

// Stocker l'heure actuelle sous forme réactive
const currentTimeReactive = ref(
    new Date().toLocaleTimeString("fr-FR", {
        hour: "2-digit",
        minute: "2-digit",
    })
);

// Charger les données depuis la base de données
onMounted(() => {
    // Remplir le tableau des jours de la semaine
    for (let i = 1; i <= 5; i++) {
        days.value.push({
            day: ["Lundi", "Mardi", "Mercredi", "Jeudi", "Vendredi"][i - 1],
            date: getWeekDate(i),
            arrival: "",
            departure: "",
            total: "",
            break_start: "",
            break_end: "",
        });
    }

    // Charger les données depuis la base de données
    axios
        .get("/dashboard/days")
        .then((response) => {
            const data = response.data;

            // Combiner les jours de la semaine avec les données de la base
            days.value = days.value.map((defaultDay) => {
                const dbDay = data.find(
                    (d) =>
                        d.day === defaultDay.day && d.date === defaultDay.date
                );
                if (dbDay) {
                    dbDay.arrival = formatDatabaseTime(dbDay.arrival);
                    dbDay.departure = formatDatabaseTime(dbDay.departure);
                    dbDay.break_start = formatDatabaseTime(dbDay.break_start);
                    dbDay.break_end = formatDatabaseTime(dbDay.break_end);
                    dbDay.total = calculateDailyTotal(
                        dbDay.arrival,
                        dbDay.departure,
                        dbDay.break_start,
                        dbDay.break_end
                    );
                    return { ...defaultDay, ...dbDay };
                } else {
                    return defaultDay;
                }
            });

            onInitialPageLoaded.value = true;
        })
        .catch((error) => {
            console.error(
                "Erreur lors de la récupération des données : ",
                error
            );
        });
});

// Ouvrir le modal
function openModal(type) {
    actionType.value = type;
    currentTime = new Date().toLocaleTimeString("fr-FR", {
        hour: "2-digit",
        minute: "2-digit",
    });
    showModal.value = true;
}

// Confirmer l'action dans le modal et enregistrer les données
function confirmAction() {
    const today = new Date().toLocaleDateString("fr-FR");
    const dayInfo = days.value.find((day) => day.date === today);

    if (actionType.value === "arrival") {
        dayInfo.arrival = formatTimeToHoursMinutes(currentTime);
    } else if (actionType.value === "departure") {
        dayInfo.departure = formatTimeToHoursMinutes(currentTime);
        dayInfo.total = calculateDailyTotal(
            dayInfo.arrival,
            dayInfo.departure,
            dayInfo.break_start,
            dayInfo.break_end
        );
    } else if (actionType.value === "break_start") {
        dayInfo.break_start = formatTimeToHoursMinutes(currentTime);
    } else if (actionType.value === "break_end") {
        dayInfo.break_end = formatTimeToHoursMinutes(currentTime);
        dayInfo.total = calculateDailyTotal(
            dayInfo.arrival,
            dayInfo.departure,
            dayInfo.break_start,
            dayInfo.break_end
        );
    }

    const formattedDate = formatDateForBackend(dayInfo.date);

    // Envoi des données à la base de données
    axios
        .post("/days/store", {
            day: dayInfo.day,
            date: formattedDate,
            arrival: dayInfo.arrival,
            departure: dayInfo.departure,
            break_start: dayInfo.break_start,
            break_end: dayInfo.break_end,
            total: dayInfo.total,
        })
        .then((response) => {
            console.log(response.data.message);
        })
        .catch((error) => {
            console.error(
                "Erreur lors de l'enregistrement des données :",
                error
            );
        });

    showModal.value = false;
}

// Annuler l'action
function cancelAction() {
    showModal.value = false;
}

// Calculer le total des heures travaillées en soustrayant 1h pour la pause midi et les heures de break
function calculateDailyTotal(arrival, departure, breakStart, breakEnd) {
    if (!arrival || !departure) return "00h00";

    const [arrivalHour, arrivalMinute] = arrival.split(":").map(Number);
    const [departureHour, departureMinute] = departure.split(":").map(Number);

    let totalMinutes =
        departureHour * 60 +
        departureMinute -
        (arrivalHour * 60 + arrivalMinute);

    // Soustraire le temps de break s'il est défini
    if (breakStart && breakEnd) {
        const [breakStartHour, breakStartMinute] = breakStart
            .split(":")
            .map(Number);
        const [breakEndHour, breakEndMinute] = breakEnd.split(":").map(Number);

        const breakMinutes =
            breakEndHour * 60 +
            breakEndMinute -
            (breakStartHour * 60 + breakStartMinute);

        totalMinutes -= breakMinutes;
    }

    // Soustraire 1 heure pour la pause déjeuner
    totalMinutes -= 60;

    if (totalMinutes < 0) totalMinutes += 24 * 60;

    const hours = Math.floor(totalMinutes / 60);
    const minutes = totalMinutes % 60;

    return `${hours}h${minutes.toString().padStart(2, "0")}`;
}


// Calculer le total de la semaine
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

// Obtenir la date de la semaine
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

// Formatter l'heure au format HH:mm
function formatTimeToHoursMinutes(time) {
    if (!time) return null;
    const [hour, minute] = time.split(":");
    return `${hour}:${minute}`;
}

// Formatter les heures de la base de données
function formatDatabaseTime(time) {
    if (!time) return null;
    const [hour, minute] = time.split(":");
    return `${hour}:${minute}`;
}

// Formatter la date pour l'envoyer au backend
function formatDateForBackend(date) {
    const [day, month, year] = date.split("/");
    return `${year}-${month}-${day}`;
}

// Toggler l'affichage des boutons de sortie
function toggleBreakButtons() {
    showBreakButtons.value = !showBreakButtons.value; // Modifier la valeur
}

// Vérifier si l'arrivée est déjà enregistrée pour aujourd'hui
const today = new Date().toLocaleDateString("fr-FR");
const isArrivalRecordedForToday = computed(() => {
    const dayInfo = days.value.find((day) => day.date === today);
    return dayInfo ? dayInfo.arrival !== null && dayInfo.arrival !== "" : false;
});

// Vérifier si le départ est déjà enregistré pour aujourd'hui
const isDepartureRecordedForToday = computed(() => {
    const dayInfo = days.value.find((day) => day.date === today);
    return dayInfo
        ? dayInfo.departure !== null && dayInfo.departure !== ""
        : false;
});

// Vérifier si le début de break est déjà enregistré pour aujourd'hui
const isBreakStartRecordedForToday = computed(() => {
    const dayInfo = days.value.find((day) => day.date === today);
    return dayInfo
        ? dayInfo.break_start !== null && dayInfo.break_start !== ""
        : false;
});

// Vérifier si la fin de break est déjà enregistrée pour aujourd'hui
const isBreakEndRecordedForToday = computed(() => {
    const dayInfo = days.value.find((day) => day.date === today);
    return dayInfo
        ? dayInfo.break_end !== null && dayInfo.break_end !== ""
        : false;
});

// Fonction pour formater la date au format "jj mois année"
function formatDateVerbose(date) {
    const options = {
        day: "numeric",
        month: "long",
        year: "numeric",
    };
    return date.toLocaleDateString("fr-FR", options);
}

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

// Mettre à jour le total de la semaine lorsque les heures changent
watch(
    days,
    () => {
        weeklyTotal.value = calculateWeeklyTotal();
    },
    { deep: true }
);
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
            class="attendance-section max-w-7xl mx-auto px-4 sm:px-6 min-h-screen lg:px-8 bg-white pt-8 pb-20"
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
                            :disabled="
                                !onInitialPageLoaded ||
                                isArrivalRecordedForToday
                            "
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
                            Heure d'arrivée enregistrée
                        </p>
                        <span
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
                            :disabled="
                                !onInitialPageLoaded ||
                                isDepartureRecordedForToday
                            "
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
                            Heure de départ enregistrée
                        </p>
                        <span
                            class="block text-lg mt-2 font-semibold text-gray-600"
                        >
                            {{
                                days.find((day) => day.date === today)
                                    ?.departure || "Non enregistré"
                            }}
                        </span>
                    </div>
                </div>

                <!-- Bouton pour afficher/masquer les boutons de sortie -->
                <div class="mt-6">
                    <button
                        @click="toggleBreakButtons"
                        class="px-4 py-2 bg-gray-700 text-white text-sm font-semibold rounded-md hover:bg-gray-800 focus:outline-none focus:ring focus:ring-gray-500 flex items-center"
                    >
                        <span v-if="!showBreakButtons"
                            >Enregistrer une sortie en dehors des heures de
                            travail <i class="fa-solid fa-arrow-right ml-2"></i></span
                        >
                        <span v-else
                            >Enregistrer une sortie en dehors des heures de
                            travail <i class="fa-solid fa-arrow-down ml-2"></i></span
                        >
                        
                    </button>
                </div>

                <!-- Boutons de début et fin de sortie (affichés uniquement si showBreakButtons est vrai) -->
                <div
                    v-if="showBreakButtons"
                    class="grid grid-cols-1 sm:grid-cols-2 gap-4 w-full max-w-lg mt-6 pb-12"
                >
                    <!-- Début de la sortie -->
                    <div
                        class="p-6 bg-white rounded-lg shadow-md hover:shadow-lg transition-shadow duration-300 text-center"
                    >
                        <h3 class="text-gray-800 text-lg font-semibold">
                            <i
                                class="fa-solid fa-door-open text-yellow-600 mr-2"
                            ></i>
                            Début de sortie
                        </h3>
                        <button
                            :disabled="
                                !onInitialPageLoaded ||
                                isBreakStartRecordedForToday
                            "
                            @click="openModal('break_start')"
                            :class="{
                                'bg-yellow-700 text-white cursor-not-allowed':
                                    isBreakStartRecordedForToday,
                                'text-yellow-700 border border-yellow-700 bg-white hover:text-white hover:bg-yellow-800 cursor-pointer':
                                    !isBreakStartRecordedForToday,
                            }"
                            class="mt-4 transition-colors duration-300 focus:ring-4 focus:outline-none font-medium rounded-full text-sm px-6 py-3"
                        >
                            <i class="fas fa-arrow-right"></i> Enregistrer
                        </button>
                        <p class="mt-2 text-gray-600 text-sm">
                            Début de sortie enregistré
                        </p>
                        <span
                            class="block text-lg mt-2 font-semibold text-gray-600"
                        >
                            {{
                                days.find((day) => day.date === today)
                                    ?.break_start || "Non enregistré"
                            }}
                        </span>
                    </div>

                    <!-- Fin de la sortie -->
                    <div
                        class="p-6 bg-white rounded-lg shadow-md hover:shadow-lg transition-shadow duration-300 text-center"
                    >
                        <h3 class="text-gray-800 text-lg font-semibold">
                            <i
                                class="fa-solid fa-door-closed text-yellow-600 mr-2"
                            ></i>
                            Fin de sortie
                        </h3>
                        <button
                            :disabled="
                                !onInitialPageLoaded ||
                                isBreakEndRecordedForToday
                            "
                            @click="openModal('break_end')"
                            :class="{
                                'bg-yellow-700 text-white cursor-not-allowed':
                                    isBreakEndRecordedForToday,
                                'text-yellow-700 border border-yellow-700 bg-white hover:text-white hover:bg-yellow-800 cursor-pointer':
                                    !isBreakEndRecordedForToday,
                            }"
                            class="mt-4 transition-colors duration-300 focus:ring-4 focus:outline-none font-medium rounded-full text-sm px-6 py-3"
                        >
                            <i class="fas fa-arrow-left"></i> Enregistrer
                        </button>
                        <p class="mt-2 text-gray-600 text-sm">
                            Fin de sortie enregistrée
                        </p>
                        <span
                            class="block text-lg mt-2 font-semibold text-gray-600"
                        >
                            {{
                                days.find((day) => day.date === today)
                                    ?.break_end || "Non enregistré"
                            }}
                        </span>
                    </div>
                </div>
                
                <!-- TABLEAU CETTE SEMAINE -->
                <h3
                    class="bg-[rgb(0,85,150)] w-full max-w-4xl mx-auto text-gray-100 px-4 py-2 text-left font-bold mt-12 mb-4"
                >
                    Cette semaine :
                </h3>
            
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
                                    class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider"
                                >
                                    Début de sortie
                                </th>
                                <th
                                    class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider"
                                >
                                    Fin de sortie
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
                                    class="px-4 py-4 whitespace-nowrap text-center"
                                >
                                    {{ day.break_start || "--:--" }}
                                </td>
                                <td
                                    class="px-4 py-4 whitespace-nowrap text-center"
                                >
                                    {{ day.break_end || "--:--" }}
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
                    class="totalHour w-full max-w-4xl mx-auto px-2 sm:px-4 py-3 bg-gray-100 text-gray-800 text-center sm:text-right"
                >
                    <h3
                        class="text-base flex justify-center sm:justify-end items-center"
                    >
                        <i class="fas fa-calendar-week mr-2"></i>
                        Total de la semaine :
                        <span
                            class="font-semibold ml-2 text-base"
                            >{{ weeklyTotal }}</span
                        >
                    </h3>
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
