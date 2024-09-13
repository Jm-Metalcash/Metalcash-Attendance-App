<script setup>
import Checkbox from "@/Components/Checkbox.vue";
import GuestLayout from "@/Layouts/GuestLayout.vue";
import InputError from "@/Components/InputError.vue";
import InputLabel from "@/Components/InputLabel.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import TextInput from "@/Components/TextInput.vue";
import { Head, Link, useForm } from "@inertiajs/vue3";


defineProps({
    canResetPassword: {
        type: Boolean,
    },
    status: {
        type: String,
    },
});



const form = useForm({
    email: "",
    password: "",
    remember: false,
});

const submit = () => {
    form.post(route("login"), {
        onFinish: () => form.reset("password"),
    });
};
</script>

<template>
    <GuestLayout>
        <Head title="Connexion" />

        <div v-if="status" class="mb-4 font-medium text-sm text-green-600">
            {{ status }}
        </div>

        <form
            @submit.prevent="submit"
            class="bg-white shadow-md rounded px-8 pt-6 pb-8 mb-4"
        >
            <div class="mb-4 relative">
                <InputLabel for="email" value="E-mail" />

                <div class="relative flex items-center">
                    <TextInput
                        id="email"
                        type="email"
                        class="mt-1 block w-full rounded-md border border-gray-300 focus:ring-indigo-500 focus:border-indigo-500 pr-10"
                        v-model="form.email"
                        required
                        autofocus
                        autocomplete="username"
                        placeholder="exemple@metalcash.be"
                    />
                    <i
                        class="fas fa-envelope absolute right-3 text-gray-500"
                    ></i>
                </div>

                <InputError class="mt-2" :message="form.errors.email" />
            </div>

            <div class="mb-4 relative">
                <InputLabel for="password" value="Mot de passe" />

                <div class="relative flex items-center">
                    <TextInput
                        id="password"
                        type="password"
                        class="mt-1 block w-full rounded-md border border-gray-300 focus:ring-indigo-500 focus:border-indigo-500 pr-10"
                        v-model="form.password"
                        required
                        autocomplete="current-password"
                        placeholder="*********"
                    />
                    <i
                        class="fa-solid fa-lock absolute right-3 text-gray-500"
                    ></i>
                </div>

                <InputError class="mt-2" :message="form.errors.password" />
            </div>

            <div class="flex items-center justify-between mb-4">
                <label class="flex items-center">
                    <Checkbox name="remember" v-model:checked="form.remember" />
                    <span class="ms-2 text-sm text-gray-600">Se souvenir de moi</span>
                </label>

                <Link
                    :href="route('password.request')"
                    class="text-sm text-indigo-600 hover:text-indigo-800"
                >
                    Mot de passe oublié?
                </Link>
            </div>

            <div class="flex items-center justify-center mt-8">
                <PrimaryButton>
                    Se connecter
                </PrimaryButton>
            </div>
        </form>
    </GuestLayout>
</template>
