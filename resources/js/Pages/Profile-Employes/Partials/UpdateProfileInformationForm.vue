<script setup>
import InputError from "@/Components/InputError.vue";
import InputLabel from "@/Components/InputLabel.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import TextInput from "@/Components/TextInput.vue";
import { Link, useForm, usePage } from "@inertiajs/vue3";

// Utiliser usePage pour accéder aux attributs comme user et roles
const page = usePage();

// Préparer le formulaire avec les informations de l'utilisateur
const form = useForm({
    name: page.props.user.name,
    email: page.props.user.email,
    // Utiliser l'ID du rôle actuel de l'utilisateur
    role:
        page.props.user.roles && page.props.user.roles.length > 0
            ? page.props.user.roles[0].id
            : "",
});
</script>

<template>
    <section>
        <header class="relative">
    <!-- Conteneur flex pour le titre -->
    <h2 class="text-lg font-medium text-gray-900">
        Informations du profil de {{ page.props.user.name }}
    </h2>

    <p class="mt-1 text-sm text-gray-600">
        Mettez à jour les informations de profil et l'adresse e-mail de
        l'utilisateur.
    </p>

    <!-- Bouton positionné en absolute sur desktop et en position normale sur mobile/tablette -->
    <Link :href="route('users.pointages', page.props.user.id)">
        <button
            class="bg-[rgb(0,85,150)] rounded-md hover:bg-[rgba(0,85,150,0.8)] text-gray-100 py-2 px-6 font-semibold
                   lg:absolute lg:top-0 lg:right-0 sm:relative sm:mt-6 w-full sm:w-auto mt-8 lg:mt-0 text-xs lg:text-sm uppercase"
        >
            Afficher les pointages
        </button>
    </Link>
</header>



        <!-- Formulaire de mise à jour -->
        <form
            @submit.prevent="
                form.patch(route('employees.update', page.props.user.id))
            "
            class="mt-6 space-y-6 max-w-xl"
        >
            <!-- Nom -->
            <div>
                <InputLabel for="name" value="Nom" />

                <TextInput
                    id="name"
                    type="text"
                    class="mt-1 block w-full"
                    v-model="form.name"
                    required
                    autofocus
                    autocomplete="name"
                />

                <InputError class="mt-2" :message="form.errors.name" />
            </div>

            <!-- E-mail -->
            <div>
                <InputLabel for="email" value="E-mail" />

                <TextInput
                    id="email"
                    type="email"
                    class="mt-1 block w-full"
                    v-model="form.email"
                    required
                    autocomplete="username"
                />

                <InputError class="mt-2" :message="form.errors.email" />
            </div>

            <!-- Sélection du rôle -->
            <div>
                <InputLabel for="role" value="Rôle" />

                <select
                    id="role"
                    v-model="form.role"
                    class="mt-1 block w-full pl-3 pr-10 py-2 border border-gray-300 sm:text-sm rounded-md"
                >
                    <!-- Afficher le rôle actuel de l'utilisateur en premier -->
                    <option
                        v-if="
                            page.props.user.roles &&
                            page.props.user.roles.length > 0
                        "
                        :value="page.props.user.roles[0].id"
                    >
                        {{ page.props.user.roles[0].name }}
                    </option>

                    <!-- Autres rôles disponibles -->
                    <option
                        v-for="role in page.props.roles"
                        :key="role.id"
                        :value="role.id"
                    >
                        {{ role.name }}
                    </option>
                </select>

                <InputError class="mt-2" :message="form.errors.role" />
            </div>

            <!-- Vérification de l'e-mail -->
            <div
                v-if="
                    page.props.mustVerifyEmail &&
                    page.props.user.email_verified_at === null
                "
            >
                <p class="text-sm mt-2 text-gray-800">
                    Cet utilisateur n'a pas encore vérifié son adresse e-mail.
                    <Link
                        :href="route('verification.send')"
                        method="post"
                        as="button"
                        class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                    >
                        Cliquez ici pour renvoyer l'e-mail de vérification.
                    </Link>
                </p>

                <div
                    v-show="page.props.status === 'verification-link-sent'"
                    class="mt-2 font-medium text-sm text-green-600"
                >
                    Un nouveau lien de vérification a été envoyé à votre adresse
                    e-mail.
                </div>
            </div>

            <!-- Bouton de sauvegarde -->
            <div class="flex items-center gap-4">
                <PrimaryButton :disabled="form.processing"
                    >Sauvegarder</PrimaryButton
                >

                <Transition
                    enter-active-class="transition ease-in-out"
                    enter-from-class="opacity-0"
                    leave-active-class="transition ease-in-out"
                    leave-to-class="opacity-0"
                >
                    <p
                        v-if="form.recentlySuccessful"
                        class="text-sm text-gray-600"
                    >
                        Enregistré avec succès
                    </p>
                </Transition>
            </div>
        </form>
    </section>
</template>
