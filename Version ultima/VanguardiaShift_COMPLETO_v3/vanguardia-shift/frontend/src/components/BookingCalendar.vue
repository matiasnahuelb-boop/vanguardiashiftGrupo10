<template>
  <!--
    BookingCalendar.vue
    
    Componente principal de reserva de turnos para VanguardiaShift.
    Implementado en Vue 3 con la Composition API y Tailwind CSS.
    
    Composition API vs Options API:
    - Options API (Vue 2): organiza el código por "tipo" (data, methods, computed).
    - Composition API (Vue 3): organiza el código por "funcionalidad", 
      lo que facilita extraer y reutilizar lógica en composables.
    
    El componente sigue un flujo de 3 pasos:
    Paso 1 → Seleccionar profesional
    Paso 2 → Seleccionar fecha y horario disponible
    Paso 3 → Confirmar reserva
  -->
  <div class="min-h-screen bg-gradient-to-br from-slate-50 to-blue-50 p-4 md:p-8">
    
    <!-- ===== ENCABEZADO ===== -->
    <header class="max-w-3xl mx-auto mb-8 text-center">
      <div class="inline-flex items-center gap-2 bg-blue-600 text-white px-4 py-1.5 rounded-full text-sm font-medium mb-4">
        <span class="w-2 h-2 bg-blue-300 rounded-full animate-pulse"></span>
        Sistema de Turnos Online
      </div>
      <h1 class="text-3xl md:text-4xl font-bold text-slate-800 tracking-tight">
        VanguardiaShift
      </h1>
      <p class="mt-2 text-slate-500 text-base">
        Reservá tu turno en segundos, sin llamadas telefónicas.
      </p>
    </header>

    <!-- ===== INDICADOR DE PASOS ===== -->
    <div class="max-w-3xl mx-auto mb-8">
      <div class="flex items-center justify-center gap-2">
        <template v-for="(paso, index) in pasos" :key="index">
          <!-- Punto de paso -->
          <div class="flex flex-col items-center gap-1">
            <div
              class="w-9 h-9 rounded-full flex items-center justify-center text-sm font-bold transition-all duration-300"
              :class="obtenerClasePaso(index + 1)"
            >
              <!-- Checkmark si el paso fue completado -->
              <svg v-if="pasoActual > index + 1" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
              </svg>
              <span v-else>{{ index + 1 }}</span>
            </div>
            <span class="text-xs font-medium hidden md:block"
              :class="pasoActual >= index + 1 ? 'text-blue-600' : 'text-slate-400'"
            >
              {{ paso }}
            </span>
          </div>
          <!-- Línea conectora entre pasos -->
          <div
            v-if="index < pasos.length - 1"
            class="h-0.5 w-12 md:w-24 mt-[-10px] transition-all duration-500"
            :class="pasoActual > index + 1 ? 'bg-blue-500' : 'bg-slate-200'"
          ></div>
        </template>
      </div>
    </div>

    <!-- ===== CARD PRINCIPAL ===== -->
    <div class="max-w-3xl mx-auto bg-white rounded-2xl shadow-lg shadow-slate-200/60 overflow-hidden border border-slate-100">

      <!-- ---------------------------------------------------------------- -->
      <!-- PASO 1: Selección de profesional                                 -->
      <!-- ---------------------------------------------------------------- -->
      <Transition name="slide-fade" mode="out-in">
        <div v-if="pasoActual === 1" key="paso1" class="p-6 md:p-8">
          <h2 class="text-xl font-semibold text-slate-800 mb-1">Seleccioná un profesional</h2>
          <p class="text-sm text-slate-400 mb-6">Elegí el especialista para tu consulta.</p>

          <!-- Estado de carga de profesionales -->
          <div v-if="cargandoProfesionales" class="space-y-3">
            <div v-for="n in 3" :key="n"
              class="h-20 bg-slate-100 rounded-xl animate-pulse"
            ></div>
          </div>

          <!-- Lista de profesionales -->
          <div v-else class="space-y-3">
            <button
              v-for="profesional in profesionales"
              :key="profesional.id"
              @click="seleccionarProfesional(profesional)"
              class="w-full text-left flex items-center gap-4 p-4 rounded-xl border-2 transition-all duration-200 hover:border-blue-400 hover:bg-blue-50/50 group"
              :class="profesionalSeleccionado?.id === profesional.id
                ? 'border-blue-500 bg-blue-50'
                : 'border-slate-200'"
            >
              <!-- Avatar generado con iniciales -->
              <div class="w-12 h-12 rounded-full flex items-center justify-center text-white font-bold text-lg flex-shrink-0"
                :style="{ backgroundColor: profesional.color }">
                {{ profesional.nombre.charAt(0) }}
              </div>
              <div class="flex-1 min-w-0">
                <p class="font-semibold text-slate-800 group-hover:text-blue-700 transition-colors">
                  {{ profesional.nombre }}
                </p>
                <p class="text-sm text-slate-400 truncate">{{ profesional.especialidad }}</p>
              </div>
              <!-- Radio button visual -->
              <div class="w-5 h-5 rounded-full border-2 flex items-center justify-center flex-shrink-0"
                :class="profesionalSeleccionado?.id === profesional.id
                  ? 'border-blue-500 bg-blue-500'
                  : 'border-slate-300'">
                <div v-if="profesionalSeleccionado?.id === profesional.id"
                  class="w-2 h-2 bg-white rounded-full"></div>
              </div>
            </button>
          </div>

          <!-- Botón Continuar -->
          <button
            @click="pasoActual = 2"
            :disabled="!profesionalSeleccionado"
            class="mt-6 w-full py-3.5 px-6 rounded-xl font-semibold text-white transition-all duration-200 disabled:opacity-40 disabled:cursor-not-allowed"
            :class="profesionalSeleccionado
              ? 'bg-blue-600 hover:bg-blue-700 shadow-md shadow-blue-200'
              : 'bg-slate-300'"
          >
            Continuar →
          </button>
        </div>
      </Transition>

      <!-- ---------------------------------------------------------------- -->
      <!-- PASO 2: Selección de fecha y horario                             -->
      <!-- ---------------------------------------------------------------- -->
      <Transition name="slide-fade" mode="out-in">
        <div v-if="pasoActual === 2" key="paso2" class="p-6 md:p-8">
          <h2 class="text-xl font-semibold text-slate-800 mb-1">Elegí fecha y horario</h2>
          <p class="text-sm text-slate-400 mb-6">
            Disponibilidad de <strong class="text-slate-600">{{ profesionalSeleccionado?.nombre }}</strong>
          </p>

          <!-- Selector de fecha -->
          <div class="mb-6">
            <label class="block text-sm font-medium text-slate-700 mb-2">
              📅 Fecha del turno
            </label>
            <input
              v-model="fechaSeleccionada"
              type="date"
              :min="fechaMinima"
              @change="cargarDisponibilidad"
              class="w-full px-4 py-3 border-2 border-slate-200 rounded-xl text-slate-800 focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100 transition-all duration-200"
            />
          </div>

          <!-- Horarios disponibles -->
          <div v-if="fechaSeleccionada">
            <label class="block text-sm font-medium text-slate-700 mb-3">
              🕐 Horarios disponibles
            </label>

            <!-- Spinner de carga -->
            <div v-if="cargandoDisponibilidad" class="flex items-center justify-center py-10">
              <div class="w-8 h-8 border-4 border-blue-500 border-t-transparent rounded-full animate-spin"></div>
              <span class="ml-3 text-sm text-slate-400">Consultando disponibilidad...</span>
            </div>

            <!-- Grilla de horarios -->
            <div v-else-if="horariosDisponibles.length > 0"
              class="grid grid-cols-3 sm:grid-cols-4 gap-2">
              <button
                v-for="horario in horariosDisponibles"
                :key="horario.hora"
                @click="horarioSeleccionado = horario"
                :disabled="!horario.disponible"
                class="py-2.5 px-2 rounded-lg text-sm font-medium text-center transition-all duration-150"
                :class="obtenerClaseHorario(horario)"
              >
                {{ horario.hora }}
              </button>
            </div>

            <!-- Sin disponibilidad -->
            <div v-else class="text-center py-8 text-slate-400">
              <p class="text-2xl mb-2">😔</p>
              <p class="text-sm">No hay horarios disponibles para esta fecha.</p>
              <p class="text-xs mt-1">Probá con otra fecha.</p>
            </div>
          </div>

          <!-- Navegación entre pasos -->
          <div class="flex gap-3 mt-6">
            <button
              @click="pasoActual = 1"
              class="flex-1 py-3.5 px-6 rounded-xl font-semibold text-slate-600 bg-slate-100 hover:bg-slate-200 transition-all duration-200"
            >
              ← Atrás
            </button>
            <button
              @click="pasoActual = 3"
              :disabled="!horarioSeleccionado"
              class="flex-[2] py-3.5 px-6 rounded-xl font-semibold text-white transition-all duration-200 disabled:opacity-40 disabled:cursor-not-allowed"
              :class="horarioSeleccionado
                ? 'bg-blue-600 hover:bg-blue-700 shadow-md shadow-blue-200'
                : 'bg-slate-300'"
            >
              Confirmar horario →
            </button>
          </div>
        </div>
      </Transition>

      <!-- ---------------------------------------------------------------- -->
      <!-- PASO 3: Confirmación y formulario                                -->
      <!-- ---------------------------------------------------------------- -->
      <Transition name="slide-fade" mode="out-in">
        <div v-if="pasoActual === 3" key="paso3" class="p-6 md:p-8">
          <h2 class="text-xl font-semibold text-slate-800 mb-1">Confirmá tu reserva</h2>
          <p class="text-sm text-slate-400 mb-6">Revisá los detalles antes de confirmar.</p>

          <!-- Resumen de la reserva -->
          <div class="bg-blue-50 border border-blue-100 rounded-xl p-4 mb-6 space-y-3">
            <div class="flex items-center gap-3">
              <div class="w-10 h-10 rounded-full flex items-center justify-center text-white font-bold"
                :style="{ backgroundColor: profesionalSeleccionado?.color }">
                {{ profesionalSeleccionado?.nombre.charAt(0) }}
              </div>
              <div>
                <p class="font-semibold text-slate-800">{{ profesionalSeleccionado?.nombre }}</p>
                <p class="text-xs text-slate-500">{{ profesionalSeleccionado?.especialidad }}</p>
              </div>
            </div>
            <div class="grid grid-cols-2 gap-2 text-sm">
              <div class="bg-white rounded-lg p-3 text-center">
                <p class="text-xs text-slate-400 uppercase tracking-wide mb-1">Fecha</p>
                <p class="font-semibold text-slate-700">{{ fechaFormateada }}</p>
              </div>
              <div class="bg-white rounded-lg p-3 text-center">
                <p class="text-xs text-slate-400 uppercase tracking-wide mb-1">Hora</p>
                <p class="font-semibold text-slate-700">{{ horarioSeleccionado?.hora }}</p>
              </div>
            </div>
          </div>

          <!-- Campo de motivo (opcional) -->
          <div class="mb-6">
            <label class="block text-sm font-medium text-slate-700 mb-2">
              Motivo de la consulta <span class="text-slate-400 font-normal">(opcional)</span>
            </label>
            <textarea
              v-model="motivo"
              rows="3"
              maxlength="500"
              placeholder="Ej: Consulta de rutina, revisión de resultados..."
              class="w-full px-4 py-3 border-2 border-slate-200 rounded-xl text-slate-800 placeholder-slate-300 focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100 transition-all duration-200 resize-none text-sm"
            ></textarea>
            <p class="text-xs text-slate-400 text-right mt-1">{{ motivo.length }}/500</p>
          </div>

          <!-- Mensaje de éxito -->
          <Transition name="fade">
            <div v-if="reservaExitosa"
              class="mb-4 p-4 bg-green-50 border border-green-200 rounded-xl flex items-center gap-3">
              <svg class="w-6 h-6 text-green-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
              </svg>
              <p class="text-sm text-green-700 font-medium">
                ¡Turno reservado exitosamente! Recibirás una confirmación por email.
              </p>
            </div>
          </Transition>

          <!-- Mensaje de error -->
          <Transition name="fade">
            <div v-if="errorReserva"
              class="mb-4 p-4 bg-red-50 border border-red-200 rounded-xl flex items-center gap-3">
              <svg class="w-6 h-6 text-red-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
              </svg>
              <p class="text-sm text-red-700">{{ errorReserva }}</p>
            </div>
          </Transition>

          <!-- Botones de acción -->
          <div class="flex gap-3">
            <button
              @click="pasoActual = 2"
              :disabled="enviandoReserva"
              class="flex-1 py-3.5 px-6 rounded-xl font-semibold text-slate-600 bg-slate-100 hover:bg-slate-200 transition-all duration-200 disabled:opacity-40"
            >
              ← Atrás
            </button>
            <button
              @click="confirmarReserva"
              :disabled="enviandoReserva || reservaExitosa"
              class="flex-[2] py-3.5 px-6 rounded-xl font-semibold text-white bg-blue-600 hover:bg-blue-700 transition-all duration-200 disabled:opacity-60 disabled:cursor-not-allowed shadow-md shadow-blue-200 flex items-center justify-center gap-2"
            >
              <!-- Spinner durante el envío -->
              <div v-if="enviandoReserva"
                class="w-5 h-5 border-2 border-white border-t-transparent rounded-full animate-spin">
              </div>
              <span>{{ enviandoReserva ? 'Reservando...' : '✓ Confirmar Turno' }}</span>
            </button>
          </div>
        </div>
      </Transition>

    </div><!-- fin card principal -->
  </div>
</template>

<script setup>
/**
 * BookingCalendar.vue - Script de lógica del componente
 *
 * Usa Vue 3 Composition API con <script setup> (sintaxis moderna y concisa).
 * <script setup> compila el código en el setup() del componente automáticamente,
 * exponiendo las variables y funciones al template sin necesidad de return {}.
 *
 * Librerías externas:
 *  - axios: cliente HTTP para comunicarse con la API de Laravel.
 *    Elegido sobre fetch() nativo por su manejo automático de errores
 *    y la posibilidad de configurar interceptors globales.
 */

import { ref, computed, onMounted } from 'vue'
import axios from 'axios'

// ============================================================================
// CONFIGURACIÓN DE AXIOS
// ============================================================================

/**
 * Instancia de Axios con configuración base.
 * Centralizar la configuración permite cambiar la URL de la API en un solo lugar.
 * En producción, la baseURL vendría de variables de entorno (.env de Vite).
 */
const api = axios.create({
  baseURL: import.meta.env.VITE_API_URL || 'http://localhost:8000',
  // Tiempo máximo de espera para una petición: 10 segundos.
  timeout: 10000,
  headers: {
    'Content-Type': 'application/json',
    'Accept': 'application/json',
  }
})

/**
 * Interceptor de request: agrega el token de autenticación a cada petición.
 * En producción, el token se obtiene del localStorage o de un store (Pinia/Vuex).
 */
api.interceptors.request.use(config => {
  const token = localStorage.getItem('auth_token')
  if (token) {
    config.headers.Authorization = `Bearer ${token}`
  }
  return config
})

// ============================================================================
// ESTADO REACTIVO (ref() crea referencias reactivas en Vue 3)
// ============================================================================

// Estado del flujo de pasos
const pasoActual = ref(1)
const pasos = ['Profesional', 'Fecha y Hora', 'Confirmación']

// Estado de profesionales
const profesionales = ref([])
const profesionalSeleccionado = ref(null)
const cargandoProfesionales = ref(false)

// Estado de disponibilidad
const fechaSeleccionada = ref('')
const horariosDisponibles = ref([])
const horarioSeleccionado = ref(null)
const cargandoDisponibilidad = ref(false)

// Estado del formulario de confirmación
const motivo = ref('')
const enviandoReserva = ref(false)
const reservaExitosa = ref(false)
const errorReserva = ref('')

// ============================================================================
// PROPIEDADES COMPUTADAS
// ============================================================================

/**
 * computed() recalcula el valor solo cuando sus dependencias reactivas cambian.
 * Es más eficiente que un método porque cachea el resultado.
 */

/** Fecha mínima seleccionable: hoy (no se pueden reservar turnos en el pasado). */
const fechaMinima = computed(() => {
  return new Date().toISOString().split('T')[0]
})

/** Formatea la fecha seleccionada para mostrarla al usuario. */
const fechaFormateada = computed(() => {
  if (!fechaSeleccionada.value) return ''
  const fecha = new Date(fechaSeleccionada.value + 'T12:00:00') // Evitamos problema de timezone
  return fecha.toLocaleDateString('es-AR', {
    weekday: 'long',
    day: 'numeric',
    month: 'long',
    year: 'numeric'
  })
})

// ============================================================================
// MÉTODOS
// ============================================================================

/**
 * Carga la lista de profesionales desde la API de Laravel.
 * Se llama al montar el componente (onMounted).
 *
 * Manejo de errores: usamos try/catch para capturar tanto errores de red
 * como errores HTTP devueltos por el servidor.
 */
const cargarProfesionales = async () => {
  cargandoProfesionales.value = true
  try {
    // En un sistema real, esta llamada iría a /api/profesionales
    // Para demostración, usamos datos locales simulados (mock).
    await new Promise(resolve => setTimeout(resolve, 800)) // Simula latencia de red

    // Datos simulados que representarían la respuesta de GET /api/profesionales
    profesionales.value = [
      { id: 1, nombre: 'Dra. Valentina Pérez', especialidad: 'Clínica Médica', color: '#3B82F6' },
      { id: 2, nombre: 'Dr. Martín García',    especialidad: 'Cardiología',    color: '#8B5CF6' },
      { id: 3, nombre: 'Lic. Sofía Romero',    especialidad: 'Psicología',     color: '#10B981' },
      { id: 4, nombre: 'Dr. Lucas Fernández',  especialidad: 'Traumatología',  color: '#F59E0B' },
    ]

    /* CÓDIGO REAL PARA PRODUCCIÓN (descomentar al integrar con Laravel):
    const response = await api.get('/api/profesionales')
    profesionales.value = response.data.data
    */

  } catch (error) {
    console.error('Error al cargar profesionales:', error)
    // En producción, mostrar un mensaje de error al usuario.
  } finally {
    // finally garantiza que el spinner se detenga, pase lo que pase.
    cargandoProfesionales.value = false
  }
}

/**
 * Maneja la selección de un profesional y avanza al paso 2.
 * @param {Object} profesional - Objeto del profesional seleccionado.
 */
const seleccionarProfesional = (profesional) => {
  profesionalSeleccionado.value = profesional
  // Reseteamos la selección de horario al cambiar de profesional.
  horarioSeleccionado.value = null
  horariosDisponibles.value = []
  fechaSeleccionada.value = ''
}

/**
 * Consulta los horarios disponibles del profesional para una fecha dada.
 * Se llama al cambiar la fecha seleccionada.
 *
 * Endpoint equivalente en Laravel:
 * GET /api/profesionales/{id}/disponibilidad?fecha=YYYY-MM-DD
 */
const cargarDisponibilidad = async () => {
  if (!fechaSeleccionada.value || !profesionalSeleccionado.value) return

  cargandoDisponibilidad.value = true
  horarioSeleccionado.value = null // Reseteamos la selección anterior

  try {
    await new Promise(resolve => setTimeout(resolve, 600)) // Simula latencia

    // Generamos horarios simulados para la demo visual.
    // En producción, estos vendrían de la respuesta de la API.
    const horariosBase = ['09:00', '09:30', '10:00', '10:30', '11:00', '11:30',
                          '14:00', '14:30', '15:00', '15:30', '16:00', '16:30']

    horariosDisponibles.value = horariosBase.map(hora => ({
      hora,
      // Simulamos algunos horarios ocupados aleatoriamente para demostración.
      disponible: Math.random() > 0.3,
      fechaHoraISO: `${fechaSeleccionada.value}T${hora}:00`
    }))

    /* CÓDIGO REAL PARA PRODUCCIÓN:
    const response = await api.get(
      `/api/profesionales/${profesionalSeleccionado.value.id}/disponibilidad`,
      { params: { fecha: fechaSeleccionada.value } }
    )
    horariosDisponibles.value = response.data.data
    */

  } catch (error) {
    console.error('Error al cargar disponibilidad:', error)
  } finally {
    cargandoDisponibilidad.value = false
  }
}

/**
 * Envía la reserva a la API de Laravel.
 * Corresponde a POST /api/turnos.
 *
 * Manejo de errores HTTP:
 *  - 201: Éxito, turno creado.
 *  - 409: Conflicto, horario ya ocupado (race condition).
 *  - 422: Error de validación del servidor.
 *  - 401/403: Problemas de autenticación.
 */
const confirmarReserva = async () => {
  enviandoReserva.value = true
  errorReserva.value = ''

  try {
    // Construimos el payload que espera el TurnoController::store()
    const payload = {
      profesional_id: profesionalSeleccionado.value.id,
      fecha_hora:     horarioSeleccionado.value.fechaHoraISO,
      motivo:         motivo.value || null,
    }

    // SIMULACIÓN: En producción, usar la línea comentada
    await new Promise(resolve => setTimeout(resolve, 1200))
    console.log('Payload enviado a POST /api/turnos:', payload)

    /* CÓDIGO REAL PARA PRODUCCIÓN:
    const response = await api.post('/api/turnos', payload)
    console.log('Turno creado:', response.data)
    */

    reservaExitosa.value = true

  } catch (error) {
    // Axios lanza una excepción para respuestas con status 4xx/5xx.
    if (error.response) {
      // El servidor respondió con un código de error.
      const status = error.response.status

      if (status === 409) {
        errorReserva.value = 'Ese horario ya fue reservado por otro usuario. Por favor, elegí otro.'
      } else if (status === 422) {
        // Laravel devuelve los errores de validación en errors.{campo}
        const errores = error.response.data.errors
        const primerError = Object.values(errores)[0]?.[0]
        errorReserva.value = primerError || 'Los datos enviados no son válidos.'
      } else if (status === 401) {
        errorReserva.value = 'Tu sesión expiró. Por favor, iniciá sesión nuevamente.'
      } else {
        errorReserva.value = 'Ocurrió un error inesperado. Intentá nuevamente.'
      }
    } else if (error.request) {
      // La petición se hizo pero no hubo respuesta (sin conexión).
      errorReserva.value = 'No se pudo conectar con el servidor. Verificá tu conexión.'
    }
  } finally {
    enviandoReserva.value = false
  }
}

// ============================================================================
// HELPERS DE ESTILOS (retornan clases de Tailwind según el estado)
// ============================================================================

/** Retorna las clases CSS para cada punto del indicador de pasos. */
const obtenerClasePaso = (numeroPaso) => {
  if (pasoActual.value > numeroPaso) return 'bg-blue-500 text-white'   // Completado
  if (pasoActual.value === numeroPaso) return 'bg-blue-600 text-white ring-4 ring-blue-100' // Activo
  return 'bg-slate-100 text-slate-400 border-2 border-slate-200'        // Pendiente
}

/** Retorna las clases CSS para cada botón de horario. */
const obtenerClaseHorario = (horario) => {
  if (!horario.disponible)
    return 'bg-slate-50 text-slate-300 cursor-not-allowed border border-slate-100'
  if (horarioSeleccionado.value?.hora === horario.hora)
    return 'bg-blue-600 text-white border-2 border-blue-600 shadow-md shadow-blue-200'
  return 'bg-white text-slate-700 border-2 border-slate-200 hover:border-blue-400 hover:text-blue-600'
}

// ============================================================================
// LIFECYCLE HOOKS
// ============================================================================

/**
 * onMounted: se ejecuta cuando el componente es insertado en el DOM.
 * Es el lugar correcto para hacer la primera carga de datos.
 */
onMounted(() => {
  cargarProfesionales()
})
</script>

<style scoped>
/**
 * Estilos locales con animaciones para las transiciones entre pasos.
 * 'scoped' garantiza que estos estilos no afecten a otros componentes.
 *
 * slide-fade: deslizamiento horizontal con desvanecimiento.
 * fade: desvanecimiento simple para mensajes de estado.
 */

.slide-fade-enter-active,
.slide-fade-leave-active {
  transition: all 0.25s ease;
}

.slide-fade-enter-from {
  opacity: 0;
  transform: translateX(16px);
}

.slide-fade-leave-to {
  opacity: 0;
  transform: translateX(-16px);
}

.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.3s ease;
}

.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}
</style>
