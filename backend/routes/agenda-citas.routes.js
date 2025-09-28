import { Router } from "express";
import pool from "../config/db.js"; // MySQL connection pool

const router = Router();

// Helper function to generate time slots
const generateTimeSlots = (start, end, interval) => {
  const slots = [];
  let current = new Date(start);
  const endDate = new Date(end);

  while (current <= endDate) {
    slots.push(new Date(current));
    current.setMinutes(current.getMinutes() + interval);
  }
  return slots;
};

// 1. Doctor creates availability
router.post("/doctors/:id_medico/availability", async (req, res) => {
  const { id_medico } = req.params;
  const { fecha, hora_inicio, hora_fin, intervalo } = req.body;

  // Validar campos requeridos
  const missingFields = [];
  if (!fecha) missingFields.push("fecha");
  if (!hora_inicio) missingFields.push("hora_inicio");
  if (!hora_fin) missingFields.push("hora_fin");
  if (!intervalo) missingFields.push("intervalo");
  if (missingFields.length > 0) {
    return res.status(400).json({ message: `Faltan los siguientes campos: ${missingFields.join(", ")}` });
  }

  // Validar formato de fecha y hora
  const fechaRegex = /^\d{4}-\d{2}-\d{2}$/;
  const horaRegex = /^([0-1]\d|2[0-3]):[0-5]\d$/;
  if (!fechaRegex.test(fecha) || !horaRegex.test(hora_inicio) || !horaRegex.test(hora_fin)) {
    return res.status(400).json({ message: "Formato de fecha (YYYY-MM-DD) o hora (HH:MM) inválido" });
  }

  const startDateTime = new Date(`${fecha}T${hora_inicio}:00-05:00`);
  const endDateTime = new Date(`${fecha}T${hora_fin}:00-05:00`);
  const interval = parseInt(intervalo);

  if (isNaN(startDateTime) || isNaN(endDateTime) || interval <= 0) {
    return res.status(400).json({ message: "Fecha o intervalo inválido" });
  }

  if (startDateTime >= endDateTime) {
    return res.status(400).json({ message: "hora_inicio debe ser anterior a hora_fin" });
  }

  if (startDateTime < new Date()) {
    return res.status(400).json({ message: "La fecha debe ser futura" });
  }

  try {
    // Verificar existencia del médico
    const [medico] = await pool.query("SELECT id_medico FROM medicos WHERE id_medico = ?", [id_medico]);
    if (medico.length === 0) {
      return res.status(404).json({ message: "Médico no encontrado" });
    }

    // Verificar duplicados
    const [existingSlots] = await pool.query(
      `SELECT fecha_hora 
       FROM v2_disponibilidad_medico 
       WHERE id_medico = ? AND fecha_hora BETWEEN ? AND ?`,
      [id_medico, startDateTime, endDateTime]
    );
    if (existingSlots.length > 0) {
      return res.status(400).json({ message: "Ya existe disponibilidad en el rango especificado" });
    }

    const timeSlots = generateTimeSlots(startDateTime, endDateTime, interval);
    const values = timeSlots.map(slot => [id_medico, slot, "Disponible"]);
    
    await pool.query(
      "INSERT INTO v2_disponibilidad_medico (id_medico, fecha_hora, estado) VALUES ?",
      [values]
    );
    res.status(201).json({ message: "Disponibilidad creada exitosamente" });
  } catch (err) {
    console.error("Error creando disponibilidad:", err);
    res.status(500).json({ message: "Error del servidor" });
  }
});
// 2. User views available hours
router.get("/doctors/:id_medico/availability", async (req, res) => {
  const { id_medico } = req.params;
  const { fecha } = req.query;

  if (!fecha) {
    return res.status(400).json({ message: "Date is required" });
  }

  try {
    const [results] = await pool.query(
      `SELECT fecha_hora 
       FROM v2_disponibilidad_medico 
       WHERE id_medico = ? AND DATE(fecha_hora) = ? AND estado = 'Disponible'
       ORDER BY fecha_hora`,
      [id_medico, fecha]
    );
    res.status(200).json(results);
  } catch (err) {
    console.error("Error fetching availability:", err);
    res.status(500).json({ message: "Server error" });
  }
});

// 3. User books an appointment
router.post("/appointments", async (req, res) => {
  const { id_usuario, id_medico, fecha_hora, motivo } = req.body;

  if (!id_usuario || !id_medico || !fecha_hora || !motivo) {
    return res.status(400).json({ message: "Missing required fields" });
  }

  try {
    // Check availability
    const [availability] = await pool.query(
      `SELECT id_disponibilidad 
       FROM v2_disponibilidad_medico 
       WHERE id_medico = ? AND fecha_hora = ? AND estado = 'Disponible'`,
      [id_medico, fecha_hora]
    );

    if (availability.length === 0) {
      return res.status(400).json({ message: "Selected time is not available" });
    }

    // Start transaction
    const connection = await pool.getConnection();
    try {
      await connection.beginTransaction();

      // Create appointment
      await connection.query(
        `INSERT INTO v2_citas (id_usuario, id_medico, fecha_cita, estado, motivo)
         VALUES (?, ?, ?, 'Pendiente', ?)`,
        [id_usuario, id_medico, fecha_hora, motivo]
      );

      // Update availability to 'No disponible'
      await connection.query(
        `UPDATE v2_disponibilidad_medico 
         SET estado = 'No disponible' 
         WHERE id_medico = ? AND fecha_hora = ?`,
        [id_medico, fecha_hora]
      );

      await connection.commit();
      res.status(201).json({ message: "Appointment booked successfully" });
    } catch (err) {
      await connection.rollback();
      throw err;
    } finally {
      connection.release();
    }
  } catch (err) {
    console.error("Error booking appointment:", err);
    res.status(500).json({ message: "Server error" });
  }
});

// 4. Doctor creates personal activity
router.post("/doctors/:id_medico/activities", async (req, res) => {
  const { id_medico } = req.params;
  const { titulo, descripcion, fecha_inicio, fecha_fin, tipo } = req.body;

  if (!titulo || !fecha_inicio || !fecha_fin || !tipo) {
    return res.status(400).json({ message: "Missing required fields" });
  }

  try {
    // Check if all slots in range are available
    const [existingAvailability] = await pool.query(
      `SELECT fecha_hora 
       FROM v2_disponibilidad_medico 
       WHERE id_medico = ? AND fecha_hora BETWEEN ? AND ? AND estado = 'No disponible'`,
      [id_medico, fecha_inicio, fecha_fin]
    );

    if (existingAvailability.length > 0) {
      return res.status(400).json({ message: "Some hours in the range are not available" });
    }

    const connection = await pool.getConnection();
    try {
      await connection.beginTransaction();

      // Check if availability exists for the range
      const [availability] = await connection.query(
        `SELECT fecha_hora 
         FROM v2_disponibilidad_medico 
         WHERE id_medico = ? AND fecha_hora BETWEEN ? AND ?`,
        [id_medico, fecha_inicio, fecha_fin]
      );

      if (availability.length === 0) {
        // Generate slots if no availability exists
        const timeSlots = generateTimeSlots(new Date(fecha_inicio), new Date(fecha_fin), 30);
        const values = timeSlots.map(slot => [id_medico, slot, "No disponible"]);
        await connection.query(
          `INSERT INTO v2_disponibilidad_medico (id_medico, fecha_hora, estado) VALUES ?`,
          [values]
        );
      } else {
        // Update existing slots to 'No disponible'
        await connection.query(
          `UPDATE v2_disponibilidad_medico 
           SET estado = 'No disponible' 
           WHERE id_medico = ? AND fecha_hora BETWEEN ? AND ?`,
          [id_medico, fecha_inicio, fecha_fin]
        );
      }

      // Create activity
      await connection.query(
        `INSERT INTO v2_actividades_personales_medico 
         (id_medico, titulo, descripcion, fecha_inicio, fecha_fin, tipo, estado)
         VALUES (?, ?, ?, ?, ?, ?, 'Programada')`,
        [id_medico, titulo, descripcion, fecha_inicio, fecha_fin, tipo]
      );

      await connection.commit();
      res.status(201).json({ message: "Activity created successfully" });
    } catch (err) {
      await connection.rollback();
      throw err;
    } finally {
      connection.release();
    }
  } catch (err) {
    console.error("Error creating activity:", err);
    res.status(500).json({ message: "Server error" });
  }
});

// 5. User cancels appointment
router.patch("/appointments/:id_cita/cancel", async (req, res) => {
  const { id_cita } = req.params;

  try {
    const [appointment] = await pool.query(
      `SELECT id_medico, fecha_cita, estado 
       FROM v2_citas 
       WHERE id_cita = ? AND estado = 'Pendiente'`,
      [id_cita]
    );

    if (appointment.length === 0) {
      return res.status(400).json({ message: "Appointment not found or not cancellable" });
    }

    const connection = await pool.getConnection();
    try {
      await connection.beginTransaction();

      // Update appointment to 'Cancelada'
      await connection.query(
        `UPDATE v2_citas SET estado = 'Cancelada' WHERE id_cita = ?`,
        [id_cita]
      );

      // Set availability back to 'Disponible'
      await connection.query(
        `UPDATE v2_disponibilidad_medico 
         SET estado = 'Disponible' 
         WHERE id_medico = ? AND fecha_hora = ?`,
        [appointment[0].id_medico, appointment[0].fecha_cita]
      );

      await connection.commit();
      res.status(200).json({ message: "Appointment cancelled successfully" });
    } catch (err) {
      await connection.rollback();
      throw err;
    } finally {
      connection.release();
    }
  } catch (err) {
    console.error("Error cancelling appointment:", err);
    res.status(500).json({ message: "Server error" });
  }
});

// 6. Doctor confirms or completes appointment
router.patch("/appointments/:id_cita", async (req, res) => {
  const { id_cita } = req.params;
  const { estado } = req.body;

  if (!["Confirmada", "Completada"].includes(estado)) {
    return res.status(400).json({ message: "Invalid status" });
  }

  try {
    const [result] = await pool.query(
      `UPDATE v2_citas SET estado = ? WHERE id_cita = ?`,
      [estado, id_cita]
    );

    if (result.affectedRows === 0) {
      return res.status(404).json({ message: "Appointment not found" });
    }

    res.status(200).json({ message: `Appointment ${estado.toLowerCase()} successfully` });
  } catch (err) {
    console.error("Error updating appointment:", err);
    res.status(500).json({ message: "Server error" });
  }
});

// 7. Doctor cancels or completes activity
router.patch("/doctors/:id_medico/activities/:id_actividad", async (req, res) => {
  const { id_actividad, id_medico } = req.params;
  const { estado } = req.body;

  if (!["Cancelada", "Completada"].includes(estado)) {
    return res.status(400).json({ message: "Invalid status" });
  }

  try {
    const connection = await pool.getConnection();
    try {
      await connection.beginTransaction();

      // Update activity status
      const [result] = await connection.query(
        `UPDATE v2_actividades_personales_medico 
         SET estado = ? 
         WHERE id_actividad = ? AND id_medico = ?`,
        [estado, id_actividad, id_medico]
      );

      if (result.affectedRows === 0) {
        return res.status(404).json({ message: "Activity not found" });
      }

      // If cancelling, set availability back to 'Disponible'
      if (estado === "Cancelada") {
        const [activity] = await connection.query(
          `SELECT fecha_inicio, fecha_fin 
           FROM v2_actividades_personales_medico 
           WHERE id_actividad = ?`,
          [id_actividad]
        );

        await connection.query(
          `UPDATE v2_disponibilidad_medico 
           SET estado = 'Disponible' 
           WHERE id_medico = ? AND fecha_hora BETWEEN ? AND ?`,
          [id_medico, activity[0].fecha_inicio, activity[0].fecha_fin]
        );
      }

      await connection.commit();
      res.status(200).json({ message: `Activity ${estado.toLowerCase()} successfully` });
    } catch (err) {
      await connection.rollback();
      throw err;
    } finally {
      connection.release();
    }
  } catch (err) {
    console.error("Error updating activity:", err);
    res.status(500).json({ message: "Server error" });
  }
});

// Get all appointments for a user or doctor
router.get("/appointments", async (req, res) => {
  const { id_usuario, id_medico } = req.query;

  if (!id_usuario && !id_medico) {
    return res.status(400).json({ message: "User ID or Doctor ID is required" });
  }

  try {
    let query = `SELECT id_cita, id_usuario, id_medico, fecha_cita, estado, motivo, respuesta 
                 FROM v2_citas WHERE `;
    const params = [];

    if (id_usuario) {
      query += `id_usuario = ?`;
      params.push(id_usuario);
    } else {
      query += `id_medico = ?`;
      params.push(id_medico);
    }

    const [results] = await pool.query(query, params);
    res.status(200).json(results);
  } catch (err) {
    console.error("Error fetching appointments:", err);
    res.status(500).json({ message: "Server error" });
  }
});

// Get all activities for a doctor
router.get("/doctors/:id_medico/activities", async (req, res) => {
  const { id_medico } = req.params;

  try {
    const [results] = await pool.query(
      `SELECT id_actividad, titulo, descripcion, fecha_inicio, fecha_fin, tipo, estado 
       FROM v2_actividades_personales_medico 
       WHERE id_medico = ? 
       ORDER BY fecha_inicio`,
      [id_medico]
    );
    res.status(200).json(results);
  } catch (err) {
    console.error("Error fetching activities:", err);
    res.status(500).json({ message: "Server error" });
  }
});

export default router;

/**
 * @swagger
 * tags:
 *   name: agenda-citas
 *   description: Endpoints para gestionar citas, disponibilidad y actividades personales de médicos
 */
/**
 * @swagger
 * /api/agenda-citas/doctors/{id_medico}/availability:
 *   post:
 *     summary: Crear disponibilidad para un médico
 *     tags: [agenda-citas]
 *     description: Crea bloques de disponibilidad para un médico en un rango de fechas y horas con un intervalo específico.
 *     parameters:
 *       - in: path
 *         name: id_medico
 *         required: true
 *         schema:
 *           type: integer
 *         description: ID del médico
 *       - in: body
 *         name: body
 *         required: true
 *         schema:
 *           type: object
 *           required:
 *             - fecha
 *             - hora_inicio
 *             - hora_fin
 *             - intervalo
 *           properties:
 *             fecha:
 *               type: string
 *               format: date
 *               example: 2025-05-23
 *               description: Fecha en formato YYYY-MM-DD
 *             hora_inicio:
 *               type: string
 *               pattern: ^([0-1]\d|2[0-3]):[0-5]\d$
 *               example: 08:00
 *               description: Hora de inicio en formato HH:MM (24 horas)
 *             hora_fin:
 *               type: string
 *               pattern: ^([0-1]\d|2[0-3]):[0-5]\d$
 *               example: 12:00
 *               description: Hora de fin en formato HH:MM (24 horas)
 *             intervalo:
 *               type: integer
 *               minimum: 1
 *               example: 30
 *               description: Intervalo en minutos para los bloques de tiempo
 *     responses:
 *       201:
 *         description: Disponibilidad creada exitosamente
 *         content:
 *           application/json:
 *             schema:
 *               type: object
 *               properties:
 *                 message:
 *                   type: string
 *                   example: Disponibilidad creada exitosamente
 *       400:
 *         description: Campos requeridos faltantes o inválidos
 *         content:
 *           application/json:
 *             schema:
 *               type: object
 *               properties:
 *                 message:
 *                   type: string
 *                   example: "Faltan los siguientes campos: fecha, hora_inicio"
 *       404:
 *         description: Médico no encontrado
 *         content:
 *           application/json:
 *             schema:
 *               type: object
 *               properties:
 *                 message:
 *                   type: string
 *                   example: Médico no encontrado
 *       500:
 *         description: Error del servidor
 *         content:
 *           application/json:
 *             schema:
 *               type: object
 *               properties:
 *                 message:
 *                   type: string
 *                   example: Error del servidor
 */

/**
 * @swagger
 * /api/agenda-citas/doctors/{id_medico}/availability:
 *   get:
 *     summary: Obtener horas disponibles de un médico
 *     tags: [agenda-citas]
 *     description: Retorna las horas disponibles de un médico para una fecha específica.
 *     parameters:
 *       - in: path
 *         name: id_medico
 *         required: true
 *         schema:
 *           type: integer
 *         description: ID del médico
 *       - in: query
 *         name: fecha
 *         required: true
 *         schema:
 *           type: string
 *         description: Fecha en formato YYYY-MM-DD
 *     responses:
 *       200:
 *         description: Lista de horas disponibles
 *         content:
 *           application/json:
 *             schema:
 *               type: array
 *               items:
 *                 type: object
 *                 properties:
 *                   fecha_hora:
 *                     type: string
 *                     example: 2025-05-23T08:00:00
 *       400:
 *         description: Fecha requerida
 *       500:
 *         description: Error del servidor
 */

/**
 * @swagger
 * /api/agenda-citas/appointments:
 *   post:
 *     summary: Agendar una cita
 *     tags: [agenda-citas]
 *     description: Permite a un usuario agendar una cita con un médico en una hora disponible.
 *     requestBody:
 *       required: true
 *       content:
 *         application/json:
 *           schema:
 *             type: object
 *             properties:
 *               id_usuario:
 *                 type: integer
 *                 example: 1
 *               id_medico:
 *                 type: integer
 *                 example: 1
 *               fecha_hora:
 *                 type: string
 *                 example: 2025-05-23T08:00:00
 *               motivo:
 *                 type: string
 *                 example: Consulta general
 *     responses:
 *       201:
 *         description: Cita agendada exitosamente
 *       400:
 *         description: Campos faltantes o hora no disponible
 *       500:
 *         description: Error del servidor
 */

/**
 * @swagger
 * /api/agenda-citas/doctors/{id_medico}/activities:
 *   post:
 *     summary: Crear actividad personal para un médico
 *     tags: [agenda-citas]
 *     description: Crea una actividad personal para un médico, marcando las horas en el rango como no disponibles.
 *     parameters:
 *       - in: path
 *         name: id_medico
 *         required: true
 *         schema:
 *           type: integer
 *         description: ID del médico
 *       - in: body
 *         name: body
 *         required: true
 *         schema:
 *           type: object
 *           properties:
 *             titulo:
 *               type: string
 *               example: Reunión personal
 *             descripcion:
 *               type: string
 *               example: Reunión con equipo
 *             fecha_inicio:
 *               type: string
 *               example: 2025-05-23T09:00:00
 *             fecha_fin:
 *               type: string
 *               example: 2025-05-23T10:00:00
 *             tipo:
 *               type: string
 *               enum: [Reunión, Evento, Descanso, Otro]
 *               example: Reunión
 *     responses:
 *       201:
 *         description: Actividad creada exitosamente
 *       400:
 *         description: Campos faltantes o horas no disponibles
 *       500:
 *         description: Error del servidor
 */

/**
 * @swagger
 * /api/agenda-citas/appointments/{id_cita}/cancel:
 *   patch:
 *     summary: Cancelar una cita
 *     tags: [agenda-citas]
 *     description: Cancela una cita pendiente y restaura la disponibilidad de la hora.
 *     parameters:
 *       - in: path
 *         name: id_cita
 *         required: true
 *         schema:
 *           type: integer
 *         description: ID de la cita
 *     responses:
 *       200:
 *         description: Cita cancelada exitosamente
 *       400:
 *         description: Cita no encontrada o no cancelable
 *       500:
 *         description: Error del servidor
 */

/**
 * @swagger
 * /api/agenda-citas/appointments/{id_cita}:
 *   patch:
 *     summary: Confirmar o completar una cita
 *     tags: [agenda-citas]
 *     description: Actualiza el estado de una cita a Confirmada o Completada.
 *     parameters:
 *       - in: path
 *         name: id_cita
 *         required: true
 *         schema:
 *           type: integer
 *         description: ID de la cita
 *       - in: body
 *         name: body
 *         required: true
 *         schema:
 *           type: object
 *           properties:
 *             estado:
 *               type: string
 *               enum: [Confirmada, Completada]
 *               example: Confirmada
 *     responses:
 *       200:
 *         description: Cita actualizada exitosamente
 *       400:
 *         description: Estado inválido
 *       404:
 *         description: Cita no encontrada
 *       500:
 *         description: Error del servidor
 */

/**
 * @swagger
 * /api/agenda-citas/doctors/{id_medico}/activities/{id_actividad}:
 *   patch:
 *     summary: Cancelar o completar una actividad personal
 *     tags: [agenda-citas]
 *     description: Actualiza el estado de una actividad personal a Cancelada o Completada, restaurando disponibilidad si se cancela.
 *     parameters:
 *       - in: path
 *         name: id_medico
 *         required: true
 *         schema:
 *           type: integer
 *         description: ID del médico
 *       - in: path
 *         name: id_actividad
 *         required: true
 *         schema:
 *           type: integer
 *         description: ID de la actividad
 *       - in: body
 *         name: body
 *         required: true
 *         schema:
 *           type: object
 *           properties:
 *             estado:
 *               type: string
 *               enum: [Cancelada, Completada]
 *               example: Cancelada
 *     responses:
 *       200:
 *         description: Actividad actualizada exitosamente
 *       400:
 *         description: Estado inválido
 *       404:
 *         description: Actividad no encontrada
 *       500:
 *         description: Error del servidor
 */

/**
 * @swagger
 * /api/agenda-citas/appointments:
 *   get:
 *     summary: Obtener citas de un usuario o médico
 *     tags: [agenda-citas]
 *     description: Retorna todas las citas asociadas a un usuario o médico.
 *     parameters:
 *       - in: query
 *         name: id_usuario
 *         schema:
 *           type: integer
 *         description: ID del usuario (opcional si se proporciona id_medico)
 *       - in: query
 *         name: id_medico
 *         schema:
 *           type: integer
 *         description: ID del médico (opcional si se proporciona id_usuario)
 *     responses:
 *       200:
 *         description: Lista de citas obtenida exitosamente
 *         content:
 *           application/json:
 *             schema:
 *               type: array
 *               items:
 *                 type: object
 *                 properties:
 *                   id_cita:
 *                     type: integer
 *                   id_usuario:
 *                     type: integer
 *                   id_medico:
 *                     type: integer
 *                   fecha_cita:
 *                     type: string
 *                   estado:
 *                     type: string
 *                   motivo:
 *                     type: string
 *                   respuesta:
 *                     type: string
 *       400:
 *         description: ID de usuario o médico requerido
 *       500:
 *         description: Error del servidor
 */

/**
 * @swagger
 * /api/agenda-citas/doctors/{id_medico}/activities:
 *   get:
 *     summary: Obtener actividades personales de un médico
 *     tags: [agenda-citas]
 *     description: Retorna todas las actividades personales de un médico.
 *     parameters:
 *       - in: path
 *         name: id_medico
 *         required: true
 *         schema:
 *           type: integer
 *         description: ID del médico
 *     responses:
 *       200:
 *         description: Lista de actividades obtenida exitosamente
 *         content:
 *           application/json:
 *             schema:
 *               type: array
 *               items:
 *                 type: object
 *                 properties:
 *                   id_actividad:
 *                     type: integer
 *                   titulo:
 *                     type: string
 *                   descripcion:
 *                     type: string
 *                   fecha_inicio:
 *                     type: string
 *                   fecha_fin:
 *                     type: string
 *                   tipo:
 *                     type: string
 *                   estado:
 *                     type: string
 *       500:
 *         description: Error del servidor
 */