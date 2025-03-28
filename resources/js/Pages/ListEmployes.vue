<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import InputError from "@/Components/InputError.vue";
import InputLabel from "@/Components/InputLabel.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import TextInput from "@/Components/TextInput.vue";
import Header from "@/Components/Header.vue";
import { Head, useForm, usePage, Link } from "@inertiajs/vue3";
import { ref, defineProps } from "vue";

// Props pour recevoir les utilisateurs
const props = defineProps({
    users: Array,
});

const page = usePage();

// Contrôle du modal
const isModalOpen = ref(false);

// Formulaire pour ajouter un employé
const form = useForm({
    name: "",
    email: "",
    password: "",
    password_confirmation: "",
    role: "Employé",
});

// Date du jour courant pour gestion du status
const today = new Date().toISOString().split("T")[0];

// Fonction pour soumettre le formulaire
const submit = () => {
    form.post(route("employees.store"), {
        onFinish: () => {
            // Si le formulaire n'a pas d'erreurs, on ferme le modal
            if (Object.keys(form.errors).length === 0) {
                form.reset("password", "password_confirmation");
                closeModal();
            }
        },
    });
};

// Ouvrir et fermer le modal
const openModal = () => (isModalOpen.value = true);
// Fonction pour réinitialiser le formulaire et fermer le modal
const closeModal = () => {
    // Réinitialiser manuellement chaque champ
    form.name = "";
    form.email = "";
    form.password = "";
    form.password_confirmation = "";
    form.role = "Employé";

    // Fermer le modal
    isModalOpen.value = false;
};
</script>

<template>
    <Head title="Liste des employés" />

    <AuthenticatedLayout>
        <template #header>
            <Header :pageTitle="'Gestion des employés'" />
        </template>

        <div
            class="container flex-grow max-w-[1700px] mt-8 sm:mt-16 mx-auto px-2 sm:px-4 md:px-6 lg:px-8 bg-gray-50 py-4 sm:py-8 min-h-[800px]"
        >
            <div
                class="p-4 sm:p-6 rounded-lg text-center w-full bg-white shadow-sm mb-4 sm:mb-8"
            >
                <h2 class="text-gray-800 text-lg sm:text-xl md:text-2xl font-semibold py-2 sm:py-4">
                    <i class="fa-solid fa-user text-[#005692] mr-2"></i> Gestion
                    des employés
                </h2>
            </div>
            <div class="bg-white rounded-xl shadow-sm p-4 sm:p-6 mb-6 sm:mb-8">
                <!-- Titre de la page -->
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-4 sm:mb-6">
                    <h2
                        class="text-lg sm:text-xl font-bold text-gray-700"
                    >
                        Liste des employés
                    </h2>

                    <!-- Bouton pour ouvrir le modal -->
                    <PrimaryButton
                        class="w-full sm:w-auto bg-[#005692] hover:bg-[rgba(0,85,150,0.8)] transition duration-150 ease-in-out"
                        @click="openModal"
                        v-if="
                            page.props.auth.roles &&
                            (page.props.auth.roles.includes('Admin') ||
                                page.props.auth.roles.includes('Informatique'))
                        "
                        ><i class="fas fa-plus mr-2"></i>Ajouter un employé</PrimaryButton
                    >
                </div>

                <!-- Table responsive -->
                <div class="block lg:hidden">
                    <!-- Vue mobile et tablette : cartes empilées -->
                    <div class="space-y-4">
                        <div v-for="user in users" :key="user.id" 
                            class="bg-white rounded-lg shadow-sm p-4 hover:bg-gray-50 transition-colors"
                            :class="{ 'border-l-4 border-red-500': user.status === 1 }"
                        >
                            <div class="flex justify-between items-start mb-3">
                                <div 
                                    @click="() => $inertia.get(route('employees.profile', user.id))"
                                    class="cursor-pointer"
                                >
                                    <h3 
                                        :class="{
                                            'text-red-500': user.status === 1,
                                            'text-gray-800 hover:text-[#0078c9]': user.status !== 1
                                        }"
                                        class="font-medium text-base"
                                    >
                                        {{ user.name }}
                                    </h3>
                                    <p class="text-sm text-gray-500 mt-1">{{ user.email }}</p>
                                </div>
                                <div>
                                    <span
                                        v-if="
                                            user.days &&
                                            user.days.length &&
                                            user.days.some(
                                                (day) =>
                                                    day.date === today &&
                                                    day.status === 1
                                            )
                                        "
                                        class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800"
                                    >
                                        Actif
                                    </span>
                                    <span
                                        v-else
                                        class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800"
                                    >
                                        Inactif
                                    </span>
                                </div>
                            </div>
                            
                            <div class="flex flex-wrap gap-2 text-sm mb-3">
                                <span class="text-gray-500">Rôle:</span>
                                <span 
                                    :class="{
                                        'text-red-500': user.status === 1,
                                        'text-gray-700': user.status !== 1
                                    }"
                                >
                                    <span
                                        v-for="role in user.roles"
                                        :key="role.id"
                                    >
                                        {{ role.name
                                        }}<span
                                            v-if="
                                                user.roles.length > 1 &&
                                                role !==
                                                    user.roles[
                                                        user.roles.length - 1
                                                    ]
                                            "
                                            >,
                                        </span>
                                    </span>
                                </span>
                            </div>
                            
                            <div class="flex justify-end mt-2">
                                <!-- Bouton "Voir historique" pour Comptabilité -->
                                <button
                                    v-if="
                                        user.status !== 1 &&
                                        page.props.auth.roles.includes(
                                            'Comptabilité'
                                        )
                                    "
                                    @click="
                                        () =>
                                            $inertia.get(
                                                route(
                                                    'users.pointages',
                                                    user.id
                                                )
                                            )
                                    "
                                    class="px-3 py-1.5 text-xs text-white bg-[#005692] rounded-md hover:bg-[rgba(0,85,150,0.8)] transition duration-150 ease-in-out shadow-sm"
                                >
                                    <i class="fa-regular fa-eye mr-1"></i>
                                    Voir historique
                                </button>

                                <!-- Bouton "Gestion des pointages" pour les autres -->
                                <button
                                    v-if="
                                        user.status !== 1 &&
                                        !page.props.auth.roles.includes(
                                            'Comptabilité'
                                        )
                                    "
                                    @click="
                                        () =>
                                            $inertia.get(
                                                route(
                                                    'users.pointages',
                                                    user.id
                                                )
                                            )
                                    "
                                    class="px-3 py-1.5 text-xs text-white bg-[#005692] rounded-md hover:bg-[rgba(0,85,150,0.8)] transition duration-150 ease-in-out shadow-sm"
                                >
                                    <i class="fa-solid fa-gear mr-1"></i>
                                    Gestion des pointages
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Vue desktop : tableau classique -->
                <div class="hidden lg:block overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 rounded-xl overflow-hidden">
                        <thead>
                            <tr class="bg-gradient-to-r from-[#005692] to-[#0078c9] text-white">
                                <th
                                    class="px-6 py-4 text-left text-sm font-medium tracking-wider"
                                >
                                    Nom
                                </th>
                                <th
                                    class="px-6 py-4 text-left text-sm font-medium tracking-wider"
                                >
                                    E-mail
                                </th>
                                <th
                                    class="px-6 py-4 text-left text-sm font-medium tracking-wider"
                                >
                                    Rôle
                                </th>
                                <th
                                    class="px-6 py-4 text-center text-sm font-medium tracking-wider"
                                >
                                    Statut
                                </th>
                                <th
                                    class="px-6 py-4 text-center text-sm font-medium tracking-wider"
                                >
                                    Action
                                </th>
                            </tr>
                        </thead>
                        <tbody
                            class="bg-white divide-y divide-gray-100"
                        >
                            <tr v-for="user in users" :key="user.id" class="hover:bg-gray-50 transition-colors">
                                <td
                                    @click="
                                        () =>
                                            $inertia.get(
                                                route(
                                                    'employees.profile',
                                                    user.id
                                                )
                                            )
                                    "
                                    :class="{
                                        'text-red-500 hover:text-red-700':
                                            user.status === 1, // Texte rouge si désactivé
                                        'hover:text-[#0078c9]':
                                            user.status !== 1, // Texte bleu au survol si actif
                                    }"
                                    class="px-6 py-4 whitespace-nowrap cursor-pointer font-medium"
                                >
                                    {{ user.name }}
                                </td>

                                <td
                                    :class="{
                                        'text-red-500 hover:text-red-700':
                                            user.status === 1, // Texte rouge si désactivé
                                        'hover:text-[#0078c9]':
                                            user.status !== 1, // Texte bleu au survol si actif
                                    }"
                                    class="px-6 py-4 whitespace-nowrap cursor-pointer"
                                    @click="
                                        () =>
                                            $inertia.get(
                                                route(
                                                    'employees.profile',
                                                    user.id
                                                )
                                            )
                                    "
                                >
                                    {{ user.email }}
                                </td>
                                <td
                                    :class="{
                                        'text-red-500 hover:text-red-700':
                                            user.status === 1, // Texte rouge si désactivé
                                        'text-gray-700':
                                            user.status !== 1, // Texte normal si actif
                                    }"
                                    class="px-6 py-4 whitespace-nowrap"
                                >
                                    <span
                                        v-for="role in user.roles"
                                        :key="role.id"
                                    >
                                        {{ role.name
                                        }}<span
                                            v-if="
                                                user.roles.length > 1 &&
                                                role !==
                                                    user.roles[
                                                        user.roles.length - 1
                                                    ]
                                            "
                                            >,
                                        </span>
                                    </span>
                                </td>
                                <td
                                    class="px-6 py-4 whitespace-nowrap cursor-auto text-center"
                                >
                                    <span
                                        v-if="
                                            user.days &&
                                            user.days.length &&
                                            user.days.some(
                                                (day) =>
                                                    day.date === today &&
                                                    day.status === 1
                                            )
                                        "
                                        class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800"
                                    >
                                        Actif
                                    </span>
                                    <span
                                        v-else
                                        class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800"
                                    >
                                        Inactif
                                    </span>
                                </td>

                                <td
                                    class="px-6 py-4 whitespace-nowrap text-center"
                                >
                                    <!-- Bouton "Voir historique" pour Comptabilité -->
                                    <button
                                        v-if="
                                            user.status !== 1 &&
                                            page.props.auth.roles.includes(
                                                'Comptabilité'
                                            )
                                        "
                                        @click="
                                            () =>
                                                $inertia.get(
                                                    route(
                                                        'users.pointages',
                                                        user.id
                                                    )
                                                )
                                        "
                                        class="px-4 py-2 text-sm text-white bg-[#005692] rounded-md hover:bg-[rgba(0,85,150,0.8)] transition duration-150 ease-in-out shadow-sm"
                                    >
                                        <i class="fa-regular fa-eye mr-1"></i>
                                        Voir historique
                                    </button>

                                    <!-- Bouton "Gestion des pointages" pour les autres -->
                                    <button
                                        v-if="
                                            user.status !== 1 &&
                                            !page.props.auth.roles.includes(
                                                'Comptabilité'
                                            )
                                        "
                                        @click="
                                            () =>
                                                $inertia.get(
                                                    route(
                                                        'users.pointages',
                                                        user.id
                                                    )
                                                )
                                        "
                                        class="px-4 py-2 text-sm text-white bg-[#005692] rounded-md hover:bg-[rgba(0,85,150,0.8)] transition duration-150 ease-in-out shadow-sm"
                                    >
                                        <i class="fa-solid fa-gear mr-1"></i>
                                        Gestion des pointages
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="mt-10 flex justify-center">
                <Link :href="route('home')">
                    <PrimaryButton class="bg-[#005692] hover:bg-[rgba(0,85,150,0.8)] transition duration-150 ease-in-out">
                        <i class="fas fa-arrow-left mr-2"></i> Retour à
                        l'accueil
                    </PrimaryButton>
                </Link>
            </div>
        </div>

        <!-- Modal pour ajouter un employé -->
        <div v-if="isModalOpen" class="fixed z-50 inset-0 flex items-center justify-center bg-gray-800 bg-opacity-75">
            <div class="flex items-center justify-center min-h-screen px-2 sm:px-4">
                <div class="bg-white rounded-xl shadow-xl p-4 sm:p-8 w-full max-w-lg mx-2 sm:mx-4 z-50">
                    <div class="mb-4 sm:mb-6">
                        <h3 class="text-xl sm:text-2xl font-bold text-gray-800">
                            Ajouter un nouvel employé
                        </h3>

                        <form @submit.prevent="submit">
                            <!-- Nom -->
                            <div class="mt-4">
                                <InputLabel for="name" value="Nom" class="text-gray-700 font-medium" />
                                <TextInput
                                    id="name"
                                    type="text"
                                    class="mt-1 block w-full rounded-md border-gray-300"
                                    v-model="form.name"
                                    required
                                />
                                <InputError
                                    :message="form.errors.name"
                                    class="mt-2"
                                />
                            </div>

                            <!-- E-mail -->
                            <div class="mt-4">
                                <InputLabel for="email" value="E-mail" class="text-gray-700 font-medium" />
                                <TextInput
                                    id="email"
                                    type="email"
                                    class="mt-1 block w-full rounded-md border-gray-300"
                                    v-model="form.email"
                                    required
                                />
                                <InputError
                                    :message="form.errors.email"
                                    class="mt-2"
                                />
                            </div>

                            <!-- Mot de passe -->
                            <div class="mt-4">
                                <InputLabel
                                    for="password"
                                    value="Mot de passe"
                                    class="text-gray-700 font-medium"
                                />
                                <TextInput
                                    id="password"
                                    type="password"
                                    class="mt-1 block w-full rounded-md border-gray-300"
                                    v-model="form.password"
                                    required
                                />
                                <InputError
                                    :message="form.errors.password"
                                    class="mt-2"
                                />
                            </div>

                            <!-- Confirmation mot de passe -->
                            <div class="mt-4">
                                <InputLabel
                                    for="password_confirmation"
                                    value="Confirmer mot de passe"
                                    class="text-gray-700 font-medium"
                                />
                                <TextInput
                                    id="password_confirmation"
                                    type="password"
                                    class="mt-1 block w-full rounded-md border-gray-300"
                                    v-model="form.password_confirmation"
                                    required
                                />
                                <InputError
                                    :message="form.errors.password_confirmation"
                                    class="mt-2"
                                />
                            </div>

                            <!-- Rôle -->
                            <div class="mt-4">
                                <InputLabel for="role" value="Rôle" class="text-gray-700 font-medium" />
                                <select
                                    id="role"
                                    v-model="form.role"
                                    class="mt-1 block w-full pl-3 pr-10 py-2 border border-gray-300 sm:text-sm rounded-md"
                                >
                                    <option value="Employé">Employé</option>
                                    <option value="Ouvrier">Ouvrier</option>
                                    <option value="Admin">Admin</option>
                                    <option value="Informatique">
                                        Informatique
                                    </option>
                                    <option value="Comptabilité">
                                        Comptabilité
                                    </option>
                                </select>
                            </div>

                            <div class="mt-6 flex flex-col sm:flex-row sm:justify-end gap-2 sm:gap-0">
                                <button
                                    @click="closeModal"
                                    type="button"
                                    class="w-full sm:w-auto mb-2 sm:mb-0 sm:mr-4 bg-gray-200 text-gray-700 px-4 py-2 rounded-md hover:bg-gray-300 transition duration-150 ease-in-out"
                                >
                                    Annuler
                                </button>
                                <PrimaryButton
                                    class="w-full sm:w-auto"
                                    :class="{ 'opacity-25': form.processing, 'bg-[#005692] hover:bg-[rgba(0,85,150,0.8)]': !form.processing }"
                                    :disabled="form.processing"
                                    >Ajouter</PrimaryButton
                                >
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<style scoped>
/* Responsiveness */
@media (max-width: 640px) {
    .sm\:w-full {
        width: 100%;
    }
    .sm\:max-w-lg {
        max-width: 100%;
    }
    
    /* Ajustements pour mobile */
    table {
        border-radius: 0.5rem;
    }
    
    th, td {
        padding-left: 0.5rem;
        padding-right: 0.5rem;
    }
}

/* Pour les tablettes */
@media (min-width: 641px) and (max-width: 768px) {
    table {
        font-size: 0.875rem;
    }
}

/* Ajout d'une petite animation de survol sur les lignes de la table */
.transition-colors:hover {
    transition: background-color 0.2s ease;
}

/* Style pour les boutons primaires */
:deep(.primary-button) {
    background-color: #005692;
}
:deep(.primary-button:hover) {
    background-color: rgba(0,85,150,0.8);
}
</style>
