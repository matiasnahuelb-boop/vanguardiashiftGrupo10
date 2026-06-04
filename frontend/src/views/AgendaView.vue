<template>
  <!--
    AgendaView.vue
    Agenda del profesional autenticado.
    Muestra los turnos del día y permite cambiar su estado.
  -->
  <div class="min-h-screen bg-slate-50">

    <!-- Navbar -->
    <nav class="bg-white border-b border-slate-200 px-6 py-4 flex items-center justify-between shadow-sm">
      <div class="flex items-center gap-4">
        <router-link to="/dashboard" class="text-slate-400 hover:text-slate-600 text-sm">← Volver</router-link>
        <span class="text-sm font-medium text-slate-700">Mi Agenda</span>
      </div>
      <input v-model="fechaSeleccionada" type="date" :min="hoy"
        class="text-sm border border-slate-200 rounded-lg px-3 py-1.5 focus:outline-none focus:border-blue-400" />
    </nav>

    <main class="max-w-3xl mx-auto p-6">
      <div class="flex items-center justify-between mb-6">
        <h1 class="text-xl font-bold text-slate-800">
          Agenda del {{ fechaFormateada }}
        </h1>
        <span class="text-sm text-slate-500">{{ turnos.length }} turno(s)</span>
      </div>

      <!-- Estado de carga -->
      <div v-if="cargando" class="flex justify-center py-12">
        <div class="w-8 h-8 border-4 border-teal-500 border-t-transparent rounded-full animate-spin"></div>
      </div>

      <!-- Sin turnos -->
      <div v-else-if="turnos.length === 0"
        class="bg-white rounded-2xl p-10 text-center border border-slate-200">
        <p class="text-3xl mb-3">📭</p>
        <p class="text-slate-500">No hay turnos para esta fecha.</p>
      </div>

      <!-- Lista de turnos -->
      <div v-else class="space-y-3">
        <div v-for="turno in turnos" :key="turno.id"
          class="bg-white rounded-xl p-4 border border-slate-200 flex items-center gap-4">

          <!-- Hora -->
          <div class="text-center w-16 flex-shrink-0">
            <div class="text-lg font-bold text-slate-800">{{ formatHora(turno.fecha_hora) }}</div>
            <div class="text-xs text-slate-400">hs</div>
          </div>

          <!-- Separador -->
          <div class="w-px h-12 bg-slate-200"></div>

          <!-- Info del turno -->
          <div class="flex-1 min-w-0">
            <div class="font-medium text-slate-800">{{ turno.cliente?.name }}</div>
            <div class="text-sm text-slate-500 truncate">{{ turno.motivo || 'Sin motivo especificado' }}</div>
          </div>

          <!-- Estado y acciones -->
          <div class="flex items-center gap-2 flex-shrink-0">
            <span class="text-xs px-2.5 py-1 rounded-full font-medium" :class="badgeEstado(turno.estado)">
              {{ turno.estado }}
            </span>

            <!-- Botones de acción (solo si está reservado) -->
            <div v-if="turno.estado === 'reservado'" class="flex gap-1">
              <button @click="cambiarEstado(turno.id, 'completado')"
                class="text-xs px-2.5 py-1 bg-teal-50 text-teal-600 hover:bg-teal-100 rounded-lg transition-colors font-medium">
                ✓ Completar
              </button>
              <button @click="cambiarEstado(turno.id, 'cancelado')"
                class="text-xs px-2.5 py-1 bg-red-50 text-red-500 hover:bg-red-100 rounded-lg transition-colors font-medium">
                ✕ Cancelar
              </button>
            </div>
          </div>
        </div>
      </div>
    </main>
  </div>
</template>

<script setup>
import { ref, computed, watch, onMounted } from 'vue'
import { api } from '@/services/api'

const hoy              = new Date().toISOString().split('T')[0]
const fechaSeleccionada = ref(hoy)
const turnos            = ref([])
const cargando          = ref(false)

const fechaFormateada = computed(() => {
  const fecha = new Date(fechaSeleccionada.value + 'T12:00:00')
  return fecha.toLocaleDateString('es-AR', { weekday: 'long', day: 'numeric', month: 'long' })
})

const formatHora = (fechaHora) => {
  return new Date(fechaHora).toLocaleTimeString('es-AR', { hour: '2-digit', minute: '2-digit' })
}

const badgeEstado = (estado) => ({
  'bg-blue-100 text-blue-700':   estado === 'reservado',
  'bg-teal-100 text-teal-700':   estado === 'completado',
  'bg-red-100 text-red-600':     estado === 'cancelado',
  'bg-amber-100 text-amber-700': estado === 'confirmado',
}[`bg-${['blue','teal','red','amber'].find(c => ({
  reservado:'blue',completado:'teal',cancelado:'red',confirmado:'amber'
})[estado] === c)}-100 text-${['blue','teal','red','amber'].find(c => ({
  reservado:'blue',completado:'teal',cancelado:'red',confirmado:'amber'
})[estado] === c)}-700`] ? Object.fromEntries([
  ['bg-blue-100 text-blue-700', estado === 'reservado'],
  ['bg-teal-100 text-teal-700', estado === 'completado'],
  ['bg-red-100 text-red-600',   estado === 'cancelado'],
  ['bg-amber-100 text-amber-700', estado === 'confirmado'],
]) : {})

async function cargarAgenda() {
  cargando.value = true
  try {
    const res = await api.get('/turnos/profesional', { params: { fecha: fechaSeleccionada.value } })
    turnos.value = res.data.data
  } catch (err) {
    console.error('Error al cargar agenda:', err)
  } finally {
    cargando.value = false
  }
}

async function cambiarEstado(turnoId, nuevoEstado) {
  try {
    await api.put(`/turnos/${turnoId}/estado`, { estado: nuevoEstado })
    // Actualizar localmente sin recargar toda la lista
    const turno = turnos.value.find(t => t.id === turnoId)
    if (turno) turno.estado = nuevoEstado
  } catch (err) {
    alert('No se pudo actualizar el estado del turno.')
  }
}

watch(fechaSeleccionada, cargarAgenda)
onMounted(cargarAgenda)
</script>
