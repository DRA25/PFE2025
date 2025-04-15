<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
</script>

<template>
    <Head title="Welcome">
        <link rel="preconnect" href="https://rsms.me/" />
        <link rel="stylesheet" href="https://rsms.me/inter/inter.css" />
    </Head>
    <div class="flex min-h-screen flex-col items-center bg-[#042c68] p-6 text-[#1b1b18]  lg:justify-center lg:p-8">
        <header class="not-has-[nav]:hidden mb-6 w-full max-w-[335px] text-sm lg:max-w-4xl">
            <nav class="flex items-center justify-end gap-4">


            </nav>
        </header>
        <div class="duration-750 starting:opacity-0 flex w-full items-center justify-center opacity-100 transition-opacity lg:grow">
            <main class="flex w-full max-w-[335px] flex-col-reverse overflow-hidden rounded-lg lg:max-w-4xl lg:flex-row">
                <div
                    class="flex-1 rounded-bl-lg rounded-br-lg bg-[#f3b41c] p-6  text-[13px] leading-[20px] shadow-[inset_0px_0px_0px_1px_rgba(26,26,0,0.16)]  lg:rounded-br-none lg:rounded-tl-lg lg:p-20"
                >

                        <h1 class="mb-1  font-bold text-2xl text-[#042c68] flex space-x-8">NAFTAL</h1>

                        <p class="mb-2 text-[#042c68] text-base ">
                            Une Relation De Confiance <br> Depuis 1982
                        </p>


                    <ul class="flex gap-3 text-sm leading-normal mt-15">
                        <li>
                            <Link
                                v-if="$page.props.auth.user"
                                :href="route('dashboard')"
                                class="inline-block rounded-sm bg-[#042c68]  px-5 py-1.5 text-sm leading-normal text-white hover:border-white "
                            >
                                Dashboard
                            </Link>
                            <template v-else>
                                <Link
                                    :href="route('login')"
                                    class="inline-block rounded-sm bg-[#042c68]  px-5 py-1.5 mx-2  text-sm leading-normal text-white hover:border-white "

                                >
                                    Log in
                                </Link>
                                <Link
                                    :href="route('register')"
                                    class="inline-block rounded-sm border border-[#042c68]  px-5 py-1.5 text-sm leading-normal text-[#042c68] hover:border-[#042c68]"
                                >
                                    Register
                                </Link>
                            </template>

                        </li>
                    </ul>
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
