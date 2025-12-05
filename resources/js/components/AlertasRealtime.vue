<template>
  <div class="alertas-realtime">
    <h2>Alertas en Tiempo Real</h2>
    <div v-if="alertas.length === 0" class="no-alertas">
      No hay alertas activas
    </div>
    <div v-else class="alertas-list">
      <div
        v-for="alerta in alertas"
        :key="alerta.id"
        :class="['alerta-card', alerta.nivel_criticidad]"
      >
        <div class="alerta-header">
          <span class="tipo-fallo">{{ alerta.tipo_fallo }}</span>
          <span class="nivel">{{ alerta.nivel_criticidad }}</span>
        </div>
        <p class="descripcion">{{ alerta.descripcion }}</p>
        <small class="timestamp">{{ formatTimestamp(alerta.created_at) }}</small>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'AlertasRealtime',
  emits: ['alerta-recibida'],
  data() {
    return {
      alertas: [],
      equiposSuscriptos: [] // Mantener track de equipos suscriptos
    }
  },
  mounted() {
    // Escuchar el canal general de alertas
    window.Echo.channel('alertas')
      .listen('.nueva-alerta', (e) => {
        console.log('Nueva alerta IA recibida:', e);
        // Adaptar la estructura de datos del evento
        const alertaFormateada = {
          id: e.id,
          tipo_fallo: `Alerta IA - ${e.equipo}`,
          nivel_criticidad: e.nivel,
          descripcion: e.mensaje,
          created_at: e.timestamp
        };
        this.alertas.unshift(alertaFormateada);
        // Mantener solo las últimas 10 alertas
        if (this.alertas.length > 10) {
          this.alertas = this.alertas.slice(0, 10);
        }
      });

    // Escuchar canales específicos de equipos para lecturas críticas
    this.suscribirAEquiposCriticos();
  },
  beforeUnmount() {
    // Limpiar el listener general
    window.Echo.leaveChannel('alertas');

    // Limpiar listeners de equipos específicos
    this.equiposSuscriptos.forEach(equipoId => {
      window.Echo.leaveChannel(`alertas.${equipoId}`);
    });
  },
  methods: {
    suscribirAEquiposCriticos() {
      // En una implementación real, obtener equipos del usuario actual
      // Por ahora, escuchar canales de equipos críticos como ejemplo

      // Ejemplo: Suscribirse a equipos críticos (IDs 1, 2, 3)
      const equiposCriticos = [1, 2, 3]; // En producción: obtener de API

      equiposCriticos.forEach(equipoId => {
        window.Echo.channel(`alertas.${equipoId}`)
          .listen('.lectura-critica', (e) => {
            console.log(`Lectura crítica en equipo ${equipoId}:`, e);
            this.mostrarNotificacionCritica(e);
            this.agregarAlertaLista(e);
            this.reproducirSonidoAlerta();
          });

        this.equiposSuscriptos.push(equipoId);
      });
    },

    mostrarNotificacionCritica(eventData) {
      // Mostrar notificación visual (requiere librería de notificaciones)
      if (window.$toast || window.toastr) {
        const toast = window.$toast || window.toastr;
        toast.error(eventData.mensaje, `Alerta Crítica - ${eventData.equipo_nombre}`, {
          timeOut: 10000, // 10 segundos
          extendedTimeOut: 5000
        });
      } else {
        // Fallback: usar alert del navegador
        alert(`🚨 ALERTA CRÍTICA 🚨\n${eventData.mensaje}\nEquipo: ${eventData.equipo_nombre}`);
      }
    },

    agregarAlertaLista(eventData) {
      const alertaFormateada = {
        id: Date.now(),
        tipo_fallo: `Lectura Crítica - ${eventData.equipo_nombre}`,
        nivel_criticidad: eventData.nivel_criticidad.toLowerCase(),
        descripcion: `${eventData.mensaje} - Valor: ${eventData.valor_actual} (${eventData.sensor_tipo})`,
        created_at: eventData.timestamp,
        es_critica: true
      };

      this.alertas.unshift(alertaFormateada);

      // Mantener solo las últimas 10 alertas
      if (this.alertas.length > 10) {
        this.alertas = this.alertas.slice(0, 10);
      }

      // Emitir evento para que otros componentes se actualicen
      this.$emit('alerta-recibida', alertaFormateada);
    },

    reproducirSonidoAlerta() {
      // Reproducir sonido de alerta si está disponible
      if ('Audio' in window) {
        try {
          const audio = new Audio('/sounds/alerta.mp3'); // Archivo de sonido
          audio.volume = 0.5;
          audio.play().catch(e => {
            console.log('No se pudo reproducir sonido de alerta:', e);
          });
        } catch (e) {
          console.log('Error al reproducir sonido:', e);
        }
      }
    },

    formatTimestamp(timestamp) {
      return new Date(timestamp).toLocaleString();
    }
  }
}
</script>

<style scoped>
.alertas-realtime {
  margin-top: 30px;
  padding: 20px;
  background: #fff;
  border-radius: 8px;
  box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.no-alertas {
  text-align: center;
  color: #666;
  font-style: italic;
}

.alertas-list {
  display: flex;
  flex-direction: column;
  gap: 10px;
}

.alerta-card {
  padding: 15px;
  border-radius: 6px;
  border-left: 4px solid;
}

.alerta-card.bajo {
  background: #e8f5e8;
  border-left-color: #4caf50;
}

.alerta-card.medio {
  background: #fff3cd;
  border-left-color: #ffc107;
}

.alerta-card.alto {
  background: #f8d7da;
  border-left-color: #dc3545;
}

.alerta-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 8px;
}

.tipo-fallo {
  font-weight: bold;
}

.nivel {
  padding: 2px 8px;
  border-radius: 12px;
  font-size: 0.8em;
  text-transform: uppercase;
}

.alerta-card.bajo .nivel {
  background: #4caf50;
  color: white;
}

.alerta-card.medio .nivel {
  background: #ffc107;
  color: black;
}

.alerta-card.alto .nivel {
  background: #dc3545;
  color: white;
}

.descripcion {
  margin: 0 0 8px 0;
}

.timestamp {
  color: #666;
  font-size: 0.8em;
}
</style>