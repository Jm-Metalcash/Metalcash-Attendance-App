<template>
    <div
        v-if="user"
        class="bg-white overflow-hidden shadow rounded-lg border mt-8"
    >
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
                            {{ user.prenom }}
                        </dd>
                    </div>
                    <div class="sm:col-span-1 px-4 md:px-0 mt-3 md:mt-0">
                        <dt class="text-sm text-gray-500 font-bold">Nom</dt>
                        <dd class="mt-1 text-sm text-gray-400 sm:mt-0">
                            {{ user.nom }}
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
                            {{ user.email }}
                        </dd>
                    </div>
                    <div
                        class="sm:col-span-1 px-4 md:px-0 mt-3 md:mt-0 mb-4 md:mb-0"
                    >
                        <dt class="text-sm font-bold text-gray-500">
                            Numéro de téléphone
                        </dt>
                        <dd class="mt-1 text-sm text-gray-400 sm:mt-0">
                            {{ user.telephone }}
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
                            {{ user.adresse.rue }}
                        </dd>
                    </div>
                    <div class="sm:col-span-1 px-4 md:px-0 mt-3 md:mt-0">
                        <dt class="text-sm font-bold text-gray-500">
                            Code postal
                        </dt>
                        <dd class="mt-1 text-sm text-gray-400 sm:mt-0">
                            {{ user.adresse.codePostal }}
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
                            {{ user.adresse.localite }}
                        </dd>
                    </div>
                    <div
                        class="sm:col-span-1 px-4 md:px-0 mt-3 md:mt-0 mb-4 md:mb-0"
                    >
                        <dt class="text-sm font-bold text-gray-500">Pays</dt>
                        <dd class="mt-1 text-sm text-gray-400 sm:mt-0">
                            {{ user.adresse.pays }}
                        </dd>
                    </div>
                </div>

                <!-- Section Description -->
                <div>
                    <div class="px-4 py-5 sm:px-6 bg-gray-100">
                        <p
                            class="mt-1 max-w-2xl text-sm text-gray-500 font-bold"
                        >
                            Note
                        </p>
                    </div>
                    <div class="py-3 sm:py-5 px-4 md:px-6">
                        <p class="text-sm text-gray-400">
                            Aucune note actuellement pour ce fournisseur.
                        </p>
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
                <tr v-for="(record, index) in user.historique" :key="index">
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
                <tr v-if="!user.historique.length">
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

<script setup>
import { ref } from "vue";

defineProps({
    user: {
        type: Object,
        required: true,
    },
});

const showHistory = ref(false);

const toggleHistory = () => {
    showHistory.value = !showHistory.value;
};
</script>

<style scoped></style>
