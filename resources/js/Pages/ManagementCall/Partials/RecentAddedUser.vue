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
        class="mt-0 border p-4 bg-zinc-50"
    >
        <h3 class="text-base font-semibold py-3 px-5 bg-[#005691] text-zinc-50">
            Liste des derniers prospects ajoutés
        </h3>
        <div
            v-for="(prospect, index) in recentAddedProspects"
            :key="prospect.id"
            @click="selectProspect(prospect)"
            class="text-sm flex flex-wrap justify-between items-center cursor-pointer rounded-md px-2 py-2 my-2 w-full text-gray-700 hover:bg-blue-50 hover:text-blue-700"
        >
            <!-- Nom complet -->
            <div class="flex w-full sm:w-1/6 items-center">
                <span
                    class="flex justify-center items-center mr-4 h-3 w-3 rounded-full bg-yellow-500"
                ></span>
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

            <!-- Date d'ajout et utilisateur ayant ajouté -->
            <div class="text-xs w-full sm:w-1/6">
                Ajouté par : <br />
                <span class="font-bold text-xs mr-2">
                    {{ getUserName(prospect) }}
                </span>
                <span class="font-bold text-xs">
                    {{ formatDate(prospect.created_at) }}
                </span>
            </div>
        </div>
    </div>
</template>
