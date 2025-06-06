<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, router } from '@inertiajs/vue3';
// import BarChart from '@/Components/ui/charts/BarChart.vue'; // Nous allons supprimer cette importation
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
    { title: 'Tableau de bord', href: '/directiondashboard' }, // Tableau de bord
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
    centres: { id_centre: string, adresse_centre: string }[],
    selectedCentre: string | null,
    months: { value: number, label: string }[],
    selectedMonth: number | null
}>();

// Créer des références réactives pour les valeurs de filtre
const localCentre = ref<string | null>(props.selectedCentre);
const localMonth = ref<number | null>(props.selectedMonth);

// Mettre à jour les valeurs locales lorsque les props changent
watch(() => props.selectedCentre, (newVal) => {
    localCentre.value = newVal;
});

watch(() => props.selectedMonth, (newVal) => {
    localMonth.value = newVal;
});

function applyFilters() {
    router.get('/directiondashboard', {
        id_centre: localCentre.value,
        month: localMonth.value
    }, { preserveState: false });
}

function resetFilters() {
    localCentre.value = null;
    localMonth.value = null;
    applyFilters();
}

// Couleurs de base pour le cyclage
const baseColors = [
    '#3B82F6', // Bleu
    '#10B981', // Vert
    '#f4aa2c', // Jaune
    '#EF4444', // Rouge
    '#06B6D4', // Cyan
    '#080b68', // Indigo
    '#2e2e2e', // Gris
    '#000000', // Noir
    '#D97706', // Orange
    '#EAB308', // Jaune
    '#F43F5E', // Rose
];

// Propriété calculée pour générer dynamiquement la carte centerColors
const centerColors = computed<Record<string, string>>(() => {
    const colorsMap: Record<string, string> = {};
    if (props.centres && props.centres.length > 0) {
        props.centres.forEach((centre, index) => {
            // Utiliser l'adresse_centre comme clé et cycler à travers baseColors
            colorsMap[centre.adresse_centre] = baseColors[index % baseColors.length];
        });
    }
    return colorsMap;
});

// Transformer les données draChart pour BarChartEcharts
const monthlyDraChartData = computed(() => {
    return props.draChart.labels.map((label, index) => ({
        month: label, // Utiliser 'month' comme index pour l'axe des x
        count: props.draChart.data[index] // Utiliser 'count' comme catégorie pour l'axe des y
    }));
});

// Définir un tableau simple de couleurs pour le graphique mensuel, en cyclant à travers un sous-ensemble de baseColors
const monthlyChartColors = computed(() => {
    // Vous pouvez choisir une couleur spécifique ou cycler à travers un petit sous-ensemble de baseColors
    // Pour simplifier, choisissons juste une couleur ou cyclons à travers un petit sous-ensemble pour les totaux mensuels
    return monthlyDraChartData.value.map((_, index) => baseColors[index % 3]); // Cycle à travers les 3 premières couleurs de base
});


// Transformer draCountByCentre au format requis par DonutChart
const donutData = computed(() => {
    return props.draCountByCentre.map(centre => ({
        name: centre.name,
        value: centre.total
    }));
});

// Propriété calculée pour obtenir les couleurs spécifiques pour le DonutChart
const donutChartColors = computed(() => {
    // S'assurer que le 'name' de draCountByCentre correspond à adresse_centre pour la recherche de couleur
    return props.draCountByCentre.map(item => centerColors.value[item.name] || '#CCCCCC');
});

// Propriétés calculées pour générer des tableaux de couleurs pour BarChartEcharts
const draCountByCentreBarColors = computed(() => {
    // S'assurer que le 'name' de draCountByCentre correspond à adresse_centre pour la recherche de couleur
    return props.draCountByCentre.map(item => centerColors.value[item.name] || '#CCCCCC');
});

const draAmountByCentreBarColors = computed(() => {
    // S'assurer que le 'name' de draAmountByCentre correspond à adresse_centre pour la recherche de couleur
    return props.draAmountByCentre.map(item => centerColors.value[item.name] || '#CCCCCC');
});

</script>

<template>
    <Head title="Tableau de bord" /> <AppLayout :breadcrumbs="breadcrumbs">


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

        <h2 class="text-2xl font-semibold text-gray-800 dark:text-white mb-5">Filtrer Vos Données</h2> <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
        <div>
            <label for="centre" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                Filtrer par Centre
            </label> <select
            id="centre"
            v-model="localCentre"
            class="w-full p-2 border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-blue-500 focus:border-blue-500 transition-colors"
        >
            <option :value="null">Tous les Centres</option> <option
            v-for="centre in centres"
            :key="centre.id_centre"
            :value="centre.id_centre"
        >
            {{ centre.adresse_centre }} - {{ centre.id_centre }}
        </option>
        </select>
        </div>

        <div>
            <label for="month" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                Filtrer par Mois
            </label> <select
            id="month"
            v-model="localMonth"
            class="w-full p-2 border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-blue-500 focus:border-blue-500 transition-colors"
        >
            <option :value="null">Tous les Mois</option> <option
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
            </button> <button
            @click="resetFilters"
            class="px-6 py-2 bg-gray-200 dark:bg-gray-700 text-gray-800 dark:text-white font-semibold rounded-md hover:bg-gray-300 dark:hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-gray-400 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition-all"
        >
            Réinitialiser les Filtres
        </button> </div>
    </div>
        <div ref="dashboardRef" class="mb-10">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
            <div class="bg-white dark:bg-gray-800 shadow-lg rounded-xl p-6 border border-gray-200 dark:border-gray-700">
                <h2 class="text-2xl font-semibold text-gray-800 dark:text-white mb-4">Nombre de DRA par Mois</h2> <div class="aspect-video">
                <BarChartEcharts
                    index="month"
                    :data="monthlyDraChartData"
                    :categories="['count']"
                    :y-formatter="(tick) => `${tick} DRAs`"
                    :colors="monthlyChartColors"
                    :rounded-corners="4"
                />
            </div>
            </div>

            <div class="bg-white dark:bg-gray-800 shadow-lg rounded-xl p-6 border border-gray-200 dark:border-gray-700">
                <h2 class="text-2xl font-semibold text-gray-800 dark:text-white mb-4">Nombre de DRA par Centre</h2> <div class="aspect-video">
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
                <h2 class="text-2xl font-semibold text-gray-800 dark:text-white mb-4">Montant Total de DRA par Centre</h2> <div class="aspect-video">
                <BarChartEcharts
                    index="name"
                    :data="draAmountByCentre"
                    :categories="['total']"
                    :y-formatter="(tick) => `${tick.toLocaleString()} DA`"
                    :rounded-corners="4"
                    :colors="draAmountByCentreBarColors"
                />
            </div>
            </div>

            <div class="bg-white dark:bg-gray-800 shadow-lg rounded-xl p-6 border border-gray-200 dark:border-gray-700">
                <h2 class="text-2xl font-semibold text-gray-800 dark:text-white mb-4">
                    Distribution des DRA par Centre
                </h2> <div class="aspect-video">
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
