import { Router } from "express";
import pool from "../db.js";

const router = Router();

// Obtener todos los estudiantes
router.get("/", async (req, res) => {
  try {
    const [rows] = await pool.query("SELECT * FROM estudiantes");
    res.json(rows);
  } catch (error) {
    res.status(500).json({ error: "Error al obtener estudiantes" });
  }
});

// Obtener un estudiante por ID
router.get("/:id", async (req, res) => {
  const { id } = req.params;
  try {
    const [rows] = await pool.query("SELECT * FROM estudiantes WHERE id = ?", [id]);
    if (rows.length === 0) {
      return res.status(404).json({ error: "Estudiante no encontrado" });
    }
    res.json(rows[0]);
  } catch (error) {
    res.status(500).json({ error: "Error al obtener estudiante" });
  }
});

// Crear un nuevo estudiante
router.post("/", async (req, res) => {
  const { nombres, apellidos, dni, correo, telefono, fecha_nacimiento, direccion } = req.body;
  try {
    const result = await pool.query(
      "INSERT INTO estudiantes (nombres, apellidos, dni, correo, telefono, fecha_nacimiento, direccion) VALUES (?, ?, ?, ?, ?, ?, ?)",
      [nombres, apellidos, dni, correo, telefono, fecha_nacimiento, direccion]
    );
    res.json({ id: result[0].insertId, nombres, apellidos, dni, correo, telefono, fecha_nacimiento, direccion });
  } catch (error) {
    res.status(500).json({ error: "Error al crear estudiante" });
  }
});

// Actualizar un estudiante
router.put("/:id", async (req, res) => {
  const { nombres, apellidos, dni, correo, telefono, fecha_nacimiento, direccion } = req.body;
  const { id } = req.params;
  try {
    await pool.query(
      "UPDATE estudiantes SET nombres = ?, apellidos = ?, dni = ?, correo = ?, telefono = ?, fecha_nacimiento = ?, direccion = ? WHERE id = ?",
      [nombres, apellidos, dni, correo, telefono, fecha_nacimiento, direccion, id]
    );
    res.json({ message: "Estudiante actualizado" });
  } catch (error) {
    res.status(500).json({ error: "Error al actualizar estudiante" });
  }
});

// Eliminar un estudiante
router.delete("/:id", async (req, res) => {
  const { id } = req.params;
  try {
    await pool.query("DELETE FROM estudiantes WHERE id = ?", [id]);
    res.json({ message: "Estudiante eliminado" });
  } catch (error) {
    res.status(500).json({ error: "Error al eliminar estudiante" });
  }
});

export default router;
