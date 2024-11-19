<script setup>
import { ref, watch, onMounted } from "vue";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import { Head, Link, usePage } from "@inertiajs/vue3";
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
function recordTime(actionType) {
    const today = new Date().toLocaleDateString("fr-FR");
    const currentTime = new Date().toLocaleTimeString("fr-FR", {
        hour: "2-digit",
        minute: "2-digit",
    });

    let dayInfo = days.value.find((day) => day.date === today);

    if (!dayInfo) {
        // Si aucun jour trouvé pour aujourd'hui, créez-en un nouveau localement
        dayInfo = {
            id: null, // ID attribué après la création dans le backend
            day: new Date().toLocaleDateString("fr-FR", { weekday: "long" }),
            date: today,
            arrivals: [],
            departures: [],
            total: "",
        };
        days.value.push(dayInfo);
    }

    // Ajouter l'heure actuelle dans les arrivées ou départs
    if (actionType === "arrival") {
        dayInfo.arrivals.push(currentTime);
    } else if (actionType === "departure") {
        dayInfo.departures.push(currentTime);
    }

    // Calculer le total pour aujourd'hui
    dayInfo.total = calculateDailyTotal(dayInfo.arrivals, dayInfo.departures);

    // Envoyer les données au backend
    axios
        .post("/time-entries/store", {
            user_id: props.auth.user.id,
            day_id: dayInfo.id || null, // Null si le jour n'existe pas encore
            type: actionType,
            time: currentTime,
        })
        .then((response) => {
            console.log("Données sauvegardées :", response.data);
            if (!dayInfo.id) {
                dayInfo.id = response.data.day_id; // Mise à jour de l'ID
            }
        })
        .catch((error) => {
            console.error("Erreur lors de l'enregistrement :", error);
        });
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
                </div>

                <!-- Boutons d'arrivée et de départ -->
                <div
                    class="grid grid-cols-1 sm:grid-cols-2 gap-4 w-full max-w-lg"
                >
                    <!-- Bouton Heure d'arrivée -->
                    <div
                        class="p-6 rounded-lg shadow-md text-center"
                        :class="{
                            'bg-gray-200': isArrivalButtonDisabled,
                            'bg-white': !isArrivalButtonDisabled,
                        }"
                    >
                        <h3 class="text-gray-800 text-lg font-semibold">
                            <i
                                class="fas fa-sign-in-alt text-green-600 mr-2"
                            ></i>
                            Heure d'arrivée
                        </h3>
                        <button
                            :disabled="isArrivalButtonDisabled"
                            @click="
                                recordTime('arrival');
                                updateButtonStates();
                            "
                            class="mt-4 text-green-700 border border-green-700 bg-white hover:text-white hover:bg-green-800 disabled:cursor-not-allowed disabled:opacity-50 transition-colors duration-300 font-medium rounded-full text-sm px-6 py-3"
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
                        class="p-6 rounded-lg shadow-md text-center"
                        :class="{
                            'bg-gray-200': isDepartureButtonDisabled,
                            'bg-white': !isDepartureButtonDisabled,
                        }"
                    >
                        <h3 class="text-gray-800 text-lg font-semibold">
                            <i
                                class="fas fa-sign-out-alt text-red-600 mr-2"
                            ></i>
                            Heure de départ
                        </h3>
                        <button
                            :disabled="isDepartureButtonDisabled"
                            @click="
                                recordTime('departure');
                                updateButtonStates();
                            "
                            class="mt-4 text-red-700 border border-red-700 bg-white hover:text-white hover:bg-red-800 disabled:cursor-not-allowed disabled:opacity-50 transition-colors duration-300 font-medium rounded-full text-sm px-6 py-3"
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
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase"
                                >
                                    Jour
                                </th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase"
                                >
                                    Arrivées
                                </th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase"
                                >
                                    Départs
                                </th>
                                <th
                                    class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase"
                                >
                                    Total
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- Lignes principales -->
                            <template v-for="(day, index) in days" :key="index">
                                <tr>
                                    <td class="px-6 py-4">
                                        <strong class="capitalize">{{
                                            day.day
                                        }}</strong>
                                        <br />
                                        <span class="text-sm text-gray-500">{{
                                            day.date
                                        }}</span>
                                    </td>
                                    <td class="px-6 py-4">
                                        {{
                                            formatTimeWithoutSeconds(
                                                day.arrivals[0]
                                            ) || "--:--"
                                        }}
                                        <button
                                            v-if="day.arrivals.length > 1"
                                            class="text-gray-500 text-sm ml-2"
                                            @click="toggleExpand(index)"
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
                                                day.departures[0]
                                            ) || "--:--"
                                        }}
                                        <button
                                            v-if="day.departures.length > 1"
                                            class="text-gray-500 text-sm ml-2"
                                            @click="toggleExpand(index)"
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
                                <tr>
                                    <td colspan="4" class="px-0 py-0">
                                        <transition name="slide-expand">
                                            <div
                                                v-show="
                                                    expandedDays.includes(index)
                                                "
                                                class="bg-gray-50 border rounded-lg overflow-hidden"
                                            >
                                                <!-- En-tête des détails -->
                                                <div
                                                    class="flex justify-between items-center border-b border-gray-300 pb-3 mb-3 p-4"
                                                >
                                                    <h3
                                                        class="text-base font-semibold text-gray-800"
                                                    >
                                                        <i
                                                            class="fas fa-calendar-day text-gray-500 mr-2"
                                                        ></i
                                                        >Détails du jour
                                                    </h3>
                                                    <span
                                                        class="text-sm text-gray-500"
                                                    >
                                                        {{ day.date }}
                                                    </span>
                                                </div>

                                                <!-- Contenu détaillé -->
                                                <div
                                                    class="flex flex-col space-y-4 p-4"
                                                >
                                                    <!-- Section des arrivées -->
                                                    <div>
                                                        <h4
                                                            class="text-sm font-semibold text-green-600 flex items-center"
                                                        >
                                                            <i
                                                                class="fas fa-arrow-right text-green-500 mr-2"
                                                            ></i
                                                            >Arrivées
                                                        </h4>
                                                        <div
                                                            class="flex flex-wrap mt-2 gap-2"
                                                        >
                                                            <span
                                                                v-for="(
                                                                    arrival, i
                                                                ) in day.arrivals"
                                                                :key="`arrival-${index}-${i}`"
                                                                class="bg-green-100 text-green-600 px-3 py-1 rounded-md text-sm font-medium"
                                                            >
                                                                {{
                                                                    formatTimeWithoutSeconds(
                                                                        arrival
                                                                    )
                                                                }}
                                                            </span>
                                                        </div>
                                                    </div>

                                                    <!-- Section des départs -->
                                                    <div>
                                                        <h4
                                                            class="text-sm font-semibold text-red-600 flex items-center"
                                                        >
                                                            <i
                                                                class="fas fa-arrow-left text-red-500 mr-2"
                                                            ></i
                                                            >Départs
                                                        </h4>
                                                        <div
                                                            class="flex flex-wrap mt-2 gap-2"
                                                        >
                                                            <span
                                                                v-for="(
                                                                    departure, i
                                                                ) in day.departures"
                                                                :key="`departure-${index}-${i}`"
                                                                class="bg-red-100 text-red-600 px-3 py-1 rounded-md text-sm font-medium"
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
                                        </transition>
                                    </td>
                                </tr>
                            </template>
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
                        <i class="fas fa-calendar-week mr-2"></i>Total de la
                        semaine :
                        <span class="font-semibold ml-2 text-base">{{
                            weeklyTotal
                        }}</span>
                    </h3>
                </div>

                <div class="pt-4">
                    <Link :href="route('historique')">
                        <PrimaryButton>Voir historique complet</PrimaryButton>
                    </Link>
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
