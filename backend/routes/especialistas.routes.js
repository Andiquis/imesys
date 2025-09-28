/* PROMPT PARA IA:


Este endpoint GET /api/especialistas/doctors permite obtener todos los médicos registrados.
Puedes filtrar por especialidad usando el parámetro ?especialidad=ID
Ejemplo de uso:
curl -X GET "http://localhost:5000/api/especialistas/doctors?especialidad=1" -H "accept: application/json"



*/

import { Router } from "express";
import pool from "../config/db.js"; // Assuming db.js exports a MySQL connection pool

const router = Router();

// Get all specialties for the filter
router.get("/specialties", async (req, res) => {
  try {
    const [results] = await pool.query(
      "SELECT id_especialidad, nombre_especialidad FROM especialidades ORDER BY nombre_especialidad"
    );
    res.status(200).json(results);
  } catch (err) {
    console.error("Error fetching specialties:", err);
    res.status(500).json({ message: "Server error" });
  }
});

// Get all doctors or filter by specialty
router.get("/doctors", async (req, res) => {
  const { especialidad } = req.query; // Specialty ID from query parameter
  let query;
  let params = [];

  try {
    if (especialidad && parseInt(especialidad) > 0) {
      query = `
        SELECT m.id_medico, m.nombre, m.apellido, m.foto, m.telefono, m.direccion_consultorio,
               m.numero_colegiatura, e.nombre_especialidad
        FROM medicos m
        JOIN especialidades e ON m.id_especialidad = e.id_especialidad
        WHERE m.id_especialidad = ?
        ORDER BY m.nombre, m.apellido
      `;
      params = [especialidad];
    } else {
      query = `
        SELECT m.id_medico, m.nombre, m.apellido, m.foto, m.telefono, m.direccion_consultorio,
               m.numero_colegiatura, e.nombre_especialidad
        FROM medicos m
        JOIN especialidades e ON m.id_especialidad = e.id_especialidad
        ORDER BY e.nombre_especialidad, m.nombre, m.apellido
      `;
    }

    const [results] = await pool.query(query, params);
    res.status(200).json(results);
  } catch (err) {
    console.error("Error fetching doctors:", err);
    res.status(500).json({ message: "Server error" });
  }
});

export default router;
/**
 * @swagger
 * tags:
 *   name: especialistas
 *   description: Endpoints relacionados con especialidades y médicos
 */

/**
 * @swagger
 * /api/especialistas/specialties:
 *   get:
 *     summary: Obtener todas las especialidades médicas
 *     tags: [especialistas]
 *     description: |
 *       ### Descripción:
 *       Retorna una lista de todas las especialidades ordenadas alfabéticamente.
 *       
 *       <br><br>
 *       
 *       ### Pront para IA o desarrolladores:
 *       Este endpoint `GET /api/especialistas/doctors` permite obtener todos los médicos registrados.
 *       Puedes filtrar por especialidad usando el parámetro `?especialidad=ID`.
 *       
 *       #### Ejemplo con cURL:
 *       ```bash
 *       curl -X GET "http://localhost:5000/api/especialistas/doctors?especialidad=1" -H "accept: application/json"
 *       ```
 *       
 *       #### Ejemplo sin filtro:
 *       ```bash
 *       curl -X GET "http://localhost:5000/api/especialistas/doctors" -H "accept: application/json"
 *       ```
 *     responses:
 *       200:
 *         description: Lista de especialidades obtenida correctamente.
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
 *                     example: Cardiología
 *       500:
 *         description: Error del servidor al obtener las especialidades.
 */

/**
 * @swagger
 * /api/especialistas/doctors:
 *   get:
 *     summary: Obtener médicos registrados (con o sin filtro por especialidad)
 *     tags: [especialistas]
 *     description: |
 *       ### Descripción:
 *       Retorna una lista de médicos, pudiendo filtrarse por ID de especialidad mediante el parámetro `especialidad`.
 *       
 *       <br><br>
 *       
 *       ### Pront para IA o desarrolladores:
 *       Este endpoint `GET /api/especialistas/doctors` devuelve todos los médicos registrados.
 *       Puedes filtrar por especialidad con el parámetro `?especialidad=ID`.
 *       
 *       #### Ejemplo con cURL:
 *       ```bash
 *       curl -X GET "http://localhost:5000/api/especialistas/doctors?especialidad=2" -H "accept: application/json"
 *       ```
 *       
 *       #### Ejemplo sin filtro:
 *       ```bash
 *       curl -X GET "http://localhost:5000/api/especialistas/doctors" -H "accept: application/json"
 *       ```
 *     parameters:
 *       - in: query
 *         name: especialidad
 *         schema:
 *           type: integer
 *         description: ID de la especialidad médica para filtrar
 *     responses:
 *       200:
 *         description: Lista de médicos obtenida correctamente.
 *         content:
 *           application/json:
 *             schema:
 *               type: array
 *               items:
 *                 type: object
 *                 properties:
 *                   id_medico:
 *                     type: integer
 *                     example: 1
 *                   nombre:
 *                     type: string
 *                     example: Juan
 *                   apellido:
 *                     type: string
 *                     example: Pérez
 *                   foto:
 *                     type: string
 *                     example: https://example.com/foto.jpg
 *                   telefono:
 *                     type: string
 *                     example: 987654321
 *                   direccion_consultorio:
 *                     type: string
 *                     example: Av. Salud 123
 *                   numero_colegiatura:
 *                     type: string
 *                     example: CMP123456
 *                   nombre_especialidad:
 *                     type: string
 *                     example: Dermatología
 *       500:
 *         description: Error del servidor al obtener los médicos.
 */
