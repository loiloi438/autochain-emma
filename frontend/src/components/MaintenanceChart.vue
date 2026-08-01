<template>
  <div class="chart-container">
    <h3>Maintenance par type</h3>
    <canvas ref="chartRef"></canvas>
  </div>
</template>

<script>
import { Chart as ChartJS, ArcElement, Tooltip, Legend } from 'chart.js'
import { ref, onMounted, watch } from 'vue'

ChartJS.register(ArcElement, Tooltip, Legend)

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
        type: 'doughnut',
        data: {
          labels: props.data.map((item) => item.type),
          datasets: [
            {
              data: props.data.map((item) => item.count),
              backgroundColor: ['#3b82f6', '#ef4444', '#f59e0b', '#10b981', '#8b5cf6'],
              borderColor: '#ffffff',
              borderWidth: 2,
            },
          ],
        },
        options: {
          responsive: true,
          plugins: {
            legend: {
              position: 'bottom',
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
