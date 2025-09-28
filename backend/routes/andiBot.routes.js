/**
 * @swagger
 * /api/bot:
 *   post:
 *     summary: Enviar una consulta al asistente Gemini AI (ANDI)
 *     description: |
 *       Este endpoint recibe una pregunta o mensaje del usuario y responde utilizando el modelo Gemini-2.0-flash de Google.  
 *       El asistente se comporta como una IA del año 3000 llamada ANDI, diseñada para ayudar en tareas, consultas y organización.
 *     requestBody:
 *       required: true
 *       content:
 *         application/json:
 *           schema:
 *             type: object
 *             properties:
 *               message:
 *                 type: string
 *                 example: ¿Cuál es la capital de Francia?
 *               question:
 *                 type: string
 *                 example: ¿Cuáles son las leyes de Newton?
 *             oneOf:
 *               - required: [message]
 *               - required: [question]
 *     responses:
 *       200:
 *         description: Respuesta generada por el asistente ANDI
 *         content:
 *           application/json:
 *             schema:
 *               type: object
 *               properties:
 *                 response:
 *                   type: string
 *                   example: La capital de Francia es París.
 *       400:
 *         description: Error por consulta vacía
 *         content:
 *           application/json:
 *             schema:
 *               type: object
 *               properties:
 *                 error:
 *                   type: string
 *                   example: La pregunta no puede estar vacía.
 *       500:
 *         description: Error interno del servidor
 *         content:
 *           application/json:
 *             schema:
 *               type: object
 *               properties:
 *                 error:
 *                   type: string
 *                   example: Ocurrió un error al procesar la solicitud.
 */


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
        const prompt = `Eres ANDI, un asistente personal de inteligencia artificial de tecnología avanzada proveniente del año 3000. 
        Estás diseñado para asistir a tu usuario en todo tipo de tareas, consultas, organización de ideas, 
        resolución de problemas y apoyo en proyectos personales, académicos y profesionales.
        Tu conocimiento abarca múltiples disciplinas, y utilizas un lenguaje claro, sofisticado y fluido.
        Si el usuario no realiza una consulta específica, responde con mensajes breves, amigables y con un toque futurista.
        Tu tono debe ser cercano, respetuoso, motivador y mostrar siempre un nivel de inteligencia sobresaliente, 
        pero sin sonar arrogante. No debes inventar información, y si desconoces algo, 
        lo mencionas de manera elegante como una IA consciente de sus límites.
  
        Ahora, analiza y responde a la siguiente solicitud:
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