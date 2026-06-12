VanguardiaShift

Sistema de gestión de turnos profesionales desarrollado como Trabajo Práctico Final para la materia Programación de Vanguardia (Prof. Esteban Calcagno, 2026).


¿Qué resuelve?

Los consultorios que gestionan turnos por teléfono tienen cuatro problemas concretos: la línea colapsa en horario pico, los registros en papel generan dobles reservas, el profesional no conoce su agenda hasta llegar, y no existe historial digital de cancelaciones.

VanguardiaShift digitaliza el proceso completo. El paciente reserva desde el navegador en cualquier momento y el sistema garantiza que ningún horario pueda reservarse dos veces, incluso si dos personas lo intentan al mismo tiempo.


Stack tecnológico

CapaTecnologíaMotivoFrontendVue.js 3 (SPA)Liviano, Composition API, documentación en españolBackendLaravel 11 (API REST)Autenticación, validaciones y tests incluidos de fábricaAutenticaciónLaravel SanctumLogout efectivo — el token se revoca de inmediatoTestingPHPUnit 116 tests de integración que cubren los escenarios críticosCI/CDGitHub ActionsPipeline automático en cada push a mainBase de datos (tests)SQLite in-memoryTests corren en ~2 segundos sin servidor externoBase de datos (prod)MySQL (Railway)Restricción UNIQUE como segunda defensa contra duplicados


Arquitectura

El sistema tiene dos partes completamente independientes que se comunican por HTTP enviando JSON:

[ Vue.js 3 SPA ]  ←→  HTTP/JSON  ←→  [ Laravel 11 API ]  ←→  [ MySQL ]
     Vercel                                  Railway

Esta separación permite escalar cada capa de forma independiente y reutilizar la misma API para una futura app móvil sin modificar el backend.


Decisión técnica principal — prevención de dobles reservas

Si dos pacientes hacen clic en "Reservar" exactamente al mismo instante para el mismo horario, sin protección ambos verían el horario libre y los dos quedarían con el turno asignado.

La solución usa dos capas independientes:


lockForUpdate() dentro de DB::transaction() — bloquea el registro en la base de datos mientras se procesa la operación. El segundo usuario espera y cuando el bloqueo se libera ya lee que el horario está ocupado.
Restricción UNIQUE en la tabla turnos sobre (profesional_id, fecha_hora) — impide físicamente guardar dos turnos para el mismo médico en el mismo horario, como segunda línea de defensa.



Tests automatizados

Los 6 tests son de integración (Feature Tests): simulan peticiones HTTP reales y verifican toda la cadena desde la ruta hasta la base de datos.

#Qué verifica1Un cliente autenticado puede reservar un turno y queda guardado en DB2No se puede reservar un horario ya ocupado — HTTP 4093Sin token de autenticación el acceso se rechaza — HTTP 4014Datos inválidos o incompletos generan errores de validación — HTTP 4225Un médico puede marcar su propio turno como completado6Un médico no puede modificar el turno de otro profesional — HTTP 403

Para correr los tests localmente:

bashcd backend
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate
php artisan test


Pipeline CI/CD

Cada push a main dispara automáticamente el workflow de GitHub Actions:

Checkout → PHP 8.2 → composer install → configurar .env → migrate → php artisan test

Si algún test falla, el pipeline bloquea la integración hasta que se corrija. El código en main siempre está verificado.


Estructura del repositorio

vanguardiashiftGrupo10/
├── backend/                    # Laravel 11 — API REST
│   ├── app/
│   │   ├── Http/
│   │   │   ├── Controllers/    # TurnoController, AuthController, ProfesionalController
│   │   │   ├── Middleware/     # CheckRole — verifica rol del usuario
│   │   │   └── Requests/       # StoreTurnoRequest — validaciones de entrada
│   │   └── Models/             # User, Turno — con relaciones y SoftDelete
│   ├── database/migrations/    # Estructura de tablas
│   ├── routes/api.php          # Definición de endpoints
│   └── tests/Feature/          # 6 tests de integración
├── frontend/                   # Vue.js 3 — SPA
│   └── src/
│       ├── components/         # BookingCalendar.vue — flujo de reserva en 3 pasos
│       ├── views/              # Login, Dashboard, Agenda
│       ├── stores/             # Pinia — manejo de sesión
│       ├── services/           # Axios con interceptor de token
│       └── router/             # Rutas con guardas de navegación
└── .github/workflows/          # Pipeline de GitHub Actions


Seguridad — 4 capas en secuencia

CapaQué haceCódigo HTTPSanctumVerifica que el token existe y es válido401 UnauthorizedCheckRoleVerifica que el rol del usuario coincide con la acción403 ForbiddenStoreTurnoRequestValida que los datos enviados estén completos y sean correctos422 UnprocessableTurnoControllerVerifica que el usuario es dueño del recurso que quiere modificar403 Forbidden


Equipo — Grupo 10

Trabajo Práctico Final · Programación de Vanguardia · Prof. Esteban Calcagno · Junio 2026
