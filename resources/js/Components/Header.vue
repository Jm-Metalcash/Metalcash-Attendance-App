<script setup>
import { defineProps } from "vue";
import DropdownLink from "@/Components/DropdownLink.vue";
import Dropdown from "@/Components/Dropdown.vue";
import SearchBar from "@/Components/SearchBar.vue";

defineProps({
    pageTitle: {
        type: String,
        default: "Page",
    },
});
</script>

<template>
    <div class="flex items-center justify-between px-0 py-0">
        <!-- Barre de recherche -->
        <SearchBar />

        <!-- Dropdown utilisateur -->
        <div class="hidden lg:flex lg:items-center lg:ms-6">
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
