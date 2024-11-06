<template>
    <div v-if="show" class="fixed inset-0 bg-gray-600 bg-opacity-75 flex items-center justify-center z-50">
        <div class="bg-white rounded-lg shadow p-6 max-w-2xl relative">
            <h2 class="text-lg font-medium text-gray-900">
                Êtes-vous sûr de vouloir supprimer ce fournisseur ?
            </h2>
            <p class="mt-1 text-sm text-gray-600">
                Une fois le fournisseur supprimé, toutes ses ressources et données seront définitivement supprimées.
                Veuillez entrer "CONFIRMER" ci-dessous pour valider la suppression.
            </p>

            <div class="mt-6">
                <input
                    id="confirmation"
                    v-model="confirmationInput"
                    type="text"
                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
                    placeholder="Tapez 'CONFIRMER' pour supprimer le fournisseur"
                    @keyup.enter="isConfirmationValid() && deleteUser()"
                />
                <p v-if="confirmationError" class="text-red-500 text-xs mt-2">{{ confirmationError }}</p>
            </div>

            <div class="mt-6 flex justify-end">
                <button
                    @click="closeModal"
                    class="text-gray-700 bg-white border border-gray-300 rounded-md px-4 py-2 mr-2 hover:bg-gray-100"
                >
                    Annuler
                </button>
                <button
                    class="text-white bg-red-700 hover:bg-red-800 px-4 py-2 rounded-md"
                    :class="{ 'opacity-50 cursor-not-allowed': !isConfirmationValid() }"
                    :disabled="!isConfirmationValid()"
                    @click="deleteUser"
                >
                    Supprimer le fournisseur
                </button>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref } from "vue";
import axios from "axios";

const props = defineProps({
    show: Boolean,
    userId: {
        type: Number,
        required: true,
    },
});

const emit = defineEmits(["close", "user-deleted"]);

const confirmationInput = ref("");
const confirmationError = ref("");

// Validation function
const isConfirmationValid = () => confirmationInput.value.toUpperCase() === "CONFIRMER";

const closeModal = () => {
    confirmationInput.value = "";
    confirmationError.value = "";
    emit("close");
};

// Function to delete the user
const deleteUser = () => {
    if (isConfirmationValid()) {
        axios
            .delete(`/clients/${props.userId}`)
            .then(() => {
                emit("user-deleted", props.userId);
                closeModal();
            })
            .catch((error) => {
                console.error("Failed to delete the user:", error);
            });
    } else {
        confirmationError.value = "Veuillez entrer 'CONFIRMER' pour valider.";
    }
};
</script>

<style scoped>
.fixed {
    inset: 0;
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 50;
}

.bg-opacity-75 {
    background-color: rgba(0, 0, 0, 0.75);
}

.max-w-md {
    max-width: 28rem;
}
</style>
