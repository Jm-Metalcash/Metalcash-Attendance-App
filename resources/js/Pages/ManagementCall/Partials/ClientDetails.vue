<template>
    <div class="pt-4 pb-6">
        <!-- Composant FlashMessage -->
        <FlashMessage 
            v-if="showFlashMessage" 
            :message="flashMessage" 
            @close="closeFlashMessage" 
        />
        
        <!-- Section Notes -->
        <div class="mb-8 bg-white rounded-xl shadow-sm overflow-hidden">
            <!-- En-tête avec style différent de la fiche client -->
            <div class="bg-gray-100 border-b border-gray-200 px-4 sm:px-6 py-3 flex justify-between items-center">
                <div class="flex items-center">
                    <div class="flex items-center text-blue-600 mr-2">
                        <i class="fas fa-sticky-note"></i>
                    </div>
                    <div>
                        <h3 class="text-sm sm:text-base font-medium text-gray-800">
                            Notes relatives à {{ editableClient.firstName }}
                            {{ editableClient.familyName }}
                        </h3>
                    </div>
                    <span class="ml-3 text-xs bg-blue-600 text-white px-2 py-1 rounded-full font-medium">
                        {{ editableClient.notes ? editableClient.notes.length : 0 }}
                    </span>
                </div>
                <!-- Bouton Ajouter pour desktop -->
                <PrimaryButton v-if="!showAddNote" @click="showAddNote = true" class="hidden sm:inline-flex">
                    <i class="fa-solid fa-plus mr-2"></i>
                    Ajouter
                </PrimaryButton>
                <!-- Bouton Ajouter pour mobile -->
                <button v-if="!showAddNote" @click="showAddNote = true" 
                    class="sm:hidden flex items-center justify-center bg-gray-900 text-white rounded-full w-8 h-8 shadow-md hover:bg-gray-700 transition-colors duration-200">
                    <i class="fa-solid fa-plus"></i>
                </button>
            </div>

            <!-- Section status des notes - intégrée dans la section notes sans bordures -->
            <div v-if="
                editableClient.notes && editableClient.notes.some((note) =>
                    ['avertissement', 'premium', 'attention', 'a_contacter'].includes(
                        note.type
                    )
                )
            " :class="[
                'warning-blacklist px-4 py-3',
                latestWarningType === 'avertissement' ? 'bg-orange-50' :
                latestWarningType === 'premium' ? 'bg-green-50' :
                latestWarningType === 'attention' ? 'bg-red-50' :
                latestWarningType === 'a_contacter' ? 'bg-purple-50' :
                'bg-blue-50'
            ]">
                <div :class="getWarningClass(latestWarningType)" class="px-4 py-2 relative rounded-md" role="alert">
                    <span class="font-bold text-sm">Note :</span>
                    <span class="block sm:inline text-sm md:ml-1">
                        {{ getWarningText(latestWarningType) }}
                    </span>
                </div>
            </div>

            <!-- Zone d'ajout de nouvelle note - intégrée dans la section notes -->
            <transition
                enter-active-class="transition ease-out duration-300"
                enter-from-class="transform opacity-0 scale-95"
                enter-to-class="transform opacity-100 scale-100"
                leave-active-class="transition ease-in duration-200"
                leave-from-class="transform opacity-100 scale-100"
                leave-to-class="transform opacity-0 scale-95"
            >
                <div v-if="showAddNote" class="px-4 py-3 bg-gray-50">
                    <div class="flex justify-between items-center mb-2">
                        <h4 class="text-gray-700 text-sm font-semibold">
                            Nouvelle note
                        </h4>
                        <button
                            class="inline-flex items-center px-3 py-1 bg-gray-200 border border-transparent rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-300 focus:outline-none transition ease-in-out duration-150"
                            @click="showAddNote = false">
                            <i class="fa-solid fa-minus mr-1"></i>
                            Réduire
                        </button>
                    </div>
                    <select v-model="newNote.type" class="block w-full px-3 py-2 border border-gray-300 rounded-md mb-2">
                        <option value="information">Note informative</option>
                        <option value="premium">
                            Note pour client premium
                        </option>
                        <option value="avertissement">
                            Note d'avertissement
                        </option>
                        <option value="attention">
                            Note pour client à éviter
                        </option>
                        <option value="a_contacter">Ajouter aux demandes de rappels</option>
                    </select>
                    <textarea v-model="newNote.content"
                        class="block w-full px-3 py-2 border border-gray-300 rounded-md text-sm focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500"
                        placeholder="Saisir la note..."></textarea>
                    <div class="mt-2 mb-12">
                        <PrimaryButton @click="saveNewNote">
                            Enregistrer la note
                        </PrimaryButton>
                    </div>
                </div>
            </transition>

            <div v-if="editableClient.notes && editableClient.notes.length > 0">
                <!-- Vue mobile et tablette : cartes empilées -->
                <div class="lg:hidden p-4 space-y-4 bg-gray-50">
                    <div
                        v-for="note in reversedNotes.slice(0, visibleNotesCount)"
                        :key="note.id"
                        class="relative rounded-lg overflow-hidden shadow-sm transition-all duration-200 hover:shadow-md"
                    >
                        <!-- Indicateur type note -->
                        <div :class="[
                            'h-1.5 w-full',
                            note.type === 'avertissement' ? 'bg-orange-500' :
                            note.type === 'premium' ? 'bg-green-500' :
                            note.type === 'attention' ? 'bg-red-500' :
                            note.type === 'a_contacter' ? 'bg-purple-500' :
                            'bg-blue-500'
                        ]"></div>
                        
                        <div class="p-4 bg-white">
                            <div class="flex justify-between items-start mb-3">
                                <div>
                                    <span class="font-medium text-sm text-gray-700">
                                        {{ formatDateTime(note.note_date) }}
                                    </span>
                                    <div class="mt-1">
                                        <span :class="[
                                            'text-xs px-2 py-0.5 rounded-full font-medium',
                                            note.type === 'avertissement' ? 'bg-orange-100 text-orange-700' :
                                            note.type === 'premium' ? 'bg-green-100 text-green-700' :
                                            note.type === 'attention' ? 'bg-red-100 text-red-700' :
                                            note.type === 'a_contacter' ? 'bg-purple-100 text-purple-700' :
                                            'bg-blue-100 text-blue-700'
                                        ]">
                                            {{ note.type === 'avertissement' ? 'Avertissement' :
                                               note.type === 'premium' ? 'Premium' :
                                               note.type === 'attention' ? 'À éviter' :
                                               note.type === 'a_contacter' ? 'À contacter' :
                                               'Information' }}
                                        </span>
                                    </div>
                                </div>
                                <div v-if="isNoteEditable(note.note_date)">
                                    <button @click="deleteNote(note.id)"
                                        class="text-xs bg-red-100 text-red-700 hover:text-red-800 hover:bg-red-200 px-3 py-1 rounded-md font-semibold transition duration-200">
                                        Supprimer
                                    </button>
                                </div>
                            </div>
                            
                            <div class="mb-3 mt-4 border-t border-gray-100 pt-3">
                                <div v-if="!isEditingNotes[note.id]" @click="editNote(note.id)"
                                    class="editable-text cursor-pointer hover:text-gray-600 text-sm"
                                    style="white-space: pre-wrap;">
                                    {{ note.content }}
                                </div>
                                <div v-else>
                                    <textarea v-model="note.content" @blur="saveNote(note)"
                                        class="editable-input mt-1 block w-full p-2 border-gray-300 rounded-md"></textarea>
                                    <PrimaryButton class="mt-2" @click="saveNote(note)">
                                        Enregistrer la note
                                    </PrimaryButton>
                                </div>
                            </div>
                            
                            <div class="text-right text-xs text-gray-500 border-t border-gray-100 pt-2 mt-2">
                                Ajouté par <span class="font-bold">{{ note.creator?.name || "" }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Vue desktop : tableau -->
                <div class="hidden lg:block">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-48">
                                        Date
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-28">
                                        Type
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Note
                                    </th>
                                    <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider w-40">
                                        Ajouté par
                                    </th>
                                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider w-32">
                                        Actions
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-100">
                                <tr 
                                    v-for="note in reversedNotes.slice(0, visibleNotesCount)" 
                                    :key="note.id" 
                                    class="hover:bg-gray-50 transition-colors"
                                >
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="font-medium text-sm">
                                            {{ formatDateTime(note.note_date) }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span :class="[
                                            'text-xs px-2 py-0.5 rounded-full font-medium',
                                            note.type === 'avertissement' ? 'bg-orange-100 text-orange-700' :
                                            note.type === 'premium' ? 'bg-green-100 text-green-700' :
                                            note.type === 'attention' ? 'bg-red-100 text-red-700' :
                                            note.type === 'a_contacter' ? 'bg-purple-100 text-purple-700' :
                                            'bg-blue-100 text-blue-700'
                                        ]">
                                            {{ note.type === 'avertissement' ? 'Avertissement' :
                                               note.type === 'premium' ? 'Premium' :
                                               note.type === 'attention' ? 'À éviter' :
                                               note.type === 'a_contacter' ? 'À contacter' :
                                               'Information' }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div v-if="!isEditingNotes[note.id]" @click="editNote(note.id)"
                                            class="editable-text cursor-pointer hover:text-gray-600 text-sm"
                                            style="white-space: pre-wrap;">
                                            {{ note.content }}
                                        </div>
                                        <div v-else>
                                            <textarea v-model="note.content" @blur="saveNote(note)"
                                                class="editable-input mt-1 block w-full p-2 border-gray-300 rounded-md"></textarea>
                                            <PrimaryButton class="mt-2" @click="saveNote(note)">
                                                Enregistrer la note
                                            </PrimaryButton>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-center text-sm">
                                        {{ note.creator?.name || "" }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right">
                                        <button v-if="isNoteEditable(note.note_date)" @click="deleteNote(note.id)"
                                            class="text-xs bg-red-100 text-red-700 hover:text-red-800 hover:bg-red-200 px-3 py-1 rounded-md font-semibold transition duration-200">
                                            Supprimer
                                        </button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="text-center py-4">
                    <button v-if="visibleNotesCount < (editableClient.notes ? editableClient.notes.length : 0)" @click="showMoreNotes"
                        class="text-sm text-blue-600 hover:text-blue-800 font-medium transition-colors duration-200">
                        Afficher plus de notes
                    </button>
                    <button v-if="visibleNotesCount > 5" @click="showLessNotes"
                        class="text-sm text-blue-600 hover:text-blue-800 font-medium ml-4 transition-colors duration-200">
                        Afficher moins de notes
                    </button>
                </div>
            </div>
            <div v-else class="p-8 text-center text-sm text-gray-500">
                Aucune note disponible pour ce client.
            </div>
        </div>

        <!-- Section Informations Générales -->
        <div class="mb-8 bg-white rounded-xl shadow-sm overflow-hidden">
            <!-- En-tête avec dégradé bleu comme dans Historical.vue -->
            <div class="bg-gradient-to-r from-[#005692] to-[#0078c9] px-4 sm:px-6 py-4 text-white">
                <h3 class="text-sm sm:text-base font-medium flex items-center">
                    <i class="fas fa-user mr-2"></i> Informations générales
                </h3>
            </div>

            <div class="p-6">
                <div class="sm:divide-y sm:divide-gray-200">
                    <!-- Première ligne : Prénom & Nom & Téléphone -->
                    <div class="py-3 sm:py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                        <div class="sm:col-span-1 px-4 md:px-0">
                            <dt class="text-sm font-bold text-gray-500">Prénom</dt>
                            <dd class="mt-1 text-sm text-gray-400 sm:mt-0">
                                <span v-if="!isEditing.firstName" @click="editField('firstName')" :class="{
                                        'cursor-pointer hover:text-blue-500 hover:underline': true,
                                    }">
                                    {{ editableClient.firstName || "Non renseigné" }}
                                </span>
                                <div v-else>
                                    <input v-model="editableClient.firstName" @blur="saveField('firstName')"
                                        @keydown.enter="saveField('firstName')"
                                        class="editable-input mt-1 block w-full p-2 border-gray-300 rounded-md" />
                                </div>
                            </dd>
                        </div>
                        <div class="sm:col-span-1 px-4 md:px-0 mt-3 md:mt-0">
                            <dt class="text-sm font-bold text-gray-500">Nom</dt>
                            <dd class="mt-1 text-sm text-gray-400 sm:mt-0">
                                <span v-if="!isEditing.familyName" @click="editField('familyName')" :class="{
                                        'cursor-pointer hover:text-blue-500 hover:underline': true,
                                    }">
                                    {{ editableClient.familyName || "Non renseigné" }}
                                </span>
                                <div v-else>
                                    <input v-model="editableClient.familyName" @blur="saveField('familyName')"
                                        @keydown.enter="saveField('familyName')"
                                        class="editable-input mt-1 block w-full p-2 border-gray-300 rounded-md" />
                                </div>
                            </dd>
                        </div>

                        <div class="sm:col-span-1 px-4 md:px-0 mt-3 md:mt-0">
                            <dt class="text-sm font-bold text-gray-500">
                                Numéro de téléphone
                            </dt>
                            <dd class="mt-1 text-sm text-gray-400 sm:mt-0">
                                <span v-if="!isEditing.phone" @click="editField('phone')" :class="{
                                        'cursor-pointer hover:text-blue-500 hover:underline': true,
                                    }">
                                    {{ editableClient.phone || "Non renseigné" }}
                                </span>
                                <div v-else>
                                    <input v-model="editableClient.phone" @blur="saveField('phone')"
                                        @keydown.enter="saveField('phone')"
                                        class="editable-input mt-1 block w-full p-2 border-gray-300 rounded-md" />
                                </div>
                            </dd>
                        </div>
                    </div>

                    <!-- Deuxième ligne : Ville, Pays, Email -->
                    <div class="py-3 sm:py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                        <div class="sm:col-span-1 px-4 md:px-0 mt-3 md:mt-0 mb-4 md:mb-0">
                            <dt class="text-sm font-bold text-gray-500">Ville</dt>
                            <dd class="mt-1 text-sm text-gray-400 sm:mt-0">
                                <span v-if="!isEditing.locality" @click="editField('locality')" :class="{
                                        'cursor-pointer hover:text-blue-500 hover:underline': true,
                                    }">
                                    {{ editableClient.locality || "Non renseigné" }}
                                </span>
                                <div v-else>
                                    <input v-model="editableClient.locality" @blur="saveField('locality')"
                                        @keydown.enter="saveField('locality')"
                                        class="editable-input mt-1 block w-full p-2 border-gray-300 rounded-md" />
                                </div>
                            </dd>
                        </div>
                        <div class="sm:col-span-1 px-4 md:px-0 mt-3 md:mt-0 mb-4 md:mb-0">
                            <dt class="text-sm font-bold text-gray-500">Pays</dt>
                            <dd class="mt-1 text-sm text-gray-400 sm:mt-0">
                                <span v-if="!isEditing.country" @click="editField('country')" :class="{
                                        'cursor-pointer hover:text-blue-500 hover:underline': true,
                                    }">
                                    {{ editableClient.country || "Non renseigné" }}
                                </span>
                                <div v-else>
                                    <input v-model="editableClient.country" @blur="saveField('country')"
                                        @keydown.enter="saveField('country')"
                                        class="editable-input mt-1 block w-full p-2 border-gray-300 rounded-md" />
                                </div>
                            </dd>
                        </div>
                        <div class="sm:col-span-1 px-4 md:px-0 mt-3 md:mt-0 mb-4 md:mb-0">
                            <dt class="text-sm font-bold text-gray-500">Email</dt>
                            <dd class="mt-1 text-sm text-gray-400 sm:mt-0">
                                <span v-if="!isEditing.email" @click="editField('email')" :class="{
                                        'cursor-pointer hover:text-blue-500 hover:underline': true,
                                    }">
                                    {{ editableClient.email || "Non renseigné" }}
                                </span>
                                <div v-else>
                                    <input v-model="editableClient.email" @blur="saveField('email')"
                                        @keydown.enter="saveField('email')"
                                        class="editable-input mt-1 block w-full p-2 border-gray-300 rounded-md" />
                                </div>
                            </dd>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, reactive, computed, watch } from "vue";
import axios from "axios";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import FlashMessage from "@/Components/FlashMessage.vue";

// Props
const props = defineProps({
    client: {
        type: Object,
        required: true,
    },
});

// État réactif pour le client modifiable
const editableClient = reactive({
    ...props.client,
    notes: props.client.notes || [],
});

// S'assurer que notes est toujours un tableau
if (!Array.isArray(editableClient.notes)) {
    editableClient.notes = [];
}

// Nombre réactif d'affichage des notes
const visibleNotesCount = ref(5);

// Initialiser isEditingNotes comme un objet réactif
const isEditingNotes = reactive({});

// Réactif pour afficher les messages de succès
const successMessages = reactive({
    firstName: false,
    familyName: false,
    locality: false,
    phone: false,
    country: false,
    email: false,
    notes: [],
});

// Gestion des messages flash
const flashMessage = ref("");
const showFlashMessage = ref(false);

// Affiche un message flash
const displayFlashMessage = (message) => {
    flashMessage.value = message;
    showFlashMessage.value = true;
    
    // Le composant FlashMessage se fermera automatiquement après sa durée
};

// Ferme le message flash
const closeFlashMessage = () => {
    showFlashMessage.value = false;
};

// Calcul du type de la dernière note importante
const latestWarningType = computed(() => {
    const notes = editableClient.notes || [];

    // Récupérer la note la plus récente parmi les types importants
    const importantNotes = notes
        .filter(note => ['avertissement', 'attention', 'premium'].includes(note.type))
        .sort((a, b) => new Date(b.note_date) - new Date(a.note_date));

    // Si on trouve une note importante, on la retourne
    if (importantNotes.length > 0) {
        return importantNotes[0].type;
    }

    // Sinon, on vérifie s'il y a une note "a_contacter"
    if (notes.some(note => note.type === 'a_contacter')) {
        return 'a_contacter';
    }

    return null;
});

// Fonction pour obtenir les classes CSS en fonction du type
const getWarningClass = (type) => {
    return (
        {
            avertissement: "bg-orange-100 text-orange-700",
            premium: "bg-green-100 text-green-700",
            attention: "bg-red-100 text-red-700",
            a_contacter: "bg-purple-100 text-purple-700",
        }[type] || "bg-gray-100 text-gray-700"
    );
};

// Fonction pour obtenir le texte en fonction du type
const getWarningText = (type) => {
    return (
        {
            avertissement: "Ce client possède un avertissement (voir notes).",
            premium: "Ce client est identifié comme premium (voir notes).",
            attention: "Ce client est à éviter (voir notes).",
            a_contacter: "Ce client doit être contacté (voir notes).",
        }[type] || ""
    );
};

// Émission d'événements
const emit = defineEmits(["client-updated"]);

// État réactif pour savoir quel champ est en cours d'édition
const isEditing = reactive({
    firstName: false,
    familyName: false,
    locality: false,
    phone: false,
    country: false,
    email: false,
});

// Fonction pour activer le mode édition
const editField = (field) => {
    isEditing[field] = true;
};

// Fonction pour enregistrer une modification
const saveField = (field) => {
    isEditing[field] = false;

    axios
        .put(`/clients/${editableClient.id}`, {
            [field]: editableClient[field],
        })
        .then((response) => {
            // Mettre à jour editableClient avec les données du serveur
            Object.assign(editableClient, response.data);

            // Émettre l'événement avec les données mises à jour
            emit("client-updated", JSON.parse(JSON.stringify(editableClient)));

            // Afficher le message flash au lieu du message de succès local
            displayFlashMessage(`Le champ ${field} a été mis à jour avec succès.`);
        })
        .catch((error) => {
            console.error("Failed to update the client:", error);
        });
};

const showAddNote = ref(false);
const showAddNoteSuccess = ref(false);

const newNote = ref({ content: "", type: "information" });

// Fonction pour activer le mode édition d'une note
const editNote = (noteId) => {
    const note = editableClient.notes.find((n) => n.id === noteId);
    if (note && isNoteEditable(note.note_date)) {
        isEditingNotes[noteId] = true;
    }
};

// Enregistre une note avec Axios vers la DB
const saveNote = (note) => {
    isEditingNotes[note.id] = false;

    axios
        .put(`/clients/${editableClient.id}/notes/${note.id}`, {
            content: note.content,
            type: note.type,
        })
        .then((response) => {
            const updatedNote = response.data;

            // Mettre à jour la note dans la liste
            const index = editableClient.notes.findIndex(
                (n) => n.id === note.id
            );
            if (index !== -1) {
                editableClient.notes.splice(index, 1, updatedNote);
            }

            // Forcer Vue à détecter le changement
            editableClient.notes = [...editableClient.notes];

            // Afficher un message flash pour la mise à jour
            displayFlashMessage("La note a été mise à jour avec succès.");
        })
        .catch((error) => {
            console.error("Erreur lors de la mise à jour de la note :", error);
        });
};

// Fonction pour supprimer une note
const deleteNote = async (noteId) => {
    try {
        await axios.delete(`/clients/${editableClient.id}/notes/${noteId}`);
        editableClient.notes = editableClient.notes.filter(
            (note) => note.id !== noteId
        );

        // Afficher un message flash pour la suppression
        displayFlashMessage("La note a été supprimée avec succès.");
    } catch (error) {
        console.error("Erreur lors de la suppression de la note :", error);
    }
};

// Fonction pour enregistrer une nouvelle note
const saveNewNote = () => {
    if (newNote.value.content) {
        axios
            .post(`/clients/${editableClient.id}/notes`, {
                content: newNote.value.content,
                type: newNote.value.type,
            })
            .then((response) => {
                const createdNote = response.data;

                // Ajouter la nouvelle note au début
                editableClient.notes.unshift(createdNote);

                // Forcer Vue à détecter le changement
                editableClient.notes = [...editableClient.notes];

                newNote.value = { content: "", type: "information" };
                showAddNote.value = false;

                // Afficher un message flash pour l'ajout
                displayFlashMessage("La note a été ajoutée avec succès.");
            })
            .catch((error) => {
                console.error("Erreur lors de l'ajout de la note :", error);
            });
    }
};

// Fonction pour vérifier si une note est modifiable/supprimable
const isNoteEditable = (noteDate) => {
    const now = new Date();
    const noteTime = new Date(noteDate);
    const diffInHours = (now - noteTime) / (1000 * 60 * 60);
    return diffInHours <= 24; // Modifiable si la note a été créée il y a moins de 24h
};

const formatDateTime = (dateString) => {
    const date = new Date(dateString);
    const options = {
        day: "2-digit",
        month: "2-digit",
        year: "numeric",
        hour: "2-digit",
        minute: "2-digit",
    };
    return new Intl.DateTimeFormat("fr-FR", options).format(date);
};

// Fonction pour afficher plus de notes
const showMoreNotes = () => {
    visibleNotesCount.value = editableClient.notes.length;
};

// Fonction pour revenir aux 5 premières notes
const showLessNotes = () => {
    visibleNotesCount.value = 5;
};

// Initialiser isEditingNotes et successMessages.notes
const initializeNotesState = () => {
    editableClient.notes.forEach((note) => {
        isEditingNotes[note.id] = false;
    });
};
initializeNotesState();

// Watch pour vérifier si `editableClient.notes` est bien mis à jour
watch(
    () => editableClient.notes,
    (newNotes) => {
        newNotes.forEach((note) => {
            if (!(note.id in isEditingNotes)) {
                isEditingNotes[note.id] = false;
            }
        });
    },
    { immediate: true }
);

// Calcul pour afficher les notes dans l'ordre inverse (plus récentes en premier)
const reversedNotes = computed(() => {
    if (!Array.isArray(editableClient.notes)) {
        return [];
    }
    return [...editableClient.notes].sort(
        (a, b) => new Date(b.note_date) - new Date(a.note_date)
    );
});
</script>

<style scoped>
/* Transition pour les notes */
.slide-enter-active,
.slide-leave-active {
    transition: all 0.5s ease;
}

.slide-enter-from,
.slide-leave-to {
    opacity: 0;
    transform: translateY(-10px);
}

.slide-leave-from,
.slide-enter-to {
    opacity: 1;
    transform: translateY(0);
}

.success-message {
    animation: fade-in-out 3s forwards;
    position: absolute;
}

@keyframes fade-in-out {
    0% {
        transform: translateY(20px);
        opacity: 0;
    }

    20% {
        transform: translateY(0);
        opacity: 1;
    }

    80% {
        transform: translateY(0);
        opacity: 1;
    }

    100% {
        transform: translateY(20px);
        opacity: 0;
    }
}

.editable-input {
    border: 1px solid #ccc;
    padding: 8px;
    width: 100%;
    border-radius: 4px;
}

.transition-colors:hover {
    transition: all 0.2s ease;
    transform: translateY(-1px);
}

.editable-text {
    display: inline-block;
    padding: 0.25rem 0.5rem;
    border-radius: 0.25rem;
    transition: all 0.2s ease;
}

.editable-text:hover {
    background-color: rgba(59, 130, 246, 0.1);
    transform: translateY(-1px);
}

.success-message {
    animation: fadeOut 5s forwards;
}

@keyframes fadeOut {
    0% { opacity: 1; }
    70% { opacity: 1; }
    100% { opacity: 0; }
}
</style>
