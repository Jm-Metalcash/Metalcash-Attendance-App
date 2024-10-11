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
              <tr v-for="(note, index) in editableUser.notes" :key="index">
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
          <h4 class="text-gray-700 text-sm font-semibold mb-2">Nouvelle note</h4>
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
    <div class="px-4 py-5 sm:px-6 bg-gray-200">
      <p class="mt-1 max-w-2xl text-sm text-gray-500 font-bold">
        Informations générales sur le fournisseur
      </p>
    </div>
    <div class="border-t border-gray-200 px-0 py-5 sm:p-0">
      <dl class="sm:divide-y sm:divide-gray-200">
        <!-- Première ligne : Prénom & Nom -->
        <div class="py-3 sm:py-5 sm:grid sm:grid-cols-2 sm:gap-4 sm:px-6">
          <div class="sm:col-span-1 px-4 md:px-0">
            <dt class="text-sm font-bold text-gray-500">Prénom</dt>
            <dd class="mt-1 text-sm text-gray-400 sm:mt-0">
              <span
                v-if="!isEditing.firstName"
                @click="editField('firstName')"
                class="editable-text cursor-pointer hover:text-gray-500"
              >
                {{ editableUser.firstName || "Ajouter un prénom" }}
              </span>
              <input
                v-else
                v-model="editableUser.firstName"
                @blur="saveField('firstName')"
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
                {{ editableUser.familyName || "Ajouter un nom" }}
              </span>
              <input
                v-else
                v-model="editableUser.familyName"
                @blur="saveField('familyName')"
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
        <div class="py-0 sm:py-5 sm:grid sm:grid-cols-2 sm:gap-4 sm:px-6">
          <div class="sm:col-span-1 px-4 md:px-0">
            <dt class="text-sm font-bold text-gray-500">E-mail</dt>
            <dd class="mt-1 text-sm text-gray-400 sm:mt-0">
              <span
                v-if="!isEditing.email"
                @click="editField('email')"
                class="editable-text cursor-pointer hover:text-gray-500"
              >
                {{ editableUser.email || "Ajouter une adresse e-mail" }}
              </span>
              <input
                v-else
                v-model="editableUser.email"
                @blur="saveField('email')"
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
            <dt class="text-sm font-bold text-gray-500">Numéro de téléphone</dt>
            <dd class="mt-1 text-sm text-gray-400 sm:mt-0">
              <span
                v-if="!isEditing.phone"
                @click="editField('phone')"
                class="editable-text cursor-pointer hover:text-gray-500"
              >
                {{ editableUser.phone || "Ajouter un numéro de téléphone" }}
              </span>
              <input
                v-else
                v-model="editableUser.phone"
                @blur="saveField('phone')"
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
          <p class="mt-1 max-w-2xl text-sm text-gray-500 font-bold">Adresse</p>
        </div>
        <div class="py-3 sm:py-5 sm:grid sm:grid-cols-2 sm:gap-4 sm:px-6">
          <div class="sm:col-span-1 px-4 md:px-0">
            <dt class="text-sm font-bold text-gray-500">Rue et numéro</dt>
            <dd class="mt-1 text-sm text-gray-400 sm:mt-0">
              <span
                v-if="!isEditing.address"
                @click="editField('address')"
                class="editable-text cursor-pointer hover:text-gray-500"
              >
                {{ editableUser.address || "Ajouter une adresse" }}
              </span>
              <input
                v-else
                v-model="editableUser.address"
                @blur="saveField('address')"
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
            <dt class="text-sm font-bold text-gray-500">Code postal</dt>
            <dd class="mt-1 text-sm text-gray-400 sm:mt-0">
              <span
                v-if="!isEditing.postalCode"
                @click="editField('postalCode')"
                class="editable-text cursor-pointer hover:text-gray-500"
              >
                {{ editableUser.postalCode || "Ajouter un code postal" }}
              </span>
              <input
                v-else
                v-model="editableUser.postalCode"
                @blur="saveField('postalCode')"
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
        <div class="py-0 sm:py-5 sm:grid sm:grid-cols-2 sm:gap-4 sm:px-6">
          <div class="sm:col-span-1 px-4 md:px-0">
            <dt class="text-sm font-bold text-gray-500">Localité</dt>
            <dd class="mt-1 text-sm text-gray-400 sm:mt-0">
              <span
                v-if="!isEditing.locality"
                @click="editField('locality')"
                class="editable-text cursor-pointer hover:text-gray-500"
              >
                {{ editableUser.locality || "Ajouter une localité" }}
              </span>
              <input
                v-else
                v-model="editableUser.locality"
                @blur="saveField('locality')"
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
          <div class="sm:col-span-1 px-4 md:px-0 mt-3 md:mt-0 mb-4 md:mb-0">
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
    <div v-if="showHistory" class="overflow-x-auto mt-0">
      <table class="min-w-full bg-white">
        <thead>
          <tr class="bg-gray-200">
            <th
              class="py-2 px-4 border-b text-left text-sm font-semibold text-gray-800 w-1/3"
            >
              Date
            </th>
            <th
              class="py-2 px-4 border-b text-center text-sm font-semibold text-gray-800 w-1/3"
            >
              Type de métaux
            </th>
            <th
              class="py-2 px-4 border-b text-right text-sm font-semibold text-gray-800 w-1/3 lg:pr-24"
            >
              Poids (kg)
            </th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="(record, index) in editableUser.transactions" :key="index">
            <td class="py-2 px-4 border-b text-sm text-left text-gray-500">
              {{ formatDateTime(record.date) }}
            </td>
            <td class="py-2 px-4 border-b text-sm text-center text-gray-500">
              {{ record.typeofmetal }}
            </td>
            <td class="py-2 px-4 border-b text-sm text-right text-gray-500 lg:pr-28">
              {{ record.weight }}
            </td>
          </tr>
          <tr v-if="!editableUser.transactions.length">
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
  </div>
</template>

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
const editableUser = reactive({
  ...props.user,
  notes: props.user.notes || [],
  transactions: props.user.transactions || [],
});

// Initialiser isEditingNotes comme un tableau réactif
const isEditingNotes = reactive(editableUser.notes.map(() => false));

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
  notes: reactive(editableUser.notes.map(() => false)),
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

// Fonctions pour gérer les champs d'édition
const editField = (field) => {
  isEditing[field] = true;
};

const saveField = (field) => {
  isEditing[field] = false;
  displaySuccessMessage(field);
};

const closeAllFields = () => {
  Object.keys(isEditing).forEach((field) => {
    if (isEditing[field]) {
      saveField(field);
    }
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

const saveNote = (index) => {
  isEditingNotes[index] = false;
  displayNoteSuccessMessage(index);
};

const closeAllNotes = () => {
  editableUser.notes.forEach((note, index) => {
    if (isEditingNotes[index]) {
      saveNote(index);
    }
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

const addNote = () => {
  showAddNote.value = true;
};

const saveNewNote = () => {
  if (newNote.value.content) {
    const now = new Date().toISOString();

    editableUser.notes.push({
      content: newNote.value.content,
      note_date: now,
    });

    newNote.value.content = "";
    showAddNote.value = false;

    // Mettre à jour isEditingNotes et successMessages.notes
    isEditingNotes.push(false);
    successMessages.notes.push(false);

    // Afficher le message de succès
    showAddNoteSuccess.value = true;
    setTimeout(() => {
      showAddNoteSuccess.value = false;
    }, 3000);
  }
};

// Affichage des dates en format DD-MM-YYYY | HH:mm
const formatDateTime = (dateString) => {
  const date = new Date(dateString);
  const day = String(date.getDate()).padStart(2, "0");
  const month = String(date.getMonth() + 1).padStart(2, "0");
  const year = date.getFullYear();
  const hours = String(date.getHours()).padStart(2, "0");
  const minutes = String(date.getMinutes()).padStart(2, "0");

  return `${day}-${month}-${year} | ${hours}:${minutes}`;
};

// Gérer l'affichage de l'historique
const showHistory = ref(false);
const toggleHistory = () => {
  showHistory.value = !showHistory.value;
};

onMounted(() => {
  document.addEventListener("click", handleClickOutside);
});

onBeforeUnmount(() => {
  document.removeEventListener("click", handleClickOutside);
});
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
</style>
