<template>
    <div v-if="filteredUsers.length > 0" class="py-3 text-sm">
        <div
            v-for="(user, index) in filteredUsers"
            :key="user.id || index"
            @click="selectUser(user)"
            :class="[
                'flex flex-wrap justify-between items-center cursor-pointer rounded-md px-2 py-2 my-2 w-full',
                user.blacklist === 1
                    ? 'bg-red-50 text-red-500'
                    : 'bg-sky-50 text-gray-700 hover:text-blue-400 hover:bg-blue-100',
            ]"
        >
            <!-- Colonne Nom complet (avec indicateur de statut à gauche) -->
            <div class="flex w-full sm:w-1/6 items-center mb-2 sm:mb-0">
                <span
                    :class="[ 
                        user.recently_added ? 'bg-fuchsia-500' : 'bg-green-400',
                        'h-2 w-2 mr-4 rounded-full',
                    ]"
                ></span>
                <div class="font-medium text-left">
                    {{ user.firstName }} {{ user.familyName }}
                </div>
            </div>

            <!-- Colonne Numéro de Téléphone -->
            <div class="flex w-full sm:w-1/6 mb-2 sm:mb-0">
                <div class="font-medium text-left w-full ml-6 md:ml-0">
                    {{ user.phone }}
                </div>
            </div>

            <!-- Colonne Pays -->
            <div class="flex w-full sm:w-1/6">
                <div class="text-sm font-normal text-left ml-6 md:ml-0">
                    {{ user.country }}
                </div>
            </div>

            <!-- Colonne Consultation (nom de l'utilisateur et date) -->
            <div
                v-if="user.viewedBy || user.viewedAt"
                class="flex w-full sm:w-1/3 mt-2 sm:mt-0"
            >
                <div class="text-sm font-light text-left ml-6 md:ml-0">
                    <strong>Consulté par :</strong> {{ user.viewedBy || "Inconnu" }}<br />
                    <strong>Le :</strong> {{ user.viewedAt || "Date inconnue" }}
                </div>
            </div>
        </div>
    </div>

    <!-- Message si aucun utilisateur trouvé avec v-else -->
    <div v-else class="py-3 text-sm text-center">
        Aucun utilisateur n'a été trouvé
    </div>
</template>

<script setup>
defineProps({
    filteredUsers: {
        type: Array,
        required: true,
    },
    selectUser: {
        type: Function,
        required: true,
    },
    newUserId: {
        type: Number,
        default: null,
    },
});
</script>

<style scoped></style>
