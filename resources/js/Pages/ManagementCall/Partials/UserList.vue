<template>
    <div v-if="filteredProspects.length > 0" class="py-3 text-sm">
        <div
            v-for="(prospect, index) in filteredProspects"
            :key="prospect.id || index"
            @click="selectProspect(prospect)"
            :class="[
                'flex flex-wrap justify-between items-center cursor-pointer rounded-md px-2 py-2 my-2 w-full',
                prospect.blacklist === 1
                    ? 'bg-red-50 text-red-500'
                    : 'bg-sky-50 text-gray-700 hover:text-blue-400 hover:bg-blue-100',
            ]"
        >
            <!-- Colonne Nom complet (avec indicateur de statut à gauche) -->
            <div
                class="flex w-full sm:w-1/6 items-center mb-2 sm:mb-0 space-x-4"
            >
                <!-- Indicateur vérifié pour le client ou simple rond pour le prospect -->
                <span
                    :class="[
                        'flex justify-center items-center h-3 w-3 rounded-full',
                        prospect.type === 'prospect'
                            ? 'bg-yellow-500'
                            : 'bg-blue-500',
                    ]"
                >
                    <i
                        v-if="prospect.type === 'client'"
                        class="fa-solid fa-check text-white text-[8px]"
                    ></i>
                </span>

                <!-- Nom complet -->
                <div class="font-medium text-left truncate">
                    {{ prospect.firstName }} {{ prospect.familyName }}
                </div>
            </div>

            <!-- Colonne Numéro de Téléphone -->
            <div class="flex w-full sm:w-1/6 mb-2 sm:mb-0">
                <div class="font-medium text-left w-full ml-6 md:ml-0">
                    {{ prospect.phone }}
                </div>
            </div>

            <!-- Colonne Pays -->
            <div class="flex w-full sm:w-1/6">
                <div class="text-sm font-normal text-left ml-6 md:ml-0">
                    {{ prospect.country }}
                </div>
            </div>

            <!-- Consultation par utilisateur/date -->
            <div
                v-if="prospect.viewedBy || prospect.viewedAt"
                class="flex w-full sm:w-1/6 mb-2 sm:mb-0"
            >
                <div class="text-xs font-light text-left ml-6 md:ml-0">
                    <span>Consulté par :</span><br />
                    <span class="font-bold mr-2">{{
                        prospect.viewedBy || "Inconnu"
                    }}</span>
                    <span class="font-bold">
                        {{ prospect.viewedAt || "Date inconnue" }}</span
                    >
                </div>
            </div>
        </div>
    </div>

    <!-- Message si aucun prospect trouvé avec v-else -->
    <div v-else class="py-3 text-sm text-center">
        Aucun prospect ou client trouvé
    </div>
</template>

<script setup>
defineProps({
    filteredProspects: {
        type: Array,
        required: true,
    },
    selectProspect: {
        type: Function,
        required: true,
    },
    newProspectId: {
        type: Number,
        default: null,
    },
});
</script>

<style scoped></style>
