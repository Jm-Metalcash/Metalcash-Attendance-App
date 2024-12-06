<script setup>
defineProps(["recentModifiedProspects", "selectProspect", "formatDate"]);

const getNoteClass = (noteType) => {
    return {
        avertissement: "bg-orange-100 text-orange-700",
        premium: "bg-green-100 text-green-700",
        attention: "bg-red-100 text-red-700",
    }[noteType] || "bg-gray-50 text-gray-700";
};
</script>

<template>
    <div
        v-if="recentModifiedProspects && recentModifiedProspects.length > 0"
        class="mt-8 border p-4 bg-zinc-50"
    >
        <h3 class="text-base font-semibold py-3 px-5 bg-[#005691] text-zinc-50">
            Liste des dernières modifications
        </h3>
        <div
            v-for="prospect in recentModifiedProspects"
            :key="prospect.id + '-' + prospect.type"
            @click="selectProspect(prospect)"
            :class="[
                'text-sm flex flex-wrap justify-between items-center cursor-pointer rounded-md px-2 py-2 my-2 w-full hover:bg-blue-50 hover:text-blue-700',
                getNoteClass(prospect.lastImportantNote),
            ]"
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
                        class="fa-solid fa-check text-white text-[10px]"
                    ></i>
                </span>
                <div class="font-medium text-left">
                    {{ prospect.firstName }} {{ prospect.familyName }}
                </div>
            </div>

            <!-- Numéro de téléphone -->
            <div class="flex w-full sm:w-1/6">
                <div class="font-medium text-left ml-6 md:ml-0">
                    {{ prospect.phone || "Non disponible" }}
                </div>
            </div>

            <!-- Pays -->
            <div class="flex w-full sm:w-1/6">
                <div class="text-sm font-normal text-left ml-6 md:ml-0">
                    {{ prospect.country || "Non disponible" }}
                </div>
            </div>

            <!-- Date de modification et utilisateur ayant modifié -->
            <div class="text-xs w-full sm:w-1/6">
                Modifié par : <br />
                <span class="font-bold text-xs mr-2">
                    {{ prospect.last_modifier || "Inconnu" }}
                </span>
                <span class="font-bold text-xs">
                    {{ formatDate(prospect.modified_at) }}
                </span>
            </div>
        </div>
    </div>
</template>
