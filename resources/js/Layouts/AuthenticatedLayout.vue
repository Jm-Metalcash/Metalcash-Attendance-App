<script setup>
import { ref, computed } from "vue";
import { Link, usePage } from "@inertiajs/vue3";
import Dropdown from "@/Components/Dropdown.vue";
import DropdownLink from "@/Components/DropdownLink.vue";
import ResponsiveNavLink from "@/Components/ResponsiveNavLink.vue";
import Footer from "@/Components/Footer.vue";

const page = usePage();
const showingNavigationDropdown = ref(false); // Menu mobile
const isMenuCollapsed = ref(false); // État du menu aside sur desktop
const showTimeManagementSubMenu = ref(false);
const showEmployeeManagementSubMenu = ref(false);
const logoUrl = computed(() => "/images/logo-HD.png");
</script>

<template>
    <div class="flex h-screen bg-gray-900">
        <!-- SIDEBAR (Desktop/Tablet) -->
        <aside
            :class="{
                'w-80': !isMenuCollapsed,
                'w-24': isMenuCollapsed,
            }"
            class="hidden md:flex bg-gray-800 text-gray-300 flex-col transition-all duration-300"
        >
            <!-- Header Logo and Collapse -->
            <header
                class="flex items-center justify-between border-b border-gray-700 py-4 px-6"
            >
                <Link :href="route('index')" class="flex items-center">
                    <img
                        v-if="!isMenuCollapsed"
                        :src="logoUrl"
                        alt="Logo"
                        class="w-16 h-16"
                    />
                    <span
                        v-if="!isMenuCollapsed"
                        class="text-white font-bold ml-2 text-lg"
                    >
                        Metalcash
                    </span>
                </Link>
                <button
                    @click="isMenuCollapsed = !isMenuCollapsed"
                    class="p-2 rounded hover:bg-gray-700 focus:outline-none focus:ring focus:ring-gray-500"
                >
                    <!-- Hamburger Menu Icon -->
                    <svg
                        v-if="isMenuCollapsed"
                        xmlns="http://www.w3.org/2000/svg"
                        fill="none"
                        viewBox="0 0 24 24"
                        stroke="currentColor"
                        class="h-6 w-6 text-gray-400"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M4 6h16M4 12h16M4 18h16"
                        />
                    </svg>
                    <!-- Close Menu Icon -->
                    <svg
                        v-else
                        xmlns="http://www.w3.org/2000/svg"
                        fill="none"
                        viewBox="0 0 24 24"
                        stroke="currentColor"
                        class="h-6 w-6 text-gray-400"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M6 18L18 6M6 6l12 12"
                        />
                    </svg>
                </button>
            </header>

            <!-- Profile Section -->
            <div
                v-if="!isMenuCollapsed"
                class="flex items-center border-b border-gray-700 p-4"
            >
                <div class="ml-4">
                    <p class="text-sm text-gray-400">Bienvenue,</p>
                    <Link :href="route('profile.edit')">
                        <span class="text-lg text-gray-200">{{
                            page.props.auth.user.name
                        }}</span>
                        <span class="text-sm text-gray-400 ml-1"
                            >— {{ page.props.auth.roles[0] }}</span
                        >
                    </Link>
                </div>
            </div>

            <!-- Navigation Menu -->
            <nav class="flex-grow">
                <ul class="list-none space-y-2 p-4">
                    <!-- Gestion du temps de travail -->
                    <li>
                        <div
                            @click="
                                showTimeManagementSubMenu =
                                    !showTimeManagementSubMenu
                            "
                            class="flex items-center px-4 py-3 rounded cursor-pointer hover:bg-gray-700"
                        >
                            <i class="fa fa-clock mr-3"></i>
                            <span v-if="!isMenuCollapsed"
                                >Gestion du temps de travail</span
                            >
                            <i
                                :class="{
                                    'fa-chevron-down':
                                        !showTimeManagementSubMenu,
                                    'fa-chevron-up': showTimeManagementSubMenu,
                                }"
                                class="fa ml-auto"
                            ></i>
                        </div>
                        <transition name="fade">
                            <ul
                                v-if="showTimeManagementSubMenu"
                                class="pl-8 space-y-2"
                            >
                                <li>
                                    <Link
                                        :href="route('dashboard')"
                                        class="block px-4 py-2 rounded hover:bg-gray-700"
                                    >
                                        Pointage
                                    </Link>
                                </li>
                                <li>
                                    <Link
                                        :href="route('historique')"
                                        class="block px-4 py-2 rounded hover:bg-gray-700"
                                    >
                                        Historique
                                    </Link>
                                </li>
                            </ul>
                        </transition>
                    </li>

                    <!-- Gestion des employés -->
                    <li>
                        <div
                            @click="
                                showEmployeeManagementSubMenu =
                                    !showEmployeeManagementSubMenu
                            "
                            class="flex items-center px-4 py-3 rounded cursor-pointer hover:bg-gray-700"
                        >
                            <i class="fa fa-user mr-3"></i>
                            <span>Gestion des employés</span>
                            <i
                                :class="{
                                    'fa-chevron-down':
                                        !showEmployeeManagementSubMenu,
                                    'fa-chevron-up':
                                        showEmployeeManagementSubMenu,
                                }"
                                class="fa ml-auto"
                            ></i>
                        </div>
                        <transition name="fade">
                            <ul
                                v-if="showEmployeeManagementSubMenu"
                                class="pl-8 space-y-2"
                            >
                            
                                <li
                                    v-for="user in page.props.users"
                                    :key="user.id"
                                    class="block px-4 py-2 rounded hover:bg-gray-700 text-xs"
                                >
                                    <i class="fa fa-user mr-3"></i>
                                    <Link :href="`/employes/${user.id}/profil`">
                                        {{ user.name }}
                                        <span class="text-xs text-gray-400">
                                            ({{
                                                user.roles
                                                    .map((role) => role.name)
                                                    .join(", ")
                                            }})
                                        </span>
                                    </Link>
                                </li>
                            </ul>
                        </transition>
                    </li>

                    <li
                        v-if="
                            page.props.auth.roles.includes('Admin') ||
                            page.props.auth.roles.includes('Informatique') ||
                            page.props.auth.roles.includes('Comptabilité')
                        "
                    >
                        <Link
                            :href="route('managementCall')"
                            :class="[
                                'flex items-center px-4 py-3 rounded hover:bg-gray-700',
                                route().current('managementCall')
                                    ? 'bg-gray-700 text-white'
                                    : 'text-gray-300',
                            ]"
                        >
                            <i class="fa fa-phone mr-3"></i>
                            <span v-if="!isMenuCollapsed"
                                >Gestion des appels</span
                            >
                        </Link>
                    </li>
                </ul>
            </nav>

            <!-- Footer / Logout -->
            <footer
                v-if="!isMenuCollapsed"
                class="border-t border-gray-700 p-4"
            >
                <Link
                    :href="route('logout')"
                    method="post"
                    as="button"
                    class="flex items-center text-gray-300 hover:text-gray-200"
                >
                    <i class="fa fa-sign-out mr-3"></i>
                    <span>Déconnexion</span>
                </Link>
            </footer>
        </aside>

        <!-- MAIN CONTENT AREA -->
        <!-- Conteneur principal qui contient le contenu et la nav mobile -->
        <div class="flex-1 flex flex-col bg-gray-100 min-h-screen">
            <!-- NAVBAR MOBILE -->
            <nav class="bg-white border-b border-gray-100 md:hidden">
                <!-- Top Bar Mobile -->
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                    <div class="flex justify-between h-16">
                        <div class="flex">
                            <!-- Logo -->
                            <div class="shrink-0 flex items-center">
                                <Link :href="route('index')">
                                    <img
                                        :src="logoUrl"
                                        alt="Logo Metalcash"
                                        class="w-16 h-16"
                                    />
                                </Link>
                            </div>
                        </div>

                        <div class="hidden sm:flex sm:items-center sm:ml-6">
                            <!-- Settings Dropdown -->
                            <div class="ml-3 relative">
                                <Dropdown align="right" width="48">
                                    <template #trigger>
                                        <span class="inline-flex rounded-md">
                                            <button
                                                type="button"
                                                class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-500 bg-white hover:text-gray-700 focus:outline-none transition ease-in-out duration-150"
                                            >
                                                {{ page.props.auth.user.name }}

                                                <svg
                                                    class="ml-2 -mr-0.5 h-4 w-4"
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

                                    <template #content>
                                        <DropdownLink
                                            :href="route('profile.edit')"
                                        >
                                            Mon profil
                                        </DropdownLink>
                                        <DropdownLink
                                            :href="route('logout')"
                                            method="post"
                                            as="button"
                                        >
                                            Se déconnecter
                                        </DropdownLink>
                                    </template>
                                </Dropdown>
                            </div>
                        </div>

                        <!-- Hamburger -->
                        <div class="-mr-2 flex items-center sm:hidden">
                            <button
                                @click="
                                    showingNavigationDropdown =
                                        !showingNavigationDropdown
                                "
                                class="inline-flex items-center justify-center p-2 rounded-md text-gray-400 hover:text-gray-500 hover:bg-gray-100 focus:outline-none focus:bg-gray-100 focus:text-gray-500 transition duration-150 ease-in-out"
                            >
                                <svg
                                    class="h-6 w-6"
                                    stroke="currentColor"
                                    fill="none"
                                    viewBox="0 0 24 24"
                                >
                                    <path
                                        :class="{
                                            hidden: showingNavigationDropdown,
                                            'inline-flex':
                                                !showingNavigationDropdown,
                                        }"
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M4 6h16M4 12h16M4 18h16"
                                    />
                                    <path
                                        :class="{
                                            hidden: !showingNavigationDropdown,
                                            'inline-flex':
                                                showingNavigationDropdown,
                                        }"
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M6 18L18 6M6 6l12 12"
                                    />
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Responsive Navigation Menu -->
                <div
                    :class="{
                        block: showingNavigationDropdown,
                        hidden: !showingNavigationDropdown,
                    }"
                    class="sm:hidden"
                >
                    <div class="pt-2 pb-3 space-y-1">
                        <ResponsiveNavLink
                            :href="route('dashboard')"
                            :active="route().current('dashboard')"
                        >
                            Gestion du temps de travail
                        </ResponsiveNavLink>
                        <ResponsiveNavLink
                            :href="route('historique')"
                            :active="route().current('historique')"
                        >
                            Historique du temps de travail
                        </ResponsiveNavLink>
                        <ResponsiveNavLink
                            v-if="
                                page.props.auth.roles &&
                                (page.props.auth.roles.includes('Admin') ||
                                    page.props.auth.roles.includes(
                                        'Informatique'
                                    ))
                            "
                            :href="route('employes')"
                            :active="route().current('employes')"
                        >
                            Gestion des employés
                        </ResponsiveNavLink>
                        <ResponsiveNavLink
                            v-if="
                                page.props.auth.roles &&
                                (page.props.auth.roles.includes('Admin') ||
                                    page.props.auth.roles.includes(
                                        'Informatique'
                                    ) ||
                                    page.props.auth.roles.includes(
                                        'Comptabilité'
                                    ))
                            "
                            :href="route('managementCall')"
                            :active="route().current('managementCall')"
                        >
                            Gestion des appels
                        </ResponsiveNavLink>
                    </div>

                    <!-- Responsive Settings Options -->
                    <div class="pt-4 pb-1 border-t border-gray-200">
                        <div class="px-4">
                            <div class="font-medium text-base text-gray-800">
                                {{ page.props.auth.user.name }}
                            </div>
                            <div class="font-medium text-sm text-gray-500">
                                {{ page.props.auth.user.email }}
                            </div>
                        </div>

                        <div class="mt-3 space-y-1">
                            <ResponsiveNavLink :href="route('profile.edit')">
                                Mon profil
                            </ResponsiveNavLink>
                            <ResponsiveNavLink
                                :href="route('logout')"
                                method="post"
                                as="button"
                            >
                                Se déconnecter
                            </ResponsiveNavLink>
                        </div>
                    </div>
                </div>
            </nav>

            <!-- Header Section -->
            <header
                class="bg-white text-gray-800 shadow z-50"
                v-if="$slots.header"
            >
                <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                    <slot name="header" />
                </div>
            </header>

            <!-- MAIN CONTENT SLOT -->
            <main class="flex-1 overflow-y-auto">
                <div class="mx-auto py-6 px-4 sm:px-6 lg:px-8">
                    <slot />
                </div>
            </main>

            <!-- <Footer /> -->
        </div>
    </div>
</template>

<style scoped>
.fade-enter-active,
.fade-leave-active {
    transition: all 0.3s ease;
}
.fade-enter-from,
.fade-leave-to {
    opacity: 0;
    transform: translateY(-10px);
}
</style>
