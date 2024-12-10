<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head, Link, usePage } from "@inertiajs/vue3";
import Header from "@/Components/Header.vue";

const page = usePage();
</script>

<template>
    <Head title="Accueil" />

    <AuthenticatedLayout>
        <template #header>
            <Header :pageTitle="'Accueil'" />
        </template>

        <div
            class="home-section max-w-[1700px] mt-16 mx-auto px-6 sm:px-8 py-16 bg-white rounded-lg shadow-lg min-h-[800px]"
        >
            <div class="text-center mb-8">
                <h1 class="text-2xl md:text-3xl font-semibold text-gray-900">
                    Que souhaitez-vous faire ?
                </h1>
                <p class="text-gray-600 mt-2">
                    Sélectionnez une option pour commencer.
                </p>
            </div>

            <div class="flex justify-center">
                <div class="flex flex-wrap justify-center gap-6 mt-8">
                    <Link :href="route('dashboard')" class="action-button">
                        <i class="fas fa-check-circle text-2xl mb-2"></i>
                        <span>Pointer aujourd'hui</span>
                    </Link>

                    <Link :href="route('historique')" class="action-button">
                        <i class="fas fa-history text-2xl mb-2"></i>
                        <span>Consulter mon historique</span>
                    </Link>

                    <Link
                        v-if="
                            page.props.auth.roles &&
                            (page.props.auth.roles.includes('Admin') ||
                                page.props.auth.roles.includes(
                                    'Informatique'
                                ) ||
                                page.props.auth.roles.includes('Comptabilité'))
                        "
                        :href="route('employes')"
                        class="action-button"
                    >
                        <i class="fas fa-users text-2xl mb-2"></i>
                        <span>Gestion des employés</span>
                    </Link>

                    <Link
                        v-if="
                            page.props.auth.roles &&
                            (page.props.auth.roles.includes('Admin') ||
                                page.props.auth.roles.includes(
                                    'Informatique'
                                ) ||
                                page.props.auth.roles.includes('Comptabilité'))
                        "
                        :href="route('managementCall')"
                        class="action-button"
                    >
                        <i class="fas fa-phone text-2xl mb-2"></i>
                        <span>Gestion des appels</span>
                    </Link>
                </div>
            </div>

            <!-- OTHER BUTTONS -->
            <div
                class="other-buttons mt-12 flex justify-center space-x-6 md:space-x-8 text-gray-600"
            >
                <Link
                    :href="route('profile.edit')"
                    class="flex items-center space-x-2 text-sm md:text-base hover:text-blue-600 transition-colors duration-200 ease-in-out"
                >
                    <i class="fas fa-user-circle"></i>
                    <span>Gestion de compte</span>
                </Link>

                <Link
                    :href="route('logout')"
                    method="post"
                    as="button"
                    class="flex items-center space-x-2 text-sm md:text-base hover:text-red-600 transition-colors duration-200 ease-in-out"
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
