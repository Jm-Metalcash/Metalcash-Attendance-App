<script setup>
import { Link } from "@inertiajs/vue3";
import { computed } from "vue";

const logoUrl = computed(() => "/images/logo-HD.png");
</script>

<template>
    <footer class="bg-gray-800 shadow">
            <div class="w-full max-w-screen-xl mx-auto p-4 md:py-8">
                <div class="sm:flex sm:items-center sm:justify-between">
                    <Link
                        :href="route('dashboard')"
                        class="flex items-center mb-4 sm:mb-0 space-x-3 rtl:space-x-reverse"
                    >
                        <img :src="logoUrl" alt="Logo Metalcash" class="h-8" />
                        <span
                            class="self-center text-2xl font-semibold whitespace-nowrap text-gray-100"
                            >Metalcash</span
                        >
                    </Link>

                    <ul
                        class="flex flex-wrap items-center mb-6 text-sm font-medium text-gray-200 sm:mb-0"
                    >
                        <li>
                            <Link :href="route('dashboard')" class="hover:underline me-4 md:me-6"
                                >Aujourd'hui</Link
                            >
                        </li>
                        <li>
                            <Link :href="route('historique')" class="hover:underline me-4 md:me-6"
                                >Historique</Link
                            >
                        </li>
                        <li>
                            <Link :href="route('calendrier')" class="hover:underline me-4 md:me-6"
                                >Calendrier</Link
                            >
                        </li>
                    </ul>
                </div>
                <hr
                    class="my-6 border-gray-200 sm:mx-auto lg:my-8"
                />
                <span
                    class="block text-sm text-gray-200 sm:text-center"
                    >© 2024
                    <a href="https://www.metalcash.be/" class="hover:underline"
                        >Metalcash™</a
                    >. Tous droits réservés.</span
                >
            </div>
        </footer>
</template>