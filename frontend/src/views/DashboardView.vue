<template>
  <!--
    DashboardView.vue
    Panel principal post-login. Muestra opciones según el rol del usuario.
  -->
  <div class="min-h-screen bg-slate-50">

    <!-- Navbar -->
    <nav class="bg-white border-b border-slate-200 px-6 py-4 flex items-center justify-between shadow-sm">
      <div class="flex items-center gap-3">
        <div class="w-8 h-8 bg-blue-600 rounded-lg flex items-center justify-center">
          <span class="text-white text-xs font-bold">VS</span>
        </div>
        <span class="font-semibold text-slate-800">VanguardiaShift</span>
      </div>
      <div class="flex items-center gap-4">
        <span class="text-sm text-slate-500">{{ authStore.usuario?.name }}</span>
        <span class="text-xs px-2 py-1 rounded-full font-medium"
          :class="authStore.esProfesional ? 'bg-teal-100 text-teal-700' : 'bg-blue-100 text-blue-700'">
          {{ authStore.usuario?.rol }}
        </span>
        <button @click="authStore.logout()"
          class="text-sm text-slate-400 hover:text-slate-600 transition-colors">
          Cerrar sesión
        </button>
      </div>
    </nav>

    <!-- Contenido principal -->
    <main class="max-w-4xl mx-auto p-6">
      <div class="mb-8">
        <h1 class="text-2xl font-bold text-slate-800">
          Bienvenido, {{ authStore.usuario?.name?.split(' ')[0] }} 👋
        </h1>
        <p class="text-slate-500 mt-1">¿Qué querés hacer hoy?</p>
      </div>

      <!-- Tarjetas para cliente -->
      <div v-if="authStore.esCliente" class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <router-link to="/reservar"
          class="bg-white rounded-2xl p-6 border border-slate-200 hover:border-blue-300 hover:shadow-md transition-all duration-200 group">
          <div class="w-12 h-12 bg-blue-100 rounded-xl flex items-center justify-center mb-4 group-hover:bg-blue-200 transition-colors">
            <span class="text-2xl">📅</span>
          </div>
          <h2 class="font-semibold text-slate-800 mb-1">Reservar turno</h2>
          <p class="text-sm text-slate-500">Elegí profesional, fecha y horario disponible</p>
        </router-link>

        <router-link to="/mis-turnos"
          class="bg-white rounded-2xl p-6 border border-slate-200 hover:border-teal-300 hover:shadow-md transition-all duration-200 group">
          <div class="w-12 h-12 bg-teal-100 rounded-xl flex items-center justify-center mb-4 group-hover:bg-teal-200 transition-colors">
            <span class="text-2xl">📋</span>
          </div>
          <h2 class="font-semibold text-slate-800 mb-1">Mis turnos</h2>
          <p class="text-sm text-slate-500">Historial y próximas consultas</p>
        </router-link>
      </div>

      <!-- Tarjetas para profesional -->
      <div v-if="authStore.esProfesional" class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <router-link to="/agenda"
          class="bg-white rounded-2xl p-6 border border-slate-200 hover:border-teal-300 hover:shadow-md transition-all duration-200 group">
          <div class="w-12 h-12 bg-teal-100 rounded-xl flex items-center justify-center mb-4 group-hover:bg-teal-200 transition-colors">
            <span class="text-2xl">🩺</span>
          </div>
          <h2 class="font-semibold text-slate-800 mb-1">Mi agenda</h2>
          <p class="text-sm text-slate-500">Turnos de hoy y próximos días</p>
        </router-link>
      </div>

      <!-- Métricas del sistema (demo) -->
      <div class="mt-8 grid grid-cols-3 gap-4">
        <div class="bg-white rounded-xl p-4 border border-slate-200 text-center">
          <div class="text-2xl font-bold text-blue-600">{{ stats.profesionales }}</div>
          <div class="text-xs text-slate-500 mt-1">Profesionales activos</div>
        </div>
        <div class="bg-white rounded-xl p-4 border border-slate-200 text-center">
          <div class="text-2xl font-bold text-teal-600">{{ stats.turnosHoy }}</div>
          <div class="text-xs text-slate-500 mt-1">Turnos para hoy</div>
        </div>
        <div class="bg-white rounded-xl p-4 border border-slate-200 text-center">
          <div class="text-2xl font-bold text-purple-600">{{ stats.disponibles }}</div>
          <div class="text-xs text-slate-500 mt-1">Horarios disponibles</div>
        </div>
      </div>
    </main>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { useAuthStore } from '@/stores/auth'

const authStore = useAuthStore()

// Métricas estáticas de demo — en producción vendrían de la API
const stats = ref({ profesionales: 6, turnosHoy: 12, disponibles: 48 })
</script>
