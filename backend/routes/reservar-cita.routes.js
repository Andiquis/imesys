import { Router } from "express";
import pool from "../config/db.js";

const router = Router();

// ==========================================
// Obtener datos del médico
// ==========================================
router.get("/doctor/:id", async (req, res) => {
  const { id } = req.params;
  try {
    const [results] = await pool.query(
      `
      SELECT m.id_medico, m.nombre, m.apellido, m.foto, m.direccion_consultorio, 
             e.nombre_especialidad
      FROM medicos m
      JOIN especialidades e ON m.id_especialidad = e.id_especialidad
      WHERE m.id_medico = ?
      `,
      [id]
    );
    if (results.length === 0) {
      return res.status(404).json({ message: "Doctor not found" });
    }
    res.status(200).json(results[0]);
  } catch (err) {
    console.error("Error fetching doctor:", err);
    res.status(500).json({ message: "Server error" });
  }
});

// ==========================================
// Fechas disponibles y no disponibles del doctor
// ==========================================
router.get("/doctor/:id/dates", async (req, res) => {
  const { id } = req.params;
  const today = new Date().toISOString().split("T")[0];
  try {
    const [available] = await pool.query(
      `
      SELECT DISTINCT DATE(fecha_hora) as fecha
      FROM agenda_medico 
      WHERE id_medico = ? 
      AND fecha_hora >= ?
      AND estado = 'Disponible'
      ORDER BY fecha
      `,
      [id, today]
    );

    const [unavailable] = await pool.query(
      `
      SELECT DISTINCT DATE(fecha_hora) as fecha
      FROM agenda_medico 
      WHERE id_medico = ? 
      AND fecha_hora >= ?
      AND estado = 'No disponible'
      ORDER BY fecha
      `,
      [id, today]
    );

    res.status(200).json({
      available: available.map((row) => row.fecha),
      unavailable: unavailable.map((row) => row.fecha),
    });
  } catch (err) {
    console.error("Error fetching dates:", err);
    res.status(500).json({ message: "Server error" });
  }
});

// ==========================================
// Horarios disponibles para una fecha específica
// ==========================================
router.get("/doctor/:id/schedules", async (req, res) => {
  const { id } = req.params;
  const { fecha } = req.query;

  if (!fecha) {
    return res.status(400).json({ message: "Date is required" });
  }

  try {
    // Verificar si la fecha tiene alguna entrada con hora 00:00:00
    const [blocked] = await pool.query(
      `
      SELECT COUNT(*) as count
      FROM agenda_medico 
      WHERE id_medico = ? 
      AND DATE(fecha_hora) = ?
      AND TIME(fecha_hora) = '00:00:00'
      `,
      [id, fecha]
    );

    // Si hay alguna entrada con hora 00:00:00, devolver arreglo vacío
    if (blocked[0].count > 0) {
      return res.status(200).json([]);
    }

    // Obtener horarios disponibles si la fecha no está bloqueada
    const [results] = await pool.query(
      `
      SELECT id_agenda, fecha_hora
      FROM agenda_medico 
      WHERE id_medico = ? 
      AND DATE(fecha_hora) = ?
      AND estado = 'Disponible'
      ORDER BY fecha_hora
      `,
      [id, fecha]
    );

    res.status(200).json(results);
  } catch (err) {
    console.error("Error fetching schedules:", err);
    res.status(500).json({ message: "Server error" });
  }
});

// ==========================================
// Reservar una cita
// ==========================================
router.post("/book", async (req, res) => {
  const { id_usuario, id_medico, id_agenda, motivo, fecha_hora } = req.body;

  try {
    let agendaId = id_agenda;
    if (id_agenda.startsWith("nuevo_")) {
      const [result] = await pool.query(
        `
        INSERT INTO agenda_medico (id_medico, fecha_hora, estado)
        VALUES (?, ?, 'No disponible')
        `,
        [id_medico, fecha_hora]
      );
      agendaId = result.insertId;
    } else {
      const [check] = await pool.query(
        `
        SELECT id_agenda FROM agenda_medico 
        WHERE id_agenda = ? AND estado = 'Disponible'
        `,
        [id_agenda]
      );
      if (check.length === 0) {
        return res.status(400).json({ message: "Selected schedule is no longer available" });
      }
    }

    const [insertResult] = await pool.query(
      `
      INSERT INTO citas (id_usuario, id_medico, fecha_cita, estado, motivo)
      SELECT ?, id_medico, fecha_hora, 'Pendiente', ?
      FROM agenda_medico
      WHERE id_agenda = ?
      `,
      [id_usuario, motivo, agendaId]
    );

    await pool.query(
      `
      UPDATE agenda_medico SET estado = 'No disponible' 
      WHERE id_agenda = ?
      `,
      [agendaId]
    );

    res.status(201).json({ message: "Appointment booked successfully", id_cita: insertResult.insertId });
  } catch (err) {
    console.error("Error booking appointment:", err);
    res.status(500).json({ message: "Server error" });
  }
});

// ==========================================
// Crear disponibilidad médica
// ==========================================
router.post("/disponibilidad", async (req, res) => {
  const { id_medico, fecha_hora } = req.body;

  try {
    // Validar que los campos requeridos estén presentes
    if (!id_medico || !fecha_hora) {
      return res.status(400).json({ message: "id_medico and fecha_hora are required" });
    }

    // Verificar si ya existe un horario en la misma fecha_hora para el médico
    const [existing] = await pool.query(
      `
      SELECT id_agenda FROM agenda_medico 
      WHERE id_medico = ? AND fecha_hora = ?
      `,
      [id_medico, fecha_hora]
    );

    if (existing.length > 0) {
      return res.status(400).json({ message: "Schedule already exists for this date and time" });
    }

    // Insertar nuevo horario con estado 'Disponible'
    const [result] = await pool.query(
      `
      INSERT INTO agenda_medico (id_medico, fecha_hora, estado)
      VALUES (?, ?, 'Disponible')
      `,
      [id_medico, fecha_hora]
    );

    res.status(201).json({ 
      message: "Availability created successfully", 
      id_agenda: result.insertId 
    });
  } catch (err) {
    console.error("Error creating availability:", err);
    res.status(500).json({ message: "Server error" });
  }
});


// ==========================================
// Listar citas de un usuario
// ==========================================
router.get("/user/:id/citas", async (req, res) => {
  const { id } = req.params;
  try {
    const [results] = await pool.query(
      `
      SELECT c.id_cita, m.nombre AS medico, m.apellido AS medico_apellido,
             c.fecha_cita, c.estado, c.motivo
      FROM citas c
      JOIN medicos m ON c.id_medico = m.id_medico
      WHERE c.id_usuario = ?
      ORDER BY c.fecha_cita DESC
      `,
      [id]
    );
    res.status(200).json(results);
  } catch (err) {
    console.error("Error fetching user's appointments:", err);
    res.status(500).json({ message: "Server error" });
  }
});

// ==========================================
// Listar citas de un médico
// ==========================================
router.get("/doctor/:id/citas", async (req, res) => {
  const { id } = req.params;
  try {
    const [results] = await pool.query(
      `
      SELECT c.id_cita, u.nombre AS paciente, u.apellido AS paciente_apellido,
             c.fecha_cita, c.estado, c.motivo
      FROM citas c
      JOIN usuarios u ON c.id_usuario = u.id_usuario
      WHERE c.id_medico = ?
      ORDER BY c.fecha_cita DESC
      `,
      [id]
    );
    res.status(200).json(results);
  } catch (err) {
    console.error("Error fetching doctor's appointments:", err);
    res.status(500).json({ message: "Server error" });
  }
});

// ==========================================
// Cancelar una cita
// ==========================================
router.put("/cancelar/:id", async (req, res) => {
  const { id } = req.params;
  try {
    await pool.query(
      `
      UPDATE citas SET estado = 'Cancelada'
      WHERE id_cita = ?
      `,
      [id]
    );
    res.status(200).json({ message: "Appointment canceled" });
  } catch (err) {
    console.error("Error canceling appointment:", err);
    res.status(500).json({ message: "Server error" });
  }
});


// Ruta actualizada con JOIN entre citas y agenda_medico
router.get("/doctor/:id/appointment-dates", async (req, res) => {
  const { id } = req.params;

  try {
    const [results] = await pool.query(
      `
      SELECT DISTINCT
        DATE(c.fecha_cita) as fecha,
        a.etiqueta
      FROM citas c
      LEFT JOIN agenda_medico a
        ON DATE(c.fecha_cita) = DATE(a.fecha_hora)
        AND a.id_medico = c.id_medico
      WHERE c.id_medico = ?
        AND c.motivo IS NOT NULL
        AND TRIM(c.motivo) != ''
      ORDER BY fecha
      `,
      [id]
    );

    // Mapear resultados como objetos: { fecha: 'YYYY-MM-DD', etiqueta: '...' }
    const datos = results.map(row => ({
      fecha: row.fecha.toISOString().split('T')[0],
      etiqueta: row.etiqueta || ''
    }));

    res.status(200).json(datos);
  } catch (err) {
    console.error("Error fetching appointment dates with etiquetas:", err);
    res.status(500).json({ message: "Server error" });
  }
});

// ==========================================
// Confirmar cita (médico)
/// ==========================================
router.put("/confirmar/:id", async (req, res) => {
  const { id } = req.params;
  try {
    await pool.query(
      `
      UPDATE citas SET estado = 'Confirmada'
      WHERE id_cita = ?
      `,
      [id]
    );
    res.status(200).json({ message: "Appointment confirmed" });
  } catch (err) {
    console.error("Error confirming appointment:", err);
    res.status(500).json({ message: "Server error" });
  }
});

// ==========================================
// Marcar cita como atendida (médico)
// ==========================================
router.put("/atendida/:id", async (req, res) => {
  const { id } = req.params;
  try {
    await pool.query(
      `
      UPDATE citas SET estado = 'Atendida'
      WHERE id_cita = ?
      `,
      [id]
    );
    res.status(200).json({ message: "Appointment marked as attended" });
  } catch (err) {
    console.error("Error updating appointment:", err);
    res.status(500).json({ message: "Server error" });
  }
});

// ==========================================
// Calificar a un médico después de cita
// ==========================================
router.post("/calificar", async (req, res) => {
  const { id_usuario, id_medico, id_cita, calificacion, comentario } = req.body;
  try {
    // Verificar que la cita esté atendida
    const [cita] = await pool.query(
      `
      SELECT * FROM citas
      WHERE id_cita = ? AND id_usuario = ? AND estado = 'Atendida'
      `,
      [id_cita, id_usuario]
    );
    if (cita.length === 0) {
      return res.status(400).json({ message: "Cita no encontrada o no está atendida" });
    }

    // Insertar calificación
    await pool.query(
      `
      INSERT INTO clasificacion_medicos (id_usuario, id_medico, id_cita, calificacion, comentario)
      VALUES (?, ?, ?, ?, ?)
      `,
      [id_usuario, id_medico, id_cita, calificacion, comentario]
    );

    res.status(201).json({ message: "Calificación registrada" });
  } catch (err) {
    console.error("Error saving rating:", err);
    res.status(500).json({ message: "Server error" });
  }
});


// ==========================================
// Bloquear fechas específicas para un médico
// ==========================================
router.post("/doctor/bloquear-fechas", async (req, res) => {
  const { id_medico, fechas, etiqueta, recordatorio } = req.body;

  // Validación mínima
  if (!id_medico || !Array.isArray(fechas) || fechas.length === 0) {
    return res.status(400).json({ message: "Faltan datos requeridos" });
  }

  // Armar valores a insertar
  const values = fechas.map(fecha => [
    id_medico,
    `${fecha} 00:00:00`,
    "No disponible",
    etiqueta || null,
    recordatorio || null
  ]);

  try {
    await pool.query(
      `
      INSERT INTO agenda_medico 
        (id_medico, fecha_hora, estado, etiqueta, recordatorio)
      VALUES ?
      `,
      [values]
    );

    res.status(201).json({ message: "Fechas bloqueadas exitosamente" });
  } catch (err) {
    console.error("Error al bloquear fechas:", err);
    res.status(500).json({ message: "Error del servidor" });
  }
});

// ==========================================
// Obtener calificaciones de un médico
// ==========================================
router.get("/doctor/:id/calificaciones", async (req, res) => {
  const { id } = req.params;
  try {
    const [results] = await pool.query(
      `
      SELECT calificacion, comentario, created_at
      FROM clasificacion_medicos
      WHERE id_medico = ?
      ORDER BY created_at DESC
      `,
      [id]
    );
    res.status(200).json(results);
  } catch (err) {
    console.error("Error fetching ratings:", err);
    res.status(500).json({ message: "Server error" });
  }
});

export default router;
