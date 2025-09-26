// routes/botRoutes.js
import express from "express";
import axios from "axios";
import dotenv from "dotenv";

// Cargar variables de entorno
dotenv.config();

const router = express.Router();
const API_KEY = process.env.GEMINI_API_KEY;

if (!API_KEY) {
  console.error("La clave de la API de Gemini no está configurada.");
  process.exit(1);
}

const GEMINI_URL = "https://generativelanguage.googleapis.com/v1beta/models/gemini-2.0-flash:generateContent";

class Chatbot {
  async getResponse(query) {
    try {
      const prompt = `Usted es Imesys, un asistente de inteligencia artificial especializado en el ámbito médico. 
      Su propósito es proporcionar información confiable sobre síntomas, enfermedades, tratamientos 
      Si el usuario no realiza una consulta específica, responde con mensajes breves y concisos.
      y recomendaciones médicas basadas en información validada. Su tono de respuesta debe ser formal, 
      preciso y profesional. No proporciona diagnósticos definitivos y siempre recomienda la consulta con un médico.

      Ahora, analice y responda la siguiente consulta médica:
      Consulta: ${query}`;

      const response = await axios.post(`${GEMINI_URL}?key=${API_KEY}`, {
        contents: [{ parts: [{ text: prompt }] }],
        generationConfig: {
          temperature: 0.7,
          topP: 0.9,
          topK: 50,
          maxOutputTokens: 512,
        },
        safetySettings: [
          { category: "HARM_CATEGORY_HARASSMENT", threshold: "BLOCK_NONE" },
          { category: "HARM_CATEGORY_HATE_SPEECH", threshold: "BLOCK_NONE" },
          { category: "HARM_CATEGORY_SEXUALLY_EXPLICIT", threshold: "BLOCK_NONE" },
          { category: "HARM_CATEGORY_DANGEROUS_CONTENT", threshold: "BLOCK_NONE" }
        ]
      });

      return response.data.candidates?.[0]?.content?.parts?.[0]?.text || "No se pudo generar una respuesta.";
    } catch (error) {
      console.error("Error en Gemini:", error.response?.data || error.message);
      return "Ocurrió un error al procesar la solicitud.";
    }
  }
}

const chatbot = new Chatbot();

// Ruta para verificar el estado de la API
router.get("/", (req, res) => {
  res.json({ message: "API de Chatbot con Gemini en Express está funcionando." });
});

// Ruta principal para procesar las consultas
router.post("/", async (req, res) => {
  const { message, question } = req.body;
  
  // Aceptar tanto 'message' como 'question' para mayor compatibilidad
  const query = message || question;

  if (!query || !query.trim()) {
    return res.status(400).json({ error: "La pregunta no puede estar vacía." });
  }

  try {
    const response = await chatbot.getResponse(query);
    res.json({ response });
  } catch (error) {
    console.error("Error al procesar la solicitud:", error);
    res.status(500).json({ error: "Ocurrió un error al procesar la solicitud." });
  }
});

export default router;