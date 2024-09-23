<script setup>
import DangerButton from '@/Components/DangerButton.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import Modal from '@/Components/Modal.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { useForm } from '@inertiajs/vue3';
import { nextTick, ref, defineProps } from 'vue';

// Recevoir l'utilisateur via les props
const props = defineProps({
    user: Object, // L'utilisateur sélectionné
});

const confirmingUserDeletion = ref(false);
const confirmationInput = ref(null);

const form = useForm({
    confirmation: '', // Remplacer "password" par "confirmation"
});

// Confirmer la suppression de l'utilisateur
const confirmUserDeletion = () => {
    confirmingUserDeletion.value = true;
    nextTick(() => confirmationInput.value.focus());
};

// Supprimer l'utilisateur si l'input contient "confirmer" (en ignorant la casse)
const deleteUser = () => {
    form.delete(route('employees.destroy', props.user.id), {
        preserveScroll: true,
        onSuccess: () => closeModal(),
        onFinish: () => form.reset(),
    });
};

// Fermer le modal
const closeModal = () => {
    confirmingUserDeletion.value = false;
    form.reset();
};

// Fonction pour vérifier si l'input contient "confirmer" sans tenir compte de la casse
const isConfirmationValid = () => {
    return form.confirmation.toLowerCase() === 'confirmer';
};
</script>

<template>
    <section class="space-y-6">
        <header>
            <h2 class="text-lg font-medium text-gray-900">Supprimer le compte</h2>

            <p class="mt-1 text-sm text-gray-600">
                Une fois ce compte supprimé, toutes ses ressources et données seront définitivement effacées. Veuillez entrer "confirmer" pour valider la suppression du compte.
            </p>
        </header>

        <DangerButton @click="confirmUserDeletion">Supprimer le compte</DangerButton>

        <!-- Modal de confirmation de suppression -->
        <Modal :show="confirmingUserDeletion" @close="closeModal">
            <div class="p-6">
                <h2 class="text-lg font-medium text-gray-900">
                    Êtes-vous sûr de vouloir supprimer ce compte ?
                </h2>

                <p class="mt-1 text-sm text-gray-600">
                    Une fois le compte supprimé, toutes ses ressources et données seront définitivement supprimées. Veuillez entrer "confirmer" ci-dessous pour confirmer la suppression du compte.
                </p>

                <div class="mt-6">
                    <InputLabel for="confirmation" value="Confirmation" class="sr-only" />

                    <TextInput
                        id="confirmation"
                        ref="confirmationInput"
                        v-model="form.confirmation"
                        type="text"
                        class="mt-1 block w-3/4"
                        placeholder="Tapez 'CONFIRMER' pour supprimer l'utilisateur"
                        @keyup.enter="isConfirmationValid() && deleteUser()"
                    />

                    <InputError :message="form.errors.confirmation" class="mt-2" />
                </div>

                <div class="mt-6 flex justify-end">
                    <SecondaryButton @click="closeModal"> Annuler </SecondaryButton>

                    <DangerButton
                        class="ms-3"
                        :class="{ 'opacity-25': !isConfirmationValid() }"
                        :disabled="!isConfirmationValid()"
                        @click="deleteUser"
                    >
                        Supprimer le compte
                    </DangerButton>
                </div>
            </div>
        </Modal>
    </section>
</template>
