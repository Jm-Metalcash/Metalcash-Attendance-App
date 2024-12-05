<template>
    <div class="bg-white overflow-hidden shadow rounded-lg border mt-8">
        <!-- Section status des notes -->
        <div
            v-if="
                editableProspect.notes.some((note) =>
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
            <div
                v-if="
                    editableProspect.notes && editableProspect.notes.length > 0
                "
            >
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
                                    class="py-2 px-4 border-b text-left text-sm font-semibold text-gray-600 w-4/5"
                                >
                                    Note
                                </th>
                                <th
                                    class="py-2 px-4 border-b text-left text-sm font-semibold text-gray-600 w-4/5"
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
                                :class="getNoteClass(note.type)"
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
                                </td>
                                <td
                                    class="py-2 px-4 border-b text-sm text-left w-1/5"
                                >
                                    <button
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
                        <button
                            v-if="
                                visibleNotesCount <
                                editableProspect.notes.length
                            "
                            @click="showMoreNotes"
                            class="mt-3 bg-white text-gray-600 rounded-md px-4 py-2 text-sm hover:bg-gray-100 transition-colors duration-200"
                        >
                            Voir plus <i class="fa-solid fa-angle-down"></i>
                        </button>

                        <!-- Bouton Voir moins -->
                        <button
                            v-if="
                                visibleNotesCount >=
                                    editableProspect.notes.length &&
                                editableProspect.notes.length > 5
                            "
                            @click="showLessNotes"
                            class="mt-3 bg-white text-gray-600 rounded-md px-4 py-2 text-sm hover:bg-gray-100 transition-colors duration-200"
                        >
                            Voir moins <i class="fa-solid fa-angle-up"></i>
                        </button>
                    </div>
                </div>
            </div>
            <div v-else>
                <p class="text-sm text-gray-400 px-2 pt-4">
                    Aucune note actuellement pour ce prospect.
                </p>
            </div>

            <!-- Message de succès lors de l'ajout d'une note -->
            <p
                v-if="showAddNoteSuccess"
                class="text-green-500 text-sm md:ml-52 mx-auto success-message"
            >
                La note a été ajoutée avec succès !
            </p>

            <!-- Bouton d'ajout de note stylisé -->
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

                <!-- Bouton pour réduire le formulaire si showAddNote est true -->
                <button
                    v-if="showAddNote"
                    class="flex items-center text-sm text-gray-600 bg-white border border-gray-300 rounded-md px-4 py-2 hover:bg-gray-100 hover:border-gray-400 transition-colors duration-200"
                    @click="showAddNote = false"
                >
                    <i class="fa-solid fa-minus text-gray-500 mr-2"></i>
                    Réduire le formulaire
                </button>
            </div>

            <!-- Zone d'ajout de nouvelle note -->
            <div v-if="showAddNote" class="mt-4">
                <div class="bg-gray-50 p-4 rounded-md shadow-md">
                    <h4 class="text-gray-700 text-sm font-semibold mb-2">
                        Nouvelle note
                    </h4>
                    <select
                        v-model="newNote.type"
                        class="block w-full px-3 py-2 border border-gray-300 rounded-md mb-2"
                    >
                        <option value="information">Informatif</option>
                        <option value="premium">Prospect premium</option>
                        <option value="avertissement">
                            Prospect suspicieux
                        </option>
                        <option value="attention">Prospect à éviter</option>
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
                Informations générales du prospect
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
                                    editableProspect.firstName ||
                                    "Ajouter un prénom"
                                }}
                            </span>
                            <input
                                v-else
                                v-model="editableProspect.firstName"
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
                                    editableProspect.familyName ||
                                    "Ajouter un nom"
                                }}
                            </span>
                            <input
                                v-else
                                v-model="editableProspect.familyName"
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
                                    editableProspect.phone ||
                                    "Ajouter un numéro de téléphone"
                                }}
                            </span>
                            <input
                                v-else
                                v-model="editableProspect.phone"
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
                                    editableProspect.locality ||
                                    "Ajouter une ville"
                                }}
                            </span>
                            <input
                                v-else
                                v-model="editableProspect.locality"
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
                                    editableProspect.country ||
                                    "Ajouter un pays"
                                }}
                            </span>
                            <input
                                v-else
                                v-model="editableProspect.country"
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
import { ref, reactive, onMounted, computed, watch, defineEmits } from "vue";
import axios from "axios";

// Props
const props = defineProps({
    prospect: {
        type: Object,
        required: true,
    },
});

//const pour vérifier l'état 'avertissement' d'une note
const hasWarning = ref(props.prospect.has_warning);
// Fonction pour mettre à jour dynamiquement `hasWarning`
const updateHasWarning = () => {
    hasWarning.value = editableProspect.notes.some(
        (note) => note.type === "avertissement"
    );
};

// Émission d'événements
const emit = defineEmits(["prospect-updated"]);

// État réactif pour le prospect modifiable
const editableProspect = reactive({
    ...props.prospect,
    notes: props.prospect.notes || [],
    bordereauHistoriques: props.prospect.bordereauHistoriques || [],
});

// Nombre réactif d'affichage des notes
const visibleNotesCount = ref(5);

// Fonction pour afficher plus de notes
const showMoreNotes = () => {
    visibleNotesCount.value = editableProspect.notes.length;
};

// Fonction pour revenir aux 5 premières notes
const showLessNotes = () => {
    visibleNotesCount.value = 5;
};

// Initialiser isEditingNotes comme un objet réactif
const isEditingNotes = reactive({});

// Réactif pour afficher les messages de succès
const successMessages = reactive({
    firstName: false,
    familyName: false,
    locality: false,
    phone: false,
    country: false,
    notes: [],
});

// Fonction pour obtenir les classes CSS basées sur le type de la note
const getNoteClass = (type) => {
    return (
        {
            avertissement: "bg-orange-100 text-orange-700",
            premium: "bg-green-100 text-green-700",
            attention: "bg-red-100 text-red-700",
            information: "bg-gray-50 text-gray-700",
        }[type] || "bg-gray-50 text-gray-700"
    );
};

// Calcul du type de la dernière note importante
const latestWarningType = computed(() => {
    const importantNotes = editableProspect.notes.filter((note) =>
        ["avertissement", "premium", "attention"].includes(note.type)
    );
    return importantNotes.length > 0
        ? importantNotes[importantNotes.length - 1].type
        : null;
});

// Mapping des classes CSS
const getWarningClass = (type) => {
    return (
        {
            avertissement: "bg-orange-100 text-orange-700",
            premium: "bg-green-100 text-green-700",
            attention: "bg-red-100 text-red-700",
        }[type] || "bg-gray-100 text-gray-700"
    );
};

// Mapping des textes
const getWarningText = (type) => {
    return (
        {
            avertissement:
                "Ce prospect est identifié comme suspicieux (voir notes).",
            premium: "Ce prospect est identifié comme premium (voir notes).",
            attention: "Ce prospect est à éviter (voir notes).",
        }[type] || ""
    );
};

// Initialiser isEditingNotes et successMessages.notes
const initializeNotesState = () => {
    editableProspect.notes.forEach((note) => {
        isEditingNotes[note.id] = false;
    });
};
initializeNotesState();

watch(
    () => editableProspect.notes,
    (newNotes) => {
        newNotes.forEach((note) => {
            if (!(note.id in isEditingNotes)) {
                isEditingNotes[note.id] = false;
            }
        });
    },
    { immediate: true }
);

onMounted(() => {
    initializeNotesState();
});

// État réactif pour savoir quel champ est en cours d'édition
const isEditing = reactive({
    firstName: false,
    familyName: false,
    locality: false,
    phone: false,
    country: false,
});

// Fonctions pour gérer les champs d'édition
const editField = (field) => {
    isEditing[field] = true;
};

// Enregistre les données avec Axios vers la DB
const saveField = (field) => {
    isEditing[field] = false;

    axios
        .put(`/prospects/${editableProspect.id}`, {
            [field]: editableProspect[field],
        })
        .then((response) => {
            // Mettre à jour editableProspect avec les données du serveur
            Object.assign(editableProspect, response.data);

            // Émettre l'événement avec les données mises à jour
            emit(
                "prospect-updated",
                JSON.parse(JSON.stringify(editableProspect))
            );

            displaySuccessMessage(field);
        })
        .catch((error) => {
            console.error("Failed to update the prospect:", error);
        });
};

const displaySuccessMessage = (field) => {
    successMessages[field] = true;
    setTimeout(() => {
        successMessages[field] = false;
    }, 3000);
};

// Fonctions pour gérer les notes
const editNote = (noteId) => {
    isEditingNotes[noteId] = true;
};

const reversedNotes = computed(() => {
    return [...editableProspect.notes].reverse();
});

// Fonction pour supprimer une note
const deleteNote = async (noteId) => {
    try {
        await axios.delete(`/prospects/${editableProspect.id}/notes/${noteId}`);
        // Filtrer les notes pour retirer celle qui est supprimée
        editableProspect.notes = editableProspect.notes.filter(
            (note) => note.id !== noteId
        );
        // Vérifier si l'avertissement existe encore
        hasWarning.value = editableProspect.notes.some(
            (note) => note.type === "avertissement"
        );
    } catch (error) {
        console.error("Erreur lors de la suppression de la note :", error);
    }
};

// Enregistre une note avec Axios vers la DB
const saveNote = (note) => {
    isEditingNotes[note.id] = false;

    axios
        .put(`/prospects/${editableProspect.id}/notes/${note.id}`, {
            content: note.content,
        })
        .then((response) => {
            // Mettre à jour la note dans editableProspect.notes
            const index = editableProspect.notes.findIndex(
                (n) => n.id === note.id
            );
            if (index !== -1) {
                editableProspect.notes[index] = response.data;
            }

            displayNoteSuccessMessage(note.id);
        })
        .catch((error) => {
            console.error("Erreur lors de la mise à jour de la note :", error);
        });
};

const displayNoteSuccessMessage = (noteId) => {
    successMessages.notes[noteId] = true;
    setTimeout(() => {
        successMessages.notes[noteId] = false;
    }, 3000);
};

// Ajout de nouvelles notes
const showAddNoteSuccess = ref(false);
const showAddNote = ref(false);
const newNote = ref({
    type: "information",
    content: "",
    note_date: new Date().toISOString(),
});

const saveNewNote = () => {
    if (newNote.value.content) {
        const now = new Date().toISOString();

        const blacklistValue = newNote.value.type === "avertissement" ? 1 : 0;

        axios
            .post(`/prospects/${editableProspect.id}/notes`, {
                content: newNote.value.content,
                note_date: now,
                type: newNote.value.type, // Envoie le type de la note
            })
            .then((response) => {
                editableProspect.notes.push({
                    id: response.data.id,
                    content: newNote.value.content,
                    note_date: now,
                    type: newNote.value.type, // Ajoute le type de la note
                });

                return axios.put(`/prospects/${editableProspect.id}`, {
                    blacklist: blacklistValue,
                });
            })
            .then(() => {
                editableProspect.blacklist = blacklistValue;

                // Vérifier si l'avertissement existe encore
                updateHasWarning();

                // Émettre un événement pour informer le parent que le blacklist a été mis à jour
                emit(
                    "prospect-updated",
                    JSON.parse(JSON.stringify(editableProspect))
                );

                newNote.value.content = "";
                newNote.value.type = "information"; // Réinitialiser à la valeur par défaut
                showAddNote.value = false;

                showAddNoteSuccess.value = true;
                setTimeout(() => {
                    showAddNoteSuccess.value = false;
                }, 3000);
            })
            .catch((error) => {
                console.error(
                    "Erreur lors de l'ajout de la note ou de la mise à jour du prospect :",
                    error
                );
            });
    }
};

// Fonction de formatage de la date
const formatDateTime = (dateString) => {
    const date = new Date(dateString);

    // Si la date n'est pas valide, retourner une chaîne vide
    if (isNaN(date.getTime())) {
        return "";
    }

    // Formatage en fonction du fuseau horaire local
    const options = {
        timeZone: "Europe/Paris", // Remplace par le fuseau souhaité
        day: "2-digit",
        month: "2-digit",
        year: "numeric",
        hour: "2-digit",
        minute: "2-digit",
    };

    return new Intl.DateTimeFormat("fr-FR", options).format(date);
};

// Fonction pour formater le statut
const formatStatus = (status) => {
    switch (status) {
        case 0:
            return "En cours";
        case 1:
            return "Clôturé";
        case 2:
            return "Annulation";
        default:
            return "Inconnu";
    }
};

watch(
    () => editableProspect.notes,
    (newNotes) => {
        newNotes.forEach((note) => {
            if (!(note.id in isEditingNotes)) {
                isEditingNotes[note.id] = false;
            }
        });
    },
    { immediate: true }
);
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
