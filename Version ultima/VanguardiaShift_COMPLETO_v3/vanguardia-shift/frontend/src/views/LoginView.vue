<template>
  <!--
    LoginView.vue
    Pantalla de inicio de sesión de VanguardiaShift.
    Muestra el formulario, valida campos y llama al store de autenticación.
  -->
  <div class="min-h-screen bg-gradient-to-br from-slate-50 to-blue-50 flex items-center justify-center p-4">
    <div class="w-full max-w-md">

      <!-- Logo y título -->
      <div class="text-center mb-8">
        <div class="inline-flex items-center gap-2 bg-blue-600 text-white px-5 py-2 rounded-full text-sm font-semibold mb-4 shadow-md">
          <span class="w-2 h-2 bg-blue-200 rounded-full animate-pulse"></span>
          Sistema de Turnos Online
        </div>
        <h1 class="text-3xl font-bold text-slate-800">VanguardiaShift</h1>
        <p class="text-slate-500 mt-1 text-sm">Gestión profesional de turnos médicos</p>
      </div>

      <!-- Card del formulario -->
      <div class="bg-white rounded-2xl shadow-lg shadow-slate-200/60 p-8 border border-slate-100">
        <h2 class="text-xl font-semibold text-slate-800 mb-6">Iniciar sesión</h2>

        <!-- Alerta de sesión expirada -->
        <div v-if="sesionExpirada"
          class="mb-4 p-3 bg-amber-50 border border-amber-200 rounded-lg text-sm text-amber-700 flex items-center gap-2">
          <svg class="w-4 h-4 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
          </svg>
          Tu sesión expiró. Iniciá sesión nuevamente.
        </div>

        <!-- Mensaje de error -->
        <div v-if="error"
          class="mb-4 p-3 bg-red-50 border border-red-200 rounded-lg text-sm text-red-600">
          {{ error }}
        </div>

        <!-- Formulario -->
        <div class="space-y-4">
          <!-- Email -->
          <div>
            <label class="block text-sm font-medium text-slate-700 mb-1.5">
              Correo electrónico
            </label>
            <input
              v-model="form.email"
              type="email"
              placeholder="tu@email.com"
              :disabled="cargando"
              @keyup.enter="handleLogin"
              class="w-full px-4 py-3 border-2 border-slate-200 rounded-xl text-slate-800 placeholder-slate-300 focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100 transition-all duration-200 disabled:opacity-50"
            />
          </div>

          <!-- Contraseña -->
          <div>
            <label class="block text-sm font-medium text-slate-700 mb-1.5">
              Contraseña
            </label>
            <input
              v-model="form.password"
              type="password"
              placeholder="••••••••"
              :disabled="cargando"
              @keyup.enter="handleLogin"
              class="w-full px-4 py-3 border-2 border-slate-200 rounded-xl text-slate-800 placeholder-slate-300 focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100 transition-all duration-200 disabled:opacity-50"
            />
          </div>

          <!-- Botón de ingreso -->
          <button
            @click="handleLogin"
            :disabled="cargando || !form.email || !form.password"
            class="w-full py-3.5 px-6 rounded-xl font-semibold text-white transition-all duration-200 flex items-center justify-center gap-2 disabled:opacity-50 disabled:cursor-not-allowed bg-blue-600 hover:bg-blue-700 shadow-md shadow-blue-200"
          >
            <div v-if="cargando" class="w-5 h-5 border-2 border-white border-t-transparent rounded-full animate-spin"></div>
            <span>{{ cargando ? 'Ingresando...' : 'Ingresar' }}</span>
          </button>
        </div>

        <!-- Demo credentials -->
        <div class="mt-6 p-4 bg-blue-50 rounded-xl border border-blue-100">
          <p class="text-xs font-semibold text-blue-700 mb-2 uppercase tracking-wide">Credenciales de demostración</p>
          <div class="space-y-1">
            <button @click="usarDemo('cliente@vanguardiashift.com', 'demo1234')"
              class="w-full text-left text-xs text-blue-600 hover:text-blue-800 transition-colors py-1">
              👤 Cliente: cliente@vanguardiashift.com / demo1234
            </button>
            <button @click="usarDemo('v.perez@vanguardiashift.com', 'password123')"
              class="w-full text-left text-xs text-blue-600 hover:text-blue-800 transition-colors py-1">
              🩺 Profesional: v.perez@vanguardiashift.com / password123
            </button>
          </div>
        </div>

        <!-- Link al registro -->
        <p class="text-center text-sm text-slate-500 mt-6">
          ¿No tenés cuenta?
          <router-link to="/register" class="text-blue-600 hover:text-blue-700 font-medium">
            Registrate gratis
          </router-link>
        </p>
      </div>
    </div>
  </div>
</template>

<script setup>
/**
 * LoginView — Lógica de la pantalla de inicio de sesión.
 * Usa el store de autenticación (Pinia) para gestionar el login.
 */
import { ref, computed } from 'vue'
import { useRoute } from 'vue-router'
import { useAuthStore } from '@/stores/auth'

const route     = useRoute()
const authStore = useAuthStore()

// Estado del formulario
const form = ref({ email: '', password: '' })
const error   = ref('')
const cargando = ref(false)

// Detectar si viene redirigido por sesión expirada
const sesionExpirada = computed(() => route.query.session_expired === 'true')

/**
 * Maneja el intento de inicio de sesión.
 * Delega al store que llama a la API y gestiona el token.
 */
async function handleLogin() {
  if (cargando.value) return
  error.value = ''
  cargando.value = true

  try {
    await authStore.login(form.value.email, form.value.password)
    // El store redirige automáticamente al dashboard si el login es exitoso
  } catch (err) {
    // El interceptor de Axios maneja los errores de red
    // Aquí manejamos el mensaje específico para el usuario
    if (err.response?.status === 401) {
      error.value = 'Email o contraseña incorrectos. Verificá tus datos.'
    } else if (!err.response) {
      error.value = 'No se pudo conectar con el servidor. Verificá tu conexión.'
    } else {
      error.value = err.response?.data?.mensaje || 'Error al iniciar sesión.'
    }
  } finally {
    cargando.value = false
  }
}

/** Rellena el formulario con credenciales de demo */
function usarDemo(email, password) {
  form.value.email    = email
  form.value.password = password
}
</script>
