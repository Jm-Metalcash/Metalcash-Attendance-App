<script setup>
defineProps(["recentModifiedProspects", "selectProspect", "formatDate"]);

const getNoteClass = (noteType) => {
    return {
        avertissement: "bg-orange-50 text-orange-800",
        premium: "bg-green-50 text-green-800",
        attention: "bg-red-50 text-red-800",
    }[noteType] || "bg-gray-50 text-gray-800";
};
</script>

<template>
    <div
        v-if="recentModifiedProspects && recentModifiedProspects.length > 0"
        class="bg-white rounded-xl shadow-sm overflow-hidden"
    >
        <!-- En-tête avec dégradé bleu comme dans Historical.vue -->
        <div class="bg-gradient-to-r from-[#005692] to-[#0078c9] px-4 sm:px-6 py-4 text-white">
            <h3 class="text-sm sm:text-base font-medium">
                <i class="fas fa-history mr-2"></i> Liste des dernières modifications
            </h3>
        </div>

        <!-- Vue mobile et tablette : cartes empilées -->
        <div class="lg:hidden p-2 space-y-3 bg-gray-50">
            <div
                v-for="prospect in recentModifiedProspects"
                :key="prospect.id + '-' + prospect.type"
                @click="selectProspect(prospect)"
                class="p-4 cursor-pointer hover:bg-blue-50 transition-colors border border-gray-200 rounded-md shadow-sm bg-white hover:shadow-md"
                :class="[getNoteClass(prospect.lastImportantNote)]"
            >
                <div class="flex items-center mb-2">
                    <span
                        :class="[
                            'flex-shrink-0 h-3 w-3 rounded-full mr-3',
                            prospect.type === 'prospect' ? 'bg-yellow-500' : 'bg-blue-500',
                        ]"
                    >
                        <i
                            v-if="prospect.type === 'client'"
                            class="fa-solid fa-check text-white text-[10px]"
                        ></i>
                    </span>
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
                    Modifié par <span class="font-bold">{{ prospect.last_note_updater || prospect.last_modifier || "Inconnu" }}</span> le {{ formatDate(prospect.modified_at) }}
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
                                Modifié par
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-100">
                        <tr 
                            v-for="prospect in recentModifiedProspects" 
                            :key="prospect.id + '-' + prospect.type"
                            @click="selectProspect(prospect)"
                            class="hover:bg-blue-50 transition-colors cursor-pointer"
                            :class="[getNoteClass(prospect.lastImportantNote)]"
                        >
                            <td class="px-6 py-4 whitespace-nowrap" style="width: 25%">
                                <div class="flex items-center">
                                    <span
                                        :class="[
                                            'flex-shrink-0 h-3 w-3 rounded-full mr-3',
                                            prospect.type === 'prospect' ? 'bg-yellow-500' : 'bg-blue-500',
                                        ]"
                                    >
                                        <i
                                            v-if="prospect.type === 'client'"
                                            class="fa-solid fa-check text-white text-[10px]"
                                        ></i>
                                    </span>
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
                                    <span class="font-bold">{{ prospect.last_note_updater || prospect.last_modifier || "Inconnu" }}</span>
                                    <br>
                                    {{ formatDate(prospect.modified_at) }}
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
