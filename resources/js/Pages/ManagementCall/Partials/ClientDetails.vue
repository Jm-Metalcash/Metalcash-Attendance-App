<template>
    <div class="bg-white overflow-hidden shadow rounded-lg border mt-8">
        <!-- Section type de notes -->
        <div
            v-if="
                editableClient.notes.some((note) =>
                    ['avertissement', 'premium', 'attention'].includes(
                        note.type
                    )
                )
            "
            class="warning-blacklist mb-4"
        >
            <div
                :class="getWarningClass(latestWarningType)"
                class="px-4 py-2 relative"
                role="alert"
            >
                <span class="font-bold text-sm">Note :</span>
                <span class="block sm:inline text-sm md:ml-1">
                    {{ getWarningText(latestWarningType) }}
                </span>
            </div>
        </div>

        <!-- Section Notes -->
        <div class="pb-12 px-0 md:px-0">
            <h1 class="font-bold text-sm text-gray-600 py-3 px-3">
                Notes relatives à {{ editableClient.firstName }}
                {{ editableClient.familyName }}
            </h1>
            <div v-if="editableClient.notes && editableClient.notes.length > 0">
                <!-- Tableau des notes -->
                <div class="overflow-x-auto">
                    <table class="min-w-full bg-white">
                        <thead>
                            <tr class="bg-gray-200">
                                <th
                                    class="py-2 px-4 border-b text-left text-sm font-semibold text-gray-600 w-1/5"
                                >
                                    Date
                                </th>
                                <th
                                    class="py-2 px-4 border-b text-left text-sm font-semibold text-gray-600 w-3/5"
                                >
                                    Note
                                </th>
                                <th
                                    class="py-2 px-4 border-b text-left text-sm font-semibold text-gray-600 w-1/5"
                                ></th>
                            </tr>
                        </thead>
                        <transition-group name="slide" tag="tbody">
                            <tr
                                v-for="note in reversedNotes.slice(
                                    0,
                                    visibleNotesCount
                                )"
                                :key="note.id"
                                :class="
                                    note.type === 'avertissement'
                                        ? 'bg-orange-100 text-orange-700'
                                        : note.type === 'premium'
                                        ? 'bg-green-100 text-green-700'
                                        : note.type === 'attention'
                                        ? 'bg-red-100 text-red-700'
                                        : 'bg-gray-50 text-gray-700'
                                "
                            >
                                <td
                                    class="py-2 px-4 border-b text-sm text-left w-1/6"
                                >
                                    {{ formatDateTime(note.note_date) }}
                                </td>
                                <td
                                    class="py-2 px-4 border-b text-sm text-left w-5/6"
                                >
                                    <span
                                        v-if="!isEditingNotes[note.id]"
                                        @click="editNote(note.id)"
                                        class="editable-text cursor-pointer"
                                    >
                                        {{ note.content }}
                                    </span>
                                    <textarea
                                        v-else
                                        v-model="note.content"
                                        @blur="saveNote(note)"
                                        @keydown.enter.prevent="saveNote(note)"
                                        class="editable-input mt-1 block w-full p-2 border-gray-300 rounded-md"
                                    ></textarea>
                                    <p
                                        v-if="successMessages.notes[note.id]"
                                        class="text-green-500 text-xs relative mt-1 success-message"
                                    >
                                        Enregistré avec succès
                                    </p>
                                </td>

                                <td
                                    class="py-2 px-4 border-b text-sm text-left w-1/5"
                                >
                                    <button
                                        v-if="isNoteEditable(note.note_date)"
                                        @click="deleteNote(note.id)"
                                        class="text-red-600 hover:text-red-800 font-semibold"
                                    >
                                        Supprimer
                                    </button>
                                </td>
                            </tr>
                        </transition-group>
                    </table>
                    <div class="text-center">
                        <!-- voir plus de notes -->
                        <button
                            v-if="
                                visibleNotesCount < editableClient.notes.length
                            "
                            @click="showMoreNotes"
                            class="mt-3 bg-white text-gray-600 rounded-md px-4 py-2 text-sm hover:bg-gray-100 transition-colors duration-200"
                        >
                            Voir plus <i class="fa-solid fa-angle-down"></i>
                        </button>

                        <!-- voir moins de notes -->
                        <button
                            v-if="
                                visibleNotesCount >=
                                    editableClient.notes.length &&
                                editableClient.notes.length > 5
                            "
                            @click="showLessNotes"
                            class="mt-3 bg-white text-gray-600 rounded-md px-4 py-2 text-sm hover:bg-gray-100 transition-colors duration-200"
                        >
                            Voir moins <i class="fa-solid fa-angle-up"></i>
                        </button>
                        <p
                            v-if="showAddNoteSuccess"
                            class="text-green-500 text-sm md:ml-52 mx-auto success-message"
                        >
                            La note a été ajoutée avec succès !
                        </p>
                    </div>
                </div>
            </div>
            <div v-else>
                <p class="text-sm text-gray-400 px-2 pt-4">
                    Aucune note actuellement pour ce client.
                </p>
            </div>

            <!-- Bouton Ajouter une note -->
            <div
                class="flex items-center justify-start md:justify-start mt-4 ml-2"
            >
                <button
                    v-if="!showAddNote"
                    class="flex items-center text-sm text-gray-600 bg-white border border-gray-300 rounded-md px-4 py-2 hover:bg-gray-100 hover:border-gray-400 transition-colors duration-200"
                    @click="showAddNote = true"
                >
                    <i class="fa-solid fa-plus text-gray-500 mr-2"></i>
                    Ajouter une note
                </button>
                <button
                    v-if="showAddNote"
                    class="flex items-center text-sm text-gray-600 bg-white border border-gray-300 rounded-md px-4 py-2 hover:bg-gray-100 hover:border-gray-400 transition-colors duration-200"
                    @click="showAddNote = false"
                >
                    <i class="fa-solid fa-minus text-gray-500 mr-2"></i>
                    Réduire le formulaire
                </button>
            </div>

            <!-- Formulaire pour ajouter une nouvelle note -->
            <div v-if="showAddNote" class="mt-4">
                <div class="bg-gray-50 p-4 rounded-md shadow-md">
                    <h4 class="text-gray-700 text-sm font-semibold mb-2">
                        Nouvelle note
                    </h4>
                    <select
                        v-model="newNote.type"
                        class="block w-full px-3 py-2 border border-gray-300 rounded-md mb-2"
                    >
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
                    </select>
                    <textarea
                        v-model="newNote.content"
                        @keydown.enter="saveNewNote"
                        class="block w-full px-3 py-2 border border-gray-300 rounded-md text-sm focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500"
                        placeholder="Saisir la note..."
                    ></textarea>
                    <button
                        @click="saveNewNote"
                        class="mt-3 bg-blue-600 text-white rounded-md px-4 py-2 text-sm hover:bg-blue-700 transition-colors duration-200"
                    >
                        Enregistrer la note
                    </button>
                </div>
            </div>
        </div>

        <!-- Section Informations Générales -->
        <div class="px-4 py-5 sm:px-6 bg-gray-200">
            <p class="mt-1 max-w-2xl text-sm text-gray-500 font-bold">
                Informations générales sur le client
            </p>
        </div>
        <div class="border-t border-gray-200 px-0 py-5 sm:p-0">
            <div class="sm:divide-y sm:divide-gray-200">
                <!-- Première ligne : Prénom & Nom & Téléphone -->
                <div
                    class="py-3 sm:py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6"
                >
                    <div class="sm:col-span-1 px-4 md:px-0">
                        <dt class="text-sm font-bold text-gray-500">Prénom</dt>
                        <dd class="mt-1 text-sm text-gray-400 sm:mt-0">
                            <span
                                v-if="!isEditing.firstName"
                                @click="editField('firstName')"
                                class="editable-text cursor-pointer hover:text-gray-500"
                            >
                                {{
                                    editableClient.firstName ||
                                    "Ajouter un prénom"
                                }}
                            </span>
                            <input
                                v-else
                                v-model="editableClient.firstName"
                                @blur="saveField('firstName')"
                                @keydown.enter="saveField('firstName')"
                                class="editable-input mt-1 block w-full p-2 border-gray-300 rounded-md"
                            />
                            <p
                                v-if="successMessages.firstName"
                                class="text-green-500 text-xs mt-1 success-message"
                            >
                                Enregistré avec succès
                            </p>
                        </dd>
                    </div>
                    <div class="sm:col-span-1 px-4 md:px-0 mt-3 md:mt-0">
                        <dt class="text-sm font-bold text-gray-500">Nom</dt>
                        <dd class="mt-1 text-sm text-gray-400 sm:mt-0">
                            <span
                                v-if="!isEditing.familyName"
                                @click="editField('familyName')"
                                class="editable-text cursor-pointer hover:text-gray-500"
                            >
                                {{
                                    editableClient.familyName ||
                                    "Ajouter un nom"
                                }}
                            </span>
                            <input
                                v-else
                                v-model="editableClient.familyName"
                                @blur="saveField('familyName')"
                                @keydown.enter="saveField('familyName')"
                                class="editable-input mt-1 block w-full p-2 border-gray-300 rounded-md"
                            />
                            <p
                                v-if="successMessages.familyName"
                                class="text-green-500 text-xs mt-1 success-message"
                            >
                                Enregistré avec succès
                            </p>
                        </dd>
                    </div>

                    <div class="sm:col-span-1 px-4 md:px-0 mt-3 md:mt-0">
                        <dt class="text-sm font-bold text-gray-500">
                            Numéro de téléphone
                        </dt>
                        <dd class="mt-1 text-sm text-gray-400 sm:mt-0">
                            <span
                                v-if="!isEditing.phone"
                                @click="editField('phone')"
                                class="editable-text cursor-pointer hover:text-gray-500"
                            >
                                {{
                                    editableClient.phone ||
                                    "Ajouter un numéro de téléphone"
                                }}
                            </span>
                            <input
                                v-else
                                v-model="editableClient.phone"
                                @blur="saveField('phone')"
                                @keydown.enter="saveField('phone')"
                                class="editable-input mt-1 block w-full p-2 border-gray-300 rounded-md"
                            />
                            <p
                                v-if="successMessages.phone"
                                class="text-green-500 text-xs mt-1 success-message"
                            >
                                Enregistré avec succès
                            </p>
                        </dd>
                    </div>
                </div>

                <!-- Deuxième ligne : E-mail & Numéro de téléphone -->
                <div
                    class="py-0 sm:py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6"
                >
                    <div
                        class="sm:col-span-1 px-4 md:px-0 mt-3 md:mt-0 mb-4 md:mb-0"
                    >
                        <dt class="text-sm font-bold text-gray-500">Ville</dt>
                        <dd class="mt-1 text-sm text-gray-400 sm:mt-0">
                            <span
                                v-if="!isEditing.locality"
                                @click="editField('locality')"
                                class="editable-text cursor-pointer hover:text-gray-500"
                            >
                                {{
                                    editableClient.locality ||
                                    "Ajouter une ville"
                                }}
                            </span>
                            <input
                                v-else
                                v-model="editableClient.locality"
                                @blur="saveField('locality')"
                                @keydown.enter="saveField('locality')"
                                class="editable-input mt-1 block w-full p-2 border-gray-300 rounded-md"
                            />
                            <p
                                v-if="successMessages.locality"
                                class="text-green-500 text-xs mt-1 success-message"
                            >
                                Enregistré avec succès
                            </p>
                        </dd>
                    </div>
                    <div
                        class="sm:col-span-1 px-4 md:px-0 mt-3 md:mt-0 mb-4 md:mb-0"
                    >
                        <dt class="text-sm font-bold text-gray-500">Pays</dt>
                        <dd class="mt-1 text-sm text-gray-400 sm:mt-0">
                            <span
                                v-if="!isEditing.country"
                                @click="editField('country')"
                                class="editable-text cursor-pointer hover:text-gray-500"
                            >
                                {{
                                    editableClient.country || "Ajouter un pays"
                                }}
                            </span>
                            <input
                                v-else
                                v-model="editableClient.country"
                                @blur="saveField('country')"
                                @keydown.enter="saveField('country')"
                                class="editable-input mt-1 block w-full p-2 border-gray-300 rounded-md"
                            />
                            <p
                                v-if="successMessages.country"
                                class="text-green-500 text-xs mt-1 success-message"
                            >
                                Enregistré avec succès
                            </p>
                        </dd>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, reactive, computed } from "vue";
import axios from "axios";

//Props
const props = defineProps({
    client: {
        type: Object,
        required: true,
    },
});

// Calcul du type de la dernière note importante
const latestWarningType = computed(() => {
    const importantNotes = editableClient.notes.filter((note) =>
        ["avertissement", "premium", "attention"].includes(note.type)
    );
    return importantNotes.length > 0
        ? importantNotes[importantNotes.length - 1].type
        : null;
});

// Fonction pour obtenir les classes CSS en fonction du type
const getWarningClass = (type) => {
    return (
        {
            avertissement: "bg-orange-100 text-orange-700",
            premium: "bg-green-100 text-green-700",
            attention: "bg-red-100 text-red-700",
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
        }[type] || ""
    );
};

// Émission d'événements
const emit = defineEmits(["client-updated"]);

//constante pour éditer les notes
const editableClient = reactive({
    ...props.client,
    notes: props.client.notes || [],
});

// Suivi des champs en cours d'édition
const isEditing = reactive({
    firstName: false,
    familyName: false,
    locality: false,
    phone: false,
    country: false,
});

const isEditingNotes = reactive({});
// Nombre réactif d'affichage des notes
const visibleNotesCount = ref(5);

//Reactive sur le successMessage
const successMessages = reactive({
    firstName: false,
    familyName: false,
    phone: false,
    locality: false,
    country: false,
    notes: [],
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
            // Mise à jour locale des données
            Object.assign(editableClient, response.data);

            // Notification de mise à jour
            emit("client-updated", JSON.parse(JSON.stringify(editableClient)));

            // Affichage du message de succès
            displaySuccessMessage(field);
        })
        .catch((error) => {
            console.error(`Erreur lors de la mise à jour de ${field} :`, error);
        });
};

// Affichage temporaire des messages de succès
const displaySuccessMessage = (field) => {
    successMessages[field] = true;
    setTimeout(() => {
        successMessages[field] = false;
    }, 3000);
};

const showAddNote = ref(false);
const showAddNoteSuccess = ref(false);

const newNote = ref({ content: "", type: "information" });

// Computed pour inverser l'ordre des notes
const reversedNotes = computed(() => {
    return [...editableClient.notes].sort(
        (a, b) => new Date(b.note_date) - new Date(a.note_date)
    );
});

const editNote = (noteId) => {
    const note = editableClient.notes.find((n) => n.id === noteId);
    if (note && isNoteEditable(note.note_date)) {
        isEditingNotes[noteId] = true;
    }
};

const saveNote = (note) => {
    isEditingNotes[note.id] = false;
    axios
        .put(`/clients/${editableClient.id}/notes/${note.id}`, {
            content: note.content,
            type: note.type,
        })
        .then(() => {
            successMessages.notes[note.id] = true;
            setTimeout(() => {
                successMessages.notes[note.id] = false;
            }, 3000);
        })
        .catch((error) => console.error(error));
};

const deleteNote = (noteId) => {
    axios
        .delete(`/clients/${editableClient.id}/notes/${noteId}`)
        .then(() => {
            editableClient.notes = editableClient.notes.filter(
                (note) => note.id !== noteId
            );
            updateHasWarning(); // Met à jour has_warning après suppression
        })
        .catch((error) => {
            console.error("Erreur lors de la suppression de la note :", error);
        });
};

const saveNewNote = () => {
    if (newNote.value.content) {
        axios
            .post(`/clients/${editableClient.id}/notes`, {
                content: newNote.value.content,
                type: newNote.value.type,
            })
            .then((response) => {
                // Ajouter la nouvelle note à la liste
                editableClient.notes.push(response.data);

                // Réinitialiser le formulaire
                newNote.value = { content: "", type: "information" };
                showAddNote.value = false;

                // Afficher le message de succès
                showAddNoteSuccess.value = true;
                setTimeout(() => {
                    showAddNoteSuccess.value = false;
                }, 3000);

                updateHasWarning(); // Met à jour has_warning après ajout
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
    const diffInHours = (now - noteTime) / (1000 * 60 * 60); // Différence en heures
    return diffInHours <= 24; // Modifiable si la note a été créée il y a moins de 24h
};

const formatDateTime = (dateString) => {
    const date = new Date(dateString);
    return date.toLocaleString("fr-FR", {
        day: "2-digit",
        month: "2-digit",
        year: "numeric",
        hour: "2-digit",
        minute: "2-digit",
    });
};

// Fonction pour afficher plus de notes
const showMoreNotes = () => {
    visibleNotesCount.value = editableClient.notes.length;
};

// Fonction pour revenir aux 5 premières notes
const showLessNotes = () => {
    visibleNotesCount.value = 5;
};
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
</style>
