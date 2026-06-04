/**
 * src/stores/auth.js
 *
 * Store de autenticación usando Pinia (el gestor de estado oficial de Vue 3).
 *
 * ¿Qué es un store?
 * Es un lugar centralizado donde se guarda el estado global de la aplicación:
 * datos que necesitan ser accesibles desde múltiples componentes.
 * En este caso: el token del usuario y su información de perfil.
 *
 * ¿Por qué Pinia y no Vuex?
 * Pinia es el reemplazo oficial de Vuex para Vue 3. Es más simple,
 * tiene mejor soporte para TypeScript y es la recomendación actual
 * de la documentación oficial de Vue.
 *
 * Persistencia: el token se guarda en localStorage para que
 * la sesión sobreviva a recargas de la página.
 *
 * @author VanguardiaShift Team
 */

import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import { api } from '@/services/api'
import router from '@/router'

export const useAuthStore = defineStore('auth', () => {
  // ── ESTADO (ref = variable reactiva) ───────────────────────────────────────
  // Inicializamos leyendo de localStorage para restaurar sesiones previas
  const token   = ref(localStorage.getItem('auth_token') || null)
  const usuario = ref(JSON.parse(localStorage.getItem('auth_usuario') || 'null'))

  // ── GETTERS (computed = valor derivado del estado) ──────────────────────────
  /**
   * ¿Hay un usuario autenticado actualmente?
   */
  const estaAutenticado = computed(() => !!token.value)

  /**
   * ¿El usuario autenticado es profesional?
   */
  const esProfesional = computed(() => usuario.value?.rol === 'profesional')

  /**
   * ¿El usuario autenticado es cliente?
   */
  const esCliente = computed(() => usuario.value?.rol === 'cliente')

  // ── ACCIONES ────────────────────────────────────────────────────────────────

  /**
   * Inicia sesión: llama a la API y guarda el token.
   *
   * @param {string} email
   * @param {string} password
   * @throws {Error} si las credenciales son incorrectas
   */
  async function login(email, password) {
    const response = await api.post('/auth/login', { email, password })
    const { token: nuevoToken, usuario: datosUsuario } = response.data

    // Guardar en memoria reactiva (actualiza la UI automáticamente)
    token.value   = nuevoToken
    usuario.value = datosUsuario

    // Persistir en localStorage para sobrevivir recargas
    localStorage.setItem('auth_token', nuevoToken)
    localStorage.setItem('auth_usuario', JSON.stringify(datosUsuario))

    // Redirigir al dashboard después del login exitoso
    const redirectUrl = router.currentRoute.value.query.redirect || '/dashboard'
    router.push(redirectUrl)
  }

  /**
   * Registra un nuevo usuario.
   *
   * @param {Object} datos  { name, email, password, password_confirmation }
   */
  async function register(datos) {
    const response = await api.post('/auth/register', datos)
    const { token: nuevoToken, usuario: datosUsuario } = response.data

    token.value   = nuevoToken
    usuario.value = datosUsuario
    localStorage.setItem('auth_token', nuevoToken)
    localStorage.setItem('auth_usuario', JSON.stringify(datosUsuario))

    router.push('/dashboard')
  }

  /**
   * Cierra la sesión: revoca el token en el servidor y limpia el estado local.
   */
  async function logout() {
    try {
      // Intentar revocar el token en el servidor
      await api.delete('/auth/logout')
    } catch (error) {
      // Si la petición falla (token ya expirado, sin conexión),
      // igual limpiamos el estado local
      console.warn('No se pudo revocar el token en el servidor:', error.message)
    } finally {
      // Limpiar estado local siempre
      token.value   = null
      usuario.value = null
      localStorage.removeItem('auth_token')
      localStorage.removeItem('auth_usuario')
      router.push('/login')
    }
  }

  return {
    // Estado
    token,
    usuario,
    // Getters
    estaAutenticado,
    esProfesional,
    esCliente,
    // Acciones
    login,
    register,
    logout,
  }
})
