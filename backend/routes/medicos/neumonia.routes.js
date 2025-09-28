import { Router } from "express";
import axios from "axios";
import multer from "multer";
import fs from "fs";
import FormData from "form-data";

const router = Router();

// Configuración de multer para subir archivos temporalmente
const upload = multer({ dest: "uploads/" });
router.post("/", upload.single("image"), async (req, res) => {
  if (!req.file) {
    return res.status(400).json({ error: "No se envió ninguna imagen." });
  }

  const imagePath = req.file.path;

  try {
    const imageStream = fs.createReadStream(imagePath);
    const form = new FormData();
    form.append("file", imageStream, req.file.originalname); // debe coincidir con `file` en FastAPI

    const response = await axios.post("http://127.0.0.1:8000/predict/", form, {
      headers: form.getHeaders()
    });

    res.json(response.data);
  } catch (error) {
    console.error("Error:", error.message);
    res.status(500).json({
      error: "Error al conectarse con FastAPI",
      detail: error.message
    });
  } finally {
    // Siempre eliminar archivo temporal
    fs.unlinkSync(imagePath);
  }
});


export default router;
