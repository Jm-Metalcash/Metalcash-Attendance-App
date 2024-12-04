<script setup>
import UserList from "./UserList.vue";

defineProps(["recentProspects", "selectProspect"]);
</script>

<template>
    <div v-if="recentProspects.length > 0" class="mt-8 border p-4 bg-zinc-0">
        <h3 class="text-lg font-semibold text-gray-700">
            Liste des dernières consultations
        </h3>
        <UserList
            :filteredProspects="
                recentProspects.map((view) => ({
                    ...view,
                    viewedBy: view.viewedBy || 'Inconnu',
                    viewedAt: view.viewedAt || 'Date inconnue',
                }))
            "
            :selectProspect="selectProspect"
        />
    </div>
</template>
