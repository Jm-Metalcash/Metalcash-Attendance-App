<script setup>
import { ref, watch, onMounted } from "vue";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import { Head, Link, usePage } from "@inertiajs/vue3";
import Header from "@/Components/Header.vue";
import axios from "axios";

const { props } = usePage();

// Variables réactives
const days = ref([]);
const onInitialPageLoaded = ref(false);
const expandedDays = ref([]); // Tableau des jours déployés
const weeklyTotal = ref("00h00");

const isArrivalButtonDisabled = ref(false); // Bouton arrivée actif par défaut
const isDepartureButtonDisabled = ref(true); // Bouton départ bloqué par défaut

// Fonction pour ajuster l'état des boutons
function updateButtonStates() {
    const today = new Date().toLocaleDateString("fr-FR");
    const todayData = days.value.find((day) => day.date === today);

    if (todayData) {
        const hasArrival =
            todayData.arrivals.length > todayData.departures.length;

        if (hasArrival) {
            // Si une arrivée a été enregistrée mais pas encore de départ
            isArrivalButtonDisabled.value = true;
            isDepartureButtonDisabled.value = false;
        } else {
            // Si un départ a été enregistré après une arrivée
            isArrivalButtonDisabled.value = false;
            isDepartureButtonDisabled.value = true;
        }
    } else {
        // Aucun enregistrement pour aujourd'hui
        isArrivalButtonDisabled.value = false; // Activer arrivée
        isDepartureButtonDisabled.value = true; // Bloquer départ
    }
}

// Charger les données depuis la base de données
onMounted(() => {
    // Initialiser les jours en français
    const frenchWeekdays = ["Lundi", "Mardi", "Mercredi", "Jeudi", "Vendredi"];

    // Calculer les jours de la semaine actuelle
    function getWeekDays() {
        const today = new Date();
        const dayOfWeek = today.getDay(); // Dimanche = 0, Lundi = 1, ...
        const diffToMonday = dayOfWeek === 0 ? -6 : 1 - dayOfWeek; // Ajuste pour que lundi soit 0

        const monday = new Date(today.setDate(today.getDate() + diffToMonday)); // Date du lundi

        const days = [];
        for (let i = 0; i < 5; i++) {
            // Ajouter Lundi à Vendredi
            const currentDay = new Date(monday);
            currentDay.setDate(monday.getDate() + i);
            const formattedDate = currentDay.toLocaleDateString("fr-FR", {
                day: "2-digit",
                month: "2-digit",
                year: "numeric",
            });
            days.push({ name: frenchWeekdays[i], date: formattedDate });
        }

        return days;
    }

    // Charger les données depuis l'API et compléter avec les jours manquants
    axios
        .get("/dashboard/days")
        .then((response) => {
            const apiDays = response.data.map((day) => {
                const arrivals = day.time_entries
                    .filter((entry) => entry.type === "arrival")
                    .map((entry) => entry.time);
                const departures = day.time_entries
                    .filter((entry) => entry.type === "departure")
                    .map((entry) => entry.time);

                return {
                    ...day,
                    arrivals,
                    departures,
                    total: calculateDailyTotal(arrivals, departures),
                };
            });

            const weekDays = getWeekDays();
            // Fusionner les données de l'API avec les jours de la semaine actuelle
            days.value = weekDays.map((weekday) => {
                const apiDay = apiDays.find(
                    (apiDay) => apiDay.date === weekday.date
                );
                return (
                    apiDay || {
                        day: weekday.name,
                        date: weekday.date,
                        arrivals: [],
                        departures: [],
                        total: "--:--",
                    }
                );
            });

            weeklyTotal.value = calculateWeeklyTotal();
            onInitialPageLoaded.value = true;
        })
        .catch((error) => {
            console.error("Erreur lors du chargement des journées :", error);

            // En cas d'erreur, afficher uniquement les jours de la semaine
            days.value = getWeekDays().map((weekday) => ({
                day: weekday.name,
                date: weekday.date,
                arrivals: [],
                departures: [],
                total: "--:--",
            }));
        });
});

// Fonction pour obtenir la dernière heure
function getLastTime(day, type) {
    if (!day || !day[type] || day[type].length === 0) {
        return "Aucune heure enregistrée"; // Si aucune donnée disponible, retourne ce message
    }
    return day[type][day[type].length - 1]; // Retourne la dernière heure du type spécifié (arrivals ou departures)
}

// Fonction pour enregistrer une arrivée ou un départ
async function recordTime(actionType) {
    try {
        // Obtenir l'heure du serveur
        const serverTimeResponse = await axios.get("/server-time");
        const { time, date, day } = serverTimeResponse.data;

        let dayInfo = days.value.find((d) => d.date === date);

        // Envoyer les données au backend
        const response = await axios.post("/time-entries", {
            user_id: props.auth.user.id,
            day_id: dayInfo ? dayInfo.id : null,
            type: actionType,
        });

        console.log("Données sauvegardées :", response.data);

        if (!dayInfo) {
            dayInfo = {
                id: response.data.time_entry.day_id,
                day: day,
                date: date,
                arrivals: [],
                departures: [],
                total: "",
            };
            days.value.push(dayInfo);
        }

        // Utiliser l'heure retournée par le serveur
        const serverTime = response.data.time_entry.time;
        
        if (actionType === "arrival") {
            dayInfo.arrivals.push(serverTime);
        } else if (actionType === "departure") {
            dayInfo.departures.push(serverTime);
        }

        dayInfo.total = calculateDailyTotal(dayInfo.arrivals, dayInfo.departures);
        weeklyTotal.value = calculateWeeklyTotal();

    } catch (error) {
        console.error("Erreur lors de l'enregistrement :", error);
    }
}

// Calculer le total des heures travaillées pour un jour
function calculateDailyTotal(arrivals, departures) {
    let totalMinutes = 0;

    for (let i = 0; i < Math.min(arrivals.length, departures.length); i++) {
        const [arrivalHour, arrivalMinute] = arrivals[i].split(":").map(Number);
        const [departureHour, departureMinute] = departures[i]
            .split(":")
            .map(Number);

        const minutes =
            departureHour * 60 +
            departureMinute -
            (arrivalHour * 60 + arrivalMinute);

        totalMinutes += minutes;
    }

    const hours = Math.floor(totalMinutes / 60);
    const minutes = totalMinutes % 60;

    return `${hours.toString().padStart(2, "0")}:${minutes
        .toString()
        .padStart(2, "0")}`;
}

// Calculer le total de la semaine
function calculateWeeklyTotal() {
    let totalMinutes = 0;

    days.value.forEach((day) => {
        if (day.total && day.total !== "--:--") {
            const [hours, minutes] = day.total.split(":").map(Number);

            // Vérifier si les heures et minutes sont des nombres valides
            if (!isNaN(hours) && !isNaN(minutes)) {
                totalMinutes += hours * 60 + minutes;
            }
        }
    });

    const totalHours = Math.floor(totalMinutes / 60);
    const remainingMinutes = totalMinutes % 60;

    return `${totalHours.toString().padStart(2, "0")}:${remainingMinutes
        .toString()
        .padStart(2, "0")}`;
}

// Fonction pour déplier/replier les jours
function toggleExpand(dayId) {
    const index = expandedDays.value.indexOf(dayId);
    if (index > -1) {
        // Si le jour est déjà déployé, on le retire
        expandedDays.value.splice(index, 1);
    } else {
        // Sinon, on l'ajoute
        expandedDays.value.push(dayId);
    }
}

function formatTimeWithoutSeconds(time) {
    if (!time) return "--:--";
    return time.split(":").slice(0, 2).join(":"); // Prend uniquement les heures et minutes
}

// Surveiller les modifications des jours pour mettre à jour le total hebdomadaire
watch(
    days,
    () => {
        weeklyTotal.value = calculateWeeklyTotal();
    },
    { deep: true }
);

// Observer les jours pour ajuster les états des boutons
watch(() => days.value, updateButtonStates, { deep: true });
</script>

<template>
    <Head title="Aujourd'hui" />

    <AuthenticatedLayout>
        <section class="attendance-section bg-gray-50 pt-8 pb-16 min-h-screen">
            <div class="max-w-[1700px] mx-auto px-4 sm:px-6 lg:px-8">
                <!-- Titre de la page -->
                <div class="p-6 rounded-lg text-center w-full bg-white shadow-sm mb-8">
                    <h1 class="text-gray-800 text-xl sm:text-2xl font-semibold shadow-md py-8">
                        <i class="fas fa-clock text-[#005692] mr-2"></i> Gestion du temps de travail
                    </h1>
                </div>

                <!-- Contenu principal -->
                <div class="mb-8 bg-white rounded-xl shadow-sm p-6">
                    <!-- Boutons d'arrivée et de départ -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 w-full max-w-2xl mx-auto mb-10">
                        <!-- Bouton Heure d'arrivée -->
                        <div
                            class="p-6 rounded-xl shadow-sm text-center"
                            :class="{
                                'bg-gray-100': isArrivalButtonDisabled,
                                'bg-white': !isArrivalButtonDisabled,
                            }"
                        >
                            <h3 class="text-gray-800 text-lg font-semibold">
                                <i class="fas fa-sign-in-alt text-emerald-600 mr-2"></i>
                                Heure d'arrivée
                            </h3>
                            <button
                                :disabled="isArrivalButtonDisabled"
                                @click="
                                    recordTime('arrival');
                                    updateButtonStates();
                                "
                                class="mt-4 text-emerald-700 border border-emerald-700 bg-white hover:text-white hover:bg-emerald-700 disabled:cursor-not-allowed disabled:opacity-50 transition-colors duration-300 font-medium rounded-full text-sm px-6 py-3"
                            >
                                <i class="fas fa-arrow-right"></i> À l'instant
                            </button>
                            <p class="mt-2 text-sm text-gray-500">
                                {{
                                    getLastTime(
                                        days.find(
                                            (day) =>
                                                day.date ===
                                                new Date().toLocaleDateString(
                                                    "fr-FR"
                                                )
                                        ),
                                        "arrivals"
                                    ) === "Aucune heure enregistrée"
                                        ? "Aucune heure enregistrée"
                                        : "Dernière heure enregistrée : " +
                                          formatTimeWithoutSeconds(
                                              getLastTime(
                                                  days.find(
                                                      (day) =>
                                                          day.date ===
                                                          new Date().toLocaleDateString(
                                                              "fr-FR"
                                                          )
                                                  ),
                                                  "arrivals"
                                              )
                                          )
                                }}
                            </p>
                        </div>

                        <!-- Bouton Heure de départ -->
                        <div
                            class="p-6 rounded-xl shadow-sm text-center"
                            :class="{
                                'bg-gray-100': isDepartureButtonDisabled,
                                'bg-white': !isDepartureButtonDisabled,
                            }"
                        >
                            <h3 class="text-gray-800 text-lg font-semibold">
                                <i class="fas fa-sign-out-alt text-red-600 mr-2"></i>
                                Heure de départ
                            </h3>
                            <button
                                :disabled="isDepartureButtonDisabled"
                                @click="
                                    recordTime('departure');
                                    updateButtonStates();
                                "
                                class="mt-4 text-red-700 border border-red-700 bg-white hover:text-white hover:bg-red-700 disabled:cursor-not-allowed disabled:opacity-50 transition-colors duration-300 font-medium rounded-full text-sm px-6 py-3"
                            >
                                <i class="fas fa-arrow-left"></i> Maintenant
                            </button>
                            <p class="mt-2 text-sm text-gray-500">
                                {{
                                    getLastTime(
                                        days.find(
                                            (day) =>
                                                day.date ===
                                                new Date().toLocaleDateString(
                                                    "fr-FR"
                                                )
                                        ),
                                        "departures"
                                    ) === "Aucune heure enregistrée"
                                        ? "Aucune heure enregistrée"
                                        : "Dernière heure enregistrée : " +
                                          formatTimeWithoutSeconds(
                                              getLastTime(
                                                  days.find(
                                                      (day) =>
                                                          day.date ===
                                                          new Date().toLocaleDateString(
                                                              "fr-FR"
                                                          )
                                                  ),
                                                  "departures"
                                              )
                                          )
                                }}
                            </p>
                        </div>
                    </div>

                    <!-- Statistiques -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 w-full max-w-2xl mx-auto mb-10">
                        <div class="bg-blue-50 rounded-lg p-4 flex items-center">
                            <div class="w-12 h-12 flex-shrink-0 rounded-full bg-blue-100 flex items-center justify-center">
                                <i class="fas fa-clock text-blue-600 text-lg"></i>
                            </div>
                            <div class="ml-4">
                                <h3 class="text-xs sm:text-sm font-medium text-gray-500">Total de la semaine</h3>
                                <p class="text-xl sm:text-2xl font-bold text-gray-800">{{ weeklyTotal }}</p>
                            </div>
                        </div>
                        <div class="bg-indigo-50 rounded-lg p-4 flex items-center">
                            <div class="w-12 h-12 flex-shrink-0 rounded-full bg-indigo-100 flex items-center justify-center">
                                <i class="fas fa-calendar-alt text-indigo-600 text-lg"></i>
                            </div>
                            <div class="ml-4">
                                <h3 class="text-xs sm:text-sm font-medium text-gray-500">Cette semaine</h3>
                                <p class="text-xl sm:text-2xl font-bold text-gray-800">{{ days.length }} jours</p>
                            </div>
                        </div>
                    </div>

                    <!-- TABLEAU CETTE SEMAINE -->
                    <div class="mb-6 bg-white rounded-xl shadow-sm overflow-hidden">
                        <!-- En-tête de la semaine -->
                        <div class="bg-gradient-to-r from-[#005692] to-[#0078c9] px-6 py-4 flex justify-between items-center text-white">
                            <div class="flex items-center">
                                <i class="fas fa-calendar-week mr-3"></i>
                                <h3 class="text-xs md:text-sm">
                                    Cette semaine
                                </h3>
                            </div>
                            <div class="flex items-center">
                                <span class="mr-3 text-xs md:text-sm font-medium">
                                    {{ weeklyTotal }}
                                </span>
                            </div>
                        </div>

                        <!-- Contenu du tableau -->
                        <div class="divide-y divide-gray-100">
                            <!-- Jours -->
                            <div v-for="(day, index) in days" :key="index" class="px-6 py-4 hover:bg-gray-50 transition-colors">
                                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-gray-100">
                                    <!-- Date -->
                                    <div class="flex-shrink-0 flex items-center w-48">
                                        <div class="h-10 w-10 rounded-full bg-gray-100 flex items-center justify-center mr-4">
                                            <span class="text-sm font-medium text-gray-600">
                                                {{ new Date(day.date.split('/').reverse().join('-')).getDate() }}
                                            </span>
                                        </div>
                                        <div>
                                            <p class="font-medium text-gray-800 capitalize">{{ day.day }}</p>
                                            <p class="text-xs text-gray-500">
                                                {{ day.date }}
                                            </p>
                                        </div>
                                    </div>

                                    <!-- Heures d'arrivée et départ -->
                                    <div class="flex-1 grid grid-cols-3 gap-6">
                                        <div class="text-center">
                                            <p class="text-xs text-gray-500 mb-1">Arrivée</p>
                                            <div class="flex items-center justify-center">
                                                <span class="text-emerald-600 font-medium">
                                                    {{ formatTimeWithoutSeconds(day.arrivals && day.arrivals.length > 0 ? day.arrivals[0] : null) || "--:--" }}
                                                </span>
                                                <button v-if="day.arrivals && day.arrivals.length > 1"
                                                    class="ml-2 text-xs text-gray-500 hover:text-gray-700"
                                                    @click.stop="toggleExpand(index)">
                                                    (+ {{ day.arrivals.length - 1 }})
                                                </button>
                                            </div>
                                        </div>
                                        <div class="text-center">
                                            <p class="text-xs text-gray-500 mb-1">Départ</p>
                                            <div class="flex items-center justify-center">
                                                <span class="text-red-600 font-medium">
                                                    {{ formatTimeWithoutSeconds(day.departures && day.departures.length > 0 ? day.departures[0] : null) || "--:--" }}
                                                </span>
                                                <button v-if="day.departures && day.departures.length > 1"
                                                    class="ml-2 text-xs text-gray-500 hover:text-gray-700"
                                                    @click.stop="toggleExpand(index)">
                                                    (+ {{ day.departures.length - 1 }})
                                                </button>
                                            </div>
                                        </div>
                                        <div class="text-right">
                                            <p class="text-xs text-gray-500 mb-1">Total</p>
                                            <p class="font-medium text-blue-600">{{ day.total || "0:00" }}</p>
                                        </div>
                                    </div>
                                </div>

                                <!-- Détails des heures (masquable) -->
                                <div v-if="expandedDays.includes(index)" class="mt-4 pt-4 border-t border-gray-100">
                                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                                        <div>
                                            <h4 class="text-sm font-medium text-gray-700 mb-2">Détail des arrivées</h4>
                                            <div class="flex flex-wrap gap-2">
                                                <span v-for="(arrival, i) in day.arrivals" :key="`arrival-${index}-${i}`"
                                                    class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                    {{ formatTimeWithoutSeconds(arrival) }}
                                                </span>
                                                <span v-if="day.arrivals.length === 0" class="text-sm text-gray-500">
                                                    Aucune arrivée enregistrée
                                                </span>
                                            </div>
                                        </div>
                                        <div>
                                            <h4 class="text-sm font-medium text-gray-700 mb-2">Détail des départs</h4>
                                            <div class="flex flex-wrap gap-2">
                                                <span v-for="(departure, i) in day.departures" :key="`departure-${index}-${i}`"
                                                    class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                                    {{ formatTimeWithoutSeconds(departure) }}
                                                </span>
                                                <span v-if="day.departures.length === 0" class="text-sm text-gray-500">
                                                    Aucun départ enregistré
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Lien vers l'historique -->
                    <div class="mt-10 flex justify-center">
                        <Link :href="route('historique')">
                            <PrimaryButton class="bg-[#005692] hover:bg-blue-700">
                                <i class="fas fa-history mr-2"></i> Voir historique complet
                            </PrimaryButton>
                        </Link>
                    </div>
                </div>
            </div>
        </section>
    </AuthenticatedLayout>
</template>

<style scoped>
button:disabled {
    cursor: not-allowed;
    opacity: 0.5;
}

.totalHour {
    margin-top: 0 !important;
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
</style>
