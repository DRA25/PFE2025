<template>
    <div class="min-h-screen bg-[#e0f2fe] py-6 flex flex-col justify-center sm:py-12">
        <div class="relative py-3 sm:max-w-3xl sm:mx-auto">
            <div class="absolute inset-0 bg-gradient-to-br from-[#042c68]/90 to-[#1d4ba8]/90 shadow-lg transform -skew-y-6 sm:skew-y-0 sm:-rotate-6 sm:rounded-3xl"></div>
            <div
                ref="formContainer"
                class="relative bg-gray-100 shadow-xl sm:rounded-3xl flex flex-col md:flex-row overflow-hidden transition-all duration-300"
                :class="{ 'opacity-100 scale-100': isMounted, 'opacity-0 scale-95': !isMounted }"
            >
                <div class="md:w-1/2 p-12 flex items-center justify-center bg-[#F3B21B]">
                    <img src="/images/Naftal.png" alt="Logo Naftal" class="max-w-full max-h-72 object-contain transition-opacity duration-300 hover:opacity-90" />
                </div>
                <div class="md:w-1/2 p-8">
                    <div class="max-w-md mx-auto">
                        <Head title="S'inscrire" />

                        <div class="mb-8">
                            <h2 class="text-2xl font-semibold text-[#042c68] tracking-tight">Créer un compte</h2>
                            <p class="text-gray-600 text-sm">Veuillez remplir les informations ci-dessous pour créer votre compte.</p>
                        </div>

                        <div v-if="status" class="mb-4 text-center text-sm font-medium text-green-600">
                            {{ status }}
                        </div>

                        <form @submit.prevent="submit" class="space-y-6">
                            <div class="space-y-4">
                                <div>
                                    <Label for="name" class="block text-sm font-medium text-gray-700">Nom</Label>
                                    <div class="mt-1">
                                        <Input
                                            id="name"
                                            type="text"
                                            required
                                            autofocus
                                            :tabindex="1"
                                            autocomplete="name"
                                            v-model="form.name"
                                            placeholder="Nom complet"
                                            class="shadow-sm focus:ring-[#f3b41c] focus:border-[#f3b41c] block w-full sm:text-sm border-gray-300 rounded-md transition-shadow duration-200 focus:shadow-md"
                                        />
                                        <InputError :message="form.errors.name" class="mt-2 text-red-500 text-sm" />
                                    </div>
                                </div>

                                <div>
                                    <Label for="email" class="block text-sm font-medium text-gray-700">Adresse e-mail</Label>
                                    <div class="mt-1">
                                        <Input
                                            id="email"
                                            type="email"
                                            required
                                            :tabindex="2"
                                            autocomplete="email"
                                            v-model="form.email"
                                            placeholder="votre.email@naftal.dz"
                                            class="shadow-sm focus:ring-[#f3b41c] focus:border-[#f3b41c] block w-full sm:text-sm border-gray-300 rounded-md transition-shadow duration-200 focus:shadow-md"
                                        />
                                        <InputError :message="form.errors.email" class="mt-2 text-red-500 text-sm" />
                                    </div>
                                </div>

                                <div>
                                    <Label for="password" class="block text-sm font-medium text-gray-700">Mot de passe</Label>
                                    <div class="mt-1">
                                        <Input
                                            id="password"
                                            type="password"
                                            required
                                            :tabindex="3"
                                            autocomplete="new-password"
                                            v-model="form.password"
                                            placeholder="••••••••"
                                            class="shadow-sm focus:ring-[#f3b41c] focus:border-[#f3b41c] block w-full sm:text-sm border-gray-300 rounded-md transition-shadow duration-200 focus:shadow-md"
                                        />
                                        <InputError :message="form.errors.password" class="mt-2 text-red-500 text-sm" />
                                    </div>
                                </div>

                                <div>
                                    <Label for="password_confirmation" class="block text-sm font-medium text-gray-700">Confirmer le mot de passe</Label>
                                    <div class="mt-1">
                                        <Input
                                            id="password_confirmation"
                                            type="password"
                                            required
                                            :tabindex="4"
                                            autocomplete="new-password"
                                            v-model="form.password_confirmation"
                                            placeholder="••••••••"
                                            class="shadow-sm focus:ring-[#f3b41c] focus:border-[#f3b41c] block w-full sm:text-sm border-gray-300 rounded-md transition-shadow duration-200 focus:shadow-md"
                                        />
                                        <InputError :message="form.errors.password_confirmation" class="mt-2 text-red-500 text-sm" />
                                    </div>
                                </div>
                            </div>

                            <div>
                                <Button
                                    type="submit"
                                    class="w-full flex justify-center py-3 px-4 border border-transparent rounded-md shadow-md text-sm font-medium text-white bg-[#042c68] hover:bg-[#1d4ba8] focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#042c68] transition-colors duration-200"
                                    :tabindex="5"
                                    :disabled="form.processing"
                                >
                                    <LoaderCircle v-if="form.processing" class="h-5 w-5 mr-2 animate-spin text-white" />
                                    Créer un compte
                                </Button>
                            </div>
                        </form>

                        <div class="mt-6 text-center text-sm text-gray-500">
                            Déjà un compte?
                            <TextLink :href="route('login')" class="font-medium text-[#042c68] hover:underline transition-colors duration-200" :tabindex="6">Se connecter</TextLink>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import InputError from '@/components/InputError.vue';
import TextLink from '@/components/TextLink.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { LoaderCircle } from 'lucide-vue-next';
import { ref, onMounted } from 'vue';

const form = useForm({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
});

const formContainer = ref<HTMLDivElement | null>(null);
const isMounted = ref(false);

const submit = () => {
    form.post(route('register'), {
        onFinish: () => form.reset('password', 'password_confirmation'),
    });
};

onMounted(() => {
    setTimeout(() => {
        isMounted.value = true;
    }, 100); // Small delay for smooth transition
});
</script>
