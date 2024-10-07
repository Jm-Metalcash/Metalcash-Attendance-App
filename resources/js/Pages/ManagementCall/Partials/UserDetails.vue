<template>
    <div
        v-if="user"
        class="bg-white overflow-hidden shadow rounded-lg border mt-8"
    >
        <div class="px-4 py-5 sm:px-6 bg-gray-100">
            <p class="mt-1 max-w-2xl text-sm text-gray-500 font-bold">
                Informations générales sur l'utilisateur.
            </p>
        </div>
        <div class="border-t border-gray-200 px-4 py-5 sm:p-0">
            <dl class="sm:divide-y sm:divide-gray-200">
                <!-- Première ligne : Prénom & Nom -->
                <div
                    class="py-3 sm:py-5 sm:grid sm:grid-cols-2 sm:gap-4 sm:px-6"
                >
                    <div class="sm:col-span-1">
                        <dt class="text-sm font-medium text-gray-500">
                            Prénom
                        </dt>
                        <dd class="mt-1 text-sm text-gray-900 sm:mt-0">
                            {{ user.prenom }}
                        </dd>
                    </div>
                    <div class="sm:col-span-1">
                        <dt class="text-sm font-medium text-gray-500">Nom</dt>
                        <dd class="mt-1 text-sm text-gray-900 sm:mt-0">
                            {{ user.nom }}
                        </dd>
                    </div>
                </div>

                <!-- Deuxième ligne : E-mail & Numéro de téléphone -->
                <div
                    class="py-3 sm:py-5 sm:grid sm:grid-cols-2 sm:gap-4 sm:px-6"
                >
                    <div class="sm:col-span-1">
                        <dt class="text-sm font-medium text-gray-500">
                            E-mail
                        </dt>
                        <dd class="mt-1 text-sm text-gray-900 sm:mt-0">
                            {{ user.email }}
                        </dd>
                    </div>
                    <div class="sm:col-span-1">
                        <dt class="text-sm font-medium text-gray-500">
                            Numéro de téléphone
                        </dt>
                        <dd class="mt-1 text-sm text-gray-900 sm:mt-0">
                            {{ user.telephone }}
                        </dd>
                    </div>
                </div>

                <!-- Troisième ligne : Rue et numéro & Code postal -->
                <div
                    class="py-3 sm:py-5 sm:grid sm:grid-cols-2 sm:gap-4 sm:px-6"
                >
                    <div class="sm:col-span-1">
                        <dt class="text-sm font-medium text-gray-500">
                            Rue et numéro
                        </dt>
                        <dd class="mt-1 text-sm text-gray-900 sm:mt-0">
                            {{ user.adresse.rue }}
                        </dd>
                    </div>
                    <div class="sm:col-span-1">
                        <dt class="text-sm font-medium text-gray-500">
                            Code postal
                        </dt>
                        <dd class="mt-1 text-sm text-gray-900 sm:mt-0">
                            {{ user.adresse.codePostal }}
                        </dd>
                    </div>
                </div>

                <!-- Quatrième ligne : Localité & Pays -->
                <div
                    class="py-3 sm:py-5 sm:grid sm:grid-cols-2 sm:gap-4 sm:px-6"
                >
                    <div class="sm:col-span-1">
                        <dt class="text-sm font-medium text-gray-500">
                            Localité
                        </dt>
                        <dd class="mt-1 text-sm text-gray-900 sm:mt-0">
                            {{ user.adresse.localite }}
                        </dd>
                    </div>
                    <div class="sm:col-span-1">
                        <dt class="text-sm font-medium text-gray-500">Pays</dt>
                        <dd class="mt-1 text-sm text-gray-900 sm:mt-0">
                            {{ user.adresse.pays }}
                        </dd>
                    </div>
                </div>

                <!-- Section Description -->
                <div>
                    <div class="px-4 py-5 sm:px-6 bg-gray-100">
                        <p
                            class="mt-1 max-w-2xl text-sm text-gray-500 font-bold"
                        >
                            Description
                        </p>
                    </div>
                    <div class="py-3 sm:py-5 sm:px-6">
                        <p class="text-sm text-gray-700">
                            Aucune description actuellement pour cet
                            utilisateur.
                        </p>
                    </div>
                </div>
            </dl>
        </div>
    </div>
</template>

<script setup>
defineProps({
    user: {
        type: Object,
        required: true,
    },
});
</script>

<style scoped></style>
