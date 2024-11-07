<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head, Link } from "@inertiajs/vue3";
</script>

<template>
    <Head title="Accueil" />

    <AuthenticatedLayout>
        <template #header>
            <h2
                class="font-semibold text-xl text-white bg-gray-800 leading-tight"
            >
                Accueil
            </h2>
        </template>

        <div
            class="home-section w-full max-w-7xl mx-auto min-h-[800px] px-6 sm:px-8 py-16 bg-white rounded-lg shadow-lg"
        >
            <div class="text-center mb-8 md:mt-24">
                <h1 class="text-2xl md:text-3xl font-semibold text-gray-900">
                    Que souhaitez-vous faire ?
                </h1>
                <p class="text-gray-600 mt-2">
                    Sélectionnez une option pour commencer.
                </p>
            </div>

            <div
                class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mt-8"
            >
                <Link
                    :href="route('dashboard')"
                    class="flex items-center mb-4 sm:mb-0 space-x-3 rtl:space-x-reverse action-button"
                >
                    <i class="fas fa-check-circle text-2xl mb-2"></i>
                    <span>Pointer aujourd'hui</span>
                </Link>

                <Link
                    :href="route('historique')"
                    class="flex items-center mb-4 sm:mb-0 space-x-3 rtl:space-x-reverse action-button"
                >
                    <i class="fas fa-history text-2xl mb-2"></i>
                    <span>Consulter l'historique</span>
                </Link>

                <Link
                    :href="route('employes')"
                    class="flex items-center mb-4 sm:mb-0 space-x-3 rtl:space-x-reverse action-button"
                >
                    <i class="fas fa-users text-2xl mb-2"></i>
                    <span>Gestion des employés</span>
                </Link>

                <Link
                    :href="route('managementCall')"
                    class="flex items-center mb-4 sm:mb-0 space-x-3 rtl:space-x-reverse action-button"
                >
                    <i class="fas fa-phone text-2xl mb-2"></i>
                    <span>Gestion des appels</span>
                </Link>
            </div>

            <!-- OTHER BUTTONS -->
            <div
                class="other-buttons mt-12 md:mt-80 flex justify-center space-x-6 md:space-x-8 text-gray-600"
            >
                <Link
                    :href="route('profile.edit')"
                    class="flex items-center space-x-2 text-sm md:text-lg hover:text-blue-600 transition-colors duration-200 ease-in-out"
                >
                    <i class="fas fa-user-circle"></i>
                    <span>Gestion de compte</span>
                </Link>

                <Link
                    :href="route('logout')"
                    method="post"
                    as="button"
                    class="flex items-center space-x-2 text-sm md:text-lg hover:text-red-600 transition-colors duration-200 ease-in-out"
                >
                    <i class="fas fa-sign-out-alt"></i>
                    <span>Se déconnecter</span>
                </Link>
            </div>

        </div>
    </AuthenticatedLayout>
</template>

<style scoped>
.action-button {
    @apply flex flex-col items-center justify-center bg-gray-100 text-gray-800 p-6 rounded-lg shadow-md font-medium hover:bg-[rgb(0,86,146)] hover:text-white transition-colors duration-300 ease-in-out;
}

.action-button:hover {
    transform: scale(1.05);
}
</style>
