<template>
  <div class="ar-container">
    <h3 class="text-lg font-semibold text-gray-700 mb-4">Visualización AR de Equipos</h3>
    <div class="ar-scene" v-if="arSupported">
      <a-scene embedded arjs="sourceType: webcam; debugUIEnabled: false;">
        <a-marker preset="hiro">
          <!-- Equipo principal -->
          <a-box position="0 0.5 0" material="color: #4CAF50" scale="0.5 0.5 0.5">
            <a-animation attribute="rotation" dur="4000" to="0 360 0" repeat="indefinite"></a-animation>
          </a-box>
          <a-text value="Motor Principal" position="0 1.2 0" align="center" color="#ffffff" scale="0.5 0.5 0.5"></a-text>
          <a-text value="Estado: Operativo" position="0 0.8 0" align="center" color="#4CAF50" scale="0.3 0.3 0.3"></a-text>

          <!-- Sensores asociados -->
          <a-cylinder position="-0.8 0.3 0" radius="0.1" height="0.2" material="color: #2196F3">
            <a-text value="Temp: 45°C" position="0 0.3 0" align="center" scale="0.2 0.2 0.2"></a-text>
          </a-cylinder>
          <a-cylinder position="0.8 0.3 0" radius="0.1" height="0.2" material="color: #FF9800">
            <a-text value="Vib: 2.1 mm/s" position="0 0.3 0" align="center" scale="0.2 0.2 0.2"></a-text>
          </a-cylinder>
          <a-cylinder position="0 0.3 -0.8" radius="0.1" height="0.2" material="color: #9C27B0">
            <a-text value="Pres: 1.8 Bar" position="0 0.3 0" align="center" scale="0.2 0.2 0.2"></a-text>
          </a-cylinder>

          <!-- Indicador de mantenimiento -->
          <a-ring position="0 -0.2 0" radius-inner="0.3" radius-outer="0.4" material="color: #FFC107" rotation="-90 0 0">
            <a-text value="Próximo mantenimiento: 15 días" position="0 -0.1 0" align="center" scale="0.15 0.15 0.15"></a-text>
          </a-ring>
        </a-marker>
        <a-entity camera></a-entity>
      </a-scene>
    </div>
    <div v-else class="ar-placeholder">
      <p>La Realidad Aumentada requiere una cámara y un navegador compatible.</p>
      <p v-if="errorMessage" class="error">{{ errorMessage }}</p>
      <p>Apunte la cámara a un marcador Hiro para ver la visualización AR.</p>
      <p class="hint">Asegúrese de permitir el acceso a la cámara cuando se solicite.</p>
    </div>
  </div>
</template>

<script>
export default {
  name: 'ARComponent',
  data() {
    return {
      arSupported: false,
      errorMessage: ''
    }
  },
  async mounted() {
    // Request camera permission first
    try {
      const stream = await navigator.mediaDevices.getUserMedia({ video: true });
      stream.getTracks().forEach(track => track.stop()); // Stop the test stream
      this.cameraAllowed = true;
    } catch (error) {
      console.error('Camera permission denied:', error);
      this.arSupported = false;
      this.errorMessage = 'Permiso de cámara denegado. Por favor, permita el acceso a la cámara para usar Realidad Aumentada.';
      return;
    }

    // Load A-Frame and AR.js from CDN
    this.loadScripts();

    // Listen for AR errors
    window.addEventListener('arjs-video-error', (event) => {
      console.error('AR.js video error:', event.detail);
      this.arSupported = false;
      this.errorMessage = 'Error de cámara: ' + (event.detail?.message || 'Error desconocido');
    });
  },
  methods: {
    loadScripts() {
      const scripts = [
        'https://aframe.io/releases/1.4.0/aframe.min.js',
        'https://raw.githack.com/AR-js-org/AR.js/master/aframe/build/aframe-ar.js'
      ];

      let loaded = 0;
      scripts.forEach(src => {
        const script = document.createElement('script');
        script.src = src;
        script.onload = () => {
          loaded++;
          if (loaded === scripts.length) {
            this.arSupported = true;
          }
        };
        script.onerror = () => {
          console.warn('Failed to load AR script:', src);
        };
        document.head.appendChild(script);
      });
    }
  }
}
</script>

<style scoped>
.ar-container {
  background: white;
  border-radius: 8px;
  padding: 20px;
  box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.ar-scene {
  width: 100%;
  height: 400px;
  border: 1px solid #ddd;
  border-radius: 4px;
  overflow: hidden;
}

.error {
  color: #dc3545;
  font-weight: bold;
  margin: 10px 0;
}

.hint {
  font-size: 0.9em;
  color: #666;
  margin-top: 10px;
}
</style>