<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, router } from '@inertiajs/vue3';
import BarChartEcharts from '@/Components/ui/charts/BarChartEcharts.vue';
import DonutChart from '@/Components/ui/charts/DonutChart.vue';
import { ref, watch, computed } from 'vue';

import { toPng } from 'dom-to-image-more';
import jsPDF from 'jspdf';
const dashboardRef = ref<HTMLElement | null>(null);

import domtoimage from 'dom-to-image-more';
import { Button } from '@/components/ui/button';


async function exportPDF() {
    if (!dashboardRef.value) return;

    const html = document.documentElement;
    const originalClass = html.className;
    html.classList.remove('dark');
    await new Promise(resolve => setTimeout(resolve, 100));

    // Add paddingBottom temporarily
    const originalPaddingBottom = dashboardRef.value.style.paddingBottom;
    dashboardRef.value.style.paddingBottom = '10px';

    try {
        const dataUrl = await toPng(dashboardRef.value, {
            cacheBust: true,
            backgroundColor: '#ffffff',
        });

        const pdf = new jsPDF('p', 'mm', 'a4');
        const pageWidth = pdf.internal.pageSize.getWidth();
        const headerHeight = 25;
        const imageStartY = headerHeight + 20;

        const logo = new Image();
        logo.src = '/images/Naftal.png';

        logo.onload = () => {
            pdf.setFillColor(50, 50, 50);
            pdf.rect(0, 0, pageWidth, headerHeight, 'F');

            const aspectRatio = logo.width / logo.height;
            const logoHeight = 15;
            const logoWidth = logoHeight * aspectRatio;

            pdf.addImage(logo, 'PNG', 10, 5, logoWidth, logoHeight);

            pdf.setTextColor(255, 255, 255);
            pdf.setFont('helvetica', 'bold');
            pdf.setFontSize(16);
            pdf.text('Tableau de Bord - Rapport', pageWidth / 2, 15, { align: 'center' });

            pdf.setFontSize(10);
            pdf.setFont('helvetica', 'normal');
            pdf.setTextColor(80, 80, 80);
            const today = new Date().toLocaleDateString();
            pdf.text(`Date d'export : ${today}`, 10, headerHeight + 8);

            const chartImage = new Image();
            chartImage.src = dataUrl;

            chartImage.onload = () => {
                const imgWidth = pageWidth;
                const imgHeight = (chartImage.height / chartImage.width) * imgWidth;

                pdf.addImage(chartImage, 'PNG', 0, imageStartY, imgWidth, imgHeight);
                pdf.save('tableau-de-bord.pdf');
            };
        };
    } catch (error) {
        console.error('Export failed:', error);
    } finally {
        html.className = originalClass;
        // Restore original padding
        if (dashboardRef.value) {
            dashboardRef.value.style.paddingBottom = originalPaddingBottom;
        }
    }
}



const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Tableau de bord', href: '/dashboard' },
];

const props = defineProps<{
    draChart: {
        labels: string[],
        data: number[]
    },
    draCountByCentre: {
        name: string,
        total: number
    }[],
    draAmountByCentre: {
        name: string,
        total: number
    }[],
    draAmountByMonth: {
        month: string,
        total: number
    }[],

    centres: { id_centre: string, adresse_centre: string }[],
    selectedCentre: string | null,
    months: { value: number, label: string }[],
    selectedMonth: number | null
}>();

const localMonth = ref<number | null>(props.selectedMonth);

watch(() => props.selectedMonth, (newVal) => {
    localMonth.value = newVal;
});

function applyFilters() {
    router.get('/dashboard', {
        month: localMonth.value
    }, { preserveState: false });
}

function resetFilters() {
    localMonth.value = null;
    applyFilters();
}

const baseColors = [
    '#3B82F6', // Bleu
    '#EF4444', // Rouge
    '#10B981', // Vert
    '#f4aa2c', // Jaune
    '#06B6D4', // Cyan
    '#080b68', // Indigo
    '#2e2e2e', // Gris
    '#000000', // Noir
    '#D97706', // Orange
    '#EAB308', // Jaune
    '#F43F5E', // Rose
];

const centerColors = computed<Record<string, string>>(() => {
    const colorsMap: Record<string, string> = {};
    if (props.centres && props.centres.length > 0) {
        props.centres.forEach((centre, index) => {
            colorsMap[centre.adresse_centre] = baseColors[index % baseColors.length];
        });
    }
    return colorsMap;
});

const monthlyDraChartData = computed(() => {
    return props.draChart.labels.map((label, index) => ({
        month: label,
        total: props.draChart.data[index]
    }));
});

const monthlyDraAmountChartData = computed(() => {
    return props.draAmountByMonth;
});



const monthlyChartColors = computed(() => {
    return monthlyDraChartData.value.map((_, index) => baseColors[index % 3]);
});


// Use monthlyDraChartData.value for the DonutChart
const donutData = computed(() => {
    return monthlyDraAmountChartData.value.map(monthData => ({
        name: monthData.month,
        value: monthData.total
    }));
});

const donutChartColors = computed(() => {
    return donutData.value.map((_, index) => baseColors[index % baseColors.length]);
});

const draCountByCentreBarColors = computed(() => {
    return props.draCountByCentre.map(item => centerColors.value[item.name] || '#10B981');
});

const draAmountByCentreBarColors = computed(() => {
    return props.draAmountByCentre.map(item => centerColors.value[item.name] || '#f4aa2c');
});

const draAmountByMonthChartColors = computed(() => {
    return props.draAmountByMonth.map((_, index) => baseColors[index % baseColors.length]);
});

</script>

<template>
    <Head title="Tableau de bord" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="container mx-auto px-4 py-8">
            <div class="flex justify-between">
                <h1 class="text-4xl font-extrabold text-gray-900 dark:text-white mb-8">Aperçu du Tableau de Bord</h1>
                <Button
                    @click="exportPDF"
                    class="px-6 py-2 bg-green-600 text-white font-semibold rounded-md hover:bg-green-700"
                >
                    Exporter les Diagrammes en PDF
                </Button>
            </div>
            <div class="bg-white dark:bg-gray-800 shadow-lg rounded-xl p-6 mb-8 border border-gray-200 dark:border-gray-700">
                <h2 class="text-2xl font-semibold text-gray-800 dark:text-white mb-5">Filtrer Vos Données</h2>
                <div class="grid grid-cols-1 md:grid-cols-1 gap-6 mb-6">
                    <div>
                        <label for="month" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Filtrer par Mois
                        </label>
                        <select
                            id="month"
                            v-model="localMonth"
                            class="w-full p-2 border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-blue-500 focus:border-blue-500 transition-colors"
                        >
                            <option :value="null">Tous les Mois</option>
                            <option
                                v-for="month in months"
                                :key="month.value"
                                :value="month.value"
                            >
                                {{ month.label }}
                            </option>
                        </select>
                    </div>
                </div>

                <div class="flex space-x-4">
                    <button
                        @click="applyFilters"
                        class="px-6 py-2 bg-blue-600 text-white font-semibold rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition-all"
                    >
                        Appliquer les Filtres
                    </button>
                    <button
                        @click="resetFilters"
                        class="px-6 py-2 bg-gray-200 dark:bg-gray-700 text-gray-800 dark:text-white font-semibold rounded-md hover:bg-gray-300 dark:hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-gray-400 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition-all"
                    >
                        Réinitialiser les Filtres
                    </button>
                </div>
            </div>
            <div ref="dashboardRef">
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
                    <div class="bg-white dark:bg-gray-800 shadow-lg rounded-xl p-6 border border-gray-200 dark:border-gray-700">
                        <h2 class="text-2xl font-semibold text-gray-800 dark:text-white mb-4">Nombre de DRA par Mois</h2>
                        <div class="aspect-video">
                            <BarChartEcharts
                                index="month"
                                :data="monthlyDraChartData"
                                :categories="['total']"
                                :y-formatter="(tick) => `${tick.toLocaleString()} DRAs`"
                                :colors="monthlyChartColors"
                                :rounded-corners="4"
                            />
                        </div>
                    </div>

                    <div class="bg-white dark:bg-gray-800 shadow-lg rounded-xl p-6 border border-gray-200 dark:border-gray-700">
                        <h2 class="text-2xl font-semibold text-gray-800 dark:text-white mb-4">Nombre de DRA par Centre</h2>
                        <div class="aspect-video">
                            <BarChartEcharts
                                index="name"
                                :data="draCountByCentre"
                                :categories="['total']"
                                :y-formatter="(tick) => `${tick} DRAs`"
                                :rounded-corners="4"
                                :colors="draCountByCentreBarColors"
                            />
                        </div>
                    </div>

                    <div class="bg-white dark:bg-gray-800 shadow-lg rounded-xl p-6 border border-gray-200 dark:border-gray-700">
                        <h2 class="text-2xl font-semibold text-gray-800 dark:text-white mb-4">
                            Montant Total de DRA par Mois
                        </h2>
                        <div class="aspect-video">
                            <BarChartEcharts
                                index="month"
                                :data="props.draAmountByMonth"
                                :categories="['total']"
                                :y-formatter="(tick) => `${tick.toLocaleString()} DA`"
                                :rounded-corners="4"
                                :colors="draAmountByMonthChartColors"
                            />
                        </div>
                    </div>


                    <div class="bg-white dark:bg-gray-800 shadow-lg rounded-xl p-6 border border-gray-200 dark:border-gray-700">
                        <h2 class="text-2xl font-semibold text-gray-800 dark:text-white mb-4">
                            Montant Total de DRA par Mois
                        </h2>
                        <div class="aspect-video">
                            <DonutChart
                                :data="donutData"
                                :radius="['40%', '70%']"
                                :colors="donutChartColors"
                            />
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
