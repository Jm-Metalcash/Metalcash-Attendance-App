<script setup>
import { ref, computed, watch, defineProps, nextTick, reactive, onMounted } from "vue";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head, Link, usePage } from "@inertiajs/vue3";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import axios from "axios";
import { router } from '@inertiajs/vue3';

const page = usePage();
const props = defineProps(["days", "isShow", "user"]);

// Variables pour les messages flash
const showFlash = ref(false);
const flashMessage = ref('');
const flashType = ref('success');

// État local pour stocker les jours
const localDays = ref([...props.days]);

// Mise à jour des jours locaux et recalcul des filtres et totaux
const updateLocalDays = (newDays) => {
    localDays.value = newDays;
    filterDays();
    calculateTotalMinutes();
};

// Synchronisation avec les props
watch(() => props.days, (newDays) => {
    localDays.value = [...newDays];
    filterDays();
}, { deep: true });

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

// Surveiller les changements dans props.days
watch(() => props.days, (newDays) => {
    localDays.value = [...newDays];
    filterDays();
}, { deep: true });

// Filtrer les jours en fonction de l'année et du mois sélectionnés
const filteredDays = ref([]);
const totalMinutesWorked = ref(0); // Stocke le total des minutes

// Variables pour le déploiement des détails
const expandedDays = ref([]); // Contiendra des identifiants uniques pour chaque jour

// Ajouter la fonction pour déterminer le numéro de semaine dans le mois
function getWeekNumberInMonth(date) {
    const firstDayOfMonth = new Date(date.getFullYear(), date.getMonth(), 1);

    // Déterminer le début de la première semaine (lundi précédant ou égal au premier jour du mois)
    let firstWeekStart = new Date(firstDayOfMonth);
    const firstDayOfWeek = firstDayOfMonth.getDay(); // 0 = dimanche, 1 = lundi, etc.

    // Si le premier jour n'est pas un lundi (1), reculer jusqu'au lundi précédent
    if (firstDayOfWeek !== 1) {
        // Si c'est un dimanche (0), reculer de 6 jours
        // Si c'est un autre jour, reculer de (jour - 1) jours
        const daysToSubtract = firstDayOfWeek === 0 ? 6 : firstDayOfWeek - 1;
        firstWeekStart.setDate(firstWeekStart.getDate() - daysToSubtract);
    }

    // Calculer le numéro de semaine
    const daysDiff = Math.floor((date.getTime() - firstWeekStart.getTime()) / (24 * 60 * 60 * 1000));
    const weekNumber = Math.floor(daysDiff / 7) + 1;

    // Soustraire 1 pour que la première semaine soit toujours "Semaine 1"
    return weekNumber - 1 <= 0 ? 1 : weekNumber - 1;
}

// Variables pour le pliage/dépliage des semaines
const collapsedWeeks = ref([]);

// Fonction pour plier/déplier une semaine
function toggleWeek(monthIndex, weekIndex) {
    const weekId = `${monthIndex}-${weekIndex}`;
    const idx = collapsedWeeks.value.indexOf(weekId);
    if (idx > -1) {
        collapsedWeeks.value.splice(idx, 1);
    } else {
        collapsedWeeks.value.push(weekId);
    }
}

// Fonction pour calculer le total d'heures par semaine
function calculateWeekTotal(week) {
    let totalMinutes = 0;

    week.days.forEach(day => {
        if (day.total && typeof day.total === 'string') {
            const [hours, minutes] = day.total.split(":").map(Number);
            totalMinutes += hours * 60 + minutes;
        }
    });

    const hours = Math.floor(totalMinutes / 60);
    const minutes = totalMinutes % 60;

    return `${hours}:${minutes.toString().padStart(2, "0")}`;
}

// Fonction pour formater le total des heures en format lisible
function formatTotal(total) {
    if (!total) return "0h00";
    const [hours, minutes] = total.split(":").map(Number);
    return `${hours}h${minutes.toString().padStart(2, "0")}`;
}

// Fonction pour calculer le total des minutes travaillées d'une journée
const calculateDailyTotal = (arrivals, departures) => {
    if (!arrivals || !departures || arrivals.length === 0 || departures.length === 0) {
        return null;
    }

    let totalMinutes = 0;
    const validPairs = Math.min(arrivals.length, departures.length);

    for (let i = 0; i < validPairs; i++) {
        if (arrivals[i] && departures[i]) {
            const arrivalTime = new Date(`2000-01-01T${arrivals[i]}`);
            const departureTime = new Date(`2000-01-01T${departures[i]}`);

            if (departureTime > arrivalTime) {
                const diffMs = departureTime - arrivalTime;
                totalMinutes += diffMs / (1000 * 60);
            }
        }
    }

    const hours = Math.floor(totalMinutes / 60);
    const minutes = Math.round(totalMinutes % 60);
    return `${hours}:${minutes.toString().padStart(2, '0')}`;
};

// Fonction pour formater l'heure sans les secondes
const formatTimeWithoutSeconds = (time) => {
    if (!time) return null;
    return time.substring(0, 5);
};

// Fonction pour calculer le total des minutes travaillées
function calculateTotalMinutes() {
    let totalMinutes = 0;

    filteredDays.value.forEach((monthGroup) => {
        monthGroup.weeks.forEach((week) => {
            week.days.forEach((day) => {
                if (day.total) {
                    const [hours, minutes] = day.total.split(':').map(Number);
                    totalMinutes += hours * 60 + minutes;
                }
            });
        });
    });

    totalMinutesWorked.value = totalMinutes;
}

// Formater le temps total en heures et minutes
const formattedTotalTime = computed(() => {
    const hours = Math.floor(totalMinutesWorked.value / 60);
    const minutes = totalMinutesWorked.value % 60;
    return `${hours}h${minutes.toString().padStart(2, '0')}`;
});

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
    const daysFilteredByYear = localDays.value.filter((day) => {
        const dayDate = new Date(day.date);
        return dayDate.getFullYear() === selectedYear.value;
    });

    if (selectedMonth.value === 0) {
        const groupedByMonth = {};

        daysFilteredByYear.forEach((day) => {
            const dayDate = new Date(day.date);
            const month = dayDate.getMonth() + 1; 
            const monthName = months.find((m) => m.value === month).name;

            if (!groupedByMonth[month]) {
                groupedByMonth[month] = {
                    monthName: monthName,
                    weeks: {}, 
                };
            }

            const weekNumber = getWeekNumberInMonth(dayDate);

            if (!groupedByMonth[month].weeks[weekNumber]) {
                groupedByMonth[month].weeks[weekNumber] = {
                    weekNumber: weekNumber,
                    days: []
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

            groupedByMonth[month].weeks[weekNumber].days.push({
                ...day,
                arrivals,
                departures,
                total: calculateDailyTotal(arrivals, departures),
            });
        });

        filteredDays.value = Object.values(groupedByMonth).map(month => {
            const sortedWeeks = Object.values(month.weeks).sort((a, b) => a.weekNumber - b.weekNumber);
            sortedWeeks.forEach(week => {
                week.days.sort((a, b) => new Date(a.date) - new Date(b.date));
            });
            return {
                ...month,
                weeks: sortedWeeks
            };
        }).sort(
            (a, b) =>
                months.findIndex((m) => m.name === a.monthName) -
                months.findIndex((m) => m.name === b.monthName)
        );
    } else {
        const daysFilteredByMonth = daysFilteredByYear.filter(day => {
            const dayDate = new Date(day.date);
            return dayDate.getMonth() + 1 === selectedMonth.value;
        });

        const groupedByWeek = {};
        daysFilteredByMonth.forEach(day => {
            const dayDate = new Date(day.date);
            const weekNumber = getWeekNumberInMonth(dayDate);

            if (!groupedByWeek[weekNumber]) {
                groupedByWeek[weekNumber] = {
                    weekNumber: weekNumber,
                    days: []
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

            groupedByWeek[weekNumber].days.push({
                ...day,
                arrivals,
                departures,
                total: calculateDailyTotal(arrivals, departures),
            });
        });

        const sortedWeeks = Object.values(groupedByWeek).sort((a, b) => a.weekNumber - b.weekNumber);
        sortedWeeks.forEach(week => {
            week.days.sort((a, b) => new Date(a.date) - new Date(b.date));
        });

        filteredDays.value = [
            {
                monthName: months.find((m) => m.value === selectedMonth.value).name,
                weeks: sortedWeeks
            },
        ];
    }
}

// Nombre de jours enregistrés
const totalDaysRecorded = computed(() => {
    return filteredDays.value.reduce((totalDays, monthGroup) => {
        return totalDays + monthGroup.weeks.reduce((totalDays, week) => {
            return totalDays + week.days.length;
        }, 0);
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

// Liste des années disponibles (2023 à 2025)
const years = ref([2023, 2024, 2025]);

// Variables pour le modal
const isModalOpen = ref(false);
const selectedDay = ref(null);
const originalDayData = ref(null);

// Ouvrir le modal pour modifier un jour
const openModal = (day) => {
    if (
        page.props.auth.roles.includes("Admin") ||
        page.props.auth.roles.includes("Informatique")
    ) {
        selectedDay.value = {
            ...day,
            arrivals: [...(day.arrivals || [])],
            departures: [...(day.departures || [])]
        };
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
        const formattedArrivals = selectedDay.value.arrivals.map((time) =>
            time ? time.substring(0, 5) : null
        );
        const formattedDepartures = selectedDay.value.departures.map((time) =>
            time ? time.substring(0, 5) : null
        );

        await axios.post(`/update-day/${selectedDay.value.id}`, {
            arrivals: formattedArrivals,
            departures: formattedDepartures,
        });

        const updatedDays = [...localDays.value];
        const dayIndex = updatedDays.findIndex(day => day.id === selectedDay.value.id);
        
        if (dayIndex !== -1) {
            updatedDays[dayIndex] = {
                ...updatedDays[dayIndex],
                time_entries: [
                    ...formattedArrivals.filter(t => t).map(time => ({ type: 'arrival', time })),
                    ...formattedDepartures.filter(t => t).map(time => ({ type: 'departure', time }))
                ]
            };
            
            updateLocalDays(updatedDays);
        }

        displayFlash('Le jour a été modifié avec succès !', 'success');
        closeModal();
    } catch (error) {
        displayFlash('Erreur lors de la modification du jour.', 'error');
        console.error(
            "Erreur lors de la sauvegarde des modifications :",
            error
        );
    }
};

// Fonction pour trouver un jour spécifique dans les données filtrées
const findDay = (dayId) => {
    for (const monthGroup of filteredDays.value) {
        for (const week of monthGroup.weeks) {
            for (const day of week.days) {
                if (day.id === dayId) {
                    return day;
                }
            }
        }
    }
    return null;
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
        
        const updatedDays = localDays.value.filter(day => day.id !== selectedDay.value.id);
        
        updateLocalDays(updatedDays);
        
        displayFlash('Le jour a été supprimé avec succès !', 'success');
        closeModal();
    } catch (error) {
        displayFlash('Erreur lors de la suppression du jour.', 'error');
        console.error("Erreur lors de la suppression du jour :", error);
    }
};

// Fonction pour ajouter un jour aux données filtrées
const addDayToFilteredData = (newDay) => {
    const dayDate = new Date(newDay.date);
    const month = dayDate.getMonth() + 1; 
    const monthName = months.find((m) => m.value === month).name;
    
    const updatedFilteredDays = JSON.parse(JSON.stringify(filteredDays.value));
    
    let monthIndex = updatedFilteredDays.findIndex(mg => mg.monthName === monthName);
    let monthGroup;
    
    if (monthIndex === -1) {
        monthGroup = {
            monthName: monthName,
            weeks: []
        };
        updatedFilteredDays.push(monthGroup);
        monthIndex = updatedFilteredDays.length - 1;
        
        updatedFilteredDays.sort((a, b) => 
            months.findIndex((m) => m.name === a.monthName) - 
            months.findIndex((m) => m.name === b.monthName)
        );
        
        monthIndex = updatedFilteredDays.findIndex(mg => mg.monthName === monthName);
    } else {
        monthGroup = updatedFilteredDays[monthIndex];
    }
    
    const weekNumber = getWeekNumberInMonth(dayDate);
    
    let weekIndex = monthGroup.weeks.findIndex(w => w.weekNumber === weekNumber);
    let week;
    
    if (weekIndex === -1) {
        week = {
            weekNumber: weekNumber,
            days: []
        };
        monthGroup.weeks.push(week);
        weekIndex = monthGroup.weeks.length - 1;
        
        monthGroup.weeks.sort((a, b) => a.weekNumber - b.weekNumber);
        
        weekIndex = monthGroup.weeks.findIndex(w => w.weekNumber === weekNumber);
    } else {
        week = monthGroup.weeks[weekIndex];
    }
    
    week.days.push(newDay);
    
    week.days.sort((a, b) => new Date(a.date) - new Date(b.date));
    
    filteredDays.value = updatedFilteredDays;
    
    nextTick(() => {
        console.log('Jour ajouté avec succès et visible dans l\'interface');
    });
};

// Variables pour le modal d'ajout
const isAddDayModalOpen = ref(false);
const newDay = ref({
    day: "Lundi", 
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
    newDay.value = { day: "Lundi", date: "", arrivals: [], departures: [] }; 
};

// Fonction pour ajouter un nouveau jour
const addDay = async () => {
    try {
        const formattedArrivals = newDay.value.arrivals.map((time) =>
            time ? time.substring(0, 5) : null
        );
        const formattedDepartures = newDay.value.departures.map((time) =>
            time ? time.substring(0, 5) : null
        );

        const response = await axios.post("/add-day", {
            user_id: props.user.id,
            day: newDay.value.day,
            date: newDay.value.date,
            arrivals: formattedArrivals,
            departures: formattedDepartures,
        });

        // Sauvegarder les filtres actuels et la position de défilement
        const currentYear = selectedYear.value;
        const currentMonth = selectedMonth.value;
        const scrollPosition = window.scrollY;
        
        // Stocker les valeurs dans sessionStorage
        sessionStorage.setItem('historical_year', currentYear);
        sessionStorage.setItem('historical_month', currentMonth);
        sessionStorage.setItem('historical_scroll', scrollPosition);
        
        displayFlash('Le jour a été ajouté avec succès !', 'success');
        closeAddDayModal();
        
        // Rafraîchir la page
        window.location.reload();
    } catch (error) {
        displayFlash('Erreur lors de l\'ajout du jour.', 'error');
        console.error("Erreur lors de l'ajout du jour :", error);
    }
};

// Restaurer les filtres et la position de défilement après chargement
onMounted(() => {
    // Restaurer les filtres si présents
    const savedYear = sessionStorage.getItem('historical_year');
    const savedMonth = sessionStorage.getItem('historical_month');
    const savedScroll = sessionStorage.getItem('historical_scroll');
    
    if (savedYear) {
        selectedYear.value = parseInt(savedYear);
        sessionStorage.removeItem('historical_year');
    }
    
    if (savedMonth) {
        selectedMonth.value = parseInt(savedMonth);
        sessionStorage.removeItem('historical_month');
    }
    
    // Appliquer les filtres
    filterDays();
    
    // Restaurer la position de défilement après un court délai
    if (savedScroll) {
        setTimeout(() => {
            window.scrollTo(0, parseInt(savedScroll));
            sessionStorage.removeItem('historical_scroll');
        }, 100);
    }
});

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

    <Head :title="pageTitle" />

    <AuthenticatedLayout>
        <section class="attendance-history bg-gray-50 pt-8 pb-16 min-h-screen">
            <!-- Flash messages -->
            <transition enter-active-class="transition ease-out duration-300 transform"
                enter-from-class="opacity-0 -translate-y-2" enter-to-class="opacity-100 translate-y-0"
                leave-active-class="transition ease-in duration-300 transform"
                leave-from-class="opacity-100 translate-y-0" leave-to-class="opacity-0 -translate-y-2">
                <div v-if="showFlash" :class="[
                    'fixed top-5 right-5 px-4 py-3 rounded-lg shadow-lg z-50 flex items-center',
                    flashType === 'success' ? 'bg-green-600 text-white' : 'bg-red-600 text-white'
                ]">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <i :class="[
                                'fas',
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

            <div class="max-w-[1700px] mx-auto px-4 sm:px-6 lg:px-8">
                <div class="p-6 rounded-lg text-center w-full bg-white">
                    <h1 class="text-gray-800 text-xl sm:text-2xl font-semibold shadow-md py-8">
                        <i class="fa-solid fa-clock-rotate-left text-[#005692] mr-2"></i>
                        Historique des pointages
                        <span v-if="isShow">de {{ user.name }}</span>
                    </h1>
                </div>

                <div class="mb-8 bg-white rounded-xl shadow-sm p-6">
                    <!-- En-tête et filter -->
                    <div class="flex flex-col space-y-6">
                        <!-- Bouton d'ajout et filtres -->
                        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                            <!-- Bouton d'ajout de jour pour les admins -->
                            <div class="mb-4 sm:mb-0">
                                <PrimaryButton
                                    v-if="isShow && page.props.auth.roles &&
                                        (page.props.auth.roles.includes('Admin') || page.props.auth.roles.includes('Informatique'))"
                                    @click="openAddDayModal" class="bg-[#005692] hover:bg-blue-700 w-full sm:w-auto">
                                    <i class="fas fa-plus mr-2"></i> Ajouter un jour
                                </PrimaryButton>
                            </div>

                            <!-- Filtres -->
                            <div class="flex flex-wrap gap-4 w-full sm:w-auto sm:justify-end">
                                <div class="relative w-full xs:w-auto">
                                    <label for="year"
                                        class="absolute -top-2.5 left-2 px-1 bg-white text-xs font-medium text-gray-600">
                                        Année
                                    </label>
                                    <select id="year" v-model="selectedYear"
                                        class="block w-full xs:w-28 rounded-md border-gray-300 pl-3 pr-10 py-2 text-sm shadow-sm focus:ring-blue-500 focus:border-blue-500">
                                        <option v-for="year in years" :key="year" :value="year">{{ year }}</option>
                                    </select>
                                </div>

                                <div class="relative w-full xs:w-auto">
                                    <label for="month"
                                        class="absolute -top-2.5 left-2 px-1 bg-white text-xs font-medium text-gray-600">
                                        Mois
                                    </label>
                                    <select id="month" v-model="selectedMonth"
                                        class="block w-full xs:w-40 rounded-md border-gray-300 pl-3 pr-10 py-2 text-sm shadow-sm focus:ring-blue-500 focus:border-blue-500">
                                        <option v-for="month in months" :key="month.value" :value="month.value">
                                            {{ month.name }}
                                        </option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <!-- Statistiques -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 w-full">
                            <div class="bg-indigo-50 rounded-lg p-4 flex items-center">
                                <div
                                    class="w-12 h-12 flex-shrink-0 rounded-full bg-indigo-100 flex items-center justify-center">
                                    <i class="fas fa-calendar-alt text-indigo-600 text-lg"></i>
                                </div>
                                <div class="ml-4">
                                    <h3 class="text-xs sm:text-sm font-medium text-gray-500">Jours enregistrés</h3>
                                    <p class="text-xl sm:text-2xl font-bold text-gray-800">{{ totalDaysRecorded }}</p>
                                </div>
                            </div>
                            <div class="bg-blue-50 rounded-lg p-4 flex items-center">
                                <div
                                    class="w-12 h-12 flex-shrink-0 rounded-full bg-blue-100 flex items-center justify-center">
                                    <i class="fas fa-clock text-blue-600 text-lg"></i>
                                </div>
                                <div class="ml-4">
                                    <h3 class="text-xs sm:text-sm font-medium text-gray-500">Total des heures</h3>
                                    <p class="text-xl sm:text-2xl font-bold text-gray-800 text-right sm:text-left">{{ formattedTotalTime }}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Contenu principal -->
                    <div v-for="(monthGroup, monthIndex) in filteredDays" :key="monthIndex" class="mb-10">
                        <!-- En-tête du mois -->
                        <div class="flex items-center mb-6">
                            <div class="h-0.5 flex-1 bg-gray-200"></div>
                            <h2 class="px-4 text-xl font-bold text-gray-700">{{ monthGroup.monthName }}</h2>
                            <div class="h-0.5 flex-1 bg-gray-200"></div>
                        </div>

                        <!-- Semaines -->
                        <div v-for="(week, weekIndex) in monthGroup.weeks" :key="`${monthIndex}-${weekIndex}`"
                            class="mb-6 bg-white rounded-xl shadow-sm overflow-hidden">

                            <!-- En-tête de la semaine -->
                            <div class="bg-gradient-to-r from-[#005692] to-[#0078c9] px-6 py-4 flex justify-between items-center 
                                text-white cursor-pointer" @click="toggleWeek(monthIndex, weekIndex)">
                                <div class="flex items-center">
                                    <i class="fas fa-calendar-week mr-3"></i>
                                    <h3 class="text-xs md:text-sm">
                                        Semaine {{ week.weekNumber }}
                                        <span class="text-xs md:text-sm font-normal opacity-80 ml-2">
                                            ({{ new Date(week.days[0].date).toLocaleDateString("fr-FR", {
                                                day: 'numeric',
                                            month:
                                            'short'}) }} -
                                            {{ new Date(week.days[week.days.length - 1].date).toLocaleDateString("fr-FR",
                                            {day:
                                            'numeric', month: 'short'}) }})
                                        </span>
                                    </h3>
                                </div>
                                <div class="flex items-center">
                                    <span class="mr-3 text-xs md:text-sm font-medium">
                                        {{ formatTotal(calculateWeekTotal(week)) }}
                                    </span>
                                    <i :class="[
                                        'fas text-xs md:text-sm',
                                        collapsedWeeks.includes(`${monthIndex}-${weekIndex}`)
                                            ? 'fa-chevron-down '
                                            : 'fa-chevron-up'
                                    ]"></i>
                                </div>
                            </div>

                            <!-- Contenu de la semaine -->
                            <div v-show="!collapsedWeeks.includes(`${monthIndex}-${weekIndex}`)"
                                class="divide-y divide-gray-100">
                                <div v-if="week.days.length === 0" class="px-6 py-8 text-center text-gray-500">
                                    Aucun pointage trouvé pour cette semaine.
                                </div>

                                <!-- Jours -->
                                <div v-for="(day, dayIndex) in week.days"
                                    :key="`${monthIndex}-${weekIndex}-${dayIndex}`"
                                    class="px-6 py-4 hover:bg-gray-50 transition-colors"
                                    :class="{ 'cursor-pointer': isShow }" @click="isShow ? openModal(day) : null">

                                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-gray-100">
                                        <!-- Date -->
                                        <div class="flex-shrink-0 flex items-center w-64">
                                            <div
                                                class="h-10 w-10 rounded-full bg-gray-100 flex items-center justify-center mr-4">
                                                <span class="text-sm font-medium text-gray-600">
                                                    {{ new Date(day.date).getDate() }}
                                                </span>
                                            </div>
                                            <div>
                                                <p class="font-medium text-gray-800 capitalize">{{ day.day }}</p>
                                                <p class="text-xs text-gray-500">
                                                    {{ new Date(day.date).toLocaleDateString("fr-FR") }}
                                                </p>
                                            </div>
                                        </div>

                                        <!-- Heures d'arrivée et départ -->
                                        <div class="flex-1 grid grid-cols-3 gap-8">
                                            <div class="text-center">
                                                <p class="text-xs text-gray-500 mb-1">Arrivée</p>
                                                <div class="flex items-center justify-center">
                                                    <span class="text-emerald-600 font-medium">
                                                        {{ formatTimeWithoutSeconds(day.arrivals && day.arrivals.length
                                                        > 0 ?
                                                        day.arrivals[0] : null) || "--:--" }}
                                                    </span>
                                                    <button v-if="day.arrivals && day.arrivals.length > 1"
                                                        class="ml-2 text-xs text-gray-500 hover:text-gray-700"
                                                        @click.stop="toggleExpand(monthIndex, `${weekIndex}-${dayIndex}`)">
                                                        (+ {{ day.arrivals.length - 1 }})
                                                    </button>
                                                </div>
                                            </div>
                                            <div class="text-center">
                                                <p class="text-xs text-gray-500 mb-1">Départ</p>
                                                <div class="flex items-center justify-center">
                                                    <span class="text-red-600 font-medium">
                                                        {{ formatTimeWithoutSeconds(day.departures &&
                                                            day.departures.length > 0
                                                        ? day.departures[0] : null) || "--:--" }}
                                                    </span>
                                                    <button v-if="day.departures && day.departures.length > 1"
                                                        class="ml-2 text-xs text-gray-500 hover:text-gray-700"
                                                        @click.stop="toggleExpand(monthIndex, `${weekIndex}-${dayIndex}`)">
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
                                    <div v-if="expandedDays.includes(`${monthIndex}-${weekIndex}-${dayIndex}`)"
                                        class="mt-4 pt-4 border-t border-gray-100">
                                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                                            <div>
                                                <h4 class="text-sm font-medium text-gray-700 mb-2">Détail des arrivées
                                                </h4>
                                                <div class="flex flex-wrap gap-2">
                                                    <span v-for="(arrival, i) in day.arrivals" :key="`arrival-${weekIndex}-${dayIndex}-${i}`"
                                                        class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                        {{ formatTimeWithoutSeconds(arrival) }}
                                                    </span>
                                                    <span v-if="day.arrivals.length === 0"
                                                        class="text-sm text-gray-500">
                                                        Aucune arrivée enregistrée
                                                    </span>
                                                </div>
                                            </div>
                                            <div>
                                                <h4 class="text-sm font-medium text-gray-700 mb-2">Détail des départs
                                                </h4>
                                                <div class="flex flex-wrap gap-2">
                                                    <span v-for="(departure, i) in day.departures" :key="`departure-${weekIndex}-${dayIndex}-${i}`"
                                                        class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                                        {{ formatTimeWithoutSeconds(departure) }}
                                                    </span>
                                                    <span v-if="day.departures.length === 0"
                                                        class="text-sm text-gray-500">
                                                        Aucun départ enregistré
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Total de la semaine -->
                                <div class="px-6 py-3 bg-indigo-50 flex justify-end items-center">
                                    <div class="mr-8 font-medium text-gray-700">
                                        Total semaine {{ week.weekNumber }}:
                                    </div>
                                    <div class="font-bold text-blue-700">
                                        {{ formatTotal(calculateWeekTotal(week)) }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Bouton retour -->
                    <div class="mt-10 flex justify-center">
                        <Link v-if="isShow" :href="route('employes')">
                        <PrimaryButton class="bg-gray-800 hover:bg-gray-700">
                            <i class="fas fa-arrow-left mr-2"></i> Retour à la gestion des employés
                        </PrimaryButton>
                        </Link>
                        <Link v-else :href="route('home')">
                        <PrimaryButton class="bg-gray-800 hover:bg-gray-700">
                            <i class="fas fa-arrow-left mr-2"></i> Retour à l'accueil
                        </PrimaryButton>
                        </Link>
                    </div>
                </div>
            </div>
        </section>

        <!-- Modals -->
        <div v-if="isModalOpen" class="fixed z-50 inset-0 flex items-center justify-center bg-gray-800 bg-opacity-75">
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
