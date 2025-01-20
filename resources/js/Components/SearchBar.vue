<script setup>
import { ref, computed } from "vue";
import { router, usePage } from "@inertiajs/vue3";

// Récupération du rôle de l'utilisateur
const user = usePage().props.auth.user;
const userRole = computed(() => user.roles?.[0]?.name || '');

// Configuration des accès par rôle
const roleAccess = {
    Admin: ['dashboard', 'employes', 'management-call', 'index', 'historique', 'demandes-de-rappel'],
    Informatique: ['dashboard', 'employes', 'management-call', 'index', 'historique', 'demandes-de-rappel'],
    Comptabilité: ['dashboard', 'management-call', 'index', 'historique', 'demandes-de-rappel'],
    Employé: ['dashboard', 'index', 'historique'],
    Ouvrier: ['dashboard', 'index', 'historique']
};

// Liste des routes avec leurs mots-clés associés
const allRoutes = [
    {
        path: '/pointage',
        name: 'dashboard',
        label: 'Pointer aujourd\'hui',
        keywords: ['pointage', 'pointer', 'présence', 'aujourd\'hui', 'entrée', 'sortie', 'temps de travail']
    },
    {
        path: '/liste-des-employes',
        name: 'employes',
        label: 'Liste des employés',
        keywords: ['employés', 'liste', 'personnel', 'équipe', 'gestion des employés', 'trombinoscope', 'Jordan', 'Omar', 'Fatima', 'Chris', 'Nicolas', 'Ouvrier', 'Informatique', 'Comptabilité', 'Admin', 'Employé']
    },
    {
        path: '/gestion-appels-telephoniques',
        name: 'management-call',
        label: 'Gestion des appels',
        keywords: ['appels', 'téléphone', 'prospects', 'notes','clients', 'rechercher un prospect', 'appels téléphoniques', 'gestion des appels', 'encoder', 'encoder un appel', 'prospect', 'client', 'ajouter un prospect', 'ajouter un client', 'ajouter', 'ajouter un nouveau prospect', 'ajouter un nouveau client', 'ajouter un nouveau prospect', 'ajouter un nouveau client']
    },
    {
        path: '/index',
        name: 'index',
        label: 'Accueil',
        keywords: ['accueil', 'dashboard', 'tableau de bord', 'menu principal']
    },
    {
        path: '/historique',
        name: 'historique',
        label: 'Historique',
        keywords: ['Historique', 'Consulter historique', 'Pointages']
    },
    {
        path: '/demandes-de-rappel',
        name: 'demandes-de-rappel',
        label: 'Demandes de rappel',
        keywords: ['Demandes de rappel', 'Consulter les demandes de rappel', 'Historique des demandes de rappel', 'Relance', 'Commercial']
    },
];

// Filtrer les routes selon le rôle de l'utilisateur
const routes = computed(() => {
    const role = userRole.value;
    const allowedRoutes = roleAccess[role] || [];
    console.log('Role actuel:', role); // Pour le débogage
    console.log('Routes autorisées:', allowedRoutes); // Pour le débogage
    return allRoutes.filter(route => allowedRoutes.includes(route.name));
});

// État réactif pour la recherche
const searchQuery = ref('');
const isFocused = ref(false);
const showSuggestions = ref(false);

// Fonction de recherche améliorée
const filteredSuggestions = computed(() => {
    if (!searchQuery.value) return [];
    
    const query = searchQuery.value.toLowerCase();
    const words = query.split(' ').filter(word => word.length > 0);
    
    return routes.value
        .map(route => {
            // Créer une chaîne de recherche qui inclut tous les mots-clés
            const searchString = [
                route.label.toLowerCase(),
                route.path.toLowerCase(),
                ...route.keywords.map(k => k.toLowerCase())
            ].join(' ');
            
            // Calculer un score de pertinence
            const score = words.reduce((acc, word) => {
                if (searchString.includes(word)) {
                    // Bonus si le mot est trouvé dans le label
                    if (route.label.toLowerCase().includes(word)) acc += 2;
                    // Bonus si le mot est trouvé dans les mots-clés exacts
                    if (route.keywords.some(k => k.toLowerCase() === word)) acc += 3;
                    // Score de base pour chaque mot trouvé
                    acc += 1;
                }
                return acc;
            }, 0);
            
            return { ...route, score };
        })
        .filter(route => route.score > 0) // Garder uniquement les résultats pertinents
        .sort((a, b) => b.score - a.score); // Trier par score décroissant
});

const handleFocus = () => {
    isFocused.value = true;
    showSuggestions.value = true;
};

const handleBlur = () => {
    setTimeout(() => {
        isFocused.value = false;
        showSuggestions.value = false;
    }, 200);
};

const navigateToRoute = (routeName) => {
    router.get(route(routeName));
    searchQuery.value = '';
    showSuggestions.value = false;
};
</script>

<template>
    <div
        class="relative transition-all"
        :class="{ 'w-[500px] bg-white': isFocused, 'w-[300px]': !isFocused }"
    >
        <input
            v-model="searchQuery"
            type="text"
            placeholder="Rechercher une page..."
            class="w-full py-2 pl-10 pr-4 text-gray-700 border rounded-full outline-none transition-all"
            :class="{
                'border-blue-500': isFocused,
                'border-gray-300': !isFocused,
            }"
            @focus="handleFocus"
            @blur="handleBlur"
        />
        <div class="absolute inset-y-0 left-0 flex items-center pl-3">
            <svg
                xmlns="http://www.w3.org/2000/svg"
                class="w-5 h-5 transition-all"
                :class="{
                    'text-blue-500': isFocused,
                    'text-gray-400': !isFocused,
                }"
                fill="none"
                viewBox="0 0 24 24"
                stroke="currentColor"
            >
                <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"
                />
            </svg>
        </div>

        <!-- Liste des suggestions améliorée -->
        <div
            v-if="showSuggestions && filteredSuggestions.length > 0"
            class="absolute w-full mt-2 bg-white rounded-lg shadow-lg border border-gray-200 z-50 max-h-[400px] overflow-y-auto"
        >
            <div
                v-for="suggestion in filteredSuggestions"
                :key="suggestion.path"
                @mousedown="navigateToRoute(suggestion.name)"
                class="px-4 py-3 hover:bg-gray-100 cursor-pointer border-b border-gray-100 last:border-b-0"
            >
                <div class="font-medium text-gray-700">{{ suggestion.label }}</div>
                <div class="text-sm text-gray-500 mt-1">
                    <span class="text-blue-500">#</span>
                    {{ suggestion.keywords.slice(0, 3).join(' • ') }}
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
.transition-all {
    transition: all 0.3s ease;
}
</style>
