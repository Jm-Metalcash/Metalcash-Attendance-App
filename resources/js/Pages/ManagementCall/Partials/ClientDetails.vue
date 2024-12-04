<template>
    <div class="bg-white overflow-hidden shadow rounded-lg border mt-8">
        <!-- Section Notes -->
        <div class="pb-12 px-0 md:px-0">
            <div v-if="editableClient.notes && editableClient.notes.length > 0">
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
                                    class="py-2 px-4 border-b text-left text-sm font-semibold text-gray-600"
                                ></th>
                            </tr>
                        </thead>
                        <transition-group name="slide" tag="tbody">
                            <tr
                                v-for="note in reversedNotes.slice(0, visibleNotesCount)"
                                :key="note.id"
                                :class="
                                    note.type === 'avertissement'
                                        ? 'bg-red-100 text-red-700'
                                        : 'text-gray-500'
                                "
                            >
                                <td class="py-2 px-4 border-b text-sm text-left w-1/6">
                                    {{ formatDateTime(note.note_date) }}
                                </td>
                                <td class="py-2 px-4 border-b text-sm text-left w-5/6">
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
                                <td class="py-2 px-4 border-b text-sm text-left">
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
                            v-if="visibleNotesCount < editableClient.notes.length"
                            @click="showMoreNotes"
                            class="mt-3 bg-white text-gray-600 rounded-md px-4 py-2 text-sm hover:bg-gray-100 transition-colors duration-200"
                        >
                            Voir plus <i class="fa-solid fa-angle-down"></i>
                        </button>
                        <button
                            v-if="visibleNotesCount >= editableClient.notes.length && editableClient.notes.length > 5"
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
                    Aucune note actuellement pour ce client.
                </p>
            </div>

            <!-- Bouton Ajouter une note -->
            <div class="flex items-center justify-start md:justify-start mt-4 ml-2">
                <button
                    v-if="!showAddNote"
                    @click="showAddNote = true"
                    class="flex items-center text-sm text-gray-600 bg-white border border-gray-300 rounded-md px-4 py-2 hover:bg-gray-100 hover:border-gray-400 transition-colors duration-200"
                >
                    <i class="fa-solid fa-plus text-gray-500 mr-2"></i> Ajouter une note
                </button>
                <button
                    v-if="showAddNote"
                    @click="showAddNote = false"
                    class="flex items-center text-sm text-gray-600 bg-white border border-gray-300 rounded-md px-4 py-2 hover:bg-gray-100 hover:border-gray-400 transition-colors duration-200"
                >
                    <i class="fa-solid fa-minus text-gray-500 mr-2"></i> Réduire
                </button>
            </div>

            <!-- Formulaire d'ajout de note -->
            <div v-if="showAddNote" class="mt-4">
                <div class="bg-gray-50 p-4 rounded-md shadow-md">
                    <h4 class="text-gray-700 text-sm font-semibold mb-2">
                        Nouvelle note
                    </h4>
                    <select
                        v-model="newNote.type"
                        class="mt-1 mb-2 block w-full pl-3 pr-10 py-2 text-base border-gray-300 rounded-md"
                    >
                        <option value="information">Information</option>
                        <option value="avertissement">Avertissement</option>
                    </select>
                    <textarea
                        v-model="newNote.content"
                        class="block w-full px-3 py-2 border border-gray-300 rounded-md text-sm"
                        placeholder="Saisir la note..."
                    ></textarea>
                    <button
                        @click="saveNewNote"
                        class="mt-3 bg-blue-600 text-white rounded-md px-4 py-2 text-sm hover:bg-blue-700 transition-colors duration-200"
                    >
                        Enregistrer
                    </button>
                </div>
            </div>
        </div>

        <!-- Section Informations Générales -->
        <div class="px-4 py-5 sm:px-6 bg-gray-200">
            <p class="mt-1 max-w-2xl text-sm text-gray-500 font-bold">
                Informations générales
            </p>
        </div>
        <div class="border-t border-gray-200 px-4 py-5 sm:p-0">
            <div class="sm:divide-y sm:divide-gray-200">
                <div class="py-3 sm:py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                    <div>
                        <dt class="text-sm font-bold text-gray-500">Prénom</dt>
                        <dd class="mt-1 text-sm text-gray-400">
                            {{ editableClient.firstName || "Non spécifié" }}
                        </dd>
                    </div>
                    <div>
                        <dt class="text-sm font-bold text-gray-500">Nom</dt>
                        <dd class="mt-1 text-sm text-gray-400">
                            {{ editableClient.familyName || "Non spécifié" }}
                        </dd>
                    </div>
                    <div>
                        <dt class="text-sm font-bold text-gray-500">Téléphone</dt>
                        <dd class="mt-1 text-sm text-gray-400">
                            {{ editableClient.phone || "Non spécifié" }}
                        </dd>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { reactive, ref, computed } from "vue";
import axios from "axios";

const props = defineProps({
    client: {
        type: Object,
        required: true,
    },
});

const editableClient = reactive({
    ...props.client,
    notes: props.client.notes || [],
});

const isEditingNotes = reactive({});
const visibleNotesCount = ref(5);
const showAddNote = ref(false);
const newNote = reactive({ type: "information", content: "" });

const saveNewNote = async () => {
    if (!newNote.content) return;
    try {
        const response = await axios.post(`/clients/${editableClient.id}/notes`, newNote);
        editableClient.notes.push(response.data);
        newNote.content = "";
        showAddNote.value = false;
    } catch (error) {
        console.error("Erreur lors de l'ajout de la note :", error);
    }
};

const deleteNote = async (noteId) => {
    try {
        await axios.delete(`/clients/${editableClient.id}/notes/${noteId}`);
        editableClient.notes = editableClient.notes.filter(
            (note) => note.id !== noteId
        );
    } catch (error) {
        console.error("Erreur lors de la suppression de la note :", error);
    }
};

const saveNote = async (note) => {
    try {
        await axios.put(`/clients/${editableClient.id}/notes/${note.id}`, {
            content: note.content,
        });
    } catch (error) {
        console.error("Erreur lors de la mise à jour de la note :", error);
    }
};

const reversedNotes = computed(() => [...editableClient.notes].reverse());

const showMoreNotes = () => (visibleNotesCount.value = editableClient.notes.length);
const showLessNotes = () => (visibleNotesCount.value = 5);

const formatDateTime = (dateString) => {
    const date = new Date(dateString);
    return new Intl.DateTimeFormat("fr-FR", {
        day: "2-digit",
        month: "2-digit",
        year: "numeric",
        hour: "2-digit",
        minute: "2-digit",
    }).format(date);
};
</script>
