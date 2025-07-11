<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, router } from '@inertiajs/vue3';
import BarChartEcharts from '@/Components/ui/charts/BarChartEcharts.vue';
import DonutChart from '@/Components/ui/charts/DonutChart.vue';
import { ref, watch, computed, onMounted, onUnmounted } from 'vue'; // Added onMounted, onUnmounted
import { toPng } from 'dom-to-image-more';
import jsPDF from 'jspdf';
import { Button } from '@/components/ui/button';

const dashboardRef = ref<HTMLElement | null>(null);

// --- Dark Mode Detection ---
const isDarkMode = ref(false);
const checkDarkMode = () => {
    isDarkMode.value = document.documentElement.classList.contains('dark');
};
let observer: MutationObserver | null = null; // Declare observer here

onMounted(() => {
    checkDarkMode(); // Initial check
    // Listen for changes to the 'dark' class on the html element
    observer = new MutationObserver(checkDarkMode);
    observer.observe(document.documentElement, { attributes: true, attributeFilter: ['class'] });
});

onUnmounted(() => {
    if (observer) {
        observer.disconnect(); // Clean up observer on unmount
    }
});
// --- End Dark Mode Detection ---

async function exportPDF() {
    if (!dashboardRef.value) return;

    const html = document.documentElement;
    const originalClass = html.className;
    html.classList.remove('dark'); // Force light mode for PDF export for consistent appearance
    await new Promise(resolve => setTimeout(resolve, 100)); // Small delay for rendering

    // Temporarily add paddingBottom and capture original styles for dashboardRef
    const originalPaddingBottom = dashboardRef.value.style.paddingBottom;
    const originalBoxShadow = dashboardRef.value.style.boxShadow;
    const originalBorder = dashboardRef.value.style.border;

    dashboardRef.value.style.paddingBottom = '10px';
    // Remove shadows/borders for cleaner PDF capture if they interfere
    dashboardRef.value.style.boxShadow = 'none';
    dashboardRef.value.style.border = 'none';

    try {
        const dataUrl = await toPng(dashboardRef.value, {
            cacheBust: true,
            backgroundColor: '#ffffff', // Ensure white background for the image
        });

        const pdf = new jsPDF('p', 'mm', 'a4');
        const pageWidth = pdf.internal.pageSize.getWidth();
        const headerHeight = 25;
        const imageStartY = headerHeight + 20;

        const logo = new Image();
        logo.src = '/images/Naftal.png';

        logo.onload = () => {
            // Header background color matching your primary brand color
            pdf.setFillColor(4, 43, 98); // #042B62
            pdf.rect(0, 0, pageWidth, headerHeight, 'F');

            // Add logo
            const aspectRatio = logo.width / logo.height;
            const logoHeight = 15;
            const logoWidth = logoHeight * aspectRatio;
            pdf.addImage(logo, 'PNG', 10, 5, logoWidth, logoHeight);

            // Header text
            pdf.setTextColor(255, 255, 255); // White text
            pdf.setFont('helvetica', 'bold');
            pdf.setFontSize(16);
            pdf.text('Tableau de Bord - Rapport', pageWidth / 2, 15, { align: 'center' });

            // Date of export
            pdf.setFontSize(10);
            pdf.setFont('helvetica', 'normal');
            pdf.setTextColor(80, 80, 80); // Dark grey text for date
            const today = new Date().toLocaleDateString('fr-FR'); // Format for French locale
            pdf.text(`Date d'export : ${today}`, 10, headerHeight + 8);

            const chartImage = new Image();
            chartImage.src = dataUrl;

            chartImage.onload = () => {
                const imgWidth = pageWidth;
                const imgHeight = (chartImage.height / chartImage.width) * imgWidth;

                // Ensure image fits within PDF, scale down if too large
                if (imgHeight > pdf.internal.pageSize.getHeight() - imageStartY - 10) { // 10mm bottom margin
                    const scaleFactor = (pdf.internal.pageSize.getHeight() - imageStartY - 10) / imgHeight;
                    imgHeight *= scaleFactor;
                    imgWidth *= scaleFactor;
                }
                pdf.addImage(chartImage, 'PNG', (pageWidth - imgWidth) / 2, imageStartY, imgWidth, imgHeight); // Center image
                pdf.save('tableau-de-bord.pdf');
            };
        };
    } catch (error) {
        console.error('Export failed:', error);
    } finally {
        // Restore original styles
        html.className = originalClass;
        if (dashboardRef.value) {
            dashboardRef.value.style.paddingBottom = originalPaddingBottom;
            dashboardRef.value.style.boxShadow = originalBoxShadow;
            dashboardRef.value.style.border = originalBorder;
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
    '#3B82F6', // Blue (primary brand color)
    '#EF4444', // Red
    '#10B981', // Green
    '#f4aa2c', // Yellow
    '#06B6D4', // Cyan
    '#080b68', // Indigo (darker blue)
    '#2e2e2e', // Gray (dark)
    '#D97706', // Orange
    '#EAB308', // Amber (another yellow)
    '#F43F5E', // Rose
    '#8B5CF6', // Purple
    '#EC4899', // Pink
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
        <div class="min-h-screen bg-gradient-to-br from-white to-blue-50 dark:from-slate-900 dark:to-gray-900 font-inter text-gray-800 dark:text-gray-200 p-4 sm:p-6 lg:p-8">
            <div class="max-w-6xl mx-auto bg-white dark:bg-gray-800 rounded-3xl shadow-2xl overflow-hidden transition-all duration-500">
                <div class="p-6 sm:p-8 lg:p-10">
                    <div class="flex items-center justify-between mb-10 flex-wrap gap-4">
                        <h1 class="text-3xl sm:text-4xl lg:text-5xl font-extrabold text-[#042B62] dark:text-[#F3B21B] tracking-tight leading-tight">
                            Aperçu du Tableau de Bord
                        </h1>
                        <Button
                            @click="exportPDF"
                            class="px-6 py-3 bg-[#042B62] text-white font-semibold rounded-lg hover:bg-blue-700 dark:bg-[#F3B21B] dark:text-[#042B62] dark:hover:bg-yellow-400 transition-colors duration-300 shadow-md"
                        >
                            Exporter les Diagrammes en PDF
                        </Button>
                    </div>

                    <div class="bg-gray-50 dark:bg-gray-900 shadow-inner rounded-xl p-6 mb-8 border border-gray-100 dark:border-gray-700">
                        <h2 class="text-2xl font-bold text-[#042B62] dark:text-[#F3B21B] mb-5">
                            Filtrer Vos Données
                        </h2>
                        <div class="grid grid-cols-1 md:grid-cols-1 gap-6 mb-6">
                            <div>
                                <label for="month" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    Filtrer par Mois
                                </label>
                                <select
                                    id="month"
                                    v-model="localMonth"
                                    class="w-full p-3 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-blue-500 focus:border-blue-500 transition-colors duration-200 shadow-sm"
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
                                class="px-8 py-3 bg-[#042B62] text-white font-semibold rounded-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition-all duration-300 shadow-md"
                            >
                                Appliquer les Filtres
                            </button>
                            <button
                                @click="resetFilters"
                                class="px-8 py-3 bg-gray-200 dark:bg-gray-700 text-gray-800 dark:text-white font-semibold rounded-lg hover:bg-gray-300 dark:hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-gray-400 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition-all duration-300 shadow-md"
                            >
                                Réinitialiser les Filtres
                            </button>
                        </div>
                    </div>
                    <div ref="dashboardRef">
                        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
                            <div class="bg-white dark:bg-gray-800 shadow-lg rounded-xl p-6 border border-gray-200 dark:border-gray-700">
                                <h2 class="text-2xl font-bold text-[#042B62] dark:text-[#F3B21B] mb-4">Nombre de DRA par Mois</h2>
                                <div class="aspect-video">
                                    <BarChartEcharts
                                        index="month"
                                        :data="monthlyDraChartData"
                                        :categories="['total']"
                                        :y-formatter="(tick) => `${tick.toLocaleString()} DRAs`"
                                        :colors="monthlyChartColors"
                                        :rounded-corners="4"
                                        :is-dark-mode="isDarkMode"
                                    />
                                </div>
                            </div>

                            <div class="bg-white dark:bg-gray-800 shadow-lg rounded-xl p-6 border border-gray-200 dark:border-gray-700">
                                <h2 class="text-2xl font-bold text-[#042B62] dark:text-[#F3B21B] mb-4">Nombre de DRA par Centre</h2>
                                <div class="aspect-video">
                                    <BarChartEcharts
                                        index="name"
                                        :data="draCountByCentre"
                                        :categories="['total']"
                                        :y-formatter="(tick) => `${tick} DRAs`"
                                        :rounded-corners="4"
                                        :colors="draCountByCentreBarColors"
                                        :is-dark-mode="isDarkMode"
                                    />
                                </div>
                            </div>

                            <div class="bg-white dark:bg-gray-800 shadow-lg rounded-xl p-6 border border-gray-200 dark:border-gray-700">
                                <h2 class="text-2xl font-bold text-[#042B62] dark:text-[#F3B21B] mb-4">
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
                                        :is-dark-mode="isDarkMode"
                                    />
                                </div>
                            </div>

                            <div class="bg-white dark:bg-gray-800 shadow-lg rounded-xl p-6 border border-gray-200 dark:border-gray-700">
                                <h2 class="text-2xl font-bold text-[#042B62] dark:text-[#F3B21B] mb-4">
                                    Distribution du Montant des DRAs par Mois
                                </h2>
                                <div class="aspect-video">
                                    <DonutChart
                                        :data="donutData"
                                        :radius="['55%', '85%']" :colors="donutChartColors"
                                        :is-dark-mode="isDarkMode" :legend="{
                                            orient: 'horizontal',
                                            left: 'center',
                                            top: 'bottom',
                                            formatter: '{name}',
                                            textStyle: {
                                                color: isDarkMode ? '#FFFFFF' : '#333333'
                                            }
                                        }"
                                    />
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
