<template>
  <div class="min-h-screen bg-gradient-to-br from-blue-50 to-indigo-100">
    <!-- Navigation Header -->
    <nav class="bg-white shadow-lg">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
          <div class="flex items-center">
            <h1 class="text-xl font-bold text-gray-800">
              Sistema de Mantenimiento Predictivo
            </h1>
          </div>
          <div class="flex items-center space-x-4">
            <span class="text-sm text-gray-600">Usuario: Admin</span>
            <button class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
              Cerrar Sesión
            </button>
          </div>
        </div>
      </div>
    </nav>

    <!-- Main Content -->
    <div class="max-w-7xl mx-auto p-6">
      <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-800 mb-2">
          Dashboard Principal
        </h1>
        <p class="text-gray-600">
          Monitoreo en tiempo real del estado de los equipos industriales
        </p>
      </div>
      <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        <div class="bg-white rounded-xl shadow-lg p-6 hover:shadow-xl transition-all duration-300 transform hover:-translate-y-1">
          <div class="flex items-center justify-between">
            <div>
              <h3 class="text-lg font-semibold text-gray-700 mb-2">Equipos</h3>
              <p class="text-3xl font-bold text-blue-600">{{ equiposCount }}</p>
            </div>
            <div class="text-4xl text-blue-500">⚙️</div>
          </div>
        </div>
        <div class="bg-white rounded-xl shadow-lg p-6 hover:shadow-xl transition-all duration-300 transform hover:-translate-y-1">
          <div class="flex items-center justify-between">
            <div>
              <h3 class="text-lg font-semibold text-gray-700 mb-2">Sensores</h3>
              <p class="text-3xl font-bold text-green-600">{{ sensoresCount }}</p>
            </div>
            <div class="text-4xl text-green-500">📡</div>
          </div>
        </div>
        <div class="bg-white rounded-xl shadow-lg p-6 hover:shadow-xl transition-all duration-300 transform hover:-translate-y-1">
          <div class="flex items-center justify-between">
            <div>
              <h3 class="text-lg font-semibold text-gray-700 mb-2">Alertas Activas</h3>
              <p class="text-3xl font-bold text-red-600">{{ alertasCount }}</p>
            </div>
            <div class="text-4xl text-red-500">🚨</div>
          </div>
        </div>
      </div>
      <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
        <ChartComponent
          v-if="chartData"
          title="Distribución de Alertas por Nivel"
          type="bar"
          :data="chartData.alertas_por_nivel"
        />
        <ARComponent />
      </div>
      <AlertasRealtime />
    </div>
  </div>
</template>

<script>
import AlertasRealtime from './AlertasRealtime.vue';
import ChartComponent from './ChartComponent.vue';
import ARComponent from './ARComponent.vue';

export default {
  name: 'Dashboard',
  components: {
    AlertasRealtime,
    ChartComponent,
    ARComponent
  },
  data() {
    return {
      equiposCount: 0,
      sensoresCount: 0,
      alertasCount: 0,
      chartData: null
    }
  },
  async mounted() {
    await this.loadDashboardData();
  },
  methods: {
    async loadDashboardData() {
      try {
        const response = await axios.get('/api/dashboard');
        const data = response.data;
        this.equiposCount = data.equipos;
        this.sensoresCount = data.sensores;
        this.alertasCount = data.alertas_activas;
        this.chartData = data.chart_data;
      } catch (error) {
        console.error('Error loading dashboard data:', error);
        // Fallback to example data
        this.equiposCount = 5;
        this.sensoresCount = 20;
        this.alertasCount = 2;
        this.chartData = {
          alertas_por_nivel: {
            labels: ['Bajo', 'Medio', 'Alto'],
            datasets: [{
              label: 'Alertas',
              data: [1, 2, 3],
              backgroundColor: ['green', 'yellow', 'red']
            }]
          }
        };
      }
    }
  }
}
</script>
