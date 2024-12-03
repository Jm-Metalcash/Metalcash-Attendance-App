<script setup>
defineProps(["recentModifiedProspects", "selectProspect", "formatDate"]);
</script>

<template>
    <div
        v-if="recentModifiedProspects.length > 0"
        class="mt-8 border p-4 bg-zinc-50"
    >
        <h3 class="text-lg font-semibold text-gray-700">
            Derniers fournisseurs modifiés
        </h3>
        <div
            v-for="(prospect, index) in recentModifiedProspects"
            :key="prospect.id || index"
            @click="selectProspect(prospect)"
            class="text-sm flex flex-wrap justify-between items-center cursor-pointer rounded-md px-2 py-2 my-2 w-full bg-green-50 text-gray-700 hover:bg-green-100 hover:text-green-800"
        >
            <!-- Nom complet -->
            <div class="flex w-full sm:w-1/6 items-center">
                <span
                    :class="[
                        prospect.recently_added
                            ? 'bg-yellow-500'
                            : 'bg-blue-400',
                        'h-2 w-2 mr-4 rounded-full',
                    ]"
                ></span>
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

            <!-- Date de modification et utilisateur ayant modifié -->
            <div class="text-xs w-full sm:w-1/6">
                Modifié par : <br />
                <span class="font-bold text-xs mr-2">
                    {{
                        prospect.updated_by
                            ? prospect.updated_by.name
                            : "Inconnu"
                    }}
                </span>
                <span class="font-bold text-xs">
                    {{ formatDate(prospect.updated_at) }}
                </span>
            </div>
        </div>
    </div>
</template>
