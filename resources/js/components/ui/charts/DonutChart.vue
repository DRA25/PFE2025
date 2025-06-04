<script setup lang="ts">
import { PieChart } from 'echarts/charts';
import { use } from 'echarts/core';
import { TitleComponent, TooltipComponent, LegendComponent } from 'echarts/components';
import { CanvasRenderer } from 'echarts/renderers';
import VChart from 'vue-echarts';
import { ref, watch } from 'vue';

// Initialize ECharts components
use([TitleComponent, TooltipComponent, LegendComponent, PieChart, CanvasRenderer]);

const props = defineProps<{
    data: { name: string; value: number }[];
    colors?: string[];
    radius?: [string, string];
    title?: string;
}>();

const chartOptions = ref({
    title: {
        text: props.title || '',
        left: 'center'
    },
    tooltip: {
        trigger: 'item',
        formatter: '{a} <br/>{b}: {c} ({d}%)'
    },
    legend: {
        orient: 'vertical',
        right: 10,
        top: 'center',
        data: props.data.map(item => item.name)
    },
    series: [
        {
            name: 'Distribution',
            type: 'pie',
            radius: props.radius || ['50%', '80%'],
            avoidLabelOverlap: false,
            itemStyle: {
                borderRadius: 5,
                borderColor: '#fff',
                borderWidth: 2
            },
            label: {
                show: false,
                position: 'center'
            },
            emphasis: {
                label: {
                    show: true,
                    fontSize: 18,
                    fontWeight: 'bold'
                }
            },
            labelLine: {
                show: false
            },
            data: props.data,
            color: props.colors || [
                '#3B82F6', '#10B981', '#F59E0B',
                '#EF4444', '#8B5CF6', '#EC4899'
            ]
        }
    ]
});

// Update options when props change
watch(() => props.data, (newData) => {
    chartOptions.value.legend.data = newData.map(item => item.name);
    chartOptions.value.series[0].data = newData;
}, { deep: true });
</script>

<template>
    <div class="donut-chart-container">
        <VChart
            :option="chartOptions"
            :autoresize="true"
            class="w-full h-[400px]"
        />
    </div>
</template>

<style scoped>
.donut-chart-container {
    width: 100%;
    height: 100%;
    min-height: 400px;
}
</style>
