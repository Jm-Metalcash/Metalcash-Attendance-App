<template>
    <div
        v-if="showModal"
        class="fixed inset-0 bg-gray-900 bg-opacity-50 flex justify-center items-center z-50 px-4"
    >
        <div class="bg-white shadow-lg rounded-2xl p-8 w-full max-w-3xl">
            <!-- Header -->
            <h2 class="text-center text-2xl font-semibold text-gray-800 mb-6">
                Ajouter un prospect
            </h2>

            <!-- Message d'erreur général -->
            <div
                v-if="formSubmitted && hasErrors"
                class="mb-4 text-center text-sm text-red-700 bg-red-100 rounded-lg py-2 px-3"
            >
                Veuillez corriger les champs nécessaires avant de poursuivre.
            </div>

            <!-- Formulaire -->
            <form @submit.prevent="addProspectWithNote" class="space-y-4">
                <!-- Champs du formulaire en 2 colonnes -->
                <div class="grid grid-cols-2 gap-4">
                    <!-- Prénom -->
                    <div>
                        <label for="prenom" class="block text-sm text-gray-600">
                            Prénom
                        </label>
                        <input
                            v-model="newProspect.firstName"
                            id="prenom"
                            type="text"
                            class="mt-2 block w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring focus:ring-blue-300 focus:outline-none"
                            @keydown="focusNextField($event)"
                        />
                        <span
                            v-if="errors.firstName"
                            class="text-xs text-red-500"
                        >
                            {{ errors.firstName[0] }}
                        </span>
                    </div>

                    <!-- Nom -->
                    <div>
                        <label for="nom" class="block text-sm text-gray-600">
                            Nom
                        </label>
                        <input
                            v-model="newProspect.familyName"
                            id="nom"
                            type="text"
                            class="mt-2 block w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring focus:ring-blue-300 focus:outline-none"
                            @keydown="focusNextField($event)"
                        />
                        <span
                            v-if="errors.familyName"
                            class="text-xs text-red-500"
                        >
                            {{ errors.familyName[0] }}
                        </span>
                    </div>

                    <!-- Téléphone -->
                    <div>
                        <label
                            for="telephone"
                            class="block text-sm text-gray-600"
                        >
                            Téléphone
                        </label>
                        <input
                            v-model="newProspect.phone"
                            id="telephone"
                            type="text"
                            class="mt-2 block w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring focus:ring-blue-300 focus:outline-none"
                            @blur="validatePhone"
                            @keydown="focusNextField($event)"
                        />
                        <span v-if="errors.phone" class="text-xs text-red-500">
                            {{ errors.phone[0] }}
                        </span>
                    </div>

                    <!-- Email -->
                    <div>
                        <label for="email" class="block text-sm text-gray-600">
                            Email
                        </label>
                        <input
                            v-model="newProspect.email"
                            id="email"
                            type="email"
                            class="mt-2 block w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring focus:ring-blue-300 focus:outline-none"
                            @blur="validateEmail"
                            @keydown="focusNextField($event)"
                        />
                        <span v-if="errors.email" class="text-xs text-red-500">
                            {{ errors.email[0] }}
                        </span>
                    </div>

                    <!-- Pays -->
                    <div>
                        <label
                            for="country"
                            class="block text-sm text-gray-600"
                        >
                            Pays
                        </label>
                        <input
                            v-model="newProspect.country"
                            id="country"
                            type="text"
                            class="mt-2 block w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring focus:ring-blue-300 focus:outline-none"
                            @keydown="focusNextField($event)"
                        />
                    </div>
                </div>

                <!-- Note -->
                <div class="mt-6">
                    <label
                        for="noteContent"
                        class="block text-sm text-gray-600"
                    >
                        Contenu de la note
                    </label>
                    <textarea
                        v-model="newNote.content"
                        id="noteContent"
                        rows="3"
                        class="mt-2 block w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring focus:ring-blue-300 focus:outline-none"
                    ></textarea>
                </div>

                <!-- Boutons -->
                <div class="flex justify-end mt-6 space-x-4">
                    <button
                        type="button"
                        @click="closeModal"
                        class="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-100 focus:ring focus:ring-gray-200"
                    >
                        Annuler
                    </button>
                    <button
                        type="submit"
                        class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 focus:ring focus:ring-blue-300"
                    >
                        Ajouter
                    </button>
                </div>
            </form>
        </div>
    </div>
</template>

<script setup>
import { ref, defineEmits, computed } from "vue";
import axios from "axios";

const props = defineProps({
    showModal: {
        type: Boolean,
        required: true,
    },
    newProspect: {
        type: Object,
        required: true,
    },
});

const emit = defineEmits(["toggleModal", "addProspect"]);

// Réactifs pour le formulaire
const newProspect = ref({
    firstName: "",
    familyName: "",
    phone: "",
    email: "",
    country: "",
});

const newNote = ref({
    content: "",
});

// Erreurs
const errors = ref({});
const hasErrors = computed(() => Object.keys(errors.value).length > 0);

const formSubmitted = ref(false);

// Valider téléphone
const isValidPhone = (phone) => /^\+?[0-9\s-]{7,15}$/.test(phone);
const validatePhone = () => {
    if (!isValidPhone(newProspect.value.phone)) {
        errors.value.phone = ["Le format du numéro de téléphone est invalide."];
    } else {
        delete errors.value.phone;
    }
};

// Valider email
const isValidEmail = (email) => /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
const validateEmail = () => {
    if (newProspect.value.email && !isValidEmail(newProspect.value.email)) {
        errors.value.email = ["Le format de l'adresse e-mail est invalide."];
    } else {
        delete errors.value.email;
    }
};

// Réinitialiser
const resetFields = () => {
    newProspect.value = {
        firstName: "",
        familyName: "",
        phone: "",
        email: "",
        country: "",
    };
    newNote.value.content = "";
    errors.value = {};
    formSubmitted.value = false;
};

// Fermer le modal
const closeModal = () => {
    resetFields();
    emit("toggleModal");
};

// Ajouter un prospect avec une note
const addProspectWithNote = async () => {
    formSubmitted.value = true;
    validatePhone();
    validateEmail();

    if (hasErrors.value) return;

    try {
        const response = await axios.post("/prospects", newProspect.value);
        const prospectId = response.data.id;

        let noteData = null;
        if (newNote.value.content) {
            const noteResponse = await axios.post(
                `/prospects/${prospectId}/notes`,
                {
                    content: newNote.value.content,
                    note_date: new Date().toISOString(),
                    type: "information",
                }
            );
            noteData = noteResponse.data;
        }

        resetFields();
        emit("addProspect", {
            ...response.data,
            notes: noteData ? [noteData] : [],
        });
    } catch (error) {
        if (error.response && error.response.status === 422) {
            errors.value = error.response.data.errors;
        } else {
            console.error("Erreur :", error);
        }
    }
};

// Champ suivant
const focusNextField = (event) => {
    if (event.key === "Enter") {
        event.preventDefault();
        const inputs = document.querySelectorAll("input, textarea, select");
        const currentIndex = Array.from(inputs).indexOf(event.target);

        if (inputs[currentIndex + 1]) {
            inputs[currentIndex + 1].focus();
        }
    }
};
</script>
