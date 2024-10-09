<script setup>
import { ref, reactive, onMounted, onBeforeUnmount } from "vue";

// Props
const props = defineProps({
    user: {
        type: Object,
        required: true,
    },
});

// Fonction pour gérer le clic en dehors de l'input
const handleClickOutside = (event) => {
    if (
        !event.target.closest(".editable-input") &&
        !event.target.closest(".editable-text")
    ) {
        closeAllFields();
    }
};

// Réactif pour éditer l'utilisateur
const editableUser = reactive({ ...props.user });

// État pour savoir quel champ est en cours d'édition
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

// Fonction pour activer l'édition d'une note
const editNote = (index) => {
    isEditing.notes[index] = true;
};

const saveNote = (index) => {
    isEditing.notes[index] = false;
    successMessages.notes[index] = true;
    setTimeout(() => {
        successMessages.notes[index] = false;
    }, 3000);
};

// Fonction pour activer l'édition d'un champ
const editField = (field) => {
    if (field.includes("adresse")) {
        const [obj, prop] = field.split(".");
        isEditing[obj][prop] = true;
    } else {
        isEditing[field] = true;
    }
};

// Fonction pour fermer tous les champs d'édition
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

// Fonction pour sauvegarder le champ et désactiver l'édition
const saveField = (field) => {
    if (field.includes("adresse")) {
        const [obj, prop] = field.split(".");
        isEditing[obj][prop] = false;
        if (editableUser[obj][prop]) {
            successMessages[obj][prop] = true;
            setTimeout(() => {
                successMessages[obj][prop] = false;
            }, 3000);
        }
    } else {
        isEditing[field] = false;
        if (editableUser[field]) {
            successMessages[field] = true;
            setTimeout(() => {
                successMessages[field] = false;
            }, 3000);
        }
    }
};

// Gérer l'affichage de l'historique
const showHistory = ref(false);
const toggleHistory = () => {
    showHistory.value = !showHistory.value;
};

onMounted(() => {
    if (editableUser.notes && editableUser.notes.length > 0) {
        // Initialiser chaque note avec false pour isEditing et successMessages
        editableUser.notes.forEach(() => {
            isEditing.notes.push(false);  // Ajouter `false` pour chaque note
            successMessages.notes.push(false);  // Ajouter `false` pour chaque note
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
            <div v-if="editableUser.notes && editableUser.notes.length > 0">
                <!-- Tableau des notes -->
                <div class="overflow-x-auto">
                    <table class="min-w-full bg-white">
                        <thead>
                            <tr class="bg-gray-100">
                                <th class="py-2 px-4 border-b text-left text-sm font-semibold text-gray-800">
                                    Date
                                </th>
                                <th class="py-2 px-4 border-b text-left text-sm font-semibold text-gray-800">
                                    Note
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="(note, index) in editableUser.notes" :key="index">
                                <td class="py-2 px-4 border-b text-sm text-left text-gray-500">
                                    {{ note.date }}
                                </td>
                                <td class="py-2 px-4 border-b text-sm text-left text-gray-500">
                                    <!-- Modification ici : on vérifie que isEditing.notes[index] existe avant de l'utiliser -->
                                    <span v-if="!isEditing.notes[index]" @click="editNote(index)" class="editable-text cursor-pointer">
                                        {{ note.content }}
                                    </span>
                                    <textarea
                                        v-else
                                        v-model="note.content"
                                        @blur="saveNote(index)"
                                        class="editable-input mt-1 block w-full p-2 border-gray-300 rounded-md"
                                    ></textarea>
                                    <p v-if="successMessages.notes[index]" class="text-green-500 text-xs mt-1 success-message">
                                        Modification avec succès
                                    </p>
                                </td>
                            </tr>
                            <tr v-if="editableUser.notes.length === 0">
                                <td colspan="2" class="py-5 px-4 border-b text-sm text-center text-gray-500">
                                    Aucune note pour ce fournisseur.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
            <div v-else>
                <p class="text-sm text-gray-400">Aucune note actuellement pour ce fournisseur.</p>
            </div>
        </div>

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
                        {{ record.date }}
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
