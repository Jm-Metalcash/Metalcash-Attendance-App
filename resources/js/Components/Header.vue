<script setup>
import { ref, defineProps } from "vue";
import DropdownLink from "@/Components/DropdownLink.vue";
import Dropdown from "@/Components/Dropdown.vue";

defineProps({
    pageTitle: {
        type: String,
        default: "Page",
    },
});

// État réactif pour suivre si l'input est en focus
const isFocused = ref(false);

const handleFocus = () => {
    isFocused.value = true;
};

const handleBlur = () => {
    isFocused.value = false;
};
</script>

<template>
    <div class="flex items-center justify-between px-0 py-0">
        <!-- Barre de recherche -->
        <div
            class="relative transition-all"
            :class="{ 'w-[500px] bg-white': isFocused, 'w-[300px]': !isFocused }"
        >
            <input
                type="text"
                placeholder="Rechercher..."
                class="w-full py-2 pl-10 pr-4 text-gray-700 border rounded-full outline-none transition-all"
                :class="{
                    'border-blue-500': isFocused,
                    'border-gray-300': !isFocused,
                }"
                @focus="handleFocus"
                @blur="handleBlur"
            />
            <div class="absolute inset-y-0 left-0 flex items-center pl-3">
                <svg
                    xmlns="http://www.w3.org/2000/svg"
                    class="w-5 h-5 transition-all"
                    :class="{
                        'text-blue-500': isFocused,
                        'text-gray-400': !isFocused,
                    }"
                    fill="none"
                    viewBox="0 0 24 24"
                    stroke="currentColor"
                >
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"
                    />
                </svg>
            </div>
        </div>

        <!-- Dropdown utilisateur -->
        <div class="hidden sm:flex sm:items-center sm:ms-6">
            <div class="ms-3 relative">
                <Dropdown align="right" width="48">
                    <!-- Bouton déclencheur -->
                    <template #trigger>
                        <span class="inline-flex rounded-md">
                            <button
                                type="button"
                                class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-700 bg-gray-50 hover:text-gray-500 focus:outline-none transition ease-in-out duration-150"
                            >
                                {{ $page.props.auth.user.name }}
                                <svg
                                    class="ms-2 -me-0.5 h-4 w-4"
                                    xmlns="http://www.w3.org/2000/svg"
                                    viewBox="0 0 20 20"
                                    fill="currentColor"
                                >
                                    <path
                                        fill-rule="evenodd"
                                        d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                                        clip-rule="evenodd"
                                    />
                                </svg>
                            </button>
                        </span>
                    </template>

                    <!-- Contenu du Dropdown -->
                    <template #content>
                        <DropdownLink :href="route('profile.edit')">
                            Mon profil
                        </DropdownLink>
                        <DropdownLink
                            :href="route('logout')"
                            method="post"
                            as="button"
                        >
                            Déconnexion
                        </DropdownLink>
                    </template>
                </Dropdown>
            </div>
        </div>
    </div>
</template>

<style scoped>
.transition-all {
    transition: all 0.3s ease;
}
</style>
