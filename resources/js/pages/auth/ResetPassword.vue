<script setup lang="ts">
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Head, useForm } from '@inertiajs/vue3';
import { LoaderCircle } from 'lucide-vue-next';
import { ref, onMounted } from 'vue';

interface Props {
    token: string;
    email: string;
}

const props = defineProps<Props>();

const form = useForm({
    token: props.token,
    email: props.email,
    password: '',
    password_confirmation: '',
});

const submit = () => {
    form.post(route('password.store'), {
        onFinish: () => {
            form.reset('password', 'password_confirmation');
        },
    });
};

const passwordInput = ref<HTMLInputElement | null>(null);
const formContainer = ref<HTMLDivElement | null>(null);
const isMounted = ref(false);

onMounted(() => {
    setTimeout(() => {
        isMounted.value = true;
    }, 100); // Petit délai pour une transition fluide
});
</script>

<template>
    <div class="min-h-screen bg-[#e0f2fe] py-6 flex flex-col justify-center sm:py-12">
        <div class="relative py-3 sm:max-w-3xl sm:mx-auto">
            <div class="absolute inset-0 bg-gradient-to-br from-[#042c68]/90 to-[#1d4ba8]/90 shadow-lg transform -skew-y-6 sm:skew-y-0 sm:-rotate-6 sm:rounded-3xl"></div>
            <div
                ref="formContainer"
                class="relative shadow-xl sm:rounded-3xl flex flex-col md:flex-row overflow-hidden transition-all duration-300"
                :class="{ 'opacity-100 scale-100': isMounted, 'opacity-0 scale-95': !isMounted }"
            >
                <div class="md:w-1/2 p-12 flex items-center justify-center bg-gray-100">
                    <img src="/images/Naftal.png" alt="Logo Naftal" class="max-w-full max-h-72 object-contain transition-opacity duration-300 hover:opacity-90" />
                </div>
                <div class="md:w-1/2 p-8 bg-gradient-to-t from-gray-100 to-white">
                    <div class="max-w-md mx-auto">
                        <Head title="Réinitialisation du mot de passe" />

                        <div class="mb-8">
                            <h2 class="text-2xl font-semibold text-[#042c68] tracking-tight">Nouveau mot de passe</h2>
                            <p class="text-gray-600 text-sm">Veuillez entrer votre nouveau mot de passe ci-dessous</p>
                        </div>

                        <form @submit.prevent="submit" class="space-y-6">
                            <div class="space-y-4">
                                <div>
                                    <Label for="email" class="block text-sm font-medium text-gray-700">Email</Label>
                                    <div class="mt-1">
                                        <Input
                                            id="email"
                                            type="email"
                                            name="email"
                                            autocomplete="email"
                                            v-model="form.email"
                                            class="shadow-sm bg-gray-100 block w-full sm:text-sm border-gray-300 rounded-md cursor-not-allowed"
                                            readonly
                                        />
                                        <InputError :message="form.errors.email" class="mt-2 text-red-500 text-sm" />
                                    </div>
                                </div>

                                <div>
                                    <Label for="password" class="block text-sm font-medium text-gray-700">Mot de passe</Label>
                                    <div class="mt-1">
                                        <Input
                                            ref="passwordInput"
                                            id="password"
                                            type="password"
                                            name="password"
                                            autocomplete="new-password"
                                            v-model="form.password"
                                            placeholder="••••••••"
                                            class="shadow-sm focus:ring-[#f3b41c] focus:border-[#f3b41c] block w-full sm:text-sm border-gray-300 rounded-md transition-shadow duration-200 focus:shadow-md"
                                            autofocus
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
                                            name="password_confirmation"
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
                                    :disabled="form.processing"
                                >
                                    <LoaderCircle v-if="form.processing" class="h-5 w-5 mr-2 animate-spin text-white" />
                                    Réinitialiser
                                </Button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
