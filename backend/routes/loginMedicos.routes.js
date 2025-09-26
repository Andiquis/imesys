// routes/loginMedicos.routes.js
import { Router } from "express";
import pool from "../config/db.js";  // Asegúrate de que la configuración de la base de datos esté bien importada
import bcrypt from 'bcryptjs';  // Librería para comparar contraseñas encriptadas
import validator from 'validator';  // Para validar inputs si es necesario

const router = Router();

// ======================
// RUTA DE LOGIN PARA MÉDICOS
// ======================

router.post("/login", async (req, res) => {
  const { correo, contrasena } = req.body;

  // Validamos si los datos requeridos están presentes
  if (!correo || !contrasena) {
    return res.status(400).json({ error: "Correo y contraseña son requeridos" });
  }

  // Aquí puedes agregar validaciones adicionales, como si el correo es válido
  if (!validator.isEmail(correo)) {
    return res.status(400).json({ error: "Correo no válido" });
  }

  try {
    // Realizamos el JOIN entre medicos y especialidades
    const [rows] = await pool.query(`
      SELECT medicos.*, especialidades.nombre_especialidad
      FROM medicos
      LEFT JOIN especialidades ON medicos.id_especialidad = especialidades.id_especialidad
      WHERE medicos.correo = ?
    `, [correo]);

    if (rows.length === 0) {
      return res.status(404).json({ error: "Médico no encontrado" });
    }

    const medico = rows[0];

    // Comparar la contraseña ingresada con la almacenada en la base de datos
    const isMatch = await bcrypt.compare(contrasena, medico.contrasena);

    if (!isMatch) {
      return res.status(400).json({ error: "Contraseña incorrecta" });
    }

    // Responder con los datos del médico, incluyendo la especialidad
    res.json({
      message: "Login exitoso",
      user: {
        id_medico: medico.id_medico,
        nombre: medico.nombre,
        apellido: medico.apellido,
        correo: medico.correo,
        telefono: medico.telefono,
        especialidad: medico.nombre_especialidad,  // Nombre de la especialidad
        numero_colegiatura: medico.numero_colegiatura,
        foto: medico.foto,
        direccion_consultorio: medico.direccion_consultorio
      }
    });

  } catch (error) {
    console.error("Error en POST /medicos/login:", error);
    res.status(500).json({ error: "Error al intentar iniciar sesión" });
  }
});

export default router;
