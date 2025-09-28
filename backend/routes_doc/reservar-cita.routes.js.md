# Documentación de `reservar-cita.routes.js`

Este archivo define las rutas API para la gestión de citas médicas y disponibilidad de médicos en una aplicación Express. Utiliza una conexión a una base de datos MySQL (mediante *pool*) para realizar operaciones CRUD relacionadas con médicos, citas, disponibilidad y calificaciones. A continuación, se describe cada *endpoint* y las acciones que permite realizar.

## Endpoints y Acciones

### 1. GET /doctor/:id

* **Descripción:** Obtiene los datos de un médico específico.
* **Acción:** Recupera información como nombre, apellido, foto, dirección del consultorio y especialidad de un médico identificado por su `id_medico`.
* **Uso:** Consultar detalles del médico para mostrar en la interfaz (por ejemplo, perfil del médico).
* **Respuesta:**
    * **Éxito:** Devuelve un objeto con los datos del médico.
    * **Error:** `404` si el médico no se encuentra, `500` si hay un error en el servidor.

### 2. GET /doctor/:id/dates

* **Descripción:** Obtiene las fechas disponibles y no disponibles de un médico.
* **Acción:** Lista las fechas (a partir de hoy) en las que el médico tiene horarios disponibles (`estado = 'Disponible'`) o no disponibles (`estado = 'No disponible'`) en la tabla `agenda_medico`.
* **Uso:** Mostrar un calendario con fechas disponibles para que los usuarios puedan seleccionar una cita.
* **Respuesta:**
    * **Éxito:** Devuelve un objeto con dos *arrays*: `available` (fechas disponibles) y `unavailable` (fechas no disponibles).
    * **Error:** `500` si hay un error en el servidor.

### 3. GET /doctor/:id/schedules

* **Descripción:** Obtiene los horarios disponibles de un médico para una fecha específica.
* **Acción:** Recupera los horarios (`fecha_hora`) con `estado = 'Disponible'` en `agenda_medico` para un médico y una fecha dada (enviada como parámetro `fecha`).
* **Uso:** Mostrar los horarios disponibles en un día seleccionado para que el usuario elija uno.
* **Respuesta:**
    * **Éxito:** Devuelve una lista de objetos con `id_agenda` y `fecha_hora`.
    * **Error:** `400` si no se proporciona la fecha, `500` si hay un error en el servidor.

### 4. POST /book

* **Descripción:** Reserva una cita para un usuario con un médico.
* **Acción:**
    * Si el `id_agenda` es nuevo (empieza con `"nuevo_"`), crea un horario en `agenda_medico` con `estado = 'No disponible'`.
    * Si el `id_agenda` ya existe, verifica que esté disponible.
    * Inserta una cita en la tabla `citas` con `estado = 'Pendiente'`.
    * Actualiza el horario en `agenda_medico` a `estado = 'No disponible'`.
* **Uso:** Permitir a un usuario reservar una cita en un horario específico.
* **Respuesta:**
    * **Éxito:** `201` con un mensaje y el `id_cita` de la cita creada.
    * **Error:** `400` si el horario no está disponible, `500` si hay un error en el servidor.

### 5. POST /disponibilidad

* **Descripción:** Crea un horario disponible para un médico.
* **Acción:**
    * Inserta un nuevo registro en `agenda_medico` con `id_medico`, `fecha_hora` y `estado = 'Disponible'`.
    * Verifica que no exista un horario en la misma `fecha_hora` para el médico.
* **Uso:** Permitir al médico definir horarios disponibles en su agenda.
* **Respuesta:**
    * **Éxito:** `201` con un mensaje y el `id_agenda` del horario creado.
    * **Error:** `400` si falta `id_medico` o `fecha_hora`, o si el horario ya existe; `500` si hay un error en el servidor.

### 6. GET /user/:id/citas

* **Descripción:** Lista las citas de un usuario.
* **Acción:** Recupera todas las citas de un usuario (`id_usuario`), incluyendo detalles del médico, fecha, estado y motivo.
* **Uso:** Mostrar el historial de citas de un usuario en la interfaz.
* **Respuesta:**
    * **Éxito:** Devuelve una lista de citas ordenadas por fecha descendente.
    * **Error:** `500` si hay un error en el servidor.

### 7. GET /doctor/:id/citas

* **Descripción:** Lista las citas de un médico.
* **Acción:** Recupera todas las citas de un médico (`id_medico`), incluyendo detalles del paciente, fecha, estado y motivo.
* **Uso:** Mostrar la agenda de citas del médico en la interfaz.
* **Respuesta:**
    * **Éxito:** Devuelve una lista de citas ordenadas por fecha descendente.
    * **Error:** `500` si hay un error en el servidor.

### 8. PUT /cancelar/:id

* **Descripción:** Cancela una cita.
* **Acción:** Actualiza el estado de una cita (`id_cita`) a `'Cancelada'` en la tabla `citas`.
* **Uso:** Permitir al usuario o médico cancelar una cita programada.
* **Respuesta:**
    * **Éxito:** `200` con un mensaje de confirmación.
    * **Error:** `500` si hay un error en el servidor.

### 9. PUT /confirmar/:id

* **Descripción:** Confirma una cita (por el médico).
* **Acción:** Actualiza el estado de una cita (`id_cita`) a `'Confirmada'` en la tabla `citas`.
* **Uso:** Permitir al médico confirmar una cita pendiente.
* **Respuesta:**
    * **Éxito:** `200` con un mensaje de confirmación.
    * **Error:** `500` si hay un error en el servidor.

### 10. PUT /atendida/:id

* **Descripción:** Marca una cita como atendida (por el médico).
* **Acción:** Actualiza el estado de una cita (`id_cita`) a `'Atendida'` en la tabla `citas`.
* **Uso:** Registrar que una cita ha sido completada.
* **Respuesta:**
    * **Éxito:** `200` con un mensaje de confirmación.
    * **Error:** `500` si hay un error en el servidor.

### 11. POST /calificar

* **Descripción:** Registra una calificación y comentario para un médico tras una cita.
* **Acción:**
    * Verifica que la cita (`id_cita`) esté en estado `'Atendida'` y pertenezca al usuario.
    * Inserta un registro en `clasificacion_medicos` con la calificación y comentario.
* **Uso:** Permitir a los usuarios calificar a los médicos después de una cita.
* **Respuesta:**
    * **Éxito:** `201` con un mensaje de confirmación.
    * **Error:** `400` si la cita no está atendida o no pertenece al usuario, `500` si hay un error en el servidor.

### 12. GET /doctor/:id/calificaciones

* **Descripción:** Obtiene las calificaciones de un médico.
* **Acción:** Recupera todas las calificaciones y comentarios de un médico (`id_medico`) en `clasificacion_medicos`.
* **Uso:** Mostrar las reseñas de un médico en la interfaz.
* **Respuesta:**
    * **Éxito:** Devuelve una lista de calificaciones ordenadas por fecha descendente.
    * **Error:** `500` si hay un error en el servidor.

## Notas Generales

* **Base de datos:** Los *endpoints* interactúan con las tablas `medicos`, `especialidades`, `agenda_medico`, `citas`, y `clasificacion_medicos`.
* **Seguridad:** Se asume que la autenticación de usuarios y médicos se maneja en otro módulo. Considerar agregar *middleware* para validar permisos.
* **Errores:** Todos los *endpoints* manejan errores con mensajes claros y códigos de estado HTTP apropiados.
* **Uso en frontend:** Los *endpoints* están diseñados para integrarse con un *frontend* (por ejemplo, Angular en `agenda-citas.component.ts`), soportando operaciones como mostrar agendas, reservar citas y gestionar disponibilidad.

## Ejemplo de Flujo

* Un médico usa `POST /disponibilidad` para crear horarios disponibles.
* Un usuario consulta `GET /doctor/:id/dates` y `GET /doctor/:id/schedules` para elegir un horario.
* El usuario reserva una cita con `POST /book`.
* El médico confirma la cita con `PUT /confirmar/:id` y la marca como atendida con `PUT /atendida/:id`.
* El usuario califica al médico con `POST /calificar`.

Este archivo proporciona una API robusta para la gestión de citas médicas, con soporte para médicos y pacientes.