<script setup>
import { ref, reactive, onMounted, onBeforeUnmount } from "vue";

// Props
const props = defineProps({
    user: {
        type: Object,
        required: true,
    },
});

// État réactif pour l'utilisateur modifiable
const editableUser = reactive({ ...props.user });

// État réactif pour savoir quel champ est en cours d'édition
const isEditing = reactive({
    prenom: false,
    nom: false,
    email: false,
    telephone: false,
    adresse: {
        rue: false,
        codePostal: false,
        localite: false,
        pays: false,
    },
    notes: [],
});

// Réactif pour afficher les messages de succès
const successMessages = reactive({
    prenom: false,
    nom: false,
    email: false,
    telephone: false,
    adresse: {
        rue: false,
        codePostal: false,
        localite: false,
        pays: false,
    },
    notes: [],
});

// Fonction pour gérer le clic en dehors de l'input
const handleClickOutside = (event) => {
    if (
        !event.target.closest(".editable-input") &&
        !event.target.closest(".editable-text")
    ) {
        closeAllFields();
        closeAllNotes();
    }
};

// Fonctions pour gérer les champs d'édition (prénom, nom, email, etc.)
const editField = (field) => {
    if (field.includes("adresse")) {
        const [obj, prop] = field.split(".");
        isEditing[obj][prop] = true;
    } else {
        isEditing[field] = true;
    }
};

const saveField = (field) => {
    if (field.includes("adresse")) {
        const [obj, prop] = field.split(".");
        isEditing[obj][prop] = false;
        displaySuccessMessage(obj, prop);
    } else {
        isEditing[field] = false;
        displaySuccessMessage(field);
    }
};

const closeAllFields = () => {
    Object.keys(isEditing).forEach((field) => {
        if (typeof isEditing[field] === "object") {
            Object.keys(isEditing[field]).forEach((nestedField) => {
                if (isEditing[field][nestedField]) {
                    saveField(`${field}.${nestedField}`);
                }
            });
        } else if (isEditing[field]) {
            saveField(field);
        }
    });
};

const displaySuccessMessage = (field, prop = null) => {
    if (Array.isArray(successMessages[field])) {
        successMessages[field][prop] = true;
        setTimeout(() => {
            successMessages[field][prop] = false;
        }, 3000);
    } else if (prop) {
        successMessages[field][prop] = true;
        setTimeout(() => {
            successMessages[field][prop] = false;
        }, 3000);
    } else {
        successMessages[field] = true;
        setTimeout(() => {
            successMessages[field] = false;
        }, 3000);
    }
};

// Fonctions pour gérer les notes
const editNote = (index) => {
    isEditing.notes[index] = true;
};

const saveNote = (index) => {
    isEditing.notes[index] = false;
    displaySuccessMessage("notes", index);
};

const closeAllNotes = () => {
    editableUser.notes.forEach((note, index) => {
        if (isEditing.notes[index]) {
            saveNote(index);
        }
    });
};

// Ajout de nouvelles notes
const showAddNote = ref(false);
const newNote = ref({
    content: "",
    date: new Date().toISOString().slice(0, 10),
});

const addNote = () => {
    showAddNote.value = true;
};

const saveNewNote = () => {
    if (newNote.value.content) {
        const now = new Date();
        const isoStringWithTime = now.toISOString(); // Format ISO inclut l'heure et les minutes

        editableUser.notes.push({
            content: newNote.value.content,
            date: isoStringWithTime,
        });
        newNote.value.content = ""; // Réinitialiser la nouvelle note
        showAddNote.value = false; // Cacher la zone d'ajout
        isEditing.notes.push(false);
        successMessages.notes.push(false);
    }
};



//Affichage des dates en DD-MM-YYYY
const formatDate = (dateString) => {
    const date = new Date(dateString);
    const day = String(date.getDate()).padStart(2, '0');
    const month = String(date.getMonth() + 1).padStart(2, '0'); // Les mois commencent à 0
    const year = date.getFullYear();
    return `${day}-${month}-${year}`;
};


//Affichage des dates en DD-MM-YYYY
const formatDateTime = (dateString) => {
    const date = new Date(dateString);
    const day = String(date.getDate()).padStart(2, '0');
    const month = String(date.getMonth() + 1).padStart(2, '0');
    const year = date.getFullYear();
    const hours = String(date.getHours()).padStart(2, '0');
    const minutes = String(date.getMinutes()).padStart(2, '0');

    return `${day}-${month}-${year} ${hours}:${minutes}`;
};


// Gérer l'affichage de l'historique
const showHistory = ref(false);
const toggleHistory = () => {
    showHistory.value = !showHistory.value;
};

// Montage et démontage
onMounted(() => {
    if (editableUser.notes.length > 0) {
        editableUser.notes.forEach(() => {
            isEditing.notes.push(false);
            successMessages.notes.push(false);
        });
    }
    document.addEventListener("click", handleClickOutside);
});

onBeforeUnmount(() => {
    document.removeEventListener("click", handleClickOutside);
});
</script>

<template>
    <div
        v-if="editableUser"
        class="bg-white overflow-hidden shadow rounded-lg border mt-8"
    >
        <!-- Section Notes -->
        <div class="pb-12 px-0 md:px-0">
            <div v-if="editableUser.notes.length > 0">
                <!-- Tableau des notes -->
                <div class="overflow-x-auto">
                    <table class="min-w-full bg-white">
                        <thead>
                            <tr class="bg-gray-100">
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
                            </tr>
                        </thead>
                        <tbody>
                            <tr
                                v-for="(note, index) in editableUser.notes"
                                :key="index"
                            >
                                <td
                                    class="py-2 px-4 border-b text-sm text-left text-gray-500 w-1/6"
                                >
                                        {{ formatDateTime(note.date) }}
                                </td>
                                <td
                                    class="py-2 px-4 border-b text-sm text-left text-gray-500 w-5/6"
                                >
                                    <span
                                        v-if="!isEditing.notes[index]"
                                        @click="editNote(index)"
                                        class="editable-text cursor-pointer"
                                    >
                                        {{ note.content }}
                                    </span>
                                    <textarea
                                        v-else
                                        v-model="note.content"
                                        @blur="saveNote(index)"
                                        class="editable-input mt-1 block w-full p-2 border-gray-300 rounded-md"
                                    ></textarea>
                                    <p
                                        v-if="successMessages.notes[index]"
                                        class="text-green-500 text-xs relative mt-1 success-message"
                                    >
                                        Modification avec succès
                                    </p>
                                </td>
                            </tr>
                            <tr v-if="editableUser.notes.length === 0">
                                <td
                                    colspan="2"
                                    class="py-5 px-4 border-b text-sm text-center text-gray-500"
                                >
                                    Aucune note pour ce fournisseur.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
            <div v-else>
                <p class="text-sm text-gray-400">
                    Aucune note actuellement pour ce fournisseur.
                </p>
            </div>

            <!-- Bouton d'ajout de note stylisé -->
            <div class="flex items-center justify-start mt-4 ml-2">
                <button
                    class="flex items-center text-sm text-gray-600 bg-white border border-gray-300 rounded-md px-4 py-2 hover:bg-gray-100 hover:border-gray-400 transition-colors duration-200"
                    @click="addNote"
                >
                    <i class="fa-solid fa-plus text-gray-500 mr-2"></i>
                    Ajouter une note
                </button>
            </div>

            <!-- Zone d'ajout de nouvelle note -->
            <div v-if="showAddNote" class="mt-4">
                <div class="bg-gray-50 p-4 rounded-md shadow-md">
                    <h4 class="text-gray-700 text-sm font-semibold mb-2">
                        Nouvelle note
                    </h4>
                    <input
                        type="text"
                        v-model="newNote.content"
                        class="block w-full px-3 py-2 border border-gray-300 rounded-md text-sm focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500"
                        placeholder="Saisir la note..."
                    />
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
        <div class="px-4 py-5 sm:px-6 bg-gray-100">
            <p class="mt-1 max-w-2xl text-sm text-gray-500 font-bold">
                Informations générales sur le fournisseur
            </p>
        </div>
        <div class="border-t border-gray-200 px-0 py-5 sm:p-0">
            <dl class="sm:divide-y sm:divide-gray-200">
                <!-- Première ligne : Prénom & Nom -->
                <div
                    class="py-3 sm:py-5 sm:grid sm:grid-cols-2 sm:gap-4 sm:px-6"
                >
                    <div class="sm:col-span-1 px-4 md:px-0">
                        <dt class="text-sm font-bold text-gray-500">Prénom</dt>
                        <dd class="mt-1 text-sm text-gray-400 sm:mt-0">
                            <span
                                v-if="!isEditing.prenom"
                                @click="editField('prenom')"
                                class="editable-text cursor-pointer"
                                >{{ editableUser.prenom }}</span
                            >
                            <input
                                v-else
                                ref="prenomInput"
                                v-model="editableUser.prenom"
                                @blur="saveField('prenom')"
                                class="editable-input mt-1 block w-full p-2 border-gray-300 rounded-md"
                            />
                            <p
                                v-if="successMessages.prenom"
                                class="text-green-500 text-xs mt-1 success-message"
                            >
                                Modification avec succès
                            </p>
                        </dd>
                    </div>
                    <div class="sm:col-span-1 px-4 md:px-0 mt-3 md:mt-0">
                        <dt class="text-sm text-gray-500 font-bold">Nom</dt>
                        <dd class="mt-1 text-sm text-gray-400 sm:mt-0">
                            <span
                                v-if="!isEditing.nom"
                                @click="editField('nom')"
                                class="editable-text cursor-pointer"
                                >{{ editableUser.nom }}</span
                            >
                            <input
                                v-else
                                ref="nomInput"
                                v-model="editableUser.nom"
                                @blur="saveField('nom')"
                                class="editable-input mt-1 block w-full p-2 border-gray-300 rounded-md"
                            />
                            <p
                                v-if="successMessages.nom"
                                class="text-green-500 text-xs mt-1 success-message"
                            >
                                Modification avec succès
                            </p>
                        </dd>
                    </div>
                </div>

                <!-- Deuxième ligne : E-mail & Numéro de téléphone -->
                <div
                    class="py-0 sm:py-5 sm:grid sm:grid-cols-2 sm:gap-4 sm:px-6"
                >
                    <div class="sm:col-span-1 px-4 md:px-0">
                        <dt class="text-sm font-bold text-gray-500">E-mail</dt>
                        <dd class="mt-1 text-sm text-gray-400 sm:mt-0">
                            <span
                                v-if="!isEditing.email"
                                @click="editField('email')"
                                class="editable-text cursor-pointer"
                                >{{ editableUser.email }}</span
                            >
                            <input
                                v-else
                                ref="emailInput"
                                v-model="editableUser.email"
                                @blur="saveField('email')"
                                class="editable-input mt-1 block w-full p-2 border-gray-300 rounded-md"
                            />
                            <p
                                v-if="successMessages.email"
                                class="text-green-500 text-xs mt-1 success-message"
                            >
                                Modification avec succès
                            </p>
                        </dd>
                    </div>
                    <div class="sm:col-span-1 px-4 md:px-0 mt-3 md:mt-0">
                        <dt class="text-sm font-bold text-gray-500">
                            Numéro de téléphone
                        </dt>
                        <dd class="mt-1 text-sm text-gray-400 sm:mt-0">
                            <span
                                v-if="!isEditing.telephone"
                                @click="editField('telephone')"
                                class="editable-text cursor-pointer"
                                >{{ editableUser.telephone }}</span
                            >
                            <input
                                v-else
                                ref="telephoneInput"
                                v-model="editableUser.telephone"
                                @blur="saveField('telephone')"
                                class="editable-input mt-1 block w-full p-2 border-gray-300 rounded-md"
                            />
                            <p
                                v-if="successMessages.telephone"
                                class="text-green-500 text-xs mt-1 success-message"
                            >
                                Modification avec succès
                            </p>
                        </dd>
                    </div>
                </div>

                <!-- Troisième ligne : Rue et numéro & Code postal -->
                <div class="px-4 py-5 sm:px-6 bg-gray-100">
                    <p class="mt-1 max-w-2xl text-sm text-gray-500 font-bold">
                        Adresse
                    </p>
                </div>
                <div
                    class="py-3 sm:py-5 sm:grid sm:grid-cols-2 sm:gap-4 sm:px-6"
                >
                    <div class="sm:col-span-1 px-4 md:px-0">
                        <dt class="text-sm font-bold text-gray-500">
                            Rue et numéro
                        </dt>
                        <dd class="mt-1 text-sm text-gray-400 sm:mt-0">
                            <span
                                v-if="!isEditing.adresse.rue"
                                @click="editField('adresse.rue')"
                                class="editable-text cursor-pointer"
                                >{{ editableUser.adresse.rue }}</span
                            >
                            <input
                                v-else
                                ref="rueInput"
                                v-model="editableUser.adresse.rue"
                                @blur="saveField('adresse.rue')"
                                class="editable-input mt-1 block w-full p-2 border-gray-300 rounded-md"
                            />
                            <p
                                v-if="successMessages.adresse.rue"
                                class="text-green-500 text-xs mt-1 success-message"
                            >
                                Modification avec succès
                            </p>
                        </dd>
                    </div>
                    <div class="sm:col-span-1 px-4 md:px-0 mt-3 md:mt-0">
                        <dt class="text-sm font-bold text-gray-500">
                            Code postal
                        </dt>
                        <dd class="mt-1 text-sm text-gray-400 sm:mt-0">
                            <span
                                v-if="!isEditing.adresse.codePostal"
                                @click="editField('adresse.codePostal')"
                                class="editable-text cursor-pointer"
                                >{{ editableUser.adresse.codePostal }}</span
                            >
                            <input
                                v-else
                                ref="codePostalInput"
                                v-model="editableUser.adresse.codePostal"
                                @blur="saveField('adresse.codePostal')"
                                class="editable-input mt-1 block w-full p-2 border-gray-300 rounded-md"
                            />
                            <p
                                v-if="successMessages.adresse.codePostal"
                                class="text-green-500 text-xs mt-1 success-message"
                            >
                                Modification avec succès
                            </p>
                        </dd>
                    </div>
                </div>

                <!-- Quatrième ligne : Localité & Pays -->
                <div
                    class="py-0 sm:py-5 sm:grid sm:grid-cols-2 sm:gap-4 sm:px-6"
                >
                    <div class="sm:col-span-1 px-4 md:px-0">
                        <dt class="text-sm font-bold text-gray-500">
                            Localité
                        </dt>
                        <dd class="mt-1 text-sm text-gray-400 sm:mt-0">
                            <span
                                v-if="!isEditing.adresse.localite"
                                @click="editField('adresse.localite')"
                                class="editable-text cursor-pointer"
                                >{{ editableUser.adresse.localite }}</span
                            >
                            <input
                                v-else
                                ref="localiteInput"
                                v-model="editableUser.adresse.localite"
                                @blur="saveField('adresse.localite')"
                                class="editable-input mt-1 block w-full p-2 border-gray-300 rounded-md"
                            />
                            <p
                                v-if="successMessages.adresse.localite"
                                class="text-green-500 text-xs mt-1 success-message"
                            >
                                Modification avec succès
                            </p>
                        </dd>
                    </div>
                    <div
                        class="sm:col-span-1 px-4 md:px-0 mt-3 md:mt-0 mb-4 md:mb-0"
                    >
                        <dt class="text-sm font-bold text-gray-500">Pays</dt>
                        <dd class="mt-1 text-sm text-gray-400 sm:mt-0">
                            <span
                                v-if="!isEditing.adresse.pays"
                                @click="editField('adresse.pays')"
                                class="editable-text cursor-pointer"
                                >{{ editableUser.adresse.pays }}</span
                            >
                            <input
                                v-else
                                ref="paysInput"
                                v-model="editableUser.adresse.pays"
                                @blur="saveField('adresse.pays')"
                                class="editable-input mt-1 block w-full p-2 border-gray-300 rounded-md"
                            />
                            <p
                                v-if="successMessages.adresse.pays"
                                class="text-green-500 text-xs mt-1 success-message"
                            >
                                Modification avec succès
                            </p>
                        </dd>
                    </div>
                </div>
            </dl>
        </div>
    </div>

    <!-- Bouton pour voir l'historique des transactions -->
    <button
        class="mt-12 text-sm bg-[rgb(0,85,150)] hover:bg-[rgb(5,121,198)] text-white font-bold py-2 px-4 rounded"
        @click="toggleHistory"
    >
        {{ showHistory ? "Cacher" : "Voir" }} l'historique des transactions
    </button>

    <!-- Section Historique -->
    <div v-if="showHistory" class="overflow-x-auto mt-6">
        <table class="min-w-full bg-white">
            <thead>
                <tr class="bg-gray-100">
                    <th
                        class="py-2 px-4 border-b text-left text-sm font-semibold text-gray-800"
                    >
                        Date
                    </th>
                    <th
                        class="py-2 px-4 border-b text-sm font-semibold text-gray-800 text-center"
                    >
                        Type de métaux
                    </th>
                    <th
                        class="py-2 px-4 border-b text-center text-sm font-semibold text-gray-800"
                    >
                        Poids (kg)
                    </th>
                </tr>
            </thead>
            <tbody>
                <tr
                    v-for="(record, index) in editableUser.historique"
                    :key="index"
                >
                    <td
                        class="py-2 px-4 border-b text-sm text-left text-gray-500"
                    >
                        {{ formatDate(record.date) }}
                    </td>
                    <td
                        class="py-2 px-4 border-b text-sm text-center text-gray-500"
                    >
                        {{ record.type }}
                    </td>
                    <td
                        class="py-2 px-4 border-b text-sm text-center text-gray-500"
                    >
                        {{ record.poids }}
                    </td>
                </tr>
                <tr v-if="!editableUser.historique.length">
                    <td
                        colspan="3"
                        class="py-5 px-4 border-b text-sm text-center text-gray-500"
                    >
                        Aucun historique pour ce fournisseur.
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</template>

<style scoped>
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
</style>
