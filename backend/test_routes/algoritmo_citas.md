###################################################

1. Módulo de Médicos
Funcionalidades:

Crear horario de atención (disponibilidad de citas)
Ver citas:
Filtro por fecha o nombre de paciente
Ver citas pendientes
Ver citas canceladas
Ver historial de citas (citas pasadas)
Agenda médica (resumen diario o semanal de citas)

Medico
 ├── CrearHorario(fecha, horaInicio, horaFin, intervalo)
 ├── ConsultarAgenda(filtroFecha, filtroNombrePaciente)
 ├── ListarCitas(estado) // Pendiente, Cancelada, Historial
 └── VerDetallesCita(citaId)

 2. Módulo de Usuarios (Pacientes)
Funcionalidades:
Reservar cita en una fecha disponible
Consultar citas agendadas
Registrar consulta médica (cuando es atendido)
Usuario
 ├── VerDisponibilidad(medicoId, fecha)
 ├── ReservarCita(medicoId, fecha, hora)
 ├── CancelarCita(citaId)
 ├── ConsultarMisCitas()
 └── RegistrarConsulta(citaId, descripcion, motivoConsulta)



Usuarios ok

Medico ok
 ├── id
 ├── nombre
 ├── especialidad
 ├── horariosDisponibles [Horario]

Horario ok
 ├── id
 ├── medicoId
 ├── fecha
 ├── horaInicio
 ├── horaFin
 ├── intervaloMinutos

Cita ok
 ├── id
 ├── medicoId
 ├── usuarioId
 ├── fecha
 ├── hora
 ├── motivoCita
 ├── estado (pendiente / cancelada / atendida)



################## routes citas => reservar-cita.routes.js ########################

1. GET /doctor/:id
   Descripción: Obtener los datos de un médico (nombre, apellido, especialidad, foto, dirección).

2. GET /doctor/:id/dates
   Descripción: Obtener fechas disponibles y no disponibles del médico.

3. GET /doctor/:id/schedules?fecha=YYYY-MM-DD
   Descripción: Obtener horarios disponibles para una fecha específica del médico.

4. POST /book
   Descripción: Reservar una cita (nuevo horario o usando un horario existente).

5. GET /user/:id/citas
   Descripción: Listar todas las citas de un usuario.

6. GET /doctor/:id/citas
   Descripción: Listar todas las citas de un médico.

7. PUT /cancelar/:id
   Descripción: Cancelar una cita (por el paciente o sistema).

8. PUT /confirmar/:id
   Descripción: Confirmar una cita (acción del médico).

9. PUT /atendida/:id
   Descripción: Marcar una cita como atendida (acción del médico).

10. POST /calificar
    Descripción: Calificar a un médico después de que la cita haya sido atendida.

11. GET /doctor/:id/calificaciones
    Descripción: Obtener todas las calificaciones y comentarios de un médico




pendiente:
    pedir administracion general de medicos en el component.ts si es necesario con subcomponentes
    pedir administracion general de usuarios en el component.ts si es necesario con subcomponentes
    revisando el routes y el index y queda fino
    adecuar el frontend y diseño y queda fino
    ***

    en el modulo de citas mantener la funcion de recibir el id del medico para la creacion de una cita
    ademas si no se proporciona debe mostrar una opcion para elegir medico

exit

