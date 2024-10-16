<template>
    <div v-if="filteredUsers.length > 0" class="py-3 text-sm">
        <div
            v-for="(user, index) in filteredUsers"
            :key="index"
            @click="selectUser(user)"
            class="flex flex-wrap justify-between items-center cursor-pointer text-gray-700 hover:text-blue-400 hover:bg-blue-100 rounded-md px-2 py-2 my-2 w-full"
        >
            <!-- Colonne Nom complet (avec indicateur de statut à gauche) -->
            <div class="flex w-full sm:w-1/6 items-center mb-2 sm:mb-0">
                <span
                    :class="user.blacklist === 0 ? 'bg-green-400' : 'bg-red-400'"
                    class="h-2 w-2 mr-4 rounded-full"
                ></span>
                <div class="font-medium text-left">{{ user.firstName }} {{ user.familyName }}</div>
            </div>
  
            <!-- Colonne Numéro de Téléphone -->
            <div class="flex w-full sm:w-1/6 mb-2 sm:mb-0">
                <div class="font-medium text-left w-full ml-6 md:ml-0">{{ user.phone }}</div>
            </div>
  
            <!-- Colonne Pays -->
            <div class="flex w-full sm:w-1/6">
                <div class="text-sm font-normal text-left text-gray-500 ml-6 md:ml-0">{{ user.country }}</div>
            </div>
        </div>
    </div>
  
    <!-- Message si aucun utilisateur trouvé -->
    <div v-if="filteredUsers.length === 0" class="text-center text-gray-500 py-4">
        Aucun utilisateur n'a été trouvé
    </div>
  </template>
  
  <script setup>
  defineProps({
    filteredUsers: {
        type: Array,
        required: true,
    },
    selectUser: {
        type: Function,
        required: true,
    },
  });
  </script>
  
  <style scoped></style>