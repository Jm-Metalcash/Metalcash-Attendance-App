<script setup>
defineProps(["recentAddedProspects", "selectProspect", "formatDate"]);
</script>

<template>
    <div
        v-if="recentAddedProspects.length > 0"
        class="mt-0 border p-4 bg-zinc-50"
    >
        <h3 class="text-lg font-semibold text-gray-700">
            Nouveaux prospects et clients ajoutés
        </h3>
        <div
            v-for="(prospect, index) in recentAddedProspects"
            :key="prospect.id || index"
            @click="selectProspect(prospect)"
            class="text-sm flex flex-wrap justify-between items-center cursor-pointer rounded-md px-2 py-2 my-2 w-full bg-orange-50 text-gray-700 hover:bg-orange-100 hover:text-orange-900"
        >
            <!-- Nom complet -->
            <div class="flex w-full sm:w-1/6 items-center">
                <span
                    :class="[ 
                        'flex justify-center items-center mr-4 h-3 w-3 rounded-full',
                        prospect.type === 'prospect' ? 'bg-yellow-500' : 'bg-blue-500',
                    ]"
                >
                    <i
                        v-if="prospect.type === 'client'"
                        class="fa-solid fa-check text-white text-[8px]"
                    ></i>
                </span>
                <div class="font-medium text-left">
                    {{ prospect.firstName }} {{ prospect.familyName }}
                </div>
            </div>

            <!-- Numéro de téléphone -->
            <div class="flex w-full sm:w-1/6">
                <div class="font-medium text-left ml-6 md:ml-0">
                    {{ prospect.phone }}
                </div>
            </div>

            <!-- Pays -->
            <div class="flex w-full sm:w-1/6">
                <div class="text-sm font-normal text-left ml-6 md:ml-0">
                    {{ prospect.country }}
                </div>
            </div>

            <!-- Date d'ajout et utilisateur ayant ajouté -->
            <div class="text-xs w-full sm:w-1/6">
                Ajouté par : <br />
                <span class="font-bold text-xs mr-2">
                    {{
                        prospect.created_by
                            ? prospect.created_by.name
                            : "Inconnu"
                    }}
                </span>
                <span class="font-bold text-xs">
                    {{ formatDate(prospect.created_at) }}
                </span>
            </div>
        </div>
    </div>
</template>
