<script setup>
import { ref, computed } from "vue";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head, Link } from "@inertiajs/vue3";
import PrimaryButton from "@/Components/PrimaryButton.vue";

// Les données des jours sont passées à partir du contrôleur Laravel via Inertia
const props = defineProps(['days']);

// Filtre sélectionné pour l'année et le mois
const selectedYear = ref(new Date().getFullYear());
const selectedMonth = ref(0); // 0 pour "Tous les mois"

// Filtrer les jours en fonction de l'année et du mois sélectionnés
const filteredDays = computed(() => {
    return props.days.filter(day => {
        const dayDate = new Date(day.date);
        const isSameYear = dayDate.getFullYear() === selectedYear.value;
        const isSameMonth = selectedMonth.value === 0 || (dayDate.getMonth() + 1 === selectedMonth.value);
        return isSameYear && isSameMonth;
    });
});

// Calcul du total des heures travaillées en fonction du filtre
const totalHoursWorked = computed(() => {
    return filteredDays.value.reduce((total, day) => {
        if (day.total) {
            const [hours, minutes] = day.total.split("h").map(Number);
            total += hours * 60 + minutes;
        }
        return total;
    }, 0);
});

// Conversion du total des minutes en heures et minutes
const formattedTotalHours = computed(() => {
    const totalMinutes = totalHoursWorked.value;
    const hours = Math.floor(totalMinutes / 60);
    const minutes = totalMinutes % 60;
    return `${hours}h${minutes.toString().padStart(2, "0")}`;
});

// Nombre de jours enregistrés
const totalDaysRecorded = computed(() => {
    return filteredDays.value.length;
});

// Condition pour vérifier si des jours sont disponibles après filtrage
const hasDays = computed(() => filteredDays.value.length > 0);

// Liste des années disponibles (2023 à 2025)
const years = ref([2023, 2024, 2025]);

// Liste des mois, avec l'option "Tous les mois"
const months = [
    { value: 0, name: 'Tous les mois' },
    { value: 1, name: 'Janvier' },
    { value: 2, name: 'Février' },
    { value: 3, name: 'Mars' },
    { value: 4, name: 'Avril' },
    { value: 5, name: 'Mai' },
    { value: 6, name: 'Juin' },
    { value: 7, name: 'Juillet' },
    { value: 8, name: 'Août' },
    { value: 9, name: 'Septembre' },
    { value: 10, name: 'Octobre' },
    { value: 11, name: 'Novembre' },
    { value: 12, name: 'Décembre' }
];
</script>


<template>
    <Head title="Historique des pointages" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-white bg-gray-800 leading-tight">
                Historique des pointages
            </h2>
        </template>

        <section class="attendance-section w-full max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 bg-white pt-8 pb-20">
            <!-- Statistiques et actions -->
            <div class="w-full max-w-4xl mx-auto mb-8">
                <div class="flex flex-col md:flex-row justify-between items-center bg-gray-100 p-6 rounded-lg shadow-md">
                    <div class="flex items-center space-x-4 text-gray-700">
                        <div class="flex items-center text-sm">
                            <i class="fas fa-clock text-blue-500 mr-2"></i>
                            <span><strong>Total des heures travaillées :</strong> {{ formattedTotalHours }}</span>
                        </div>
                        <div class="flex items-center text-sm">
                            <i class="fas fa-calendar-alt text-green-500 mr-2"></i>
                            <span><strong>Nombre de jours enregistrés :</strong> {{ totalDaysRecorded }}</span>
                        </div>
                    </div>

                    <!-- Filtres par année et mois -->
                    <div class="mt-4 md:mt-0 flex items-center space-x-4">
                        <div class="flex items-center space-x-2">
                            <label for="year" class="text-gray-600 font-semibold text-sm">Année :</label>
                            <select id="year" v-model="selectedYear" class="border-gray-300 text-sm rounded-md shadow-sm focus:border-indigo-500 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                <option v-for="year in years" :key="year" :value="year">{{ year }}</option>
                            </select>
                        </div>
                        <div class="flex items-center space-x-2">
                            <label for="month" class="text-gray-600 font-semibold text-sm">Mois :</label>
                            <select id="month" v-model="selectedMonth" class="border-gray-300 text-sm rounded-md shadow-sm focus:border-indigo-500 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                <option v-for="month in months" :key="month.value" :value="month.value">{{ month.name }}</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Table -->
            <div class="w-full max-w-4xl mx-auto overflow-x-auto bg-white shadow-md rounded-lg">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Jour
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Date
                            </th>
                            <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Arrivée
                            </th>
                            <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Départ
                            </th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Total
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <!-- Si aucun pointage trouvé -->
                        <tr v-if="filteredDays.length === 0">
                            <td colspan="5" class="px-6 py-4 text-center">Aucun pointage trouvé pour la période sélectionnée.</td>
                        </tr>
                        <tr v-for="day in filteredDays" :key="day.id" class="hover:bg-gray-50 transition-colors">
                            <td class="px-6 py-4 whitespace-nowrap">{{ day.day }}</td>
                            <td class="px-6 py-4 whitespace-nowrap">{{ new Date(day.date).toLocaleDateString('fr-FR') }}</td>
                            <td class="px-6 py-4 text-center whitespace-nowrap">{{ day.arrival || '--:--' }}</td>
                            <td class="px-6 py-4 text-center whitespace-nowrap">{{ day.departure || '--:--' }}</td>
                            <td class="px-6 py-4 text-right whitespace-nowrap">{{ day.total || '--:--' }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Retour au Dashboard -->
            <div class="pt-8 flex justify-center">
                <Link :href="route('dashboard')">
                    <PrimaryButton>
                        <i class="fas fa-arrow-left mr-2"></i> Retour au Dashboard
                    </PrimaryButton>
                </Link>
            </div>
        </section>
    </AuthenticatedLayout>
</template>

<style scoped>
/* Ajout d'une petite animation de survol sur les lignes de la table */
tr:hover {
    background-color: #f9fafb;
    transition: background-color 0.3s ease;
}
</style>

