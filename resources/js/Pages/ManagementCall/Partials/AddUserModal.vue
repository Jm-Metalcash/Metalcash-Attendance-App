<template>
    <div
        v-if="showModal"
        class="fixed inset-0 bg-gray-600 bg-opacity-80 flex justify-center items-center z-50 px-4 md:px-0"
    >
        <div class="bg-white rounded-lg shadow- p-8 w-full max-w-2xl">
            <h2 class="text-2xl font-semibold text-gray-700 mb-6">
                Ajouter un fournisseur
            </h2>

            <!-- Formulaire de création de client -->
            <div class="grid grid-cols-2 gap-4 mb-4">
                <div>
                    <label for="prenom" class="block text-sm font-medium text-gray-700">Prénom</label>
                    <input v-model="newUser.firstName" id="prenom" class="mt-1 block w-full p-2 border-gray-300 rounded-md" />
                    <span v-if="errors.firstName" class="text-red-500 text-xs mt-1">{{ errors.firstName[0] }}</span>
                </div>
                <div>
                    <label for="nom" class="block text-sm font-medium text-gray-700">Nom</label>
                    <input v-model="newUser.familyName" id="nom" class="mt-1 block w-full p-2 border-gray-300 rounded-md" />
                    <span v-if="errors.familyName" class="text-red-500 text-xs mt-1">{{ errors.familyName[0] }}</span>
                </div>
                <div>
                    <label for="telephone" class="block text-sm font-medium text-gray-700">Téléphone</label>
                    <input v-model="newUser.phone" id="telephone" class="mt-1 block w-full p-2 border-gray-300 rounded-md" />
                    <span v-if="errors.phone" class="text-red-500 text-xs mt-1">{{ errors.phone[0] }}</span>
                </div>
                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
                    <input v-model="newUser.email" id="email" class="mt-1 block w-full p-2 border-gray-300 rounded-md" />
                    <span v-if="errors.email" class="text-red-500 text-xs mt-1">{{ errors.email[0] }}</span>
                </div>
                <div>
                    <label for="company" class="block text-sm font-medium text-gray-700">Société</label>
                    <input v-model="newUser.company" id="company" class="mt-1 block w-full p-2 border-gray-300 rounded-md" />
                </div>
                <div>
                    <label for="companyvat" class="block text-sm font-medium text-gray-700">Numéro TVA</label>
                    <input v-model="newUser.companyvat" id="companyvat" class="mt-1 block w-full p-2 border-gray-300 rounded-md" />
                </div>
            </div>

            <h3 class="text-lg font-semibold text-gray-700 mb-2">Adresse</h3>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label for="rue" class="block text-sm font-medium text-gray-700">Rue et numéro</label>
                    <input v-model="newUser.address" id="rue" class="mt-1 block w-full p-2 border-gray-300 rounded-md" />
                    <span v-if="errors.address" class="text-red-500 text-xs mt-1">{{ errors.address[0] }}</span>
                </div>
                <div>
                    <label for="codePostal" class="block text-sm font-medium text-gray-700">Code postal</label>
                    <input v-model="newUser.postalCode" id="codePostal" class="mt-1 block w-full p-2 border-gray-300 rounded-md" />
                    <span v-if="errors.postalCode" class="text-red-500 text-xs mt-1">{{ errors.postalCode[0] }}</span>
                </div>
                <div>
                    <label for="localite" class="block text-sm font-medium text-gray-700">Localité</label>
                    <input v-model="newUser.locality" id="localite" class="mt-1 block w-full p-2 border-gray-300 rounded-md" />
                    <span v-if="errors.locality" class="text-red-500 text-xs mt-1">{{ errors.locality[0] }}</span>
                </div>
                <div>
                    <label for="pays" class="block text-sm font-medium text-gray-700">Pays</label>
                    <input v-model="newUser.country" id="pays" class="mt-1 block w-full p-2 border-gray-300 rounded-md" />
                    <span v-if="errors.country" class="text-red-500 text-xs mt-1">{{ errors.country[0] }}</span>
                </div>
            </div>

            <!-- Note associée -->
            <div class="mt-4">
                <label for="noteContent" class="block text-sm font-medium text-gray-700">Contenu de la note</label>
                <textarea v-model="newNote.content" id="noteContent" class="mt-1 block w-full p-2 border-gray-300 rounded-md"></textarea>
            </div>

            <div class="flex justify-end mt-6">
                <button @click="emit('toggleModal')" class="bg-gray-400 hover:bg-gray-500 text-white font-bold py-2 px-4 rounded mr-4">Annuler</button>
                <button @click="addUserWithNote" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">Ajouter</button>
            </div>
        </div>
    </div>
</template>



<script setup>
import { ref, defineEmits } from "vue";
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

const errors = ref({});

// Fonction pour ajouter un client avec une note
const addUserWithNote = async () => {
    try {
        // Ajouter le client
        const response = await axios.post("/clients", newUser.value);
        const clientId = response.data.id;

        let noteData = null;

        // Ajouter la note associée au client s'il y a un contenu
        if (newNote.value.content) {
            const noteResponse = await axios.post(`/clients/${clientId}/notes`, {
                content: newNote.value.content,
                note_date: new Date().toISOString(), // Utilisation d'une date ISO
            });

            // Assigner les données de la note, y compris la date formatée
            noteData = noteResponse.data;
        }

        // Réinitialiser les champs
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

        // Émettre l'événement 'addUser' avec le client ajouté et les notes
        emit("addUser", { ...response.data, notes: noteData ? [noteData] : [] });

    } catch (error) {
        if (error.response && error.response.status === 422) {
            errors.value = error.response.data.errors;
        } else {
            console.error("Error while adding user with note:", error);
        }
    }
};

</script>
