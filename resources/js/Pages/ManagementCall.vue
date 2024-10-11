<script setup>
import { ref, computed, watch } from "vue";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head } from "@inertiajs/vue3";
import UserDetails from "./ManagementCall/Partials/UserDetails.vue";
import UserList from "./ManagementCall/Partials/UserList.vue";
import AddUserModal from "./ManagementCall/Partials/AddUserModal.vue";

// Les clients sont passés via Inertia
const props = defineProps(['clients']);

// Les données des clients à partir des props
const users = ref(props.clients);

// Terme de recherche
const searchTerm = ref("");

// Utilisateur sélectionné
const selectedUser = ref(null);

// Propriété calculée pour filtrer les utilisateurs en fonction du terme de recherche
const filteredUsers = computed(() => {
  return users.value.filter((user) => {
    const searchLower = searchTerm.value.toLowerCase();
    return (
      (user.fullName ? user.fullName.toLowerCase().includes(searchLower) : false) ||
      (user.familyName ? user.familyName.toLowerCase().includes(searchLower) : false) ||
      (user.firstName ? user.firstName.toLowerCase().includes(searchLower) : false) ||
      (user.address ? user.address.toLowerCase().includes(searchLower) : false) ||
      (user.locality ? user.locality.toLowerCase().includes(searchLower) : false) ||
      (user.postalCode ? user.postalCode.toLowerCase().includes(searchLower) : false) ||
      (user.country ? user.country.toLowerCase().includes(searchLower) : false) ||
      (user.email ? user.email.toLowerCase().includes(searchLower) : false) ||
      (user.phone ? user.phone.toLowerCase().includes(searchLower) : false) ||
      (user.company ? user.company.toLowerCase().includes(searchLower) : false)
    );
  });
});

// Fonction pour sélectionner un utilisateur
const selectUser = (user) => {
  selectedUser.value = user;
};

// Réinitialiser selectedUser si searchTerm change
watch(searchTerm, () => {
  selectedUser.value = null;
});

// État du modal pour ajouter un utilisateur
const showModal = ref(false);

// Fonction pour ouvrir et fermer le modal
const toggleModal = () => {
  showModal.value = !showModal.value;
};

// Nouvel utilisateur à ajouter
const newUser = ref({
  fullName: "",
  familyName: "",
  firstName: "",
  address: "",
  locality: "",
  postalCode: "",
  country: "",
  email: "",
  phone: "",
  company: "",
  companyvat: "",
  regdate: "",
  notes: [],
  transactions: []
});

// Fonction pour ajouter un nouvel utilisateur
const addUser = () => {
  const addedUser = { ...newUser.value };
  users.value.push(addedUser);
  newUser.value = {
    fullName: "",
    familyName: "",
    firstName: "",
    address: "",
    locality: "",
    postalCode: "",
    country: "",
    email: "",
    phone: "",
    company: "",
    companyvat: "",
    regdate: "",
    notes: [],
    transactions: []
  };
  toggleModal();
  selectedUser.value = null;

  // Sélectionner le nouvel utilisateur
  setTimeout(() => {
    selectUser(addedUser);
  }, 0);
};

// Fonction pour mettre à jour l'utilisateur dans la liste
const updateUserInList = (updatedUser) => {
  // Trouver l'index de l'utilisateur dans la liste
  const index = users.value.findIndex(user => user.id === updatedUser.id);

  if (index !== -1) {
    // Mettre à jour l'utilisateur dans la liste
    users.value[index] = { ...updatedUser };
  }

  // Mettre à jour selectedUser si nécessaire
  if (selectedUser.value && selectedUser.value.id === updatedUser.id) {
    selectedUser.value = users.value[index];
  }
};
</script>

<template>
  <Head title="Gestion des appels téléphoniques" />

  <AuthenticatedLayout>
    <template #header>
      <h2 class="font-semibold text-xl text-white bg-gray-800 leading-tight">
        Gestion des appels téléphoniques
      </h2>
    </template>

    <div class="container mx-auto flex flex-col items-center justify-center px-0 md:px-4 sm:px-8 max-w-7xl mt-2 md:mt-8">
      <div class="w-full max-w-7xl mt-0 mx-auto px-0">
        <div class="flex justify-center p-4 px-3 py-10">
          <div class="w-full">
            <div class="bg-white shadow-md rounded-lg px-3 py-4 pb-6 mb-4">
              <!-- Conteneur pour le texte "Rechercher un client" et le bouton -->
              <div class="flex flex-row justify-between items-center mb-1 md:mb-6">
                <!-- Rechercher un client -->
                <div class="text-gray-700 pt-4 md:pt-0 text-sm md:text-lg font-semibold mb-4 sm:mb-0">
                  Rechercher un fournisseur
                </div>
                <!-- Bouton Ajouter un client -->
                <button
                  @click="toggleModal"
                  class="text-xs md:text-sm bg-gray-900 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded"
                >
                  Encoder un appel
                </button>
              </div>

              <!-- BARRE DE RECHERCHE -->
              <div class="flex items-center bg-[rgb(237,242,247)] rounded-md mb-4">
                <div class="pl-2">
                  <svg class="fill-current text-gray-400 w-6 h-6" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
                    <path class="heroicon-ui" d="M16.32 14.9l5.39 5.4a1 1 0 0 1-1.42 1.4l-5.38-5.38a8 8 0 1 1 1.41-1.41zM10 16a6 6 0 1 0 0-12 6 6 0 0 0 0 12z" />
                  </svg>
                </div>
                <input
                  v-model="searchTerm"
                  class="w-full rounded-md text-sm md:text-base bg-[rgb(237,242,247)] text-gray-500 leading-tight focus:outline-none focus:ring-0 border-none py-3 px-2 placeholder-gray-500 placeholder-opacity-60"
                  id="search"
                  type="text"
                  placeholder="Tapez le numéro de téléphone, nom, e-mail ou pays pour chercher"
                />
              </div>

              <!-- Affichage de la liste d'utilisateurs -->
              <UserList v-if="!selectedUser && searchTerm.length > 0" :filteredUsers="filteredUsers" :selectUser="selectUser" />

              <!-- Détails de l'utilisateur sélectionné -->
              <UserDetails 
                v-if="selectedUser" 
                :user="selectedUser" 
                @user-updated="updateUserInList" 
              />

              <!-- Modal d'ajout de client -->
              <AddUserModal :showModal="showModal" :newUser="newUser" @toggleModal="toggleModal" @addUser="addUser" />
            </div>
          </div>
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>

<style scoped>
/* Pour s'assurer que le bouton reste à droite en version mobile également */
@media (max-width: 640px) {
  .flex-row {
    flex-direction: row;
  }
}
</style>
