<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import { Link } from '@inertiajs/vue3';

const form = useForm({
    code: '',
});

const submit = () => {
    form.post(route('login.perform'));
};
</script>

<template>
    <GuestLayout>
        <Head title="Login" />

        <div class="max-w-md mx-auto rounded-lg p-6 text-white">
            <div>
                <Link href="/">
                    <ApplicationLogo class="h-20 w-20 fill-current text-gray-500 mx-auto mb-5" />
                </Link>
            </div>
            <h1 class="text-xl font-semibold mb-4 text-center">Accesso con codice</h1>

            <form @submit.prevent="submit" class="space-y-4">
                <div>
                    <input
                        id="code"
                        v-model="form.code"
                        type="text"
                        class="mt-1 block w-full placeholder:text-[#6F6F6F] rounded-md font-semibold border-bgd shadow-sm focus:border-gray-400 focus:ring-gray-900 capitalize bg-bgd"
                        autocomplete="off"
                        maxlength="8"
                        placeholder="Inserisci il codice"
                        required
                    />
                    <div v-if="form.errors.code" class="mt-1 text-sm text-red-600">
                        {{ form.errors.code }}
                    </div>
                </div>

                <button
                    type="submit"
                    class="w-full inline-flex justify-center items-center px-4 py-2 rounded-md border border-transparent text-sm font-medium text-black bg-gradient-to-r from-amber-200 to-yellow-500"
                    :disabled="form.processing"
                >
                    Entra
                </button>
            </form>
        </div>
    </GuestLayout>
</template>
