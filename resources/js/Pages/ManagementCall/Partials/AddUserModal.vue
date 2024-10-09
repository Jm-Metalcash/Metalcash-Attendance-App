<template>
    <div
        v-if="showModal"
        class="fixed inset-0 bg-gray-600 bg-opacity-80 flex justify-center items-center z-50 px-4 md:px-0"
    >
        <div class="bg-white rounded-lg shadow- p-8 w-full max-w-2xl">
            <!-- Affichage du mois -->
            <div class="w-full text-center mb-6">
                <h3 class="text-lg font-semibold text-gray-700">
                    {{ currentMonth }}
                </h3>
            </div>

            <h2 class="text-2xl font-semibold text-gray-700 mb-6">
                Ajouter un fournisseur
            </h2>

            <div class="grid grid-cols-1 mb-4">
                <label
                    for="noteContent"
                    class="block text-sm font-medium text-gray-700"
                    >Contenu de la note</label
                >
                <textarea
                    v-model="newNote.content"
                    id="noteContent"
                    class="mt-1 block w-full p-2 border-gray-300 rounded-md"
                ></textarea>
            </div>

            <div class="grid grid-cols-2 gap-4 mb-4">
                <div>
                    <label
                        for="prenom"
                        class="block text-sm font-medium text-gray-700"
                        >Prénom</label
                    >
                    <input
                        v-model="newUser.prenom"
                        id="prenom"
                        class="mt-1 block w-full p-2 border-gray-300 rounded-md"
                    />
                </div>
                <div>
                    <label
                        for="nom"
                        class="block text-sm font-medium text-gray-700"
                        >Nom</label
                    >
                    <input
                        v-model="newUser.nom"
                        id="nom"
                        class="mt-1 block w-full p-2 border-gray-300 rounded-md"
                    />
                </div>
                <div>
                    <label
                        for="telephone"
                        class="block text-sm font-medium text-gray-700"
                        >Téléphone</label
                    >
                    <input
                        v-model="newUser.telephone"
                        id="telephone"
                        class="mt-1 block w-full p-2 border-gray-300 rounded-md"
                        required
                    />
                </div>
                <div>
                    <label
                        for="email"
                        class="block text-sm font-medium text-gray-700"
                        >Email</label
                    >
                    <input
                        v-model="newUser.email"
                        id="email"
                        class="mt-1 block w-full p-2 border-gray-300 rounded-md"
                    />
                </div>
            </div>

            <h3 class="text-lg font-semibold text-gray-700 mb-2">Adresse</h3>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label
                        for="rue"
                        class="block text-sm font-medium text-gray-700"
                        >Rue et numéro</label
                    >
                    <input
                        v-model="newUser.adresse.rue"
                        id="rue"
                        class="mt-1 block w-full p-2 border-gray-300 rounded-md"
                    />
                </div>
                <div>
                    <label
                        for="codePostal"
                        class="block text-sm font-medium text-gray-700"
                        >Code postal</label
                    >
                    <input
                        v-model="newUser.adresse.codePostal"
                        id="codePostal"
                        class="mt-1 block w-full p-2 border-gray-300 rounded-md"
                    />
                </div>
                <div>
                    <label
                        for="localite"
                        class="block text-sm font-medium text-gray-700"
                        >Localité</label
                    >
                    <input
                        v-model="newUser.adresse.localite"
                        id="localite"
                        class="mt-1 block w-full p-2 border-gray-300 rounded-md"
                    />
                </div>
                <div>
                    <label
                        for="pays"
                        class="block text-sm font-medium text-gray-700"
                        >Pays</label
                    >
                    <input
                        v-model="newUser.country"
                        id="pays"
                        class="mt-1 block w-full p-2 border-gray-300 rounded-md"
                    />
                </div>
            </div>

            <div class="flex justify-end mt-6">
                <button
                    @click="$emit('toggleModal')"
                    class="bg-gray-400 hover:bg-gray-500 text-white font-bold py-2 px-4 rounded mr-4"
                >
                    Annuler
                </button>
                <button
                    @click="addUserWithNote"
                    class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded"
                >
                    Ajouter
                </button>
            </div>
        </div>
    </div>
</template>

<script setup>
import { reactive, computed } from "vue";

// Props et événements
const props = defineProps({
    showModal: Boolean,
    newUser: Object,
});
const emit = defineEmits(["toggleModal", "addUser"]);

// Réactif pour la nouvelle note
const newNote = reactive({
    content: "", // Contenu de la note que l'utilisateur ajoutera
    date: new Date().toISOString(), // Date et heure actuelles
});

// Calculer le mois courant pour l'affichage
const currentMonth = computed(() => {
    const date = new Date();
    return date.toLocaleString("fr-FR", { month: "long", year: "numeric" });
});

// Fonction pour ajouter un utilisateur avec une note
const addUserWithNote = () => {
    if (!props.newUser.notes) {
        props.newUser.notes = [];
    }

    // Ajouter la nouvelle note si elle n'est pas vide
    if (newNote.content) {
        props.newUser.notes.push({
            content: newNote.content,
            date: new Date().toISOString(),
        });

        newNote.content = "";
    }

    // Émettre l'événement pour ajouter l'utilisateur
    emit("addUser");
};
</script>

<style scoped></style>
