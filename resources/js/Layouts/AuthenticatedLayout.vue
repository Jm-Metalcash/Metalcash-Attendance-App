<script setup>
import { ref, computed, defineProps } from "vue";
import Dropdown from "@/Components/Dropdown.vue";
import DropdownLink from "@/Components/DropdownLink.vue";
import NavLink from "@/Components/NavLink.vue";
import ResponsiveNavLink from "@/Components/ResponsiveNavLink.vue";
import Footer from "@/Components/Footer.vue";
import { Link, usePage } from "@inertiajs/vue3";

const page = usePage();

const showingNavigationDropdown = ref(false);
const logoUrl = computed(() => "/images/logo-HD.png");
</script>

<template>
    <div class="flex flex-col min-h-screen bg-gray-100">
        <!-- Navigation Section -->
        <nav class="bg-white border-b border-gray-100">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between h-16">
                    <div class="flex">
                        <!-- Logo -->
                        <div class="shrink-0 flex items-center">
                            <Link :href="route('index')">
                                <img :src="logoUrl" alt="Logo Metalcash" class="w-16 h-16" />
                            </Link>
                        </div>

                        <!-- Navigation Links -->
                        <div class="hidden space-x-8 sm:-my-px sm:ms-10 sm:flex">
                            <NavLink :href="route('index')" :active="route().current('index')">
                                Accueil
                            </NavLink>
                            <NavLink :href="route('dashboard')" :active="route().current('dashboard')">
                                Aujourd'hui
                            </NavLink>
                            <NavLink :href="route('historique')" :active="route().current('historique')">
                                Historique
                            </NavLink>
                            <NavLink v-if="page.props.auth.roles && (page.props.auth.roles.includes('Admin') || page.props.auth.roles.includes('Informatique') || page.props.auth.roles.includes('Comptabilité'))" :href="route('employes')" :active="route().current('employes')">
                                Gestion des employés
                            </NavLink>
                            <NavLink v-if="page.props.auth.roles && (page.props.auth.roles.includes('Admin') || page.props.auth.roles.includes('Informatique') || page.props.auth.roles.includes('Comptabilité'))" :href="route('managementCall')" :active="route().current('managementCall')">
                                Gestion des appels
                            </NavLink>
                        </div>
                    </div>

                    <!-- Settings Dropdown -->
                    <div class="hidden sm:flex sm:items-center sm:ms-6">
                        <div class="ms-3 relative">
                            <Dropdown align="right" width="48">
                                <template #trigger>
                                    <span class="inline-flex rounded-md">
                                        <button type="button" class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-500 bg-white hover:text-gray-700 focus:outline-none transition ease-in-out duration-150">
                                            {{ $page.props.auth.user.name }}
                                            <svg class="ms-2 -me-0.5 h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                                <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                            </svg>
                                        </button>
                                    </span>
                                </template>
                                <template #content>
                                    <DropdownLink :href="route('profile.edit')">Mon profil</DropdownLink>
                                    <DropdownLink :href="route('logout')" method="post" as="button">Se déconnecter</DropdownLink>
                                </template>
                            </Dropdown>
                        </div>
                    </div>
                </div>
            </div>
        </nav>

        <!-- Header Section -->
        <header class="bg-gray-800 shadow" v-if="$slots.header">
            <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                <slot name="header" />
            </div>
        </header>

        <!-- Main Content Section with flex-grow to occupy available space -->
        <main class="flex-grow">
            <slot />
        </main>

        <!-- Footer Component at the bottom -->
        <Footer />
    </div>
</template>
