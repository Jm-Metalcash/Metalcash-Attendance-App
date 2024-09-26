<script setup>
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { useForm } from '@inertiajs/vue3';
import { ref, defineProps } from 'vue';
import axios from 'axios';

// Recevoir l'utilisateur via les props
const props = defineProps({
    user: Object, // L'employé sélectionné
});

// Formulaire de réinitialisation de mot de passe
const form = useForm({
    email: props.user.email, // Utiliser l'email de l'utilisateur
});

// Messages de succès et d'erreur
const successMessage = ref('');
const showSuccessMessage = ref(false);
const showErrorMessage = ref(false);

// Fonction pour envoyer l'e-mail de réinitialisation
const sendResetLink = async () => {
    try {
        const response = await axios.post('/send-reset-link', {
            email: form.email,
        });
        successMessage.value = response.data.success;
        showSuccessMessage.value = true;
        showErrorMessage.value = false; // Masquer l'erreur s'il y a succès
    } catch (error) {
        form.errors.email = error.response.data.error;
        showErrorMessage.value = true;
        showSuccessMessage.value = false; // Masquer le succès s'il y a une erreur
    }
};
</script>

<template>
    <section>
        <header>
            <h2 class="text-lg font-medium text-gray-900">Réinitialiser le mot de passe</h2>

            <p class="mt-1 text-sm text-gray-600">
                Envoyez un lien de réinitialisation de mot de passe à l'utilisateur.
            </p>
        </header>

        <form @submit.prevent="sendResetLink" class="mt-6 space-y-6">
            <!-- Email -->
            <div>
                <InputLabel for="email" value="E-mail de l'utilisateur" />

                <TextInput
                    id="email"
                    type="email"
                    class="mt-1 block w-full"
                    v-model="form.email"
                />

                <!-- Message d'erreur -->
                <InputError v-if="showErrorMessage" class="mt-2" :message="form.errors.email" />
            </div>

            <!-- Message de succès -->
            <div v-if="showSuccessMessage" class="text-green-600 text-sm mt-2">
                {{ successMessage }}
            </div>

            <!-- Bouton pour envoyer le lien de réinitialisation -->
            <div class="flex items-center gap-4">
                <PrimaryButton :disabled="form.processing">Envoyer le lien de réinitialisation</PrimaryButton>
            </div>
        </form>
    </section>
</template>
