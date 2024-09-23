<script setup>
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { useForm } from '@inertiajs/vue3';
import { ref, defineProps } from 'vue';

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

// Fonction pour simuler l'envoi d'un lien de réinitialisation
const sendResetLink = () => {
    if (form.email === props.user.email) {
        successMessage.value = "Le lien de réinitialisation a été envoyé avec succès à l'adresse " + form.email;
        showSuccessMessage.value = true;
        showErrorMessage.value = false; // Masquer le message d'erreur si le succès est affiché
    } else {
        form.errors.email = "L'adresse e-mail ne correspond pas à l'utilisateur sélectionné.";
        showErrorMessage.value = true;
        showSuccessMessage.value = false; // Masquer le message de succès si une erreur est affichée
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
                <Transition
                    enter-active-class="transition ease-out duration-300"
                    enter-from-class="opacity-0 transform scale-95"
                    enter-to-class="opacity-100 transform scale-100"
                    leave-active-class="transition ease-in duration-300"
                    leave-from-class="opacity-100 transform scale-100"
                    leave-to-class="opacity-0 transform scale-95"
                >
                    <InputError v-if="showErrorMessage" class="mt-2" :message="form.errors.email" />
                </Transition>
            </div>

            <!-- Message de succès -->
            <Transition
                enter-active-class="transition ease-out duration-300"
                enter-from-class="opacity-0 transform scale-95"
                enter-to-class="opacity-100 transform scale-100"
                leave-active-class="transition ease-in duration-300"
                leave-from-class="opacity-100 transform scale-100"
                leave-to-class="opacity-0 transform scale-95"
            >
                <div v-if="showSuccessMessage" class="text-green-600 text-sm mt-2">
                    {{ successMessage }}
                </div>
            </Transition>

            <!-- Bouton pour envoyer le lien de réinitialisation -->
            <div class="flex items-center gap-4">
                <PrimaryButton :disabled="form.processing">Envoyer le lien de réinitialisation</PrimaryButton>
            </div>
        </form>
    </section>
</template>
