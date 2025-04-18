<script setup lang="ts">

</script>

<template>
    <div class="flex min-h-screen flex-col items-center bg-[#042c68] p-6 text-[#1b1b18]  lg:justify-center lg:p-8">
        <div class="duration-750 starting:opacity-0 flex w-full items-center justify-center opacity-100 transition-opacity lg:grow">
            <main class="flex w-full max-w-[335px] flex-col-reverse overflow-hidden rounded-lg lg:max-w-4xl lg:flex-row">
                <div
                    class="flex-1 rounded-bl-lg rounded-br-lg bg-[#f3b41c] p-6  text-[13px] leading-[20px] shadow-[inset_0px_0px_0px_1px_rgba(26,26,0,0.16)]  lg:rounded-br-none lg:rounded-tl-lg lg:p-20">



                  <slot />



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
