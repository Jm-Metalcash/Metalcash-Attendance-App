<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import InputError from "@/Components/InputError.vue";
import InputLabel from "@/Components/InputLabel.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import TextInput from "@/Components/TextInput.vue";
import { Head, useForm } from "@inertiajs/vue3";
import { ref, defineProps } from "vue";

// Props pour recevoir les utilisateurs
const props = defineProps({
    users: Array,
});

// Contrôle du modal
const isModalOpen = ref(false);

// Formulaire pour ajouter un employé
const form = useForm({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
    role: 'Employé'
});

// Fonction pour soumettre le formulaire
const submit = () => {
    form.post(route('employees.store'), {
        onFinish: () => {
            // Si le formulaire n'a pas d'erreurs, on ferme le modal
            if (Object.keys(form.errors).length === 0) {
                form.reset('password', 'password_confirmation');
                closeModal();
            }
        }
    });
};

// Ouvrir et fermer le modal
const openModal = () => isModalOpen.value = true;
const closeModal = () => isModalOpen.value = false;
</script>

<template>
    <Head title="Liste des employés" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-white bg-gray-800 leading-tight">
                Gestion des employés
            </h2>
        </template>

        <div class="container mx-auto px-4 sm:px-8 max-w-7xl">
            <div class="py-8">
                <!-- Titre de la page -->
                <div>
                    <h2 class="text-2xl font-semibold leading-tight text-gray-800">
                        Liste des employés
                    </h2>
                </div>

                <!-- Bouton pour ouvrir le modal -->
                <PrimaryButton class="mt-8 mb-0" @click="openModal">Ajouter un employé</PrimaryButton>

                <!-- Table responsive -->
                <div class="-mx-4 sm:-mx-8 px-4 sm:px-8 py-4 overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead>
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Nom
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    E-mail
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Rôle
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Statut
                                </th>
                                <th class="text-center px-6 py-3 text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Action
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <tr v-for="user in users" :key="user.id">
                                <td class="px-6 py-4 whitespace-nowrap">{{ user.name }}</td>
                                <td class="px-6 py-4 whitespace-nowrap">{{ user.email }}</td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span v-for="role in user.roles" :key="role.id">
                                        {{ role.name }}<span v-if="user.roles.length > 1 && role !== user.roles[user.roles.length - 1]">, </span>
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                        Actif
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-center">
                                    <button class="px-4 py-2 text-sm text-white bg-[rgb(0,85,150)] rounded-md hover:bg-[rgba(0,85,150,0.8)] transition duration-150 ease-in-out">
                                        <i class="fa-regular fa-eye"></i> Voir profil
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Modal pour ajouter un employé -->
        <div v-if="isModalOpen" class="fixed z-10 inset-0 overflow-y-auto">
            <div class="flex items-center justify-center min-h-screen px-4">
                <div class="fixed inset-0 bg-gray-800 opacity-75"></div>

                <div class="bg-white rounded-lg overflow-hidden shadow-xl transform transition-all sm:w-full sm:max-w-lg z-50">
                    <div class="p-6">
                        <h3 class="text-lg leading-6 font-medium text-gray-900">Ajouter un nouvel employé</h3>
                        
                        <form @submit.prevent="submit">
                            <!-- Nom -->
                            <div class="mt-4">
                                <InputLabel for="name" value="Nom" />
                                <TextInput id="name" type="text" class="mt-1 block w-full" v-model="form.name" required />
                                <InputError :message="form.errors.name" class="mt-2" />
                            </div>

                            <!-- E-mail -->
                            <div class="mt-4">
                                <InputLabel for="email" value="E-mail" />
                                <TextInput id="email" type="email" class="mt-1 block w-full" v-model="form.email" required />
                                <InputError :message="form.errors.email" class="mt-2" />
                            </div>

                            <!-- Mot de passe -->
                            <div class="mt-4">
                                <InputLabel for="password" value="Mot de passe" />
                                <TextInput id="password" type="password" class="mt-1 block w-full" v-model="form.password" required />
                                <InputError :message="form.errors.password" class="mt-2" />
                            </div>

                            <!-- Confirmation mot de passe -->
                            <div class="mt-4">
                                <InputLabel for="password_confirmation" value="Confirmer mot de passe" />
                                <TextInput id="password_confirmation" type="password" class="mt-1 block w-full" v-model="form.password_confirmation" required />
                                <InputError :message="form.errors.password_confirmation" class="mt-2" />
                            </div>

                            <!-- Rôle -->
                            <div class="mt-4">
                                <InputLabel for="role" value="Rôle" />
                                <select id="role" v-model="form.role" class="mt-1 block w-full pl-3 pr-10 py-2 border border-gray-300 sm:text-sm rounded-md">
                                    <option value="Employé">Employé</option>
                                    <option value="Admin">Admin</option>
                                </select>
                            </div>

                            <!-- Actions -->
                            <div class="mt-6 flex justify-end">
                                <button @click="closeModal" class="mr-4 bg-gray-200 text-gray-700 px-4 py-2 rounded-md">Annuler</button>
                                <PrimaryButton :class="{ 'opacity-25': form.processing }" :disabled="form.processing">Ajouter</PrimaryButton>
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
}
</style>
