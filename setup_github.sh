#!/bin/bash
# setup_github.sh
# 
# Configura los repositorios Git del proyecto VanguardiaShift.
# Ejecutar UNA SOLA VEZ desde la carpeta raíz del proyecto.
#
# ANTES de ejecutar:
#   1. Instalar Git en tu computadora (git-scm.com)
#   2. Crear cuenta en github.com si no tenés
#   3. Crear dos repositorios VACÍOS en GitHub:
#      - vanguardiashift-backend
#      - vanguardiashift-frontend
#   4. Editar la línea GITHUB_USER con tu nombre de usuario de GitHub
#
# CÓMO EJECUTAR (en la terminal, desde la carpeta del proyecto):
#   bash setup_github.sh
#
# Si te pide usuario y contraseña de GitHub, usá un "Personal Access Token":
#   GitHub → Settings → Developer settings → Personal access tokens → Generate new token

set -e

GITHUB_USER="TU-USUARIO-AQUI"  # ← CAMBIÁ ESTO por tu usuario de GitHub

echo "Configurando repositorio Backend..."
cd backend

git init -b main
git config user.name "Tu Nombre"          # ← Cambiá por tu nombre
git config user.email "tu@email.com"      # ← Cambiá por tu email

# Commit 1 — base del proyecto
git add .env.example composer.json phpunit.xml
git commit -m "setup inicial del proyecto laravel"

# Commit 2 — estructura de base de datos
git add database/
git commit -m "agrego migraciones y seeders con datos de prueba"

# Commit 3 — modelos
git add app/Models/
git commit -m "modelos User y Turno con relaciones y softdelete"

# Commit 4 — seguridad
git add app/Http/Middleware/
git commit -m "middleware para verificar rol del usuario"

# Commit 5 — controladores y validaciones
git add app/Http/Controllers/ app/Http/Requests/
git commit -m "controladores de turnos, auth y profesionales con validaciones"

# Commit 6 — rutas
git add routes/
git commit -m "defino las rutas publicas y protegidas de la api"

# Commit 7 — tests
git add tests/
git commit -m "agrego 6 tests de integracion para el modulo de turnos"

# Commit 8 — pipeline CI
git add ../.github/
git commit -m "configuro github actions para correr tests automaticamente"

git remote add origin "https://github.com/$GITHUB_USER/vanguardiashift-backend.git"

echo ""
echo "Backend listo. Para subir a GitHub ejecutá:"
echo "  cd backend && git push -u origin main"
echo ""

cd ../frontend

echo "Configurando repositorio Frontend..."
git init -b main
git config user.name "Tu Nombre"
git config user.email "tu@email.com"

git add package.json .env.example
git commit -m "setup inicial del proyecto vue 3"

git add src/services/ src/stores/
git commit -m "configuro axios con interceptor de token y store de autenticacion"

git add src/router/
git commit -m "agrego rutas del frontend con guardas de navegacion"

git add src/views/LoginView.vue src/views/DashboardView.vue
git commit -m "pantalla de login y dashboard con navegacion por rol"

git add src/components/BookingCalendar.vue src/views/ReservarView.vue
git commit -m "componente principal de reserva en 3 pasos"

git add src/views/AgendaView.vue
git commit -m "vista de agenda para el profesional con cambio de estado"

git remote add origin "https://github.com/$GITHUB_USER/vanguardiashift-frontend.git"

echo ""
echo "Frontend listo. Para subir a GitHub ejecutá:"
echo "  cd frontend && git push -u origin main"
echo ""
echo "=============================================="
echo "Una vez hecho el push:"
echo "  - Verificar que en GitHub aparecen los commits"
echo "  - Ir a la pestaña Actions del repo backend"
echo "  - Debería aparecer el pipeline corriendo (check verde si pasan los tests)"
echo "=============================================="
