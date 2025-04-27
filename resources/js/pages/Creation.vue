
<template>
    <AppLayout>

    <form>

            <h1 CLASS="flex items-center justify-center text-black underline ">DEMANDES URGENTES DES ATELIERS</h1><br>
        <p class="mt1 ml-6">veuillez remplir les champs suivant afin de rédiger un BCI</p>


        <div class="mt-10 grid grid-cols-1 gap-x-6 gap-y-8 sm:grid-cols-6">
           <div class="sm:col-span-2">
               <label for="atelier" class="text-sm/6 ml-6 font-medium text-black" >Nom Atelier:</label><br>
                 <div class=" mt-2 ml-6 flex items-center rounded-md bg-white pl-3 outline-1 -outline-offset-1 outline-gray-300 focus-within:outline-2 focus-within:-outline-offset-2 focus-within:outline-indigo-600">
                    <input type="text" name="atelier" id="atelier" class="block min-w-0 grow py-1.5 pr-3 pl-1 text-base text-gray-900 placeholder:text-gray-400 focus:outline-none sm:text-sm/6" placeholder="atelier dar el beida"/>

                 </div>
           </div>
        </div>
        <div class="mt-10 grid grid-cols-1 gap-x-6 gap-y-8 sm:grid-cols-6">
        <div class="sm:col-span-2">
                <label for="nbci" class="text-sm/6 ml-6 font-medium text-black">numéro BCI</label>
            <div class=" mt-2 ml-6 flex items-center rounded-md bg-white pl-3 outline-1 -outline-offset-1 outline-gray-300 focus-within:outline-2 focus-within:-outline-offset-2 focus-within:outline-indigo-600">
                 <input type="text" name="nbci" id="nbci" class="block min-w-0  py-1.5 pr-3 pl-1 text-base text-gray-900 placeholder:text-gray-400 focus:outline-none sm:text-sm/6" placeholder="45123"/>
            </div>
        </div>
        </div>

        <div class="mt-10 grid grid-cols-1 gap-x-6 gap-y-8 sm:grid-cols-6">
            <div class="sm:col-span-2">
                <label for="magasin" class="text-sm/6 ml-6 font-medium text-black">déstiné au magasin</label>
                <div class=" mt-2 ml-6 flex items-center rounded-md bg-white pl-3 outline-1 -outline-offset-1 outline-gray-300 focus-within:outline-2 focus-within:-outline-offset-2 focus-within:outline-indigo-600">
                    <input type="text" name="magasin" id="magasin" class="block min-w-0  py-1.5 pr-3 pl-1 text-base text-gray-900 placeholder:text-gray-400 focus:outline-none sm:text-sm/6" placeholder="magasin A20"/>
                </div>
            </div>
        </div>



        <div class="mt-10 grid grid-cols-1 gap-x-6 gap-y-8 sm:grid-cols-6">
            <div class="sm:col-span-2">
                <label for="mvehicule" class="text-sm/6 ml-6 font-medium text-black">Marque véhicule</label>
                <div class=" mt-2 ml-6 flex items-center rounded-md bg-white pl-3 outline-1 -outline-offset-1 outline-gray-300 focus-within:outline-2 focus-within:-outline-offset-2 focus-within:outline-indigo-600">
                    <input type="text" name="mvehicule" id="mvehicule" class="block min-w-0  py-1.5 pr-3 pl-1 text-base text-gray-900 placeholder:text-gray-400 focus:outline-none sm:text-sm/6" placeholder="Renault"/>
                </div>
            </div>
        </div>




        <div class="mt-10 grid grid-cols-1 gap-x-6 gap-y-8 sm:grid-cols-6">
            <div class="sm:col-span-2">
                <label for="cvehicule" class="text-sm/6 ml-6 font-medium text-black">code véhicule</label>
                <div class=" mt-2 ml-6 flex items-center rounded-md bg-white pl-3 outline-1 -outline-offset-1 outline-gray-300 focus-within:outline-2 focus-within:-outline-offset-2 focus-within:outline-indigo-600">
                    <input type="text" name="cvehicule" id="cvehicule" class="block min-w-0  py-1.5 pr-3 pl-1 text-base text-gray-900 placeholder:text-gray-400 focus:outline-none sm:text-sm/6" placeholder="L3536"/>
                </div>
            </div>
        </div>



        <div class="mt-10 grid grid-cols-1 gap-x-6 gap-y-8 sm:grid-cols-6">
            <div class="sm:col-span-2">
                <label for="refconstruc" class="text-sm/6 ml-6 font-medium text-black">Réferences constructeurs</label>
                <div class=" mt-2 ml-6 flex items-center rounded-md bg-white pl-3 outline-1 -outline-offset-1 outline-gray-300 focus-within:outline-2 focus-within:-outline-offset-2 focus-within:outline-indigo-600">
                    <input type="text" name="refconstruc" id="refconstruc" class="block min-w-0  py-1.5 pr-3 pl-1 text-base text-gray-900 placeholder:text-gray-400 focus:outline-none sm:text-sm/6" placeholder="501250510"/>
                </div>
            </div>
        </div>


        <div class="mt-10 grid grid-cols-1 gap-x-6 gap-y-8 sm:grid-cols-6">
            <div class="sm:col-span-2">
                <label for="rdesignation" class="text-sm/6 ml-6 font-medium text-black">Désignation</label>
                <div class=" mt-2 ml-6 flex items-center rounded-md bg-white pl-3 outline-1 -outline-offset-1 outline-gray-300 focus-within:outline-2 focus-within:-outline-offset-2 focus-within:outline-indigo-600">
                    <input type="text" name="designation" id="rdesignation" class="block min-w-0  py-1.5 pr-3 pl-1 text-base text-gray-900 placeholder:text-gray-400 focus:outline-none sm:text-sm/6" placeholder="ressort"/>
                </div>
            </div>
        </div>


        <div class="mt-10 grid grid-cols-1 gap-x-6 gap-y-8 sm:grid-cols-6">
            <div class="sm:col-span-2">
                <label for="quantités" class="text-sm/6 ml-6 font-medium text-black">Quantités</label>
                <div class=" mt-2 ml-6 flex items-center rounded-md bg-white pl-3 outline-1 -outline-offset-1 outline-gray-300 focus-within:outline-2 focus-within:-outline-offset-2 focus-within:outline-indigo-600">
                    <input type="text" name="quantités" id="quantités" class="block min-w-0  py-1.5 pr-3 pl-1 text-base text-gray-900 placeholder:text-gray-400 focus:outline-none sm:text-sm/6" placeholder="4"/>
                </div>
            </div>
        </div>




        <div class="mt-6 flex items-center justify-end gap-x-6">
            <button type="button" class="text-sm/6 font-semibold text-gray-900">ANNULER</button>
            <button type="submit" class="rounded-md bg-green-600 px-3 py-2 text-sm font-semibold text-white shadow-xs hover:bg-indigo-500 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600">ENVOYER</button>
        </div>











    </form>
    </AppLayout>
</template>

<script setup>

import AppLayout from '@/layouts/AppLayout.vue';
import { Label } from '@/components/ui/label/index.js';
</script>
