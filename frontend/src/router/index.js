/**
 * src/router/index.js
 *
 * Configuración del sistema de rutas del frontend Vue 3.
 *
 * Vue Router maneja la navegación dentro de la SPA (Single Page Application):
 * en lugar de recargar la página completa al navegar, Vue Router
 * cambia dinámicamente el componente que se muestra.
 *
 * Rutas definidas:
 *  /           → Redirige a /login o /dashboard según el estado de sesión
 *  /login      → Pantalla de inicio de sesión
 *  /dashboard  → Pantalla principal (protegida — requiere sesión)
 *  /reservar   → Flujo de reserva de turno (protegida — solo clientes)
 *  /agenda     → Agenda del profesional (protegida — solo profesionales)
 *  /mis-turnos → Historial del cliente (protegida — solo clientes)
 *
 * Guardas de navegación:
 *  beforeEach: verifica si el usuario está autenticado antes de
 *  mostrar cada ruta protegida. Si no está autenticado, redirige a /login.
 *
 * @author VanguardiaShift Team
 */

import { createRouter, createWebHistory } from 'vue-router'
import { useAuthStore } from '@/stores/auth'

// Importación lazy (bajo demanda): el archivo JS del componente
// solo se descarga cuando el usuario navega a esa ruta.
// Esto mejora el tiempo de carga inicial de la aplicación.
const LoginView      = () => import('@/views/LoginView.vue')
const RegisterView   = () => import('@/views/RegisterView.vue')
const DashboardView  = () => import('@/views/DashboardView.vue')
const ReservarView   = () => import('@/views/ReservarView.vue')
const AgendaView     = () => import('@/views/AgendaView.vue')
const MisTurnosView  = () => import('@/views/MisTurnosView.vue')

// Definición de rutas
// meta.requiresAuth: true  → ruta protegida, requiere login
// meta.roles: [...]        → roles permitidos para esa ruta
const routes = [
  {
    path: '/',
    redirect: () => {
      // Redirigir según el estado de autenticación
      const authStore = useAuthStore()
      return authStore.token ? '/dashboard' : '/login'
    }
  },
  {
    path: '/login',
    name: 'login',
    component: LoginView,
    meta: { requiresAuth: false, title: 'Iniciar sesión' }
  },
  {
    path: '/register',
    name: 'register',
    component: RegisterView,
    meta: { requiresAuth: false, title: 'Registrarse' }
  },
  {
    path: '/dashboard',
    name: 'dashboard',
    component: DashboardView,
    meta: { requiresAuth: true, title: 'Panel Principal' }
  },
  {
    path: '/reservar',
    name: 'reservar',
    component: ReservarView,
    meta: { requiresAuth: true, roles: ['cliente'], title: 'Reservar Turno' }
  },
  {
    path: '/mis-turnos',
    name: 'mis-turnos',
    component: MisTurnosView,
    meta: { requiresAuth: true, roles: ['cliente'], title: 'Mis Turnos' }
  },
  {
    path: '/agenda',
    name: 'agenda',
    component: AgendaView,
    meta: { requiresAuth: true, roles: ['profesional'], title: 'Mi Agenda' }
  },
  {
    // Ruta comodín: cualquier URL no definida redirige a /
    path: '/:pathMatch(.*)*',
    redirect: '/'
  }
]

// Crear la instancia del router
const router = createRouter({
  // createWebHistory usa la API de historial del navegador
  // (URLs limpias como /login en lugar de /#/login)
  history: createWebHistory(import.meta.env.BASE_URL),
  routes,
  // Volver al principio de la página en cada navegación
  scrollBehavior: () => ({ top: 0 })
})

// ── GUARDA DE NAVEGACIÓN GLOBAL ──────────────────────────────────────────────
// Se ejecuta ANTES de cada cambio de ruta
router.beforeEach((to, from, next) => {
  // Actualizar el título de la pestaña del navegador
  document.title = to.meta.title
    ? `${to.meta.title} — VanguardiaShift`
    : 'VanguardiaShift'

  const authStore = useAuthStore()

  // Si la ruta requiere autenticación y el usuario no está logueado
  if (to.meta.requiresAuth && !authStore.token) {
    // Guardar la URL a la que intentaba ir para redirigir después del login
    next({ name: 'login', query: { redirect: to.fullPath } })
    return
  }

  // Si la ruta tiene restricción de rol
  if (to.meta.roles && authStore.usuario) {
    const rolUsuario = authStore.usuario.rol
    if (!to.meta.roles.includes(rolUsuario)) {
      // El usuario no tiene el rol necesario: ir al dashboard
      next({ name: 'dashboard' })
      return
    }
  }

  // Si el usuario ya está logueado e intenta ir al login
  if (to.name === 'login' && authStore.token) {
    next({ name: 'dashboard' })
    return
  }

  // Todo bien: continuar a la ruta solicitada
  next()
})

export default router
