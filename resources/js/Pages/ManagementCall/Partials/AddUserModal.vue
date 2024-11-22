<template>
    <div
        v-if="showModal"
        class="fixed inset-0 bg-gray-900 bg-opacity-50 flex justify-center items-center z-50 px-4"
    >
        <div class="bg-white shadow-lg rounded-2xl p-8 w-full max-w-3xl">
            <!-- Header -->
            <h2 class="text-center text-2xl font-semibold text-gray-800 mb-6">
                Ajouter un fournisseur
            </h2>

            <!-- Message d'erreur général -->
            <div
                v-if="formSubmitted && hasErrors"
                class="mb-4 text-center text-sm text-red-700 bg-red-100 rounded-lg py-2 px-3"
            >
                Veuillez corriger les champs nécessaires avant de poursuivre.
            </div>

            <!-- Formulaire -->
            <form @submit.prevent="addUserWithNote" class="space-y-4">
                <!-- Champs du formulaire en 2 colonnes -->
                <div class="grid grid-cols-2 gap-4">
                    <!-- Prénom -->
                    <div>
                        <label for="prenom" class="block text-sm text-gray-600">
                            Prénom
                        </label>
                        <input
                            v-model="newUser.firstName"
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
                            v-model="newUser.familyName"
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
                            v-model="newUser.phone"
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
                            v-model="newUser.email"
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

                    <!-- Adresse -->
                    <div>
                        <label
                            for="address"
                            class="block text-sm text-gray-600"
                        >
                            Rue et numéro
                        </label>
                        <input
                            v-model="newUser.address"
                            id="address"
                            type="text"
                            class="mt-2 block w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring focus:ring-blue-300 focus:outline-none"
                            @keydown="focusNextField($event)"
                        />
                    </div>

                    <!-- Code Postal -->
                    <div>
                        <label
                            for="codePostal"
                            class="block text-sm text-gray-600"
                        >
                            Code Postal
                        </label>
                        <input
                            v-model="newUser.postalCode"
                            id="codePostal"
                            type="text"
                            class="mt-2 block w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring focus:ring-blue-300 focus:outline-none"
                            @keydown="focusNextField($event)"
                        />
                    </div>

                    <!-- Localité -->
                    <div>
                        <label
                            for="locality"
                            class="block text-sm text-gray-600"
                        >
                            Localité
                        </label>
                        <input
                            v-model="newUser.locality"
                            id="locality"
                            type="text"
                            class="mt-2 block w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring focus:ring-blue-300 focus:outline-none"
                            @keydown="focusNextField($event)"
                        />
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
                            v-model="newUser.country"
                            id="country"
                            type="text"
                            class="mt-2 block w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring focus:ring-blue-300 focus:outline-none"
                            @keydown="focusNextField($event)"
                        />
                    </div>

                    <!-- Société -->
                    <div>
                        <label
                            for="company"
                            class="block text-sm text-gray-600"
                        >
                            Société
                        </label>
                        <input
                            v-model="newUser.company"
                            id="company"
                            type="text"
                            class="mt-2 block w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring focus:ring-blue-300 focus:outline-none"
                            @keydown="focusNextField($event)"
                        />
                    </div>

                    <!-- Numéro TVA -->
                    <div>
                        <label
                            for="companyvat"
                            class="block text-sm text-gray-600"
                        >
                            Numéro TVA
                        </label>
                        <input
                            v-model="newUser.companyvat"
                            id="companyvat"
                            type="text"
                            class="mt-2 block w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring focus:ring-blue-300 focus:outline-none"
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
    newUser: {
        type: Object,
        required: true,
    },
});

const emit = defineEmits(["toggleModal", "addUser"]);

// Réactifs pour le formulaire
const newUser = ref({
    firstName: "",
    familyName: "",
    phone: "",
    email: "",
    company: "",
    companyvat: "",
    address: "",
    postalCode: "",
    locality: "",
    country: "",
});

// Réactif pour la note
const newNote = ref({
    content: "",
    note_date: new Date().toISOString(), // Initialisation de la date ici
});

// Vérifie si des erreurs existent
const errors = ref({});
const hasErrors = computed(() => Object.keys(errors.value).length > 0);

const formSubmitted = ref(false); // Variable pour suivre l'état de soumission

//fonction pour vérifier le format du numéro de téléphone
const isValidPhone = (phone) => {
    const phoneRegex = /^\+?[0-9\s-]{7,15}$/; // Accepte les numéros de 7 à 15 chiffres, avec espaces, tirets ou le préfixe "+"
    return phoneRegex.test(phone);
};

// Fonction pour vérifier le format de l'adresse e-mail
const isValidEmail = (email) => {
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return emailRegex.test(email);
};

//Affichage du message d'erreur pour le numéro de téléphone
const validatePhone = () => {
    if (!isValidPhone(newUser.value.phone)) {
        errors.value.phone = ["Le format du numéro de téléphone est invalide."];
    } else {
        delete errors.value.phone; // Supprime l'erreur si le format est valide
    }
};

// Affichage du message d'erreur pour l'e-mail
const validateEmail = () => {
    if (!isValidEmail(newUser.value.email)) {
        errors.value.email = ["Le format de l'adresse e-mail est invalide."];
    } else {
        delete errors.value.email;
    }
};


// Fonction pour réinitialiser les champs
const resetFields = () => {
    newUser.value = {
        firstName: "",
        familyName: "",
        phone: "",
        email: "",
        company: "",
        companyvat: "",
        address: "",
        postalCode: "",
        locality: "",
        country: "",
    };
    newNote.value.content = "";
    errors.value = {};
    formSubmitted.value = false;
};

// Fonction pour fermer le modal
const closeModal = () => {
    resetFields(); // Réinitialiser les champs
    emit("toggleModal"); 
};

// Fonction pour ajouter un client avec une note
const addUserWithNote = async () => {
    formSubmitted.value = true; // Activer l'état de soumission

    // Valider les champs
    validatePhone();
    validateEmail();

    if (hasErrors.value) {
        return; // Arrêter si des erreurs sont présentes
    }

    try {
        const response = await axios.post("/clients", newUser.value);
        const clientId = response.data.id;

        let noteData = null;
        if (newNote.value.content) {
            const noteResponse = await axios.post(
                `/clients/${clientId}/notes`,
                {
                    content: newNote.value.content,
                    note_date: new Date().toISOString(),
                    type: "information",
                }
            );
            noteData = noteResponse.data;
        }

        newUser.value = {
            /* Reset fields */
        };
        newNote.value.content = "";
        errors.value = {};
        formSubmitted.value = false;

        emit("addUser", {
            ...response.data,
            notes: noteData ? [noteData] : [],
        });
    } catch (error) {
        if (error.response && error.response.status === 422) {
            errors.value = error.response.data.errors;
        } else {
            console.error("Error:", error);
        }
    }
};

// Fonction pour gérer le passage au champ suivant
const focusNextField = (event) => {
    if (event.key === "Enter") {
        event.preventDefault();

        const inputs = document.querySelectorAll("input, textarea, select"); // Récupère tous les champs du formulaire
        const currentIndex = Array.from(inputs).indexOf(event.target); // Trouve l'index de l'élément actuel

        if (inputs[currentIndex + 1]) {
            inputs[currentIndex + 1].focus(); // Met le focus sur l'élément suivant
        }
    }
};
</script>
