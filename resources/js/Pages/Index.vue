<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head, Link, usePage } from "@inertiajs/vue3";
import Header from "@/Components/Header.vue";
import { computed } from 'vue';

const page = usePage();

// Calculer le nombre de cartes visibles en fonction des rôles
const visibleCards = computed(() => {
    let count = 2; // Pointage et Historique sont toujours visibles
    const roles = page.props.auth.roles || [];
    const isAdmin = roles.includes('Admin');
    const isIT = roles.includes('Informatique');
    const isCompta = roles.includes('Comptabilité');

    if (isAdmin || isIT) count++; // Gestion des employés
    if (isAdmin || isIT || isCompta) {
        count += 2; // Gestion des appels et Demandes de rappel
    }

    return count;
});
</script>

<template>
    <Head title="Accueil" />

    <AuthenticatedLayout>
        <template #header>
            <Header :pageTitle="'Accueil'" />
        </template>

        <div class="min-h-screen bg-gradient-to-br from-gray-50 to-gray-100">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
                <div class="text-center mb-12">
                    <h1 class="text-2xl sm:text-4xl md:text-5xl font-bold text-gray-900 mb-4 tracking-tight">
                        Bienvenue sur <span class="text-[rgb(0,86,146)]">Metalcash</span>
                    </h1>
                    <p class="text-base sm:text-lg text-gray-600 max-w-2xl mx-auto">
                        Gérez votre temps et vos activités efficacement
                    </p>
                </div>

                <div class="grid auto-rows-fr justify-center gap-4 lg:gap-8 max-w-6xl mx-auto" 
                    :class="{
                        'grid-cols-1 sm:grid-cols-1 md:grid-cols-1 w-full max-w-md': visibleCards === 1,
                        'grid-cols-2 sm:grid-cols-2 w-full max-w-2xl': visibleCards === 2,
                        'grid-cols-2 sm:grid-cols-2 lg:grid-cols-3 w-full': visibleCards >= 3
                    }">
                    <Link v-if="true" :href="route('dashboard')" 
                        class="action-card group">
                        <div class="card-icon-wrapper">
                            <i class="fas fa-check-circle"></i>
                        </div>
                        <h2 class="card-title">Pointer aujourd'hui</h2>
                        <p class="card-description">Enregistrez votre présence pour la journée</p>
                    </Link>

                    <Link v-if="true" :href="route('historique')" 
                        class="action-card group">
                        <div class="card-icon-wrapper">
                            <i class="fas fa-history"></i>
                        </div>
                        <h2 class="card-title">Historique</h2>
                        <p class="card-description">Consultez vos pointages précédents</p>
                    </Link>

                    <Link
                        v-if="page.props.auth.roles && (page.props.auth.roles.includes('Admin') || page.props.auth.roles.includes('Informatique'))"
                        :href="route('employes')"
                        class="action-card group">
                        <div class="card-icon-wrapper">
                            <i class="fas fa-users"></i>
                        </div>
                        <h2 class="card-title">Gestion des employés</h2>
                        <p class="card-description">Gérez les informations du personnel</p>
                    </Link>

                    <Link
                        v-if="page.props.auth.roles && (page.props.auth.roles.includes('Admin') || page.props.auth.roles.includes('Informatique') || page.props.auth.roles.includes('Comptabilité'))"
                        :href="route('managementCall')"
                        class="action-card group">
                        <div class="card-icon-wrapper">
                            <i class="fas fa-phone"></i>
                        </div>
                        <h2 class="card-title">Gestion des appels</h2>
                        <p class="card-description">Suivez et gérez les appels</p>
                    </Link>

                    <Link
                        v-if="page.props.auth.roles && (page.props.auth.roles.includes('Admin') || page.props.auth.roles.includes('Informatique') || page.props.auth.roles.includes('Comptabilité'))"
                        :href="route('contactRelance')"
                        class="action-card group">
                        <div class="card-icon-wrapper">
                            <i class="fa-solid fa-phone-volume"></i>
                        </div>
                        <h2 class="card-title">Demandes de rappel</h2>
                        <p class="card-description">Gérez les demandes de rappel</p>
                    </Link>
                </div>

                <div class="mt-16 flex flex-col sm:flex-row items-center justify-center gap-6 text-gray-600">
                    <div class="w-full sm:w-auto flex flex-col sm:flex-row items-center justify-center gap-4 sm:gap-6 px-4 sm:px-0">
                        <Link
                            :href="route('profile.edit')"
                            class="user-action-button w-full sm:w-auto justify-center bg-white hover:bg-gray-50">
                            <i class="fas fa-user-circle mr-2"></i>
                            <span>Mon compte</span>
                        </Link>

                        <Link
                            :href="route('logout')"
                            method="post"
                            as="button"
                            class="user-action-button w-full sm:w-auto justify-center bg-red-50 text-red-600 hover:bg-red-100">
                            <i class="fas fa-sign-out-alt mr-2"></i>
                            <span>Déconnexion</span>
                        </Link>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<style scoped>
.action-card {
    @apply bg-white p-4 sm:p-6 rounded-2xl shadow-sm hover:shadow-lg transition-all duration-300 ease-in-out flex flex-col items-center text-center border border-gray-100 hover:border-[rgb(0,86,146)] relative overflow-hidden;
}

.card-icon-wrapper {
    @apply w-12 h-12 sm:w-14 sm:h-14 rounded-xl bg-[rgb(0,86,146)] bg-opacity-10 flex items-center justify-center mb-3 sm:mb-4 text-[rgb(0,86,146)] group-hover:bg-[rgb(0,86,146)] group-hover:text-white transition-all duration-300;
}

.card-icon-wrapper i {
    @apply text-xl sm:text-2xl transition-transform duration-300 group-hover:scale-110;
}

.card-title {
    @apply text-base sm:text-lg font-semibold text-gray-900 mb-1 sm:mb-2;
}

.card-description {
    @apply text-xs sm:text-sm text-gray-600 group-hover:text-gray-700;
}

.user-action-button {
    @apply flex items-center px-6 py-3 sm:py-2 text-sm font-medium rounded-lg transition-all duration-200 ease-in-out border border-gray-200;
}

/* Animation subtile au hover */
.action-card::after {
    content: '';
    @apply absolute inset-0 bg-gradient-to-r from-[rgb(0,86,146)] to-blue-400 opacity-0 transition-opacity duration-300;
    mix-blend-mode: overlay;
}

.action-card:hover::after {
    @apply opacity-10;
}
</style>
