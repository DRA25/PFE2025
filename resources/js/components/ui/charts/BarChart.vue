<template>
    <Bar :data="chartData" :options="chartOptions" />
</template>

<script setup>
import { Bar } from 'vue-chartjs'
import {
    Chart as ChartJS,
    Title,
    Tooltip,
    Legend,
    BarElement,
    CategoryScale,
    LinearScale,
} from 'chart.js'

ChartJS.register(Title, Tooltip, Legend, BarElement, CategoryScale, LinearScale)

const props = defineProps({
    data: {
        type: Object,
        required: true,
    },
    labels: {
        type: Array,
        required: true,
    },
    title: {
        type: String,
        default: 'Bar Chart',
    },
})

const chartData = {
    labels: props.labels,
    datasets: [
        {
            label: props.title,
            data: props.data,
            backgroundColor: '#191970',
        },
    ],
}

const chartOptions = {
    responsive: true,
    plugins: {
        legend: {
            position: 'top',
        },
        title: {
            display: true,
            text: props.title,
        },
    },
}
</script>
