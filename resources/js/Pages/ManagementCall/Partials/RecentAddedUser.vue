<script setup>
defineProps(["recentAddedProspects", "selectProspect", "formatDate"]);

const getUserName = (item) => {
    if (item.created_by_name) {
        return item.created_by_name;
    }
    return "Inconnu";
};
</script>

<template>
    <div
        v-if="recentAddedProspects && recentAddedProspects.length > 0"
        class="bg-white rounded-xl shadow-sm overflow-hidden"
    >
        <!-- En-tête avec dégradé bleu comme dans Historical.vue -->
        <div class="bg-gradient-to-r from-[#005692] to-[#0078c9] px-4 sm:px-6 py-4 text-white">
            <h3 class="text-sm sm:text-base font-medium">
                <i class="fas fa-users mr-2"></i> Liste des derniers prospects ajoutés
            </h3>
        </div>

        <!-- Vue mobile et tablette : cartes empilées -->
        <div class="lg:hidden p-2 space-y-3 bg-gray-50">
            <div
                v-for="prospect in recentAddedProspects"
                :key="prospect.id"
                @click="selectProspect(prospect)"
                class="p-4 cursor-pointer hover:bg-blue-50 transition-colors border border-gray-200 rounded-md shadow-sm bg-white hover:shadow-md"
            >
                <div class="flex items-center mb-2">
                    <span class="flex-shrink-0 h-3 w-3 rounded-full bg-yellow-500 mr-3"></span>
                    <div class="font-medium">
                        {{ prospect.firstName }} {{ prospect.familyName }}
                    </div>
                </div>
                
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 ml-6 text-sm text-gray-600">
                    <div>
                        <div class="font-medium text-gray-500 mb-1">Téléphone:</div>
                        <div v-if="prospect.phone" class="font-medium">
                            {{ prospect.phone }}
                        </div>
                        <div v-else class="opacity-60">
                            Aucun numéro de téléphone
                        </div>
                    </div>
                    
                    <div>
                        <div class="font-medium text-gray-500 mb-1">Pays:</div>
                        <div v-if="prospect.country" class="font-normal">
                            {{ prospect.country }}
                        </div>
                        <div v-else class="opacity-60">
                            Aucun pays enregistré
                        </div>
                    </div>
                </div>
                
                <div class="mt-3 text-right text-xs text-gray-500">
                    Ajouté par <span class="font-bold">{{ getUserName(prospect) }}</span> le {{ formatDate(prospect.created_at) }}
                </div>
            </div>
        </div>

        <!-- Vue desktop : tableau -->
        <div class="hidden lg:block">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider" style="width: 25%">
                                Nom
                            </th>
                            <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider" style="width: 25%">
                                Téléphone
                            </th>
                            <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider" style="width: 25%">
                                Pays
                            </th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider" style="width: 25%">
                                Ajouté par
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-100">
                        <tr 
                            v-for="prospect in recentAddedProspects" 
                            :key="prospect.id"
                            @click="selectProspect(prospect)"
                            class="hover:bg-blue-50 transition-colors cursor-pointer"
                        >
                            <td class="px-6 py-4 whitespace-nowrap" style="width: 25%">
                                <div class="flex items-center">
                                    <span class="flex-shrink-0 h-3 w-3 rounded-full bg-yellow-500 mr-3"></span>
                                    <div class="font-medium text-gray-800">
                                        {{ prospect.firstName }} {{ prospect.familyName }}
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-center" style="width: 25%">
                                <div v-if="prospect.phone" class="text-sm font-medium text-gray-600">
                                    {{ prospect.phone }}
                                </div>
                                <div v-else class="text-sm text-gray-500 opacity-60">
                                    Aucun numéro de téléphone
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-center" style="width: 25%">
                                <div v-if="prospect.country" class="text-sm text-gray-600">
                                    {{ prospect.country }}
                                </div>
                                <div v-else class="text-sm text-gray-500 opacity-60">
                                    Aucun pays enregistré
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right" style="width: 25%">
                                <div class="text-xs text-gray-800">
                                    <span class="font-bold">{{ getUserName(prospect) }}</span>
                                    <br>
                                    {{ formatDate(prospect.created_at) }}
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</template>

<style scoped>
/* Ajout d'une animation de survol plus prononcée sur les éléments interactifs */
.transition-colors:hover {
    transition: all 0.2s ease;
    transform: translateY(-1px);
}
</style>
