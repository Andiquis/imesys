import { Router } from "express";
import pool from "../config/db.js"; // Asumiendo que db.js exporta un pool de conexión MySQL

const router = Router();

// Crear una nueva calificación de médico
router.post("/", async (req, res) => {
  const { id_usuario, id_medico, puntuacion, comentario, anonimo } = req.body;
  try {
    const [result] = await pool.query(
      `INSERT INTO clasificacion_medicos (id_usuario, id_medico, puntuacion, comentario, anonimo)
       VALUES (?, ?, ?, ?, ?)`,
      [id_usuario, id_medico, puntuacion, comentario, anonimo || 0]
    );
    res.status(201).json({
      id_clasificacion: result.insertId,
      message: "Calificación creada exitosamente",
    });
  } catch (err) {
    console.error("Error al crear calificación:", err);
    res.status(500).json({ message: "Error del servidor" });
  }
});

// Obtener todas las calificaciones o filtrar por médico o usuario
router.get("/", async (req, res) => {
  const { id_medico, id_usuario } = req.query;
  let query = `
    SELECT cm.id_clasificacion, cm.id_usuario, cm.id_medico, cm.puntuacion, cm.comentario,
           cm.fecha_clasificacion, cm.anonimo, u.nombre as nombre_usuario, u.apellido as apellido_usuario,
           m.nombre as nombre_medico, m.apellido as apellido_medico
    FROM clasificacion_medicos cm
    JOIN usuarios u ON cm.id_usuario = u.id_usuario
    JOIN medicos m ON cm.id_medico = m.id_medico
  `;
  let params = [];
  let conditions = [];

  if (id_medico) {
    conditions.push("cm.id_medico = ?");
    params.push(id_medico);
  }
  if (id_usuario) {
    conditions.push("cm.id_usuario = ?");
    params.push(id_usuario);
  }
  if (conditions.length > 0) {
    query += " WHERE " + conditions.join(" AND ");
  }
  query += " ORDER BY cm.fecha_clasificacion DESC";

  try {
    const [results] = await pool.query(query, params);
    res.status(200).json(results);
  } catch (err) {
    console.error("Error al obtener calificaciones:", err);
    res.status(500).json({ message: "Error del servidor" });
  }
});

// Obtener una calificación específica por ID
router.get("/:id", async (req, res) => {
  const { id } = req.params;
  try {
    const [results] = await pool.query(
      `SELECT cm.id_clasificacion, cm.id_usuario, cm.id_medico, cm.puntuacion, cm.comentario,
              cm.fecha_clasificacion, cm.anonimo, u.nombre as nombre_usuario, u.apellido as apellido_usuario,
              m.nombre as nombre_medico, m.apellido as apellido_medico
       FROM clasificacion_medicos cm
       JOIN usuarios u ON cm.id_usuario = u.id_usuario
       JOIN medicos m ON cm.id_medico = m.id_medico
       WHERE cm.id_clasificacion = ?`,
      [id]
    );
    if (results.length === 0) {
      return res.status(404).json({ message: "Calificación no encontrada" });
    }
    res.status(200).json(results[0]);
  } catch (err) {
    console.error("Error al obtener calificación:", err);
    res.status(500).json({ message: "Error del servidor" });
  }
});

// Actualizar una calificación
router.put("/:id", async (req, res) => {
  const { id } = req.params;
  const { puntuacion, comentario, anonimo } = req.body;
  try {
    const [result] = await pool.query(
      `UPDATE clasificacion_medicos
       SET puntuacion = ?, comentario = ?, anonimo = ?
       WHERE id_clasificacion = ?`,
      [puntuacion, comentario, anonimo || 0, id]
    );
    if (result.affectedRows === 0) {
      return res.status(404).json({ message: "Calificación no encontrada" });
    }
    res.status(200).json({ message: "Calificación actualizada exitosamente" });
  } catch (err) {
    console.error("Error al actualizar calificación:", err);
    res.status(500).json({ message: "Error del servidor" });
  }
});

// Eliminar una calificación
router.delete("/:id", async (req, res) => {
  const { id } = req.params;
  try {
    const [result] = await pool.query(
      `DELETE FROM clasificacion_medicos WHERE id_clasificacion = ?`,
      [id]
    );
    if (result.affectedRows === 0) {
      return res.status(404).json({ message: "Calificación no encontrada" });
    }
    res.status(200).json({ message: "Calificación eliminada exitosamente" });
  } catch (err) {
    console.error("Error al eliminar calificación:", err);
    res.status(500).json({ message: "Error del servidor" });
  }
});

// Obtener médicos con calificación promedio
router.get("/reports/average-ratings", async (req, res) => {
  try {
    const [results] = await pool.query(
      `SELECT m.id_medico, m.nombre, m.apellido, e.nombre_especialidad,
              COALESCE(AVG(cm.puntuacion), 0) as promedio,
              COUNT(cm.id_clasificacion) as total_calificaciones
       FROM medicos m
       LEFT JOIN especialidades e ON m.id_especialidad = e.id_especialidad
       LEFT JOIN clasificacion_medicos cm ON m.id_medico = cm.id_medico
       GROUP BY m.id_medico
       ORDER BY promedio DESC`
    );
    res.status(200).json(results);
  } catch (err) {
    console.error("Error al obtener calificaciones promedio:", err);
    res.status(500).json({ message: "Error del servidor" });
  }
});

// Endpoint para Especialista del Mes
router.get("/reports/especialista-del-mes", async (req, res) => {
  try {
    const [results] = await pool.query(
      `SELECT m.id_medico, m.nombre, m.apellido, m.foto, e.nombre_especialidad,
              COALESCE(AVG(cm.puntuacion), 0) as promedio,
              COUNT(cm.id_clasificacion) as total_calificaciones
       FROM medicos m
       LEFT JOIN especialidades e ON m.id_especialidad = e.id_especialidad
       LEFT JOIN clasificacion_medicos cm ON m.id_medico = cm.id_medico
       WHERE cm.fecha_clasificacion >= DATE_SUB(CURDATE(), INTERVAL 1 MONTH)
       GROUP BY m.id_medico
       HAVING promedio > 0
       ORDER BY promedio DESC, total_calificaciones DESC
       LIMIT 1`
    );
    res.status(200).json(results[0] || {});
  } catch (err) {
    console.error("Error al obtener especialista del mes:", err);
    res.status(500).json({ message: "Error del servidor" });
  }
});

// Endpoint para Top 3 Especialistas (modificado para incluir foto)
router.get("/reports/top-3-doctors", async (req, res) => {
  try {
    const [results] = await pool.query(
      `SELECT m.id_medico, m.nombre, m.apellido, m.foto, e.nombre_especialidad,
              COALESCE(AVG(cm.puntuacion), 0) as promedio,
              COUNT(cm.id_clasificacion) as total_calificaciones
       FROM medicos m
       LEFT JOIN especialidades e ON m.id_especialidad = e.id_especialidad
       LEFT JOIN clasificacion_medicos cm ON m.id_medico = cm.id_medico
       GROUP BY m.id_medico
       HAVING promedio > 0
       ORDER BY promedio DESC, total_calificaciones DESC
       LIMIT 3`
    );
    res.status(200).json(results);
  } catch (err) {
    console.error("Error al obtener top 3 médicos:", err);
    res.status(500).json({ message: "Error del servidor" });
  }
});

// Obtener los 3 mejores médicos por especialidad
router.get("/reports/top-3-by-specialty", async (req, res) => {
  try {
    const [results] = await pool.query(
      `SELECT e.nombre_especialidad, m.nombre, m.apellido,
              COALESCE(AVG(cm.puntuacion), 0) as promedio
       FROM medicos m
       JOIN especialidades e ON m.id_especialidad = e.id_especialidad
       LEFT JOIN clasificacion_medicos cm ON m.id_medico = cm.id_medico
       GROUP BY e.id_especialidad, m.id_medico
       HAVING promedio > 0
       ORDER BY e.nombre_especialidad, promedio DESC`
    );
    // Organizar por especialidad
    const especialidades = {};
    for (const row of results) {
      const esp = row.nombre_especialidad;
      if (!especialidades[esp]) {
        especialidades[esp] = [];
      }
      if (especialidades[esp].length < 3) {
        especialidades[esp].push(row);
      }
    }
    res.status(200).json(especialidades);
  } catch (err) {
    console.error("Error al obtener top 3 por especialidad:", err);
    res.status(500).json({ message: "Error del servidor" });
  }
});

// Obtener comentarios recientes
router.get("/reports/recent-comments", async (req, res) => {
  try {
    const [results] = await pool.query(
      `SELECT cm.puntuacion, cm.comentario, cm.fecha_clasificacion,
              u.nombre as nombre_usuario, u.apellido as apellido_usuario,
              m.nombre as nombre_medico, m.apellido as apellido_medico
       FROM clasificacion_medicos cm
       JOIN usuarios u ON cm.id_usuario = u.id_usuario
       JOIN medicos m ON cm.id_medico = m.id_medico
       WHERE cm.anonimo = 0 AND cm.comentario IS NOT NULL
       ORDER BY cm.fecha_clasificacion DESC
       LIMIT 10`
    );
    res.status(200).json(results);
  } catch (err) {
    console.error("Error al obtener comentarios recientes:", err);
    res.status(500).json({ message: "Error del servidor" });
  }
});



export default router;