<script setup lang="ts">
import InputError from '@/components/InputError.vue';
import TextLink from '@/components/TextLink.vue';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AuthBase from '@/layouts/AuthLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { LoaderCircle } from 'lucide-vue-next';

defineProps<{
    status?: string;
    canResetPassword: boolean;
}>();

const form = useForm({
    email: '',
    password: '',
    remember: false,
});

const submit = () => {
    form.post(route('login'), {
        onFinish: () => form.reset('password'),
    });
};
</script>

<template>
    <div class="flex min-h-screen flex-col items-center bg-[#042c68] p-6 text-[#1b1b18]  lg:justify-center lg:p-8">
    <div class="duration-750 starting:opacity-0 flex w-full items-center justify-center opacity-100 transition-opacity lg:grow">
        <main class="flex w-full max-w-[335px] flex-col-reverse overflow-hidden rounded-lg lg:max-w-4xl lg:flex-row">
            <div
                class="flex-1 rounded-bl-lg rounded-br-lg bg-[#f3b41c] p-6  text-[13px] leading-[20px] shadow-[inset_0px_0px_0px_1px_rgba(26,26,0,0.16)]  lg:rounded-br-none lg:rounded-tl-lg lg:p-20">

                <Head title="Log in" />

                <div v-if="status" class="mb-4 text-center text-sm font-medium text-green-600">
                    {{ status }}
                </div>
                <h1 class="mb-1  font-bold text-2xl text-[#042c68] flex space-x-8 mb-8 ">Log in</h1>
                <form @submit.prevent="submit" class="flex flex-col gap-6 text-[#042c68]">
                    <div class="grid gap-6">
                        <div class="grid gap-2 ">
                            <Label for="email" >Email address</Label>
                            <Input
                                id="email"
                                type="email"
                                required
                                autofocus
                                :tabindex="1"
                                autocomplete="email"
                                v-model="form.email"
                                placeholder="email@example.com"
                            />
                            <InputError :message="form.errors.email" />
                        </div>

                        <div class="grid gap-2">
                            <div class="flex items-center justify-between">
                                <Label for="password">Password</Label>
                                <TextLink v-if="canResetPassword" :href="route('password.request')" class="text-sm" :tabindex="5">
                                    Forgot password?
                                </TextLink>
                            </div>
                            <Input
                                id="password"
                                type="password"
                                required
                                :tabindex="2"
                                autocomplete="current-password"
                                v-model="form.password"
                                placeholder="Password"
                            />
                            <InputError :message="form.errors.password" />
                        </div>

                        <div class="flex items-center justify-between" :tabindex="3">
                            <Label for="remember" class="flex items-center space-x-3">
                                <Checkbox id="remember" v-model="form.remember" :tabindex="4" />
                                <span>Remember me</span>
                            </Label>
                        </div>

                        <Button type="submit" class="mt-4 w-full
                        inline-block rounded-sm bg-[#042c68]
                         px-5 py-1.5 mx-2  text-sm leading-normal text-white cursor-pointer hover:bg-[#1D4BA8]
                        " :tabindex="4" :disabled="form.processing">
                            <LoaderCircle v-if="form.processing" class="h-4 w-4 animate-spin" />
                            Log in
                        </Button>
                    </div>

                    <div class="text-center text-sm  text-muted-[#042c68]">
                        Don't have an account ?
                        <TextLink :href="route('register')" :tabindex="5" >Sign up</TextLink>
                    </div>
                </form>


            </div>
            <div class=" flex items-center justify-center  relative -mb-px aspect-335/376 w-1/3 shrink-0 overflow-hidden rounded-t-lg bg-white  lg:-ml-px lg:mb-0 lg:aspect-auto lg:w-[438px] lg:rounded-r-lg lg:rounded-t-none">
                <div v-if="$page.props.auth.user">
                    <a href="/dashboard"><img src="/images/Naftal.png" class="p-10 "/></a>
                </div>

                <div v-if="!$page.props.auth.user">
                    <a href="/login"><img src="/images/Naftal.png" class="p-10 "/></a>
                </div>

            </div>
        </main>
    </div>
    <div class="h-14.5 hidden lg:block"></div>
    </div>
</template>
