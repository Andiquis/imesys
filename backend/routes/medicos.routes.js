// routes/medicos.routes.js
import { Router } from "express";
import pool from "../config/db.js";
import bcrypt from 'bcryptjs';
import multer from 'multer';
import path from 'path';
import validator from 'validator';

const router = Router();
const saltRounds = 10;


// ======================
// RUTA DE LOGIN PARA MÉDICOS
// ======================
router.post("/login", async (req, res) => {
  const { correo, contrasena } = req.body;

  if (!correo || !contrasena) {
    return res.status(400).json({ error: "Correo y contraseña son requeridos" });
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



// Configuración Multer para médicos
const storageMedicos = multer.diskStorage({
  destination: (req, file, cb) => {
    cb(null, './uploads/medicos');
  },
  filename: (req, file, cb) => {
    cb(null, `${Date.now()}-${Math.round(Math.random() * 1E9)}${path.extname(file.originalname)}`);
  }
});

const uploadMedico = multer({
  storage: storageMedicos,
  limits: { fileSize: 5 * 1024 * 1024 } // Límite de 5MB
});

// ======================
// RUTAS PARA ESPECIALIDADES
// ======================
router.get("/especialidades", async (req, res) => {
  try {
    const [rows] = await pool.query("SELECT * FROM especialidades");
    res.json(rows);
  } catch (error) {
    console.error("Error en GET /especialidades:", error);
    res.status(500).json({ error: "Error al obtener especialidades" });
  }
});

router.post("/especialidades", async (req, res) => {
  const { nombre_especialidad } = req.body;

  if (!nombre_especialidad) {
    return res.status(400).json({ error: "Nombre de especialidad requerido" });
  }

  try {
    const [result] = await pool.query(
      "INSERT INTO especialidades (nombre_especialidad) VALUES (?)",
      [nombre_especialidad]
    );

    res.status(201).json({
      id_especialidad: result.insertId,
      nombre_especialidad
    });
  } catch (error) {
    console.error("Error en POST /especialidades:", error);

    if (error.code === "ER_DUP_ENTRY") {
      return res.status(409).json({ error: "La especialidad ya existe" });
    }

    res.status(500).json({ error: "Error al crear especialidad" });
  }
});

// ======================
// RUTAS PARA MÉDICOS
// ======================
router.post("/", uploadMedico.single('foto'), async (req, res) => {
  const medicoData = req.body;
  const foto = req.file ? `/uploads/medicos/${req.file.filename}` : null;

  const requiredFields = ['nombre', 'apellido', 'correo', 'contrasena', 'id_especialidad', 'numero_colegiatura', 'telefono', 'direccion_consultorio'];
  const missingFields = requiredFields.filter(field => !medicoData[field]);

  if (missingFields.length > 0) {
    return res.status(400).json({
      error: `Campos obligatorios faltantes: ${missingFields.join(', ')}`
    });
  }

  if (!validator.isEmail(medicoData.correo)) {
    return res.status(400).json({ error: "Correo electrónico no válido" });
  }

  try {
    const [especialidad] = await pool.query(
      "SELECT id_especialidad FROM especialidades WHERE id_especialidad = ?",
      [medicoData.id_especialidad]
    );

    if (especialidad.length === 0) {
      return res.status(400).json({ error: "Especialidad no válida" });
    }

    const hashedPassword = await bcrypt.hash(medicoData.contrasena, saltRounds);

    const [result] = await pool.query(
      "INSERT INTO medicos SET ?",
      {
        ...medicoData,
        contrasena: hashedPassword,
        foto
      }
    );

    res.status(201).json({
      id_medico: result.insertId,
      ...medicoData,
      foto,
      contrasena: undefined
    });

  } catch (error) {
    console.error("Error en POST /medicos:", error);

    if (error.code === "ER_DUP_ENTRY") {
      const field = error.message.includes('correo') ? 'correo' : 'número de colegiatura';
      return res.status(409).json({ error: `${field} ya registrado` });
    }

    res.status(500).json({ error: "Error al registrar médico" });
  }
});


router.get("/", async (req, res) => {
  try {
    const [rows] = await pool.query(`
      SELECT m.*, e.nombre_especialidad 
      FROM medicos m
      INNER JOIN especialidades e ON m.id_especialidad = e.id_especialidad
    `);

    const medicos = rows.map(medico => ({
      ...medico,
      foto: medico.foto ? `${req.protocol}://${req.get('host')}${medico.foto}` : null
    }));

    res.json(medicos);
  } catch (error) {
    console.error("Error en GET /medicos:", error);
    res.status(500).json({ error: "Error al obtener médicos" });
  }
});

router.get("/:id", async (req, res) => {
  const { id } = req.params;

  try {
    const [rows] = await pool.query(`
      SELECT m.*, e.nombre_especialidad 
      FROM medicos m
      INNER JOIN especialidades e ON m.id_especialidad = e.id_especialidad
      WHERE m.id_medico = ?
    `, [id]);

    if (rows.length === 0) {
      return res.status(404).json({ error: "Médico no encontrado" });
    }

    const medico = rows[0];
    medico.foto = medico.foto ? `${req.protocol}://${req.get('host')}${medico.foto}` : null;

    res.json(medico);
  } catch (error) {
    console.error("Error en GET /medicos/:id:", error);
    res.status(500).json({ error: "Error al obtener médico" });
  }
});

router.put("/:id", uploadMedico.single('foto'), async (req, res) => {
  const { id } = req.params;
  const updates = req.body;
  const foto = req.file ? `/uploads/medicos/${req.file.filename}` : null;

  try {
    const updateData = { ...updates };
    if (foto) updateData.foto = foto;

    if (updateData.contrasena) {
      updateData.contrasena = await bcrypt.hash(updateData.contrasena, saltRounds);
    }

    if (updateData.correo && !validator.isEmail(updateData.correo)) {
      return res.status(400).json({ error: "Correo electrónico no válido" });
    }

    const [result] = await pool.query(
      "UPDATE medicos SET ? WHERE id_medico = ?",
      [updateData, id]
    );

    if (result.affectedRows === 0) {
      return res.status(404).json({ error: "Médico no encontrado" });
    }

    res.json({ 
      message: "Médico actualizado",
      foto: foto ? `${req.protocol}://${req.get('host')}${foto}` : null
    });

  } catch (error) {
    console.error("Error en PUT /medicos:", error);

    if (error.code === "ER_DUP_ENTRY") {
      const field = error.message.includes('correo') ? 'correo' : 'número de colegiatura';
      return res.status(409).json({ error: `${field} ya registrado` });
    }

    res.status(500).json({ error: "Error al actualizar médico" });
  }
});

router.delete("/:id", async (req, res) => {
  const { id } = req.params;

  try {
    const [result] = await pool.query(
      "DELETE FROM medicos WHERE id_medico = ?",
      [id]
    );

    if (result.affectedRows === 0) {
      return res.status(404).json({ error: "Médico no encontrado" });
    }

    res.json({ message: "Médico eliminado correctamente" });
  } catch (error) {
    console.error("Error en DELETE /medicos:", error);
    res.status(500).json({ error: "Error al eliminar médico" });
  }
});

export default router;



/*
🩺 1. RUTAS PARA ESPECIALIDADES
✅ GET /especialidades
Acción: Obtener todas las especialidades médicas disponibles.
Uso típico: Llenar un select en un formulario al registrar o editar un médico.

✅ POST /especialidades
Acción: Crear una nueva especialidad médica.
Requiere: Campo nombre_especialidad en el body.
Validación: Evita duplicados (nombre de especialidad ya existente).





👨‍⚕️ 2. RUTAS PARA MÉDICOS
✅ POST /
Acción: Registrar un nuevo médico.
Requiere campos obligatorios:
nombre, apellido, correo, contrasena, id_especialidad, numero_colegiatura
Opcional: foto (subida con multer)
Validación incluida:
Campos requeridos
Correo electrónico válido (validator)
Verifica que la especialidad exista
Hashea la contraseña (bcrypt)
Previene duplicados (correo o colegiatura ya registrados)

✅ GET /
Acción: Obtener todos los médicos registrados.
Incluye:
Datos personales del médico
El nombre de su especialidad (JOIN con tabla especialidades)
URL completa de la foto (si existe)

✅ GET /:id
Acción: Obtener un médico por su id_medico.
Incluye:
Datos personales
Especialidad
Foto completa
Validación: Retorna 404 si no lo encuentra.

✅ PUT /:id
Acción: Actualizar los datos de un médico existente.
Opcional:
Puedes actualizar todos los campos, incluida la foto y la contraseña.
Validaciones:
Valida email si lo modificas
Hashea nueva contraseña si se cambia
Previene duplicados (correo o colegiatura)
Foto nueva: Se guarda en /uploads/medicos/

✅ DELETE /:id
Acción: Eliminar un médico por su ID.
Respuesta: Mensaje de éxito o error si no existe.

🧠 FUNCIONALIDADES EXTRA INCLUIDAS
✔️ Validación de correo electrónico (validator)
✔️ Hash de contraseñas (bcryptjs)
✔️ Manejo de archivos/fotos (multer)
✔️ Relaciones entre tablas (INNER JOIN con especialidades)
✔️ Validación de campos requeridos y duplicados
✔️ URLs completas para fotos

¿Qué podrías agregar a este sistema?
🔒 Login médico con JWT
🔍 Filtro de búsqueda por nombre/correo
📄 Paginación o límite de resultados
📁 Descarga de CV o documento profesional
📊 Estadísticas (cuántos médicos por especialidad)
*/
