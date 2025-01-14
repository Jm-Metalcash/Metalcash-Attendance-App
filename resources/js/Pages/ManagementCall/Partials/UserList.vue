<template>
    <div v-if="filteredProspects.length > 0" class="py-3 text-sm">
        <div
            v-for="(prospect, index) in filteredProspects"
            :key="prospect.id || index"
            @click="selectProspect(prospect)"
            :class="[ 
                'flex flex-wrap justify-between items-center cursor-pointer rounded-md px-2 py-2 my-2 w-full',
                getNoteClass(prospect),
            ]"
        >
            <div v-if="prospect" class="flex w-full sm:w-1/6 items-center mb-2 sm:mb-0 space-x-4">
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

                <div class="font-medium text-left truncate">
                    {{ prospect.firstName }} {{ prospect.familyName }}
                </div>
            </div>

            <div class="flex w-full sm:w-1/6 mb-2 sm:mb-0">
                <div class="font-medium text-left w-full ml-6 md:ml-0">
                    {{ prospect.phone || "Non disponible" }}
                </div>
            </div>

            <div class="flex w-full sm:w-1/6">
                <div class="text-sm font-normal text-left ml-6 md:ml-0">
                    {{ prospect.country || "Non disponible" }}
                </div>
            </div>
        </div>
    </div>

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

// Vérifiez que prospect est défini dans getNoteClass
const getNoteClass = (prospect) => {
    if (!prospect) {
        return "bg-sky-50 text-gray-700 hover:bg-blue-100";
    }

    const noteType = prospect?.last_important_note?.type || null;

    const classes = {
        premium: "bg-green-50 text-green-800 hover:bg-green-100",
        avertissement: "bg-orange-50 text-orange-800 hover:bg-orange-100",
        attention: "bg-red-50 text-red-800 hover:bg-red-100",
    };

    return classes[noteType] || "bg-sky-50 text-gray-700 hover:bg-blue-100";
};

</script>
