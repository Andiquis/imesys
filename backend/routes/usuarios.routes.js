import { Router } from "express";
import pool from "../config/db.js";
import bcrypt from 'bcryptjs';
import multer from 'multer';
import path from 'path';

const router = Router();
const saltRounds = 10; // Número de rondas para el salt de bcrypt

// Configuración de almacenamiento de multer
const storage = multer.diskStorage({
  destination: (req, file, cb) => {
    cb(null, './uploads');
  },
  filename: (req, file, cb) => {
    cb(null, Date.now() + path.extname(file.originalname));
  }
});

const upload = multer({ storage: storage });

// Ruta para el login
router.post("/login", async (req, res) => {
  const { correo, contrasena } = req.body;

  if (!correo || !contrasena) {
    return res.status(400).json({ error: "Correo y contraseña son obligatorios" });
  }

  try {
    const [rows] = await pool.query(
      "SELECT id_usuario, nombre, apellido, correo, contrasena, foto FROM usuarios WHERE correo = ?",
      [correo]
    );

    if (rows.length === 0) {
      return res.status(401).json({ error: "Credenciales incorrectas" });
    }

    const user = rows[0];
    const isPasswordCorrect = await bcrypt.compare(contrasena, user.contrasena);

    if (!isPasswordCorrect) {
      return res.status(401).json({ error: "Credenciales incorrectas" });
    }

    res.json({
      id_usuario: user.id_usuario,
      nombre: user.nombre,
      apellido: user.apellido,
      correo: user.correo,
      foto: user.foto // Añadir la propiedad `foto`
    });
  } catch (error) {
    console.error(error);
    res.status(500).json({ error: "Error durante el inicio de sesión" });
  }
});

// Obtener todos los usuarios
router.get("/", async (req, res) => {
  try {
    const [rows] = await pool.query(
      "SELECT id_usuario, nombre, apellido, correo, telefono, direccion, fecha_nacimiento, genero, foto, fecha_registro FROM usuarios"
    );
    res.json(rows);
  } catch (error) {
    console.error(error);
    res.status(500).json({ error: "Error al obtener usuarios" });
  }
});

// Obtener un usuario por ID
router.get("/:id", async (req, res) => {
  const { id } = req.params;
  try {
    const [rows] = await pool.query(
      "SELECT id_usuario, nombre, apellido, correo, telefono, direccion, fecha_nacimiento, genero, foto, fecha_registro FROM usuarios WHERE id_usuario = ?",
      [id]
    );

    if (rows.length === 0) {
      return res.status(404).json({ error: "Usuario no encontrado" });
    }

    res.json(rows[0]);
  } catch (error) {
    console.error(error);
    res.status(500).json({ error: "Error al obtener usuario" });
  }
});
// Crear un nuevo usuario con foto (CON HASH DE CONTRASEÑA)
router.post("/", upload.single('foto'), async (req, res) => {
  const { nombre, apellido, correo, contrasena, telefono, direccion, fecha_nacimiento, genero } = req.body;
  const foto = req.file ? `uploads/${req.file.filename}` : null; // Construir la ruta aquí

  if (!nombre || !apellido || !correo || !contrasena) {
    return res.status(400).json({ error: "Faltan campos obligatorios" });
  }

  try {
    // Hash de la contraseña antes de guardarla
    const contrasenaHasheada = await bcrypt.hash(contrasena, saltRounds);

    // Insertar el usuario en la base de datos
    const [result] = await pool.query(
      "INSERT INTO usuarios (nombre, apellido, correo, contrasena, telefono, direccion, fecha_nacimiento, genero, foto) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)",
      [nombre, apellido, correo, contrasenaHasheada, telefono, direccion, fecha_nacimiento, genero, foto]
    );

    // Respuesta al cliente
    res.status(201).json({
      id_usuario: result.insertId,
      nombre,
      apellido,
      correo,
      telefono,
      direccion,
      fecha_nacimiento,
      genero,
      foto: foto // Usar la misma ruta que se guardó en la base de datos
    });
  } catch (error) {
    console.error(error);

    if (error.code === "ER_DUP_ENTRY") {
      return res.status(400).json({ error: "El correo electrónico ya está registrado" });
    }

    res.status(500).json({ error: "Error al crear usuario" });
  }
});
// Actualizar un usuario (CON HASH DE CONTRASEÑA SI SE PROVEE)
router.put("/:id", upload.single('foto'), async (req, res) => {
  const { id } = req.params;
  const { nombre, apellido, correo, telefono, direccion, fecha_nacimiento, genero, contrasena } = req.body;
  const foto = req.file ? req.file.filename : null;

  try {
    let query;
    let params;
    
    if (contrasena) {
      // Si se proporciona contraseña, la hasheamos
      const contrasenaHasheada = await bcrypt.hash(contrasena, saltRounds);
      query = "UPDATE usuarios SET nombre = ?, apellido = ?, correo = ?, contrasena = ?, telefono = ?, direccion = ?, fecha_nacimiento = ?, genero = ?, foto = ? WHERE id_usuario = ?";
      params = [nombre, apellido, correo, contrasenaHasheada, telefono, direccion, fecha_nacimiento, genero, foto, id];
    } else {
      query = "UPDATE usuarios SET nombre = ?, apellido = ?, correo = ?, telefono = ?, direccion = ?, fecha_nacimiento = ?, genero = ?, foto = ? WHERE id_usuario = ?";
      params = [nombre, apellido, correo, telefono, direccion, fecha_nacimiento, genero, foto, id];
    }

    const [result] = await pool.query(query, params);

    if (result.affectedRows === 0) {
      return res.status(404).json({ error: "Usuario no encontrado" });
    }

    res.json({
      message: "Usuario actualizado correctamente",
      foto: foto ? `../uploads/perfilUser/${foto}` : null // Incluir la nueva URL de la foto
    });
  } catch (error) {
    console.error(error);

    if (error.code === "ER_DUP_ENTRY") {
      return res.status(400).json({ error: "El correo electrónico ya está registrado" });
    }

    res.status(500).json({ error: "Error al actualizar usuario" });
  }
});

// Eliminar un usuario
router.delete("/:id", async (req, res) => {
  const { id } = req.params;

  try {
    const [result] = await pool.query("DELETE FROM usuarios WHERE id_usuario = ?", [id]);

    if (result.affectedRows === 0) {
      return res.status(404).json({ error: "Usuario no encontrado" });
    }

    res.json({ message: "Usuario eliminado correctamente" });
  } catch (error) {
    console.error(error);
    res.status(500).json({ error: "Error al eliminar usuario" });
  }
});

export default router;