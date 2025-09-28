import { Router } from "express";
import pool from "../../config/db.js"; // Asumiendo que db.js exporta un pool de conexiones MySQL
import { body, param, validationResult } from "express-validator";

// Crear el router
const router = Router();

// Middleware para verificar si el médico existe
const checkDoctorExists = async (req, res, next) => {
  try {
    const [rows] = await pool.query("SELECT * FROM medicos WHERE id_medico = ?", [req.params.id]);
    if (rows.length === 0) {
      return res.status(404).json({ message: "Médico no encontrado" });
    }
    req.doctor = rows[0];
    next();
  } catch (err) {
    console.error("Error verificando médico:", err);
    res.status(500).json({ message: "Error del servidor" });
  }
};

/**
 * @swagger
 * tags:
 *   name: MisPacientes
 *   description: Endpoints para gestionar y visualizar datos relacionados con un médico
 */

/**
 * @swagger
 * /api/mis-pacientes/{id}/profile:
 *   get:
 *     summary: Obtener el perfil del médico
 *     tags: [MisPacientes]
 *     description: |
 *       ### Descripción:
 *       Retorna los detalles del perfil de un médico, incluyendo su especialidad.
 *       
 *       ### Ejemplo con cURL:
 *       ```bash
 *       curl -X GET "http://localhost:5000/api/mis-pacientes/1/profile" -H "accept: application/json"
 *       ```
 *     parameters:
 *       - in: path
 *         name: id
 *         schema:
 *           type: integer
 *         required: true
 *         description: ID del médico
 *     responses:
 *       200:
 *         description: Perfil del médico obtenido correctamente
 *         content:
 *           application/json:
 *             schema:
 *               type: object
 *               properties:
 *                 id_medico:
 *                   type: integer
 *                   example: 1
 *                 nombre:
 *                   type: string
 *                   example: Juan
 *                 apellido:
 *                   type: string
 *                   example: Pérez
 *                 correo:
 *                   type: string
 *                   example: juan.perez@medico.com
 *                 telefono:
 *                   type: string
 *                   example: 987654321
 *                 especialidad:
 *                   type: string
 *                   example: Cardiología
 *                 numero_colegiatura:
 *                   type: string
 *                   example: CMP123456
 *                 direccion_consultorio:
 *                   type: string
 *                   example: Av. Salud 123
 *                 fecha_registro:
 *                   type: string
 *                   example: 2025-05-24T14:06:00.000Z
 *                 foto:
 *                   type: string
 *                   example: https://example.com/foto.jpg
 *       404:
 *         description: Médico no encontrado
 *       500:
 *         description: Error del servidor
 */
router.get("/profile/:id", 
  [param("id").isInt().withMessage("ID del médico debe ser un número entero")],
  checkDoctorExists, 
  async (req, res) => {
    try {
      const errors = validationResult(req);
      if (!errors.isEmpty()) {
        return res.status(400).json({ errors: errors.array() });
      }
      const [specialty] = await pool.query(
        "SELECT nombre_especialidad FROM especialidades WHERE id_especialidad = ?",
        [req.doctor.id_especialidad]
      );
      res.status(200).json({
        id_medico: req.doctor.id_medico,
        nombre: req.doctor.nombre,
        apellido: req.doctor.apellido,
        correo: req.doctor.correo,
        telefono: req.doctor.telefono,
        especialidad: specialty[0]?.nombre_especialidad || "N/A",
        numero_colegiatura: req.doctor.numero_colegiatura,
        direccion_consultorio: req.doctor.direccion_consultorio,
        fecha_registro: req.doctor.fecha_registro,
        foto: req.doctor.foto
      });
    } catch (err) {
      console.error("Error obteniendo perfil del médico:", err);
      res.status(500).json({ message: "Error del servidor" });
    }
  }
);

/**
 * @swagger
 * /api/mis-pacientes/{id}/schedule:
 *   get:
 *     summary: Obtener la agenda del médico
 *     tags: [MisPacientes]
 *     description: |
 *       ### Descripción:
 *       Retorna la agenda del médico, incluyendo fechas, horas, estados, etiquetas y recordatorios.
 *       
 *       ### Ejemplo con cURL:
 *       ```bash
 *       curl -X GET "http://localhost:5000/api/mis-pacientes/1/schedule" -H "accept: application/json"
 *       ```
 *     parameters:
 *       - in: path
 *         name: id
 *         schema:
 *           type: integer
 *         required: true
 *         description: ID del médico
 *     responses:
 *       200:
 *         description: Agenda obtenida correctamente
 *         content:
 *           application/json:
 *             schema:
 *               type: array
 *               items:
 *                 type: object
 *                 properties:
 *                   id_agenda:
 *                     type: integer
 *                     example: 1
 *                   fecha_hora:
 *                     type: string
 *                     example: 2025-05-25T10:00:00
 *                   estado:
 *                     type: string
 *                     example: Disponible
 *                   etiqueta:
 *                     type: string
 *                     example: Consulta general
 *                   recordatorio:
 *                     type: string
 *                     example: Confirmar con paciente
 *                   fecha_registro:
 *                     type: string
 *                     example: 2025-05-24T14:06:00.000Z
 *       404:
 *         description: Médico no encontrado
 *       500:
 *         description: Error del servidor
 */
router.get("/schedule/:id", 
  [param("id").isInt().withMessage("ID del médico debe ser un número entero")],
  checkDoctorExists, 
  async (req, res) => {
    try {
      const errors = validationResult(req);
      if (!errors.isEmpty()) {
        return res.status(400).json({ errors: errors.array() });
      }
      const [rows] = await pool.query(
        "SELECT id_agenda, fecha_hora, estado, etiqueta, recordatorio, fecha_registro FROM agenda_medico WHERE id_medico = ?",
        [req.params.id]
      );
      res.status(200).json(rows);
    } catch (err) {
      console.error("Error obteniendo agenda:", err);
      res.status(500).json({ message: "Error del servidor" });
    }
  }
);

/**
 * @swagger
 * /api/mis-pacientes/{id}/schedule:
 *   post:
 *     summary: Crear una nueva entrada en la agenda del médico
 *     tags: [MisPacientes]
 *     description: |
 *       ### Descripción:
 *       Crea una nueva entrada en la agenda del médico con fecha, estado, etiqueta y recordatorio.
 *       
 *       ### Ejemplo con cURL:
 *       ```bash
 *       curl -X POST "http://localhost:5000/api/mis-pacientes/1/schedule" \
 *       -H "Content-Type: application/json" \
 *       -d '{"fecha_hora":"2025-05-25T10:00:00","estado":"Disponible","etiqueta":"Consulta general","recordatorio":"Confirmar con paciente"}'
 *       ```
 *     parameters:
 *       - in: path
 *         name: id
 *         schema:
 *           type: integer
 *         required: true
 *         description: ID del médico
 *     requestBody:
 *       required: true
 *       content:
 *         application/json:
 *           schema:
 *             type: object
 *             properties:
 *               fecha_hora:
 *                 type: string
 *                 format: date-time
 *                 example: 2025-05-25T10:00:00
 *               estado:
 *                 type: string
 *                 enum: [Disponible, No disponible]
 *                 example: Disponible
 *               etiqueta:
 *                 type: string
 *                 example: Consulta general
 *               recordatorio:
 *                 type: string
 *                 example: Confirmar con paciente
 *     responses:
 *       201:
 *         description: Entrada de agenda creada
 *       400:
 *         description: Datos de entrada inválidos
 *       404:
 *         description: Médico no encontrado
 *       500:
 *         description: Error del servidor
 */
router.post(
  "/schedule/:id",
  [
    param("id").isInt().withMessage("ID del médico debe ser un número entero"),
    body("fecha_hora").isISO8601().toDate().withMessage("Fecha y hora inválida"),
    body("estado").isIn(["Disponible", "No disponible"]).withMessage("Estado inválido"),
    body("etiqueta").optional().isString().withMessage("Etiqueta debe ser una cadena"),
    body("recordatorio").optional().isString().withMessage("Recordatorio debe ser una cadena")
  ],
  checkDoctorExists,
  async (req, res) => {
    const errors = validationResult(req);
    if (!errors.isEmpty()) {
      return res.status(400).json({ errors: errors.array() });
    }
    try {
      const { fecha_hora, estado, etiqueta, recordatorio } = req.body;
      await pool.query(
        "INSERT INTO agenda_medico (id_medico, fecha_hora, estado, etiqueta, recordatorio) VALUES (?, ?, ?, ?, ?)",
        [req.params.id, fecha_hora, estado, etiqueta || null, recordatorio || null]
      );
      res.status(201).json({ message: "Entrada de agenda creada" });
    } catch (err) {
      console.error("Error creando entrada de agenda:", err);
      res.status(500).json({ message: "Error del servidor" });
    }
  }
);

/**
 * @swagger
 * /api/mis-pacientes/{id}/appointments:
 *   get:
 *     summary: Obtener las citas del médico
 *     tags: [MisPacientes]
 *     description: |
 *       ### Descripción:
 *       Retorna todas las citas asociadas al médico, con información del paciente.
 *       
 *       ### Ejemplo con cURL:
 *       ```bash
 *       curl -X GET "http://localhost:5000/api/mis-pacientes/1/appointments" -H "accept: application/json"
 *       ```
 *     parameters:
 *       - in: path
 *         name: id
 *         schema:
 *           type: integer
 *         required: true
 *         description: ID del médico
 *     responses:
 *       200:
 *         description: Citas obtenidas correctamente
 *         content:
 *           application/json:
 *             schema:
 *               type: array
 *               items:
 *                 type: object
 *                 properties:
 *                   id_cita:
 *                     type: integer
 *                     example: 1
 *                   fecha_cita:
 *                     type: string
 *                     example: 2025-05-25T10:00:00
 *                   estado:
 *                     type: string
 *                     example: Pendiente
 *                   motivo:
 *                     type: string
 *                     example: Dolor torácico
 *                   respuesta:
 *                     type: string
 *                     example: Cita confirmada
 *                   nombre:
 *                     type: string
 *                     example: Ana
 *                   apellido:
 *                     type: string
 *                     example: Gómez
 *                   correo:
 *                     type: string
 *                     example: ana.gomez@email.com
 *       404:
 *         description: Médico no encontrado
 *       500:
 *         description: Error del servidor
 */
router.get("/appointments/:id", 
  [param("id").isInt().withMessage("ID del médico debe ser un número entero")],
  checkDoctorExists, 
  async (req, res) => {
    try {
      const errors = validationResult(req);
      if (!errors.isEmpty()) {
        return res.status(400).json({ errors: errors.array() });
      }
      const [rows] = await pool.query(
        `SELECT c.id_cita, c.fecha_cita, c.estado, c.motivo, c.respuesta, u.nombre, u.apellido, u.correo
         FROM citas c
         JOIN usuarios u ON c.id_usuario = u.id_usuario
         WHERE c.id_medico = ?`,
        [req.params.id]
      );
      res.status(200).json(rows);
    } catch (err) {
      console.error("Error obteniendo citas:", err);
      res.status(500).json({ message: "Error del servidor" });
    }
  }
);

/**
 * @swagger
 * /api/mis-pacientes/{id}/appointments/{appointmentId}:
 *   put:
 *     summary: Actualizar el estado y/o respuesta de una cita
 *     tags: [MisPacientes]
 *     description: |
 *       ### Descripción:
 *       Actualiza el estado y/o la respuesta de una cita específica del médico.
 *       
 *       ### Ejemplo con cURL:
 *       ```bash
 *       curl -X PUT "http://localhost:5000/api/mis-pacientes/1/appointments/1" \
 *       -H "Content-Type: application/json" \
 *       -d '{"estado":"Confirmada","respuesta":"Cita confirmada para consulta"}'
 *       ```
 *     parameters:
 *       - in: path
 *         name: id
 *         schema:
 *           type: integer
 *         required: true
 *         description: ID del médico
 *       - in: path
 *         name: appointmentId
 *         schema:
 *           type: integer
 *         required: true
 *         description: ID de la cita
 *     requestBody:
 *       required: true
 *       content:
 *         application/json:
 *           schema:
 *             type: object
 *             properties:
 *               estado:
 *                 type: string
 *                 enum: [Pendiente, Confirmada, Cancelada, Completada]
 *                 example: Confirmada
 *               respuesta:
 *                 type: string
 *                 example: Cita confirmada para consulta
 *     responses:
 *       200:
 *         description: Cita actualizada correctamente
 *       400:
 *         description: Datos de entrada inválidos
 *       404:
 *         description: Cita o médico no encontrado
 *       500:
 *         description: Error del servidor
 */
router.put(
  "/appointments/:id/:appointmentId",
  [
    param("id").isInt().withMessage("ID del médico debe ser un número entero"),
    param("appointmentId").isInt().withMessage("ID de la cita debe ser un número entero"),
    body("estado").isIn(["Pendiente", "Confirmada", "Cancelada", "Completada"]).withMessage("Estado inválido"),
    body("respuesta").optional().isString().withMessage("Respuesta debe ser una cadena")
  ],
  checkDoctorExists,
  async (req, res) => {
    const errors = validationResult(req);
    if (!errors.isEmpty()) {
      return res.status(400).json({ errors: errors.array() });
    }
    try {
      const { estado, respuesta } = req.body;
      const [result] = await pool.query(
        "UPDATE citas SET estado = ?, respuesta = ? WHERE id_cita = ? AND id_medico = ?",
        [estado, respuesta || null, req.params.appointmentId, req.params.id]
      );
      if (result.affectedRows === 0) {
        return res.status(404).json({ message: "Cita no encontrada" });
      }
      res.status(200).json({ message: "Cita actualizada" });
    } catch (err) {
      console.error("Error actualizando cita:", err);
      res.status(500).json({ message: "Error del servidor" });
    }
  }
);

/**
 * @swagger
 * /api/mis-pacientes/{id}/ratings:
 *   get:
 *     summary: Obtener las calificaciones del médico
 *     tags: [MisPacientes]
 *     description: |
 *       ### Descripción:
 *       Retorna las calificaciones y comentarios de los usuarios sobre el médico.
 *       
 *       ### Ejemplo con cURL:
 *       ```bash
 *       curl -X GET "http://localhost:5000/api/mis-pacientes/1/ratings" -H "accept: application/json"
 *       ```
 *     parameters:
 *       - in: path
 *         name: id
 *         schema:
 *           type: integer
 *         required: true
 *         description: ID del médico
 *     responses:
 *       200:
 *         description: Calificaciones obtenidas correctamente
 *         content:
 *           application/json:
 *             schema:
 *               type: array
 *               items:
 *                 type: object
 *                 properties:
 *                   id_clasificacion:
 *                     type: integer
 *                     example: 1
 *                   puntuacion:
 *                     type: integer
 *                     example: 5
 *                   comentario:
 *                     type: string
 *                     example: Excelente atención
 *                   fecha_clasificacion:
 *                     type: string
 *                     example: 2025-05-24T14:06:00.000Z
 *                   anonimo:
 *                     type: boolean
 *                     example: false
 *                   usuario:
 *                     type: string
 *                     example: Ana Gómez
 *       404:
 *         description: Médico no encontrado
 *       500:
 *         description: Error del servidor
 */
router.get("/ratings/:id", 
  [param("id").isInt().withMessage("ID del médico debe ser un número entero")],
  checkDoctorExists, 
  async (req, res) => {
    try {
      const errors = validationResult(req);
      if (!errors.isEmpty()) {
        return res.status(400).json({ errors: errors.array() });
      }
      const [rows] = await pool.query(
        `SELECT c.id_clasificacion, c.puntuacion, c.comentario, c.fecha_clasificacion, c.anonimo, u.nombre, u.apellido
         FROM clasificacion_medicos c
         JOIN usuarios u ON c.id_usuario = u.id_usuario
         WHERE c.id_medico = ?`,
        [req.params.id]
      );
      res.status(200).json(
        rows.map(row => ({
          id_clasificacion: row.id_clasificacion,
          puntuacion: row.puntuacion,
          comentario: row.comentario,
          fecha_clasificacion: row.fecha_clasificacion,
          anonimo: row.anonimo,
          usuario: row.anonimo ? "Anónimo" : `${row.nombre} ${row.apellido}`
        }))
      );
    } catch (err) {
      console.error("Error obteniendo calificaciones:", err);
      res.status(500).json({ message: "Error del servidor" });
    }
  }
);

/**
 * @swagger
 * /api/mis-pacientes/{id}/images:
 *   get:
 *     summary: Obtener imágenes médicas asociadas al médico
 *     tags: [MisPacientes]
 *     description: |
 *       ### Descripción:
 *       Retorna las imágenes médicas asociadas al médico, con resultados de análisis de IA si están disponibles.
 *       
 *       ### Ejemplo con cURL:
 *       ```bash
 *       curl -X GET "http://localhost:5000/api/mis-pacientes/1/images" -H "accept: application/json"
 *       ```
 *     parameters:
 *       - in: path
 *         name: id
 *         schema:
 *           type: integer
 *         required: true
 *         description: ID del médico
 *     responses:
 *       200:
 *         description: Imágenes obtenidas correctamente
 *         content:
 *           application/json:
 *             schema:
 *               type: array
 *               items:
 *                 type: object
 *                 properties:
 *                   id_imagen:
 *                     type: integer
 *                     example: 1
 *                   ruta_imagen:
 *                     type: string
 *                     example: https://example.com/imagen.jpg
 *                   fecha_subida:
 *                     type: string
 *                     example: 2025-05-24T14:06:00.000Z
 *                   diagnostico:
 *                     type: string
 *                     example: Normal
 *                   probabilidad:
 *                     type: number
 *                     example: 0.95
 *                   fecha_analisis:
 *                     type: string
 *                     example: 2025-05-24T14:06:00.000Z
 *       404:
 *         description: Médico no encontrado
 *       500:
 *         description: Error del servidor
 */
router.get("/images/:id", 
  [param("id").isInt().withMessage("ID del médico debe ser un número entero")],
  checkDoctorExists, 
  async (req, res) => {
    try {
      const errors = validationResult(req);
      if (!errors.isEmpty()) {
        return res.status(400).json({ errors: errors.array() });
      }
      const [rows] = await pool.query(
        `SELECT i.id_imagen, i.ruta_imagen, i.fecha_subida, r.diagnostico, r.probabilidad, r.fecha_analisis
         FROM imagenes_medicas i
         LEFT JOIN resultados_ia r ON i.id_imagen = r.id_imagen
         WHERE i.id_medico = ?`,
        [req.params.id]
      );
      res.status(200).json(rows);
    } catch (err) {
      console.error("Error obteniendo imágenes:", err);
      res.status(500).json({ message: "Error del servidor" });
    }
  }
);

/**
 * @swagger
 * /api/mis-pacientes/{id}/consultations:
 *   get:
 *     summary: Obtener el historial de consultas del médico
 *     tags: [MisPacientes]
 *     description: |
 *       ### Descripción:
 *       Retorna el historial de consultas asociadas al médico, con información del paciente.
 *       
 *       ### Ejemplo con cURL:
 *       ```bash
 *       curl -X GET "http://localhost:5000/api/mis-pacientes/1/consultations" -H "accept: application/json"
 *       ```
 *     parameters:
 *       - in: path
 *         name: id
 *         schema:
 *           type: integer
 *         required: true
 *         description: ID del médico
 *     responses:
 *       200:
 *         description: Historial de consultas obtenido correctamente
 *         content:
 *           application/json:
 *             schema:
 *               type: array
 *               items:
 *                 type: object
 *                 properties:
 *                   id_historial:
 *                     type: integer
 *                     example: 1
 *                   motivo:
 *                     type: string
 *                     example: Dolor torácico
 *                   observacion:
 *                     type: string
 *                     example: Requiere seguimiento
 *                   imagen:
 *                     type: string
 *                     example: https://example.com/imagen.jpg
 *                   fecha_hora:
 *                     type: string
 *                     example: 2025-05-24T14:06:00
 *                   dato_opcional:
 *                     type: string
 *                     example: Información adicional
 *                   nombre:
 *                     type: string
 *                     example: Ana
 *                   apellido:
 *                     type: string
 *                     example: Gómez
 *       404:
 *         description: Médico no encontrado
 *       500:
 *         description: Error del servidor
 */
router.get("/consultations/:id", 
  [param("id").isInt().withMessage("ID del médico debe ser un número entero")],
  checkDoctorExists, 
  async (req, res) => {
    try {
      const errors = validationResult(req);
      if (!errors.isEmpty()) {
        return res.status(400).json({ errors: errors.array() });
      }
      const [rows] = await pool.query(
        `SELECT h.id_historial, h.motivo, h.observacion, h.imagen, h.fecha_hora, h.dato_opcional, u.nombre, u.apellido
         FROM historial_consultas h
         JOIN usuarios u ON h.id_usuario = u.id_usuario
         WHERE h.id_medico = ?`,
        [req.params.id]
      );
      res.status(200).json(rows);
    } catch (err) {
      console.error("Error obteniendo historial de consultas:", err);
      res.status(500).json({ message: "Error del servidor" });
    }
  }
);

/**
 * @swagger
 * /api/mis-pacientes/{id}/profile:
 *   put:
 *     summary: Actualizar el perfil del médico
 *     tags: [MisPacientes]
 *     description: |
 *       ### Descripción:
 *       Actualiza los datos del perfil del médico (campos opcionales: nombre, apellido, teléfono, dirección, foto).
 *       
 *       ### Ejemplo con cURL:
 *       ```bash
 *       curl -X PUT "http://localhost:5000/api/mis-pacientes/1/profile" \
 *       -H "Content-Type: application/json" \
 *       -d '{"nombre":"Juan","apellido":"Pérez","telefono":"987654321","direccion_consultorio":"Av. Salud 123","foto":"https://example.com/foto.jpg"}'
 *       ```
 *     parameters:
 *       - in: path
 *         name: id
 *         schema:
 *           type: integer
 *         required: true
 *         description: ID del médico
 *     requestBody:
 *       required: true
 *       content:
 *         application/json:
 *           schema:
 *             type: object
 *             properties:
 *               nombre:
 *                 type: string
 *                 example: Juan
 *               apellido:
 *                 type: string
 *                 example: Pérez
 *               telefono:
 *                 type: string
 *                 example: 987654321
 *               direccion_consultorio:
 *                 type: string
 *                 example: Av. Salud 123
 *               foto:
 *                 type: string
 *                 example: https://example.com/foto.jpg
 *     responses:
 *       200:
 *         description: Perfil actualizado correctamente
 *       400:
 *         description: Datos de entrada inválidos
 *       404:
 *         description: Médico no encontrado
 *       500:
 *         description: Error del servidor
 */
router.put(
  "/profile/:id",
  [
    param("id").isInt().withMessage("ID del médico debe ser un número entero"),
    body("nombre").optional().isString().withMessage("Nombre debe ser una cadena"),
    body("apellido").optional().isString().withMessage("Apellido debe ser una cadena"),
    body("telefono").optional().isString().withMessage("Teléfono debe ser una cadena"),
    body("direccion_consultorio").optional().isString().withMessage("Dirección debe ser una cadena"),
    body("foto").optional().isString().withMessage("Foto debe ser una cadena")
  ],
  checkDoctorExists,
  async (req, res) => {
    const errors = validationResult(req);
    if (!errors.isEmpty()) {
      return res.status(400).json({ errors: errors.array() });
    }
    try {
      const { nombre, apellido, telefono, direccion_consultorio, foto } = req.body;
      const [result] = await pool.query(
        `UPDATE medicos SET 
         nombre = COALESCE(?, nombre), 
         apellido = COALESCE(?, apellido), 
         telefono = COALESCE(?, telefono), 
         direccion_consultorio = COALESCE(?, direccion_consultorio), 
         foto = COALESCE(?, foto) 
         WHERE id_medico = ?`,
        [nombre, apellido, telefono, direccion_consultorio, foto, req.params.id]
      );
      if (result.affectedRows === 0) {
        return res.status(404).json({ message: "Médico no encontrado" });
      }
      res.status(200).json({ message: "Perfil del médico actualizado" });
    } catch (err) {
      console.error("Error actualizando perfil:", err);
      res.status(500).json({ message: "Error del servidor" });
    }
  }
);

export default router;