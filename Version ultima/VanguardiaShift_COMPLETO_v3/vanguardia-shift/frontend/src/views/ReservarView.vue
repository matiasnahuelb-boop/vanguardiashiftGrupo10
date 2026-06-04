<template>
  <!--
    ReservarView.vue
    Vista de reserva de turno — envuelve el componente BookingCalendar.
    Agrega la navegación y el título de página.
  -->
  <div class="min-h-screen bg-slate-50">

    <!-- Navbar -->
    <nav class="bg-white border-b border-slate-200 px-6 py-4 flex items-center gap-4 shadow-sm">
      <router-link to="/dashboard"
        class="text-slate-400 hover:text-slate-600 transition-colors flex items-center gap-1 text-sm">
        ← Volver
      </router-link>
      <span class="text-slate-300">|</span>
      <span class="text-sm font-medium text-slate-700">Reservar turno</span>
    </nav>

    <!-- El componente principal de reserva -->
    <BookingCalendar />

  </div>
</template>

<script setup>
import BookingCalendar from '@/components/BookingCalendar.vue'
</script>
