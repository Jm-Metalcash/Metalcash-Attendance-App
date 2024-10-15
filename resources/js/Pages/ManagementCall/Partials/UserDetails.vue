<template>
    <div class="bg-white overflow-hidden shadow rounded-lg border mt-8">
        <!-- Section Notes -->
        <div class="pb-12 px-0 md:px-0">
            <div v-if="editableUser.notes && editableUser.notes.length > 0">
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
                            </tr>
                        </thead>
                        <tbody>
                            <tr
                                v-for="(note, index) in editableUser.notes"
                                :key="note.id"
                            >
                                <td
                                    class="py-2 px-4 border-b text-sm text-left text-gray-500 w-1/6"
                                >
                                    {{ formatDateTime(note.note_date) }}
                                </td>
                                <td
                                    class="py-2 px-4 border-b text-sm text-left text-gray-500 w-5/6"
                                >
                                    <!-- Affichage du contenu de la note -->
                                    <span
                                        v-if="!isEditingNotes[index]"
                                        @click="editNote(index)"
                                        class="editable-text cursor-pointer"
                                    >
                                        {{ note.content }}
                                    </span>

                                    <!-- Champ textarea lorsque la note est en mode édition -->
                                    <textarea
                                        v-else
                                        v-model="note.content"
                                        @blur="saveNote(index)"
                                        @keydown.enter="saveNote(index)"
                                        @mousedown="mouseDown = true"
                                        class="editable-input mt-1 block w-full p-2 border-gray-300 rounded-md"
                                    ></textarea>

                                    <!-- Message de succès après modification -->
                                    <p
                                        v-if="successMessages.notes[index]"
                                        class="text-green-500 text-xs relative mt-1 success-message"
                                    >
                                        Enregistré avec succès
                                    </p>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
            <div v-else>
                <p class="text-sm text-gray-400 px-2 pt-4">
                    Aucune note actuellement pour ce fournisseur.
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
            <div class="flex items-center justify-start mt-4 ml-2">
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
                    <input
                        type="text"
                        v-model="newNote.content"
                        @keydown.enter="saveNewNote"
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
        <div class="px-4 py-5 sm:px-6 bg-gray-200">
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
                                v-if="!isEditing.firstName"
                                @click="editField('firstName')"
                                class="editable-text cursor-pointer hover:text-gray-500"
                            >
                                {{
                                    editableUser.firstName ||
                                    "Ajouter un prénom"
                                }}
                            </span>
                            <input
                                v-else
                                v-model="editableUser.firstName"
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
                                    editableUser.familyName || "Ajouter un nom"
                                }}
                            </span>
                            <input
                                v-else
                                v-model="editableUser.familyName"
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
                                class="editable-text cursor-pointer hover:text-gray-500"
                            >
                                {{
                                    editableUser.email ||
                                    "Ajouter une adresse e-mail"
                                }}
                            </span>
                            <input
                                v-else
                                v-model="editableUser.email"
                                @blur="saveField('email')"
                                @keydown.enter="saveField('email')"
                                class="editable-input mt-1 block w-full p-2 border-gray-300 rounded-md"
                            />
                            <p
                                v-if="successMessages.email"
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
                                    editableUser.phone ||
                                    "Ajouter un numéro de téléphone"
                                }}
                            </span>
                            <input
                                v-else
                                v-model="editableUser.phone"
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

                <!-- Troisième ligne : Rue et numéro & Code postal -->
                <div class="px-4 py-5 sm:px-6 bg-gray-200">
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
                                v-if="!isEditing.address"
                                @click="editField('address')"
                                class="editable-text cursor-pointer hover:text-gray-500"
                            >
                                {{
                                    editableUser.address ||
                                    "Ajouter une adresse"
                                }}
                            </span>
                            <input
                                v-else
                                v-model="editableUser.address"
                                @blur="saveField('address')"
                                @keydown.enter="saveField('address')"
                                class="editable-input mt-1 block w-full p-2 border-gray-300 rounded-md"
                            />
                            <p
                                v-if="successMessages.address"
                                class="text-green-500 text-xs mt-1 success-message"
                            >
                                Enregistré avec succès
                            </p>
                        </dd>
                    </div>
                    <div class="sm:col-span-1 px-4 md:px-0 mt-3 md:mt-0">
                        <dt class="text-sm font-bold text-gray-500">
                            Code postal
                        </dt>
                        <dd class="mt-1 text-sm text-gray-400 sm:mt-0">
                            <span
                                v-if="!isEditing.postalCode"
                                @click="editField('postalCode')"
                                class="editable-text cursor-pointer hover:text-gray-500"
                            >
                                {{
                                    editableUser.postalCode ||
                                    "Ajouter un code postal"
                                }}
                            </span>
                            <input
                                v-else
                                v-model="editableUser.postalCode"
                                @blur="saveField('postalCode')"
                                @keydown.enter="saveField('postalCode')"
                                class="editable-input mt-1 block w-full p-2 border-gray-300 rounded-md"
                            />
                            <p
                                v-if="successMessages.postalCode"
                                class="text-green-500 text-xs mt-1 success-message"
                            >
                                Enregistré avec succès
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
                                v-if="!isEditing.locality"
                                @click="editField('locality')"
                                class="editable-text cursor-pointer hover:text-gray-500"
                            >
                                {{
                                    editableUser.locality ||
                                    "Ajouter une localité"
                                }}
                            </span>
                            <input
                                v-else
                                v-model="editableUser.locality"
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
                                {{ editableUser.country || "Ajouter un pays" }}
                            </span>
                            <input
                                v-else
                                v-model="editableUser.country"
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
            </dl>
        </div>

        <!-- Bouton pour voir l'historique des transactions -->
        <button
            class="mt-12 text-sm bg-[rgb(0,85,150)] hover:bg-[rgb(5,121,198)] text-white font-bold py-2 px-4 rounded mx-4 mb-6"
            @click="toggleHistory"
        >
            {{ showHistory ? "Cacher" : "Voir" }} l'historique des transactions
        </button>

        <!-- Section Historique -->
        <div
            v-if="
                showHistory &&
                editableUser.transactions &&
                editableUser.transactions.length > 0
            "
        >
            <!-- Tableau des transactions -->
            <div class="overflow-x-auto">
                <table class="min-w-full bg-white">
                    <thead>
                        <tr class="bg-gray-200">
                            <th
                                class="py-2 px-4 border-b text-left text-sm font-semibold text-gray-600 w-1/3"
                            >
                                Date
                            </th>
                            <th
                                class="py-2 px-4 border-b text-center text-sm font-semibold text-gray-600 w-1/3"
                            >
                                Type de métal
                            </th>
                            <th
                                class="py-2 px-4 border-b text-center text-sm font-semibold text-gray-600 w-1/3 lg:pr-24"
                            >
                                Poids (kg)
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr
                            v-for="(
                                transaction, index
                            ) in editableUser.transactions"
                            :key="transaction.id"
                        >
                            <td
                                class="py-2 px-4 border-b text-sm text-left text-gray-500"
                            >
                                {{ formatDateOnly(transaction.date) }}
                            </td>
                            <td
                                class="py-2 px-4 border-b text-sm text-center text-gray-500"
                            >
                                <span
                                    v-if="!isEditingTransactions[index]"
                                    @click="editTransaction(index)"
                                    class="editable-text cursor-pointer"
                                >
                                    {{ transaction.typeofmetal }}
                                </span>
                                <input
                                    v-else
                                    v-model="transaction.typeofmetal"
                                    @blur="saveTransaction(index)"
                                    @keydown.enter="saveTransaction(index)"
                                    class="editable-input mt-1 block w-full p-2 border-gray-300 rounded-md"
                                />
                            </td>
                            <td
                                class="py-2 px-4 border-b text-sm text-center text-gray-500 lg:pr-28"
                            >
                                <span
                                    v-if="!isEditingTransactions[index]"
                                    @click="editTransaction(index)"
                                    class="editable-text cursor-pointer"
                                >
                                    {{ transaction.weight }}
                                </span>
                                <input
                                    v-else
                                    v-model="transaction.weight"
                                    @blur="saveTransaction(index)"
                                    @keydown.enter="saveTransaction(index)"
                                    class="editable-input mt-1 block w-full p-2 border-gray-300 rounded-md"
                                />
                            </td>
                        </tr>
                        <tr v-if="editableUser.transactions.length < 0">
                            <td
                                colspan="3"
                                class="py-5 px-4 border-b text-sm text-center text-gray-500"
                            >
                                Aucun historique pour ce fournisseur.
                            </td>
                        </tr>
                        <!-- Message de succès après ajout d'une transaction -->
                        <p
                            v-if="successMessages.transactions"
                            class="text-green-500 text-sm mx-auto success-message relative md:right-[30%] lg:right-[40%] xl:right-[45%] hidden md:block"
                        >
                            La transaction a été ajoutée avec succès !
                        </p>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Formulaire pour ajouter une nouvelle transaction -->
        <div v-if="showHistory" class="mt-4">
            <div class="flex items-center justify-start mt-4 ml-2 mb-6">
                <!-- Bouton pour afficher le formulaire d'ajout si showAddTransaction est false -->
                <button
                    v-if="!showAddTransaction"
                    class="flex items-center text-sm text-gray-600 bg-white border border-gray-300 rounded-md px-4 py-2 hover:bg-gray-100 hover:border-gray-400 transition-colors duration-200"
                    @click="showAddTransaction = true"
                >
                    <i class="fa-solid fa-plus text-gray-500 mr-2"></i>
                    Ajouter une transaction
                </button>

                <!-- Bouton pour réduire le formulaire si showAddTransaction est true -->
                <button
                    v-if="showAddTransaction"
                    class="flex items-center text-sm text-gray-600 bg-white border border-gray-300 rounded-md px-4 py-2 hover:bg-gray-100 hover:border-gray-400 transition-colors duration-200"
                    @click="showAddTransaction = false"
                >
                    <i class="fa-solid fa-minus text-gray-500 mr-2"></i>
                    Réduire le formulaire
                </button>
            </div>

            <!-- Formulaire d'ajout de transaction -->
            <div
                v-if="showAddTransaction"
                class="bg-gray-50 p-4 rounded-md shadow-md"
            >
                <h4 class="text-gray-700 text-sm font-semibold mb-2">
                    Nouvelle transaction
                </h4>
                <input
                    type="text"
                    v-model="newTransaction.typeofmetal"
                    class="block w-full px-3 py-2 mb-2 border border-gray-300 rounded-md text-sm"
                    placeholder="Type de métal"
                />
                <input
                    type="number"
                    v-model="newTransaction.weight"
                    class="block w-full px-3 py-2 mb-2 border border-gray-300 rounded-md text-sm"
                    placeholder="Poids en kg"
                />
                <button
                    @click="saveNewTransaction"
                    class="mt-3 bg-blue-600 text-white rounded-md px-4 py-2 text-sm hover:bg-blue-700 transition-colors duration-200"
                >
                    Enregistrer la transaction
                </button>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, reactive, onMounted } from "vue";
import axios from "axios";

// Props
const props = defineProps({
    user: {
        type: Object,
        required: true,
    },
});

// Émission d'événements
const emit = defineEmits(["user-updated"]);

// État réactif pour l'utilisateur modifiable
const editableUser = reactive({
    ...props.user,
    notes: props.user.notes || [],
    transactions: props.user.transactions || [],
});

// Initialiser isEditingNotes comme un tableau réactif
const isEditingNotes = reactive([]);

// Réactif pour afficher les messages de succès
const successMessages = reactive({
    firstName: false,
    familyName: false,
    email: false,
    phone: false,
    address: false,
    postalCode: false,
    locality: false,
    country: false,
    notes: [],
    transactions: false,
});

// Initialiser isEditingNotes et successMessages.notes
const initializeNotesState = () => {
    isEditingNotes.splice(
        0,
        isEditingNotes.length,
        ...editableUser.notes.map(() => false)
    );
    successMessages.notes.splice(
        0,
        successMessages.notes.length,
        ...editableUser.notes.map(() => false)
    );
};

onMounted(() => {
    initializeNotesState();
    initializeTransactionsState();
});

// État réactif pour savoir quel champ est en cours d'édition
const isEditing = reactive({
    firstName: false,
    familyName: false,
    email: false,
    phone: false,
    address: false,
    postalCode: false,
    locality: false,
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
        .put(`/clients/${editableUser.id}`, {
            [field]: editableUser[field],
        })
        .then((response) => {
            // Mettre à jour editableUser avec les données du serveur
            Object.assign(editableUser, response.data);

            // Émettre l'événement avec les données mises à jour
            emit("user-updated", JSON.parse(JSON.stringify(editableUser)));

            displaySuccessMessage(field);
        })
        .catch((error) => {
            console.error("Failed to update the client:", error);
        });
};


const displaySuccessMessage = (field) => {
    successMessages[field] = true;
    setTimeout(() => {
        successMessages[field] = false;
    }, 3000);
};

// Fonctions pour gérer les notes
const editNote = (index) => {
    isEditingNotes[index] = true;
};

// Enregistre une note avec Axios vers la DB
const saveNote = (index) => {
    isEditingNotes[index] = false;

    // Envoyer une requête PUT à l'API pour mettre à jour la note
    axios
        .put(
            `/clients/${editableUser.id}/notes/${editableUser.notes[index].id}`,
            {
                content: editableUser.notes[index].content,
            }
        )
        .then(() => {
            displayNoteSuccessMessage(index);
        })
        .catch((error) => {
            console.error("Failed to update the note:", error);
        });
};

const displayNoteSuccessMessage = (index) => {
    successMessages.notes[index] = true;
    setTimeout(() => {
        successMessages.notes[index] = false;
    }, 3000);
};

// Ajout de nouvelles notes
const showAddNoteSuccess = ref(false);
const showAddNote = ref(false);
const newNote = ref({
    content: "",
    note_date: new Date().toISOString(),
});

const saveNewNote = () => {
    if (newNote.value.content) {
        const now = new Date().toISOString();

        axios
            .post(`/clients/${editableUser.id}/notes`, {
                content: newNote.value.content,
                note_date: now,
            })
            .then((response) => {
                // Ajouter la nouvelle note avec l'ID retourné par le serveur
                editableUser.notes.push({
                    id: response.data.id,
                    content: newNote.value.content,
                    note_date: now,
                });

                // Mettre à jour isEditingNotes et successMessages.notes
                isEditingNotes.push(false);
                successMessages.notes.push(false);

                newNote.value.content = "";
                showAddNote.value = false;

                // Afficher le message de succès
                showAddNoteSuccess.value = true;
                setTimeout(() => {
                    showAddNoteSuccess.value = false;
                }, 3000);
            })
            .catch((error) => {
                console.error("Failed to add the note:", error);
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

// Fonction de formatage de la date sans l'heure (pour les transactions)
const formatDateOnly = (dateString) => {
    const date = new Date(dateString);

    // Si la date n'est pas valide, retourner une chaîne vide
    if (isNaN(date.getTime())) {
        return "";
    }

    // Formatage pour n'afficher que la date
    const options = {
        timeZone: "Europe/Paris", // Remplace par le fuseau souhaité
        day: "2-digit",
        month: "2-digit",
        year: "numeric",
    };

    return new Intl.DateTimeFormat("fr-FR", options).format(date);
};

// Gérer l'affichage de l'historique
const showHistory = ref(false);
const toggleHistory = () => {
    showHistory.value = !showHistory.value;
};

const isEditingTransactions = reactive([]);

// Initialiser isEditingTransactions
const initializeTransactionsState = () => {
    isEditingTransactions.splice(
        0,
        isEditingTransactions.length,
        ...editableUser.transactions.map(() => false)
    );
};

// Méthode pour basculer en mode édition d'une transaction
const editTransaction = (index) => {
    isEditingTransactions[index] = true;
};

// Méthode pour sauvegarder une transaction modifiée
const saveTransaction = (index) => {
    isEditingTransactions[index] = false;

    // Envoyer la requête PUT pour mettre à jour la transaction
    axios
        .put(
            `/clients/${editableUser.id}/transactions/${editableUser.transactions[index].id}`,
            {
                typeofmetal: editableUser.transactions[index].typeofmetal,
                weight: editableUser.transactions[index].weight,
                date: editableUser.transactions[index].date,
                details: editableUser.transactions[index].details,
            }
        )
        .then(() => {
            console.log("Transaction mise à jour avec succès");

            // Afficher le message de succès
            successMessages.transactions = true;
            setTimeout(() => {
                successMessages.transactions = false;
            }, 3000);
        })
        .catch((error) => {
            console.error(
                "Erreur lors de la mise à jour de la transaction :",
                error
            );
        });
};

// Formulaire de nouvelle transaction
const newTransaction = ref({
    typeofmetal: "",
    weight: "",
    date: new Date().toISOString(),
    details: "",
});

//affiche le form pour ajouter une nouvelle transaction
const showAddTransaction = ref(false);

// Méthode pour sauvegarder une nouvelle transaction
const saveNewTransaction = () => {
    const now = new Date().toISOString(); // Stockage en UTC

    axios
        .post(`/clients/${editableUser.id}/transactions`, {
            typeofmetal: newTransaction.value.typeofmetal,
            weight: newTransaction.value.weight,
            date: now, // Enregistrer la date au format UTC
            details: newTransaction.value.details,
        })
        .then((response) => {
            // Ajouter la nouvelle transaction avec l'ID retourné par le serveur
            editableUser.transactions.push({
                id: response.data.id,
                typeofmetal: newTransaction.value.typeofmetal,
                weight: newTransaction.value.weight,
                date: now, // Utiliser la même date pour le stockage
                details: newTransaction.value.details,
            });

            // Réinitialiser le formulaire
            newTransaction.value.typeofmetal = "";
            newTransaction.value.weight = "";
            newTransaction.value.date = new Date().toISOString();
            newTransaction.value.details = "";

            // Cacher le formulaire
            showAddTransaction.value = false;

            // Afficher le message de succès
            successMessages.transactions = true;
            setTimeout(() => {
                successMessages.transactions = false;
            }, 3000);
        })
        .catch((error) => {
            console.error("Erreur lors de l'ajout de la transaction :", error);
        });
};

</script>

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

.editable-input {
    border: 1px solid #ccc;
    padding: 8px;
    width: 100%;
    border-radius: 4px;
}
</style>
