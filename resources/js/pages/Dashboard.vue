<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/vue3';
import PlaceholderPattern from '../components/PlaceholderPattern.vue';
import BarChart from '@/Components/ui/charts/BarChart.vue'
import BarChartEcharts from '@/Components/ui/charts/BarChartEcharts.vue'


const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: '/dashboard',
    },

];



const data = [100, 200, 300, 400, 500]
const labels = ['Jan', 'Feb', 'Mar', 'Apr', 'May']
const data2 = [
    { name: 'Jan', total: 1500, predicted: 1700 },
    { name: 'Feb', total: 1800, predicted: 1600 },
    { name: 'Mar', total: 1900, predicted: 2100 },
    { name: 'Apr', total: 2000, predicted: 2300 },
    { name: 'May', total: 1750, predicted: 1900 },
    { name: 'Jun', total: 1600, predicted: 1800 },
    { name: 'Jul', total: 2500, predicted: 2600 },
]

const donutData = [
    { name: 'Jan', total: Math.floor(Math.random() * 2000) + 500 },
    { name: 'Feb', total: Math.floor(Math.random() * 2000) + 500 },
    { name: 'Mar', total: Math.floor(Math.random() * 2000) + 500 },
    { name: 'Apr', total: Math.floor(Math.random() * 2000) + 500 },
    { name: 'May', total: Math.floor(Math.random() * 2000) + 500 },
    { name: 'Jun', total: Math.floor(Math.random() * 2000) + 500 },
];

</script>
<template>
    <Head title="Dashboard" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="container mx-auto p-4">
            <h1 class="text-3xl font-bold mb-6">Your Dashboard Overview</h1>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="relative p-6 aspect-video rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 shadow-md">
            <h2 class="text-xl font-bold mb-4 text-gray-800 dark:text-white">Monthly Sales</h2>
            <BarChart :data="data" :labels="labels" title="Sales by Month" />
        </div>

            <div class="relative p-6 aspect-video rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 shadow-md">
                <h2 class="text-xl font-bold mb-4 text-gray-800 dark:text-white">Predicted vs Total</h2>
                <BarChartEcharts
                    index="name"
                    :data="data2"
                    :categories="['total', 'predicted']"
                    :y-formatter="(tick) => `$${tick}`"
                    :rounded-corners="2"
                />
            </div>
        </div>

            

            <div class="mt-8 p-6 rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 shadow-md">
                <h3 class="text-2xl font-semibold mb-4">Additional Information</h3>
                <p>descriptions of the charts </p>
            </div>

        </div>
    </AppLayout>
</template>
