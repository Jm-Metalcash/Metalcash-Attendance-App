<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue'; // Layout d'accueil pour les pages de connexion
import InputError from '@/Components/InputError.vue'; // Composant pour afficher les erreurs
import InputLabel from '@/Components/InputLabel.vue'; // Composant pour le label des inputs
import PrimaryButton from '@/Components/PrimaryButton.vue'; // Bouton principal stylisé
import TextInput from '@/Components/TextInput.vue'; // Composant pour les champs de texte
import { Head, useForm } from '@inertiajs/vue3'; // Head pour le titre de la page et useForm pour le formulaire

// Props : token et email sont passés via Inertia
const props = defineProps({
    email: {
        type: String,
        required: true,
    },
    token: {
        type: String,
        required: true,
    },
});

// Formulaire pour envoyer le mot de passe
const form = useForm({
    token: props.token,  // Jeton de réinitialisation
    email: props.email,  // Email de l'utilisateur récupéré depuis les props
    password: '',
    password_confirmation: '',
});

// Fonction de soumission du formulaire avec la méthode 'put'
const submit = () => {
    form.post(route('password.store'), {  // Utiliser 'post' ici
        onFinish: () => form.reset('password', 'password_confirmation'),
    });
};

</script>

<template>
    <GuestLayout>
        <Head title="Réinitialiser le mot de passe" />
        
        <div class="max-w-md mx-auto mt-10 p-6 bg-white shadow-md rounded-lg">
            <h2 class="text-2xl font-semibold text-center text-gray-800 mb-6">Réinitialiser le mot de passe</h2>
            
            <form @submit.prevent="submit">
                <!-- Champ email -->
                <div class="mb-4">
                    <InputLabel for="email" value="Adresse e-mail" />
                    <TextInput
                        id="email"
                        type="email"
                        class="mt-1 block w-full"
                        v-model="form.email"
                        required
                        autocomplete="email"
                        readonly
                    />
                    <InputError :message="form.errors.email" class="mt-2" />
                </div>

                <!-- Champ mot de passe -->
                <div class="mb-4">
                    <InputLabel for="password" value="Mot de passe" />
                    <TextInput
                        id="password"
                        type="password"
                        class="mt-1 block w-full"
                        v-model="form.password"
                        required
                        autocomplete="new-password"
                    />
                    <InputError :message="form.errors.password" class="mt-2" />
                </div>

                <!-- Champ confirmation de mot de passe -->
                <div class="mb-4">
                    <InputLabel for="password_confirmation" value="Confirmer le mot de passe" />
                    <TextInput
                        id="password_confirmation"
                        type="password"
                        class="mt-1 block w-full"
                        v-model="form.password_confirmation"
                        required
                        autocomplete="new-password"
                    />
                    <InputError :message="form.errors.password_confirmation" class="mt-2" />
                </div>

                <!-- Bouton de soumission -->
                <div class="flex items-center justify-end mt-6">
                    <PrimaryButton :disabled="form.processing" class="w-full">
                        Réinitialiser le mot de passe
                    </PrimaryButton>
                </div>
            </form>
        </div>
    </GuestLayout>
</template>
