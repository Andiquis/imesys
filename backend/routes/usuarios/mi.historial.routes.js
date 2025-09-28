import { Router } from "express";
import pool from "../../config/db.js";

const router = Router();

/**
 * @swagger
 * tags:
 *   - name: mi-historial
 *     description: Endpoints para manejar el historial médico de los usuarios
 *   - name: especialidades
 *     description: Endpoints para manejar especialidades médicas
 */

/**
 * @swagger
 * /api/mi-historial:
 *   get:
 *     summary: Obtener el historial médico de un usuario
 *     tags: [mi-historial]
 *     description: |
 *       Retorna el historial médico del usuario especificado.
 *       Se pueden aplicar filtros opcionales por fecha de inicio, fin y especialidad médica.
 *     parameters:
 *       - in: query
 *         name: id_usuario
 *         required: true
 *         schema:
 *           type: integer
 *         description: ID del usuario al que pertenece el historial
 *         example: 12
 *       - in: query
 *         name: fecha_inicio
 *         required: false
 *         schema:
 *           type: string
 *           format: date
 *         description: Fecha mínima (YYYY-MM-DD) para filtrar el historial
 *         example: 2025-05-01
 *       - in: query
 *         name: fecha_fin
 *         required: false
 *         schema:
 *           type: string
 *           format: date
 *         description: Fecha máxima (YYYY-MM-DD) para filtrar el historial
 *         example: 2025-05-26
 *       - in: query
 *         name: especialidad
 *         required: false
 *         schema:
 *           type: integer
 *         description: ID de la especialidad médica para filtrar
 *         example: 3
 *     responses:
 *       200:
 *         description: Historial médico obtenido correctamente
 *         content:
 *           application/json:
 *             schema:
 *               type: array
 *               items:
 *                 type: object
 *                 properties:
 *                   id_historial:
 *                     type: integer
 *                     example: 15
 *                   medico_nombre:
 *                     type: string
 *                     example: Ana
 *                   medico_apellido:
 *                     type: string
 *                     example: Torres
 *                   id_especialidad:
 *                     type: integer
 *                     example: 3
 *                   nombre_especialidad:
 *                     type: string
 *                     example: Cardiología
 *                   motivo:
 *                     type: string
 *                     example: Dolor de pecho
 *                   diagnostico:
 *                     type: string
 *                     example: Angina estable
 *                   tratamiento:
 *                     type: string
 *                     example: Reposo y medicación
 *                   fecha_hora:
 *                     type: string
 *                     format: date-time
 *                     example: 2025-05-20T14:30:00Z
 *       400:
 *         description: Faltan parámetros requeridos como el id_usuario
 *       500:
 *         description: Error interno del servidor
 */
router.get("/", async (req, res) => {
  try {
    const { id_usuario, fecha_inicio, fecha_fin, especialidad } = req.query;

    if (!id_usuario) {
      return res.status(400).json({ message: "Falta el id_usuario en la consulta" });
    }

    let query = `
      SELECT 
        hc.id_historial,
        m.nombre AS medico_nombre,
        m.apellido AS medico_apellido,
        e.id_especialidad,
        e.nombre_especialidad,
        hc.motivo,
        hc.diagnostico,
        hc.tratamiento,
        hc.fecha_hora
      FROM historial_consultas hc
      JOIN medicos m ON hc.id_medico = m.id_medico
      JOIN especialidades e ON m.id_especialidad = e.id_especialidad
      WHERE hc.id_usuario = ?
    `;

    const params = [id_usuario];

    if (fecha_inicio) {
      query += " AND DATE(hc.fecha_hora) >= ?";
      params.push(fecha_inicio);
    }

    if (fecha_fin) {
      query += " AND DATE(hc.fecha_hora) <= ?";
      params.push(fecha_fin);
    }

    if (especialidad && !isNaN(parseInt(especialidad))) {
      query += " AND e.id_especialidad = ?";
      params.push(especialidad);
    }

    query += " ORDER BY hc.fecha_hora DESC";

    const [results] = await pool.query(query, params);
    res.status(200).json(results);
  } catch (error) {
    console.error("Error al obtener historial:", error);
    res.status(500).json({ message: "Error interno del servidor" });
  }
});

/**
 * @swagger
 * /api/mi-historial/especialidades:
 *   get:
 *     summary: Obtener todas las especialidades médicas
 *     tags: [especialidades]
 *     responses:
 *       200:
 *         description: Lista de especialidades obtenida correctamente
 *         content:
 *           application/json:
 *             schema:
 *               type: array
 *               items:
 *                 type: object
 *                 properties:
 *                   id_especialidad:
 *                     type: integer
 *                     example: 1
 *                   nombre_especialidad:
 *                     type: string
 *                     example: Pediatría
 *       500:
 *         description: Error interno del servidor
 */
router.get("/especialidades", async (req, res) => {
  try {
    const [especialidades] = await pool.query(
      "SELECT id_especialidad, nombre_especialidad FROM especialidades"
    );
    res.status(200).json(especialidades);
  } catch (error) {
    console.error("Error al obtener especialidades:", error);
    res.status(500).json({ message: "Error interno del servidor" });
  }
});

export default router;
