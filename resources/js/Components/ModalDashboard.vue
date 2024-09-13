<template>
    <div
      v-if="showModal"
      class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-70 transition-opacity duration-300"
    >
      <div
        class="bg-white text-center rounded-lg p-6 w-[500px] md:w-[600px] lg:w-[700px] shadow-lg transform scale-95 transition-transform duration-300"
      >
        <h2 class="text-xl font-bold mb-4 text-gray-800">
          <i class="fas fa-exclamation-circle text-yellow-600 mr-2"></i>
          Confirmer l'heure de pointage
        </h2>
        <p class="mb-6">
          Êtes-vous sûr de vouloir enregistrer 
          <span class="font-bold text-lg">{{ currentTime }} </span>
          comme
          <span class="font-bold text-lg">{{ actionType === 'arrival' ? "heure d'arrivée" : "heure de départ" }}</span>
          ?
        </p>
        <div class="flex justify-center space-x-4">
          <button
            @click="$emit('cancel')"
            class="bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-600 transition-colors duration-300"
          >
            <i class="fas fa-times"></i> Annuler
          </button>
          <button
            @click="$emit('confirm')"
            class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 transition-colors duration-300"
          >
            <i class="fas fa-check"></i> Confirmer
          </button>
        </div>
      </div>
    </div>
  </template>
  
  <script setup>
  // Props pour les données passées au composant
  const props = defineProps({
  showModal: Boolean,
  currentTime: String,
  actionType: String,
});
  </script>
  
  <style scoped></style>
  