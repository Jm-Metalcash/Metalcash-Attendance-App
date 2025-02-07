<script setup>
import { ref, computed, onMounted, watch } from "vue";
import { Link, usePage } from "@inertiajs/vue3";
import { contactCount, initializeContactCount } from '@/Stores/contactStore';

const page = usePage();
const showingNavigationDropdown = ref(false); // Menu mobile
const isMenuCollapsed = ref(false); // État du menu aside sur desktop
const showTimeManagementSubMenu = ref(false);
const showEmployeeManagementSubMenu = ref(false);
const showCallManagementSubMenu = ref(false);
const logoUrl = computed(() => "/images/logo-HD.png");

// Sauvegarder l'état du menu dans le localStorage
const saveMenuState = () => {
    localStorage.setItem('menuState', JSON.stringify({
        isCollapsed: isMenuCollapsed.value,
        timeManagement: showTimeManagementSubMenu.value,
        employeeManagement: showEmployeeManagementSubMenu.value,
        callManagement: showCallManagementSubMenu.value
    }));
};

// Charger l'état du menu depuis le localStorage
onMounted(() => {
    const savedState = localStorage.getItem('menuState');
    if (savedState) {
        const state = JSON.parse(savedState);
        isMenuCollapsed.value = state.isCollapsed;
        showTimeManagementSubMenu.value = state.timeManagement;
        showEmployeeManagementSubMenu.value = state.employeeManagement;
        showCallManagementSubMenu.value = state.callManagement;
    }
    if (page.props.initialContactCount !== undefined) {
        initializeContactCount(page.props.initialContactCount);
    }
});

// Mettre à jour le localStorage quand l'état change
watch([isMenuCollapsed, showTimeManagementSubMenu, showEmployeeManagementSubMenu, showCallManagementSubMenu], () => {
    saveMenuState();
});

// Mettre à jour le compteur quand les props changent
watch(() => page.props.initialContactCount, (newCount) => {
    if (newCount !== undefined) {
        initializeContactCount(newCount);
    }
});
</script>

<template>
    <div class="flex h-screen bg-gray-900">
        <!-- SIDEBAR (Desktop/Tablet) -->
        <aside
            :class="{
                'w-80': !isMenuCollapsed,
                'w-48': isMenuCollapsed,
            }"
            class="hidden lg:flex bg-gray-800 text-gray-300 flex-col transition-all duration-300"
        >
            <!-- Header Logo and Collapse -->
            <header
                class="flex items-center justify-between border-b border-gray-700 py-4 px-6"
            >
                <Link :href="route('index')" class="flex items-center">
                    <img
                        :src="logoUrl"
                        alt="Logo"
                        :class="{ 'w-16 h-16': !isMenuCollapsed, 'w-12 h-12': isMenuCollapsed }"
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
                            <span :class="{ 'text-[10px]': isMenuCollapsed, 'text-sm': !isMenuCollapsed }">Gestion du temps de travail</span>
                            <i
                                :class="{
                                    'fa-chevron-down text-xs': !showTimeManagementSubMenu,
                                    'fa-chevron-up text-xs': showTimeManagementSubMenu,
                                    'ml-auto': !isMenuCollapsed,
                                    'hidden': isMenuCollapsed
                                }"
                                class="fa"
                            ></i>
                        </div>
                        <transition name="fade">
                            <ul
                                v-if="showTimeManagementSubMenu"
                                :class="{ 'pl-2': isMenuCollapsed, 'pl-8': !isMenuCollapsed }"
                                class="space-y-2"
                            >
                                <li class="whitespace-nowrap overflow-hidden text-ellipsis">
                                    <Link
                                        :href="route('dashboard')"
                                        class="block px-2 py-2 rounded hover:bg-gray-700"
                                        :class="{
                                            'bg-gray-700': route().current('dashboard'),
                                            'text-[10px]': isMenuCollapsed,
                                            'text-sm': !isMenuCollapsed
                                        }"
                                    >
                                        <i class="fa-solid fa-stopwatch" :class="{ 'mr-1': isMenuCollapsed, 'mr-2': !isMenuCollapsed }"></i>
                                        <span :class="{ 'text-[10px]': isMenuCollapsed, 'text-sm': !isMenuCollapsed }">Pointage</span>
                                    </Link>
                                </li>
                                <li class="whitespace-nowrap overflow-hidden text-ellipsis">
                                    <Link
                                        :href="route('historique')"
                                        class="block px-2 py-2 rounded hover:bg-gray-700"
                                        :class="{
                                            'bg-gray-700': route().current('historique'),
                                            'text-[10px]': isMenuCollapsed,
                                            'text-sm': !isMenuCollapsed
                                        }"
                                    >
                                        <i class="fa-regular fa-calendar" :class="{ 'mr-1': isMenuCollapsed, 'mr-2': !isMenuCollapsed }"></i>
                                        <span :class="{ 'text-[10px]': isMenuCollapsed, 'text-sm': !isMenuCollapsed }">Historique</span>
                                    </Link>
                                </li>
                            </ul>
                        </transition>
                    </li>

                    <!-- Gestion des employés -->
                    <li v-if="
                                page.props.auth.roles &&
                                (page.props.auth.roles.includes('Admin') ||
                                    page.props.auth.roles.includes(
                                        'Informatique'
                                    ))
                            ">
                        <div
                            @click="
                                showEmployeeManagementSubMenu =
                                    !showEmployeeManagementSubMenu
                            "
                            class="flex items-center px-4 py-3 rounded cursor-pointer hover:bg-gray-700"
                        >
                            <i class="fa fa-user mr-3"></i>
                            <span :class="{ 'text-[10px]': isMenuCollapsed, 'text-sm': !isMenuCollapsed }">Gestion des employés</span>
                            <i
                                :class="{
                                    'fa-chevron-down text-xs': !showEmployeeManagementSubMenu,
                                    'fa-chevron-up text-xs': showEmployeeManagementSubMenu,
                                    'ml-auto': !isMenuCollapsed,
                                    'hidden': isMenuCollapsed
                                }"
                                class="fa"
                            ></i>
                        </div>
                        <transition name="fade">
                            <ul
                                v-if="showEmployeeManagementSubMenu"
                                :class="{ 'pl-2': isMenuCollapsed, 'pl-8': !isMenuCollapsed }"
                                class="space-y-2"
                            >
                                <li class="border-b-2 border-gray-500 whitespace-nowrap overflow-hidden text-ellipsis">
                                    <Link
                                        :href="route('employes')"
                                        class="block px-2 py-2 rounded hover:bg-gray-700"
                                        :class="{
                                            'bg-gray-700': route().current('employes'),
                                            'text-[10px]': isMenuCollapsed,
                                            'text-sm': !isMenuCollapsed
                                        }"
                                    >
                                        <i class="fa-solid fa-users" :class="{ 'mr-1': isMenuCollapsed, 'mr-2': !isMenuCollapsed }"></i>
                                        <span :class="{ 'text-[10px]': isMenuCollapsed, 'text-sm': !isMenuCollapsed }">Liste des employés</span>
                                    </Link>
                                </li>
                                
                                <li
                                    v-for="user in page.props.users"
                                    :key="user.id"
                                    class="whitespace-nowrap overflow-hidden text-ellipsis block px-2 py-2 rounded hover:bg-gray-700"
                                >
                                    <i class="fa fa-user" :class="{ 'mr-1': isMenuCollapsed, 'mr-3': !isMenuCollapsed }"></i>
                                    <Link :href="route('users.pointages', user.id)">
                                        <span :class="{ 'text-[10px]': isMenuCollapsed, 'text-sm': !isMenuCollapsed }">
                                            {{ user.name }}
                                            <span class="text-gray-400">
                                                ({{ user.roles.map((role) => role.name).join(", ") }})
                                            </span>
                                        </span>
                                    </Link>
                                </li>
                            </ul>
                        </transition>
                    </li>

                    <!-- Gestion des appels -->
                    <li v-if="
                                page.props.auth.roles &&
                                (page.props.auth.roles.includes('Admin') ||
                                    page.props.auth.roles.includes(
                                        'Informatique'
                                    )||
                                    page.props.auth.roles.includes(
                                        'Comptabilité'
                                    ))
                            ">
                        <div
                            @click="showCallManagementSubMenu = !showCallManagementSubMenu"
                            class="flex items-center px-4 py-3 rounded cursor-pointer hover:bg-gray-700"
                            :class="{
                                'bg-gray-700': route().current('managementCall') || route().current('contactRelance'),
                            }"
                        >
                            <i class="fa fa-phone mr-3"></i>
                            <span :class="{ 'text-[10px]': isMenuCollapsed, 'text-sm': !isMenuCollapsed }">Gestion des appels</span>
                            <i
                                :class="{
                                    'fa-chevron-down text-xs': !showCallManagementSubMenu,
                                    'fa-chevron-up text-xs': showCallManagementSubMenu,
                                    'ml-auto': !isMenuCollapsed,
                                    'hidden': isMenuCollapsed
                                }"
                                class="fa"
                            ></i>
                        </div>
                        <transition name="fade">
                            <ul
                                v-if="showCallManagementSubMenu"
                                :class="{ 'pl-2': isMenuCollapsed, 'pl-8': !isMenuCollapsed }"
                                class="space-y-2"
                            >
                                <li class="whitespace-nowrap overflow-hidden text-ellipsis">
                                    <Link
                                        :href="route('managementCall')"
                                        class="block px-2 py-2 rounded hover:bg-gray-700"
                                        :class="{
                                            'bg-gray-700': route().current('managementCall'),
                                            'text-[10px]': isMenuCollapsed,
                                            'text-sm': !isMenuCollapsed
                                        }"
                                    >
                                        <i class="fa-solid fa-phone-volume" :class="{ 'mr-1': isMenuCollapsed, 'mr-2': !isMenuCollapsed }"></i>
                                        <span :class="{ 'text-[10px]': isMenuCollapsed, 'text-sm': !isMenuCollapsed }">Gestion des fournisseurs</span>
                                    </Link>
                                </li>
                                <li v-if="page.props.auth.roles && (page.props.auth.roles.includes('Admin') || page.props.auth.roles.includes('Informatique') ||  page.props.auth.roles.includes('Comptabilité'))" class="whitespace-nowrap overflow-hidden text-ellipsis">
                                    <Link
                                        :href="route('contactRelance')"
                                        class="flex items-center px-2 py-2 rounded hover:bg-gray-700"
                                        :class="{
                                            'bg-gray-700': route().current('contactRelance'),
                                            'text-[10px]': isMenuCollapsed,
                                            'text-sm': !isMenuCollapsed
                                        }"
                                    >
                                        <i class="fa-solid fa-phone" :class="{ 'mr-1': isMenuCollapsed, 'mr-2': !isMenuCollapsed }"></i>
                                        <span :class="{ 'text-[10px]': isMenuCollapsed, 'text-sm': !isMenuCollapsed }">Demandes de rappel</span>
                                        <div class="ml-2 bg-red-500 text-white rounded-full px-2 py-1" :class="{ 'text-[8px]': isMenuCollapsed, 'text-xs': !isMenuCollapsed }">
                                            {{ contactCount }}
                                        </div>
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
                        
                    </li>
                </ul>
            </nav>

            <!-- Footer / Logout -->
            <footer
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
            <nav class="bg-white shadow-sm lg:hidden">
                <!-- Top Bar Mobile -->
                <div class="px-4">
                    <div class="flex justify-between h-16">
                        <div class="flex items-center">
                            <!-- Logo -->
                            <Link :href="route('index')" class="flex items-center">
                                <img :src="logoUrl" alt="Logo" class="w-10 h-10" />
                                <span class="text-gray-900 font-bold ml-2 text-lg">Metalcash</span>
                            </Link>
                        </div>

                        <!-- Hamburger -->
                        <button
                            @click="showingNavigationDropdown = !showingNavigationDropdown"
                            class="p-2 rounded-lg text-gray-500 hover:bg-gray-100 focus:outline-none transition duration-150 ease-in-out"
                        >
                            <svg
                                class="h-6 w-6"
                                stroke="currentColor"
                                fill="none"
                                viewBox="0 0 24 24"
                            >
                                <path
                                    :class="{ hidden: showingNavigationDropdown, 'inline-flex': !showingNavigationDropdown }"
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M4 6h16M4 12h16M4 18h16"
                                />
                                <path
                                    :class="{ hidden: !showingNavigationDropdown, 'inline-flex': showingNavigationDropdown }"
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M6 18L18 6M6 6l12 12"
                                />
                            </svg>
                        </button>
                    </div>
                </div>

                <!-- Menu Mobile -->
                <transition
                    enter-active-class="transition duration-300 ease-out"
                    enter-from-class="transform -translate-x-full opacity-0"
                    enter-to-class="transform translate-x-0 opacity-100"
                    leave-active-class="transition duration-200 ease-in"
                    leave-from-class="transform translate-x-0 opacity-100"
                    leave-to-class="transform -translate-x-full opacity-0"
                >
                    <div
                        v-show="showingNavigationDropdown"
                        class="lg:hidden fixed inset-0 z-40"
                    >
                        <!-- Overlay sombre -->
                        <div 
                            class="fixed inset-0 bg-black bg-opacity-25 transition-opacity"
                            @click="showingNavigationDropdown = false"
                        ></div>

                        <!-- Contenu du menu -->
                        <div class="fixed inset-y-0 left-0 w-3/4 max-w-sm bg-white overflow-y-auto">
                            <!-- User Info -->
                            <div class="px-4 py-4 border-b border-gray-200 bg-white">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0">
                                        <div class="h-10 w-10 rounded-full bg-[rgb(0,86,146)] text-white flex items-center justify-center font-semibold text-lg">
                                            {{ page.props.auth.user.name.charAt(0).toUpperCase() }}
                                        </div>
                                    </div>
                                    <div class="ml-3">
                                        <div class="text-base font-medium text-gray-800">
                                            {{ page.props.auth.user.name }}
                                        </div>
                                        <div class="text-sm font-medium text-gray-500">
                                            {{ page.props.auth.roles[0] }}
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Navigation Links -->
                            <div class="px-2 py-3">
                                <div class="space-y-1">
                                    <!-- Gestion du temps -->
                                    <div class="px-3 py-2 text-sm font-medium text-gray-400 uppercase tracking-wider">
                                        Gestion du temps
                                    </div>
                                    <Link
                                        :href="route('dashboard')"
                                        class="mobile-nav-link"
                                        :class="{ 'bg-[rgb(0,86,146)] text-white': route().current('dashboard') }"
                                    >
                                        <i class="fas fa-stopwatch mr-3"></i>
                                        <span>Pointage</span>
                                    </Link>
                                    <Link
                                        :href="route('historique')"
                                        class="mobile-nav-link"
                                        :class="{ 'bg-[rgb(0,86,146)] text-white': route().current('historique') }"
                                    >
                                        <i class="fas fa-history mr-3"></i>
                                        <span>Historique</span>
                                    </Link>

                                    <!-- Administration -->
                                    <template v-if="page.props.auth.roles && (page.props.auth.roles.includes('Admin') || page.props.auth.roles.includes('Informatique') || page.props.auth.roles.includes('Comptabilité'))">
                                        <div class="mt-4 px-3 py-2 text-sm font-medium text-gray-400 uppercase tracking-wider">
                                            Administration
                                        </div>
                                        <Link
                                            v-if="page.props.auth.roles && (page.props.auth.roles.includes('Admin') || page.props.auth.roles.includes('Informatique'))"
                                            :href="route('employes')"
                                            class="mobile-nav-link"
                                            :class="{ 'bg-[rgb(0,86,146)] text-white': route().current('employes') }"
                                        >
                                            <i class="fas fa-users mr-3"></i>
                                            <span>Gestion des employés</span>
                                        </Link>
                                        <Link
                                            v-if="page.props.auth.roles && (page.props.auth.roles.includes('Admin') || page.props.auth.roles.includes('Informatique') || page.props.auth.roles.includes('Comptabilité'))"
                                            :href="route('managementCall')"
                                            class="mobile-nav-link"
                                            :class="{ 'bg-[rgb(0,86,146)] text-white': route().current('managementCall') }"
                                        >
                                            <i class="fas fa-phone mr-3"></i>
                                            <span>Gestion des appels</span>
                                        </Link>
                                        <Link
                                            v-if="page.props.auth.roles && (page.props.auth.roles.includes('Admin') || page.props.auth.roles.includes('Informatique') || page.props.auth.roles.includes('Comptabilité'))"
                                            :href="route('contactRelance')"
                                            class="mobile-nav-link"
                                            :class="{ 'bg-[rgb(0,86,146)] text-white': route().current('contactRelance') }"
                                        >
                                            <i class="fa-solid fa-phone-volume mr-3"></i>
                                            <span>Demandes de rappel</span>
                                            <span v-if="contactCount > 0" class="ml-auto bg-red-500 text-white text-xs px-2 py-1 rounded-full">
                                                {{ contactCount }}
                                            </span>
                                        </Link>
                                    </template>

                                    <!-- Paramètres -->
                                    <div class="mt-4 px-3 py-2 text-sm font-medium text-gray-400 uppercase tracking-wider">
                                        Paramètres
                                    </div>
                                    <Link
                                        :href="route('profile.edit')"
                                        class="mobile-nav-link"
                                        :class="{ 'bg-[rgb(0,86,146)] text-white': route().current('profile.edit') }"
                                    >
                                        <i class="fas fa-user-circle mr-3"></i>
                                        <span>Mon profil</span>
                                    </Link>
                                    <Link
                                        :href="route('logout')"
                                        method="post"
                                        as="button"
                                        class="mobile-nav-link text-red-600 hover:text-red-700 hover:bg-red-50"
                                    >
                                        <i class="fas fa-sign-out-alt mr-3"></i>
                                        <span>Déconnexion</span>
                                    </Link>
                                </div>
                            </div>
                        </div>
                    </div>
                </transition>
            </nav>

            <!-- Header Section -->
            <header
                class="bg-white text-gray-800 shadow"
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

.mobile-nav-link {
    @apply flex items-center px-3 py-2 text-sm font-medium text-gray-700 rounded-lg hover:bg-gray-100 transition-all duration-200 w-full;
}

.mobile-nav-link.router-link-active {
    @apply bg-[rgb(0,86,146)] text-white;
}

.slide-fade-enter-active {
    transition: all 0.3s ease-out;
}

.slide-fade-leave-active {
    transition: all 0.2s ease-in;
}

.slide-fade-enter-from,
.slide-fade-leave-to {
    transform: translateX(-100%);
    opacity: 0;
}

.bg-opacity-25 {
    transition: opacity 0.3s ease-in-out;
}
</style>