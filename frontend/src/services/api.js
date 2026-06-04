/**
 * src/services/api.js
 *
 * Configuración centralizada de Axios para VanguardiaShift.
 *
 * Este archivo crea una instancia de Axios configurada con:
 *  - URL base del backend (desde variables de entorno)
 *  - Headers por defecto para API REST (Content-Type: application/json)
 *  - Interceptor de petición: agrega el token Bearer automáticamente
 *  - Interceptor de respuesta: manejo global de errores HTTP
 *
 * Al centralizar la configuración aquí, todos los componentes
 * importan 'api' desde este archivo y no necesitan configurar
 * Axios individualmente. Esto aplica el principio DRY (Don't Repeat Yourself).
 *
 * @author VanguardiaShift Team
 */

import axios from 'axios'

// ── CREAR INSTANCIA ───────────────────────────────────────────────────────────
export const api = axios.create({
  // La URL base se define en el archivo .env como VITE_API_URL.
  // En desarrollo: http://localhost:8000/api
  // En producción: https://tu-backend.railway.app/api
  baseURL: import.meta.env.VITE_API_URL || 'http://localhost:8000/api',

  // Tiempo máximo de espera: 15 segundos
  timeout: 15000,

  headers: {
    'Content-Type': 'application/json',
    'Accept':       'application/json',
    // X-Requested-With permite a Laravel identificar peticiones AJAX
    'X-Requested-With': 'XMLHttpRequest',
  },
})

// ── INTERCEPTOR DE PETICIÓN ───────────────────────────────────────────────────
// Se ejecuta ANTES de enviar cada petición.
// Agrega el token Bearer si existe en localStorage.
api.interceptors.request.use(
  (config) => {
    const token = localStorage.getItem('auth_token')
    if (token) {
      // Formato estándar de autenticación Bearer Token
      config.headers.Authorization = `Bearer ${token}`
    }
    return config
  },
  (error) => {
    // Error al preparar la petición (ej: timeout de configuración)
    return Promise.reject(error)
  }
)

// ── INTERCEPTOR DE RESPUESTA ──────────────────────────────────────────────────
// Se ejecuta DESPUÉS de recibir cada respuesta.
// Maneja errores comunes de forma centralizada.
api.interceptors.response.use(
  // Respuestas exitosas (2xx): devolver directamente
  (response) => response,

  // Respuestas de error (4xx, 5xx): manejar centralizadamente
  (error) => {
    const status  = error.response?.status
    const mensaje = error.response?.data?.mensaje

    if (status === 401) {
      // Token inválido o expirado: limpiar sesión y redirigir al login
      localStorage.removeItem('auth_token')
      localStorage.removeItem('auth_usuario')
      // Redirigir sin usar el router de Vue para evitar dependencias circulares
      if (window.location.pathname !== '/login') {
        window.location.href = '/login?session_expired=true'
      }
    }

    if (status === 403) {
      // Sin permisos: el usuario no tiene el rol necesario
      console.warn('Acceso denegado:', mensaje)
    }

    if (status >= 500) {
      // Error del servidor: loguear para diagnóstico
      console.error('Error del servidor:', error.response?.data)
    }

    if (!error.response) {
      // Sin respuesta: problema de red o servidor caído
      console.error('Error de red: no se pudo conectar con el servidor')
    }

    // Propagar el error para que cada componente pueda manejarlo específicamente
    return Promise.reject(error)
  }
)

export default api
