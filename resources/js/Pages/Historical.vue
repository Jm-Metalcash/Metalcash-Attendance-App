<script setup>
import { ref, computed, watch } from "vue";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head, Link } from "@inertiajs/vue3";
import PrimaryButton from "@/Components/PrimaryButton.vue";

// Les données des jours, de l'utilisateur et du rôle sont passées via Inertia
const props = defineProps(["days", "currentUserId", "currentUserRole", "user"]);

// Les rôles pouvant éditer les données
const editableRoles = ['Admin', 'Informatique'];

// Vérifier si l'utilisateur connecté peut modifier les heures
const canEdit = computed(() => {
    return Array.isArray(props.currentUserRole) && props.currentUserRole.some(role => editableRoles.includes(role));
});


// Filtre sélectionné pour l'année et le mois
const selectedYear = ref(new Date().getFullYear());
const selectedMonth = ref(0); // 0 pour "Tous les mois"

// Filtrer les jours en fonction de l'année et du mois sélectionnés
const filteredDays = ref([]);
const totalMinutesWorked = ref(0); // Stocke le total des minutes

// Fonction pour formater les minutes en heures
const formatMinutesToHours = (totalMinutes) => {
    const hours = Math.floor(totalMinutes / 60);
    const minutes = totalMinutes % 60;
    return `${hours}h${minutes.toString().padStart(2, "0")}`;
};

// Fonction pour calculer la différence entre l'heure d'arrivée et de départ
const calculateDailyTotal = (arrival, departure) => {
    if (!arrival || !departure) return "0h00";
    const [arrivalHours, arrivalMinutes] = arrival.split(":").map(Number);
    const [departureHours, departureMinutes] = departure.split(":").map(Number);

    const arrivalDate = new Date();
    arrivalDate.setHours(arrivalHours, arrivalMinutes, 0);
    const departureDate = new Date();
    departureDate.setHours(departureHours, departureMinutes, 0);

    const differenceInMinutes = (departureDate - arrivalDate) / 1000 / 60;
    return differenceInMinutes < 0 ? "0h00" : formatMinutesToHours(differenceInMinutes);
};

// Fonction pour filtrer les jours en fonction de l'année et du mois sélectionnés
const filterDays = () => {
    filteredDays.value = props.days
        .map((day) => {
            const total = calculateDailyTotal(day.arrival, day.departure);
            return { ...day, total };
        })
        .filter((day) => {
            const dayDate = new Date(day.date);
            const isSameYear = dayDate.getFullYear() === selectedYear.value;
            const isSameMonth = selectedMonth.value === 0 || dayDate.getMonth() + 1 === selectedMonth.value;
            return isSameYear && isSameMonth;
        });
};

// Fonction pour calculer le total des minutes travaillées
const calculateTotalMinutes = () => {
    totalMinutesWorked.value = filteredDays.value.reduce((total, day) => {
        if (day.total && typeof day.total === "string") {
            const [hours, minutes] = day.total.split("h").map(Number);
            if (!isNaN(hours) && !isNaN(minutes)) total += hours * 60 + minutes;
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
    if (!time) return "--:--";
    const [hours, minutes] = time.split(":");
    return `${hours}:${minutes}`;
}

// Fonction pour gérer la mise à jour des heures
const handleTimeUpdate = (dayId, field, value) => {
    console.log(`Mise à jour de ${field} pour le jour ${dayId} : ${value}`);
};

const years = ref([2023, 2024, 2025]);
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
    { value: 12, name: "Décembre" }
];
</script>

<template>
    <Head title="Historique des pointages" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-white bg-gray-800 leading-tight">Historique des pointages</h2>
        </template>

        <section class="attendance-section w-full max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 bg-white pt-16 md:pt-24 pb-20">
            <!-- Statistiques et filtres -->
            <div class="w-full max-w-4xl mx-auto mb-8">
                <div class="flex flex-col md:flex-row justify-between items-start md:items-center bg-gray-100 p-6 rounded-lg shadow-md space-y-6 md:space-y-0">
                    <div class="flex flex-col items-start space-y-2 text-gray-700 w-full lg:w-auto">
                        <div class="flex items-center text-sm">
                            <i class="fas fa-clock text-blue-500 mr-2"></i>
                            <span><strong>Total des heures :</strong> {{ formattedTotalHours }}</span>
                        </div>
                        <div class="flex items-center text-sm">
                            <i class="fas fa-calendar-alt text-green-500 mr-2"></i>
                            <span><strong>Jours enregistrés :</strong> {{ totalDaysRecorded }}</span>
                        </div>
                    </div>

                    <!-- Filtres -->
                    <div class="flex flex-col sm:flex-row md:flex-row items-start md:items-center space-y-4 sm:space-y-0 sm:space-x-4 w-full md:w-auto lg:ml-auto">
                        <div class="flex items-center w-full sm:w-auto">
                            <label for="year" class="text-gray-600 font-semibold text-sm mr-2">Année</label>
                            <select id="year" v-model="selectedYear" class="w-full sm:w-48 md:w-40 border-gray-300 text-sm rounded-md shadow-sm">
                                <option v-for="year in years" :key="year" :value="year">{{ year }}</option>
                            </select>
                        </div>
                        <div class="flex items-center w-full sm:w-auto">
                            <label for="month" class="text-gray-600 font-semibold text-sm mr-4 md:mr-2">Mois</label>
                            <select id="month" v-model="selectedMonth" class="w-full sm:w-48 md:w-40 border-gray-300 text-sm rounded-md shadow-sm">
                                <option v-for="month in months" :key="month.value" :value="month.value">{{ month.name }}</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Table de pointages -->
            <div class="w-full max-w-4xl mx-auto overflow-x-auto bg-white shadow-md rounded-lg">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-[rgb(0,85,150)]">
                            <tr>
                                <th class="px-4 sm:px-6 py-2 sm:py-3 text-left text-xs font-medium text-gray-100 uppercase tracking-wider">Jour</th>
                                <th class="px-4 sm:px-6 py-2 sm:py-3 text-left text-xs font-medium text-gray-100 uppercase tracking-wider">Date</th>
                                <th class="px-4 sm:px-6 py-2 sm:py-3 text-center text-xs font-medium text-gray-100 uppercase tracking-wider">Arrivée</th>
                                <th class="px-4 sm:px-6 py-2 sm:py-3 text-center text-xs font-medium text-gray-100 uppercase tracking-wider">Départ</th>
                                <th class="px-4 sm:px-6 py-2 sm:py-3 text-right text-xs font-medium text-gray-100 uppercase tracking-wider">Total</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <tr v-if="filteredDays.length === 0">
                                <td colspan="5" class="px-4 sm:px-6 py-4 text-center text-sm md:text-base">Aucun pointage trouvé pour la période sélectionnée.</td>
                            </tr>
                            <template v-for="day in filteredDays" :key="day.id">
                                <tr class="hover:bg-gray-50 transition-colors">
                                    <td class="px-4 sm:px-6 py-2 sm:py-4 whitespace-nowrap text-sm md:text-base">{{ day.day }}</td>
                                    <td class="px-4 sm:px-6 py-2 sm:py-4 whitespace-nowrap text-sm md:text-base">{{ new Date(day.date).toLocaleDateString('fr-FR') }}</td>
                                    <td class="px-4 sm:px-6 py-2 sm:py-4 text-center whitespace-nowrap text-sm md:text-base">
                                        <template v-if="canEdit">
                                            <input type="time" v-model="day.arrival" @change="handleTimeUpdate(day.id, 'arrival', $event.target.value)" class="border rounded-md px-2 py-1" />
                                        </template>
                                        <template v-else>
                                            {{ formatTime(day.arrival) }}
                                        </template>
                                    </td>
                                    <td class="px-4 sm:px-6 py-2 sm:py-4 text-center whitespace-nowrap text-sm md:text-base">
                                        <template v-if="canEdit">
                                            <input type="time" v-model="day.departure" @change="handleTimeUpdate(day.id, 'departure', $event.target.value)" class="border rounded-md px-2 py-1" />
                                        </template>
                                        <template v-else>
                                            {{ formatTime(day.departure) }}
                                        </template>
                                    </td>
                                    <td class="px-4 sm:px-6 py-2 sm:py-4 text-right whitespace-nowrap text-sm md:text-base">{{ calculateDailyTotal(day.arrival, day.departure) }}</td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Retour au Dashboard -->
            <div class="pt-16 flex justify-center">
                <Link :href="route('dashboard')">
                    <PrimaryButton><i class="fas fa-arrow-left mr-2"></i> Retour à l'accueil</PrimaryButton>
                </Link>
            </div>
        </section>
    </AuthenticatedLayout>
</template>

<style scoped>
.transition-colors:hover {
    background-color: #edf6ff;
    transition: background-color 0.3s ease;
}
</style>
