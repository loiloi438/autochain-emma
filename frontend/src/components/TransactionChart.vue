<template>
  <div class="chart-container">
    <h3>Transactions blockchain (derniers 7 jours)</h3>
    <canvas ref="chartRef"></canvas>
  </div>
</template>

<script>
import { Chart as ChartJS, CategoryScale, LinearScale, LineElement, PointElement, Title, Tooltip, Legend } from 'chart.js'
import { ref, onMounted, watch } from 'vue'

ChartJS.register(CategoryScale, LinearScale, LineElement, PointElement, Title, Tooltip, Legend)

export default {
  props: {
    data: {
      type: Array,
      default: () => [],
    },
  },
  setup(props) {
    const chartRef = ref(null)
    let chartInstance = null

    const renderChart = () => {
      if (!chartRef.value || !props.data.length) return

      const ctx = chartRef.value.getContext('2d')

      if (chartInstance) {
        chartInstance.destroy()
      }

      chartInstance = new ChartJS(ctx, {
        type: 'line',
        data: {
          labels: props.data.map((item) => item.date),
          datasets: [
            {
              label: 'Transactions confirmées',
              data: props.data.map((item) => item.confirmed),
              borderColor: '#10b981',
              backgroundColor: 'rgba(16, 185, 129, 0.1)',
              tension: 0.4,
              fill: true,
            },
            {
              label: 'Transactions en attente',
              data: props.data.map((item) => item.pending),
              borderColor: '#f59e0b',
              backgroundColor: 'rgba(245, 158, 11, 0.1)',
              tension: 0.4,
              fill: true,
            },
          ],
        },
        options: {
          responsive: true,
          plugins: {
            legend: {
              position: 'top',
            },
          },
          scales: {
            y: {
              beginAtZero: true,
            },
          },
        },
      })
    }

    onMounted(() => {
      renderChart()
    })

    watch(
      () => props.data,
      () => {
        renderChart()
      },
      { deep: true }
    )

    return { chartRef }
  },
}
</script>

<style scoped>
.chart-container {
  background: white;
  padding: 1.5rem;
  border-radius: 8px;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
}

h3 {
  margin: 0 0 1rem 0;
  font-size: 1rem;
  font-weight: 600;
}

canvas {
  max-height: 300px;
}
</style>
