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