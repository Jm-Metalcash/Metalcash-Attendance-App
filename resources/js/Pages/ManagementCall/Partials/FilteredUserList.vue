<script setup>
import UserList from "./UserList.vue";
import Legend from "@/Components/Legend.vue";

defineProps(["filteredProspects", "selectProspect", "newProspectId"]);
</script>

<template>
    <div v-if="filteredProspects.length > 0">
        <h3 class="text-lg font-semibold text-gray-700">
            Résultat de la recherche :
        </h3>
        <UserList
            :filteredProspects="filteredProspects"
            :selectProspect="selectProspect"
            :newProspectId="newProspectId"
        />
    </div>
    <Legend />
</template>
