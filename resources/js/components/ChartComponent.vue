<template>
  <div class="chart-container">
    <h3 class="text-lg font-semibold text-gray-700 mb-4">{{ title }}</h3>
    <canvas ref="chartCanvas"></canvas>
  </div>
</template>

<script>
import {
  Chart as ChartJS,
  CategoryScale,
  LinearScale,
  BarElement,
  Title,
  Tooltip,
  Legend,
  ArcElement,
  PointElement,
  LineElement,
} from 'chart.js';
import { Bar, Doughnut, Line } from 'vue-chartjs';

ChartJS.register(
  CategoryScale,
  LinearScale,
  BarElement,
  Title,
  Tooltip,
  Legend,
  ArcElement,
  PointElement,
  LineElement
);

export default {
  name: 'ChartComponent',
  props: {
    title: {
      type: String,
      default: 'Chart'
    },
    type: {
      type: String,
      default: 'bar' // bar, doughnut, line
    },
    data: {
      type: Object,
      required: true
    },
    options: {
      type: Object,
      default: () => ({})
    }
  },
  mounted() {
    this.renderChart();
  },
  watch: {
    data: {
      handler() {
        this.renderChart();
      },
      deep: true
    }
  },
  methods: {
    renderChart() {
      if (this.chart) {
        this.chart.destroy();
      }

      const ctx = this.$refs.chartCanvas.getContext('2d');

      let ChartComponent;
      switch (this.type) {
        case 'doughnut':
          ChartComponent = Doughnut;
          break;
        case 'line':
          ChartComponent = Line;
          break;
        default:
          ChartComponent = Bar;
      }

      this.chart = new ChartJS(ctx, {
        type: this.type,
        data: this.data,
        options: {
          responsive: true,
          maintainAspectRatio: false,
          ...this.options
        }
      });
    }
  },
  beforeUnmount() {
    if (this.chart) {
      this.chart.destroy();
    }
  }
}
</script>

<style scoped>
.chart-container {
  background: white;
  border-radius: 8px;
  padding: 20px;
  box-shadow: 0 2px 4px rgba(0,0,0,0.1);
  height: 400px;
}
</style>