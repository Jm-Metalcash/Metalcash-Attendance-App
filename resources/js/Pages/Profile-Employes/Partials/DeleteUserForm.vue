<script setup>
import DangerButton from "@/Components/DangerButton.vue";
import InputError from "@/Components/InputError.vue";
import InputLabel from "@/Components/InputLabel.vue";
import Modal from "@/Components/Modal.vue";
import SecondaryButton from "@/Components/SecondaryButton.vue";
import TextInput from "@/Components/TextInput.vue";
import { useForm } from "@inertiajs/vue3";
import { nextTick, ref, defineProps } from "vue";
import FlashMessage from "@/Components/FlashMessage.vue";
import { router } from "@inertiajs/vue3";

// Recevoir l'utilisateur via les props
const props = defineProps({
    user: Object, // L'utilisateur sélectionné
});

const confirmingUserDeactivation = ref(false);
const confirmationInput = ref(null);
const flashMessage = ref(""); // Message flash

const form = useForm({
    confirmation: "", // Texte de confirmation
});

// Confirmer la désactivation de l'utilisateur
const confirmUserDeactivation = () => {
    confirmingUserDeactivation.value = true;
    nextTick(() => confirmationInput.value.focus());
};

// Désactiver l'utilisateur si l'input contient "confirmer" (en ignorant la casse)
const deactivateUser = () => {
    form.put(route("employees.deactivate", props.user.id), {
        preserveScroll: true,
        onSuccess: (response) => {
            closeModal();
            if (response.props.flash && response.props.flash.success) {
                flashMessage.value = response.props.flash.success;
            }
            window.location.reload();
        },
        onFinish: () => form.reset(),
    });
};

// Fermer le modal
const closeModal = () => {
    confirmingUserDeactivation.value = false;
    form.reset();
};

// Fonction pour vérifier si l'input contient "confirmer" sans tenir compte de la casse
const isConfirmationValid = () => {
    return form.confirmation.toLowerCase() === "confirmer";
};
</script>

<template>
    <section class="space-y-6">
        <header>
            <h2 class="text-lg font-medium text-gray-900">
                Désactiver le compte
            </h2>

            <p class="mt-1 text-sm text-gray-600">
                Désactiver ce compte empêchera l'utilisateur de se connecter ou
                d'accéder à ses ressources. Veuillez entrer "confirmer" pour
                valider cette action.
            </p>
        </header>

        <DangerButton @click="confirmUserDeactivation"
            >Désactiver le compte</DangerButton
        >

        <!-- Modal de confirmation de désactivation -->
        <Modal :show="confirmingUserDeactivation" @close="closeModal">
            <div class="p-6">
                <h2 class="text-lg font-medium text-gray-900">
                    Êtes-vous sûr de vouloir désactiver ce compte ?
                </h2>

                <p class="mt-1 text-sm text-gray-600">
                    Une fois le compte désactivé, l'utilisateur ne pourra plus
                    se connecter ni accéder à ses ressources. Veuillez entrer
                    "confirmer" ci-dessous pour confirmer la désactivation.
                </p>

                <div class="mt-6">
                    <InputLabel
                        for="confirmation"
                        value="Confirmation"
                        class="sr-only"
                    />

                    <TextInput
                        id="confirmation"
                        ref="confirmationInput"
                        v-model="form.confirmation"
                        type="text"
                        class="mt-1 block w-3/4"
                        placeholder="Tapez 'CONFIRMER' pour désactiver l'utilisateur"
                        @keyup.enter="isConfirmationValid() && deactivateUser()"
                    />

                    <InputError
                        :message="form.errors.confirmation"
                        class="mt-2"
                    />
                </div>

                <div class="mt-6 flex justify-end">
                    <SecondaryButton @click="closeModal">
                        Annuler
                    </SecondaryButton>

                    <DangerButton
                        class="ms-3"
                        :class="{ 'opacity-25': !isConfirmationValid() }"
                        :disabled="!isConfirmationValid()"
                        @click="deactivateUser"
                    >
                        Désactiver le compte
                    </DangerButton>
                </div>
            </div>
        </Modal>

        <!-- Message flash -->
        <FlashMessage
            v-if="flashMessage"
            :message="flashMessage"
            @close="flashMessage = ''"
        />
    </section>
</template>
