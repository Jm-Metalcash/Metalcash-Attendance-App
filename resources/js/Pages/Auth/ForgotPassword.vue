<script setup>
import GuestLayout from "@/Layouts/GuestLayout.vue";
import InputError from "@/Components/InputError.vue";
import InputLabel from "@/Components/InputLabel.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import TextInput from "@/Components/TextInput.vue";
import { Head, useForm, Link } from "@inertiajs/vue3";

defineProps({
    status: {
        type: String,
    },
});

const form = useForm({
    email: "",
});

const submit = () => {
    form.post(route("password.email"));
};
</script>

<template>
    <GuestLayout>
        <Head title="Mot de passe oublié" />

        <div class="mb-4 text-sm text-gray-600">
            Mot de passe oublié ? Pas de problème. Indiquez-nous simplement
            votre adresse e-mail et nous vous enverrons un lien de
            réinitialisation de mot de passe par e-mail, vous permettant de
            choisir un nouveau mot de passe..
        </div>

        <div v-if="status" class="mb-4 font-medium text-sm text-green-600">
            {{ status }}
        </div>

        <form @submit.prevent="submit">
            <div>
                <InputLabel for="email" value="E-mail" />

                <TextInput
                    id="email"
                    type="email"
                    class="mt-1 block w-full"
                    v-model="form.email"
                    required
                    autofocus
                    autocomplete="username"
                    placeholder="exemple@metalcash.be"
                />

                <InputError class="mt-2" :message="form.errors.email" />
            </div>

            <div class="flex items-center justify-center mt-4 w-full">
                <PrimaryButton
                    :class="[
                        { 'opacity-25': form.processing },
                        'w-full',
                        'flex',
                        'justify-center',
                        'text-center',
                    ]"
                    :disabled="form.processing"
                >
                    Envoyer le lien de réinitialisation
                </PrimaryButton>
            </div>
        </form>

        <div
            class="mt-4 text-center text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
        >
            <Link :href="route('login')">
                <i class="fa-solid fa-arrow-left-long"></i> Retour à la page de
                connexion
            </Link>
        </div>
    </GuestLayout>
</template>
