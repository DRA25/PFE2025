<template>
    <div ref="chartRef" style="width: 100%; height: 400px" />
</template>

<script setup lang="ts">
import * as echarts from 'echarts/core'
import { BarChart as EBarChart } from 'echarts/charts'
import {
    GridComponent,
    TooltipComponent,
    LegendComponent,
    TitleComponent,
} from 'echarts/components'
import { CanvasRenderer } from 'echarts/renderers'
import { ref, onMounted, onBeforeUnmount, watch } from 'vue'

echarts.use([
    EBarChart,
    GridComponent,
    TooltipComponent,
    LegendComponent,
    TitleComponent,
    CanvasRenderer,
])

const props = defineProps<{
    index: string // e.g., 'name' for the center name
    data: Record<string, any>[] // e.g., [{ name: 'Centre A', total: 100 }, ...]
    categories: string[] // e.g., ['total'] - typically one for this type of chart
    yFormatter?: (val: number | string) => string
    roundedCorners?: number
    // The 'colors' prop is now an array, where each element is the color for the corresponding bar
    colors?: string[] // Changed to an array of strings
}>()

const chartRef = ref<HTMLElement | null>(null)
let chartInstance: echarts.ECharts | null = null

const renderChart = () => {
    if (!chartRef.value) return

    if (chartInstance) {
        chartInstance.dispose()
    }

    chartInstance = echarts.init(chartRef.value)

    // Prepare series data with individual item styles for coloring each bar
    const seriesData = props.data.map((item, i) => {
        return {
            value: item[props.categories[0]], // Assuming we're charting the first category (e.g., 'total')
            itemStyle: {
                color: props.colors?.[i] || '#3B82F6', // Use the provided color at index 'i', or a default blue
                borderRadius: props.roundedCorners ?? 4,
            },
        }
    })

    chartInstance.setOption({
        tooltip: { trigger: 'axis' },
        // Legend is typically not needed if each bar represents a unique category with its own color
        // legend: { data: props.categories },
        grid: { left: '3%', right: '4%', bottom: '3%', containLabel: true },
        xAxis: {
            type: 'category',
            data: props.data.map((item) => item[props.index]), // X-axis labels are the center names
            axisLabel: {
                interval: 0, // Show all labels
                rotate: 30, // Rotate labels for better readability if names are long
            }
        },
        yAxis: {
            type: 'value',
            axisLabel: {
                formatter:
                    props.yFormatter ??
                    ((val: number | string) =>
                        typeof val === 'number' ? val.toLocaleString() : ''),
            },
        },
        series: [
            {
                name: props.categories[0], // The name of the series (e.g., 'total')
                type: 'bar',
                data: seriesData, // Use the prepared data with individual bar styles
                // barBorderRadius is now handled by itemStyle for each data point
            },
        ],
    })
}

onMounted(renderChart)

onBeforeUnmount(() => {
    if (chartInstance) {
        chartInstance.dispose()
    }
})

// Watch for changes in data or colors to re-render the chart
watch(
    () => [props.data, props.colors, props.yFormatter, props.roundedCorners],
    () => {
        renderChart()
    },
    { deep: true } // Deep watch for data and colors objects
)
</script>
