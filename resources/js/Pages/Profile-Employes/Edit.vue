<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import UpdateProfileInformationForm from "./Partials/UpdateProfileInformationForm.vue";
import UpdatePasswordForm from "./Partials/UpdatePasswordForm.vue";
import DeleteUserForm from "./Partials/DeleteUserForm.vue";
import { Head } from "@inertiajs/vue3";
import { ref } from "vue";
import { router } from "@inertiajs/vue3";

// Recevoir l'utilisateur via les props
const props = defineProps({
    user: Object, // L'utilisateur sélectionné
    mustVerifyEmail: Boolean,
    status: String,
});

// Déterminer si le compte est désactivé
const isDisabled = ref(props.user.status === 1);

// Méthode pour réactiver le compte
const reactivateUser = () => {
    router.put(
        route("employees.reactivate", props.user.id),
        {},
        {
            preserveScroll: true,
            onSuccess: () => {
                isDisabled.value = false;
            },
        }
    );
};
</script>

<template>
    <Head :title="`Gestion de profil de ${user.name}`" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-100 leading-tight">
                Gestion de profil de {{ user.name }}
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
                <!-- Réactiver le compte si désactivé -->
                <!-- Réactiver le compte si désactivé -->
                <div
                    v-if="isDisabled"
                    class="p-6 sm:p-8 bg-gradient-to-r from-red-500 to-red-400 text-white shadow-lg sm:rounded-lg border-l-4 border-red-800"
                >
                    <div class="flex items-start space-x-4">
                        <!-- Icône -->
                        <i
                            class="fa-solid fa-triangle-exclamation text-4xl"
                        ></i>

                        <!-- Texte et bouton -->
                        <div class="flex-1">
                            <p class="text-xl font-bold mb-2">
                                Ce compte est désactivé.
                            </p>
                            <p class="text-sm mb-6">
                                Cet utilisateur ne pourra pas accéder à son
                                compte tant qu'il n'est pas réactivé. Vous
                                pouvez réactiver ce compte en cliquant sur le
                                bouton ci-dessous.
                            </p>
                            <button
                                @click="reactivateUser"
                                class="bg-blue-600 hover:bg-blue-800 text-white font-bold py-2 px-4 rounded shadow-lg transform transition-transform hover:scale-105"
                            >
                                Réactiver le compte
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Mise à jour des informations du profil -->
                <div
                    :class="{ hidden: isDisabled }"
                    class="p-4 sm:p-8 bg-white shadow sm:rounded-lg"
                >
                    <UpdateProfileInformationForm
                        :user="user"
                        :must-verify-email="mustVerifyEmail"
                        :status="status"
                        class="w-full"
                    />
                </div>

                <!-- Mise à jour du mot de passe -->
                <div
                    :class="{ hidden: isDisabled }"
                    class="p-4 sm:p-8 bg-white shadow sm:rounded-lg"
                >
                    <UpdatePasswordForm :user="user" class="max-w-xl" />
                </div>

                <!-- Suppression du compte -->
                <div
                    :class="{ hidden: isDisabled }"
                    class="p-4 sm:p-8 bg-white shadow sm:rounded-lg"
                >
                    <DeleteUserForm :user="user" class="max-w-xl" />
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
