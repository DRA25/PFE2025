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
    index: string
    data: Record<string, any>[]
    categories: string[]
    yFormatter?: (val: number | string, index: number) => string
    roundedCorners?: number

}>()

const defaultColors = ['#f3b41c', '#191970']
const chartRef = ref<HTMLElement | null>(null)
let chartInstance: echarts.ECharts | null = null

const renderChart = () => {
    if (!chartRef.value) return

    if (chartInstance) {
        chartInstance.dispose()
    }

    chartInstance = echarts.init(chartRef.value)

    chartInstance.setOption({
        tooltip: { trigger: 'axis' },
        legend: { data: props.categories },
        grid: { left: '3%', right: '4%', bottom: '3%', containLabel: true },
        xAxis: {
            type: 'category',
            data: props.data.map((item) => item[props.index]),
        },
        yAxis: {
            type: 'value',
            axisLabel: {
                formatter:
                    props.yFormatter ??
                    ((val: number | string) =>
                        typeof val === 'number' ? val.toString() : ''),
            },
        },
        series: props.categories.map((category, i) => ({
            name: category,
            type: 'bar',
            data: props.data.map((item) => item[category]),
            barBorderRadius: props.roundedCorners ?? 4,
            itemStyle: {
                color: props.colors?.[i] ?? defaultColors[i] ?? '#8884d8',
            },
        }))

    })
}

onMounted(renderChart)

onBeforeUnmount(() => {
    if (chartInstance) {
        chartInstance.dispose()
    }
})

watch(
    () => props.data,
    () => {
        renderChart()
    }
)
</script>
