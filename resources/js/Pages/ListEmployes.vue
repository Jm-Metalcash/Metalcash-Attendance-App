<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head } from "@inertiajs/vue3";
import { defineProps } from 'vue'

const props = defineProps({
  users: Array 
})
</script>

<template>
    <Head title="Liste des employés" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-white bg-gray-800 leading-tight">
                Liste des employés
            </h2>
        </template>

        <div class="container mx-auto px-4 sm:px-8 max-w-3xl">
    <div class="py-8">
      <!-- Titre de la page -->
      <div>
        <h2 class="text-2xl font-semibold leading-tight text-gray-800">Liste des employés</h2>
      </div>

      <!-- Table responsive -->
      <div class="-mx-4 sm:-mx-8 px-4 sm:px-8 py-4 overflow-x-auto">
        <div class="inline-block min-w-full shadow rounded-lg overflow-hidden">
          <table class="min-w-full leading-normal">
            <thead>
              <tr>
                <th
                  class="px-5 py-3 bg-gray-100 border-b-2 border-gray-200 text-gray-600 text-left text-sm uppercase font-semibold">
                  Nom
                </th>
                <th
                  class="px-5 py-3 bg-gray-100 border-b-2 border-gray-200 text-gray-600 text-left text-sm uppercase font-semibold">
                  Email
                </th>
                <th
                  class="px-5 py-3 bg-gray-100 border-b-2 border-gray-200 text-gray-600 text-left text-sm uppercase font-semibold">
                  Action
                </th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="user in users" :key="user.id" class="hover:bg-gray-100">
                <td class="px-5 py-5 border-b border-gray-200 bg-white text-sm">
                  <div class="flex items-center">
                    <div class="ml-3">
                      <p class="text-gray-900 whitespace-no-wrap">{{ user.name }}</p>
                    </div>
                  </div>
                </td>
                <td class="px-5 py-5 border-b border-gray-200 bg-white text-sm">
                  <p class="text-gray-900 whitespace-no-wrap">{{ user.email }}</p>
                </td>
                <td class="px-5 py-5 border-b border-gray-200 bg-white text-sm">
                  <button
                    class="text-indigo-600 hover:text-indigo-900 font-semibold focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 rounded-md">
                    Voir profil
                  </button>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
    </AuthenticatedLayout>
</template>

<style scoped>
</style>
