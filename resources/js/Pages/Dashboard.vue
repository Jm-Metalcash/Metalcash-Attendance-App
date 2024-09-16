<script setup>
import { ref, watch } from "vue";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import { Head, Link } from "@inertiajs/vue3";
import ModalDashboard from "@/Components/ModalDashboard.vue";

// Tableau des jours
const days = ref([
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
]);

const arrivalButtonRef = ref(null);
const departureButtonRef = ref(null);

//variable qui stock le total heure de travail de la semaine
const weeklyTotal = ref("00h00");

// Variables pour le modal
const showModal = ref(false);
const actionType = ref("");
let currentTime = "";

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

    // Additionner les heures et minutes de chaque jour
    days.value.forEach((day) => {
        if (day.total) {
            const [hours, minutes] = day.total.split("h").map(Number);
            totalMinutes += hours * 60 + minutes;
        }
    });

    const hours = Math.floor(totalMinutes / 60);
    const minutes = totalMinutes % 60;

    return `${hours}h${minutes.toString().padStart(2, "0")}`;
}

// Met à jour le total de la semaine chaque fois que les heures d'un jour sont modifiées
watch(
    days,
    () => {
        weeklyTotal.value = calculateWeeklyTotal();
    },
    { deep: true }
);

// Confirmer l'action et enregistrer l'heure
function confirmAction() {
    const today = new Date().toLocaleDateString("fr-FR");
    const dayInfo = days.value.find((day) => day.date === today);

    if (actionType.value === "arrival") {
        dayInfo.arrival = currentTime;
        const arrivalButton = document.getElementById("arrivalButton");
        arrivalButton.classList.remove("bg-white", "text-green-700");
        arrivalButton.classList.add("bg-green-700", "text-white");
        arrivalButton.style.cursor = "not-allowed";
        arrivalButton.disabled = true;
        const hourArrivalToday = document.getElementById("hourArrivalToday");
        hourArrivalToday.textContent = currentTime;
        hourArrivalToday.classList.add("text-green-700");
    } else if (actionType.value === "departure") {
        dayInfo.departure = currentTime;
        const departureButton = document.getElementById("departureButton");
        departureButton.classList.remove("bg-white", "text-red-700");
        departureButton.classList.add("bg-red-700", "text-white");
        departureButton.style.cursor = "not-allowed";
        departureButton.disabled = true;
        const hourDepartureToday =
            document.getElementById("hourDepartureToday");
        hourDepartureToday.textContent = currentTime;
        hourDepartureToday.classList.add("text-red-700");

        // Calcule le total après avoir enregistré l'heure de départ
        dayInfo.total = calculateTotal(dayInfo.arrival, dayInfo.departure);
        //calculer le total de la semaine après avoir mis à jour l'heure de départ
        weeklyTotal.value = calculateWeeklyTotal();
    }

    showModal.value = false;
}

// Annuler l'action
function cancelAction() {
    showModal.value = false;
}

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

// Format Jour Mois Année
function formatDateVerbose(date) {
    const options = {
        weekday: "long",
        year: "numeric",
        month: "long",
        day: "numeric",
    };
    return date.toLocaleDateString("fr-FR", options);
}
</script>

<template>
    <Head title="Aujourd'hui" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-white bg-gray-800 leading-tight">
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
                        Bonjour <span class="font-bold">{{ $page.props.auth.user.name }}</span>, nous sommes le :
                        <span class="font-semibold text-gray-900">{{
                            formatDateVerbose(new Date())
                        }}</span>
                    </p>
                    <p class="text-gray-600 mt-2 text-sm sm:text-base">
                        <i class="fas fa-hourglass-half mr-1"></i> Heure
                        actuelle :
                        <span class="font-semibold text-gray-900">{{
                            new Date().toLocaleTimeString("fr-FR", {
                                hour: "2-digit",
                                minute: "2-digit",
                            })
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
                            ref="arrivalButtonRef"
                            id="arrivalButton"
                            @click="openModal('arrival')"
                            class="mt-4 cursor-pointer text-green-700 border border-green-700 bg-white hover:text-white hover:bg-green-800 transition-colors duration-300 focus:ring-4 focus:outline-none focus:ring-green-300 font-medium rounded-full text-sm px-6 py-3"
                        >
                            <i class="fas fa-arrow-right"></i> Enregistrer
                        </button>
                        <p class="mt-2 text-gray-600 text-sm">
                            Heure d'arrivée enregistrée pour aujourd'hui
                        </p>
                        <span
                            id="hourArrivalToday"
                            class="block text-lg mt-2 font-semibold text-gray-600"
                            >Non enregistré</span
                        >
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
                            ref="departureButtonRef"
                            id="departureButton"
                            @click="openModal('departure')"
                            class="mt-4 cursor-pointer text-red-700 border border-red-700 bg-white hover:text-white hover:bg-red-800 transition-colors duration-300 focus:ring-4 focus:outline-none focus:ring-red-300 font-medium rounded-full text-sm px-6 py-3"
                        >
                            <i class="fas fa-arrow-left"></i> Enregistrer
                        </button>
                        <p class="mt-2 text-gray-600 text-sm">
                            Heure de départ enregistrée pour aujourd'hui
                        </p>
                        <span
                            id="hourDepartureToday"
                            class="block text-lg mt-2 font-semibold text-gray-600"
                            >Non enregistré</span
                        >
                    </div>
                </div>

                <!-- Table des heures de la semaine -->
                <div class="w-full max-w-4xl mx-auto overflow-x-auto">
                    <h3 class="bg-[rgb(0,85,150)] text-gray-100 p-4 text-left font-bold mt-12 mb-4">Cette semaine : </h3>
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
                                    class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                                >
                                    Arrivée
                                </th>
                                <th
                                    class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                                >
                                    Départ
                                </th>
                                <th
                                    class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
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
                                <td class="px-4 py-4 whitespace-nowrap">
                                    <input
                                        v-model="day.arrival"
                                        type="time"
                                        class="w-full text-gray-900 border border-gray-300 rounded-md pr-10 cursor-not-allowed"
                                        disabled
                                    />
                                </td>
                                <td class="px-4 py-4 whitespace-nowrap">
                                    <input
                                        v-model="day.departure"
                                        type="time"
                                        class="w-full text-gray-900 border border-gray-300 rounded-md pr-10 cursor-not-allowed"
                                        disabled
                                    />
                                </td>
                                <td class="px-4 py-4 whitespace-nowrap">
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
