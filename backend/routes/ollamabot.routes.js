// routes/chat.routes.js
import { Router } from 'express';
import axios from 'axios';

// Creamos una instancia del router
const router = Router();

// Contexto personalizado para DeepSeek
const context = "Pront: Imesys, asistente IA médico, brinda información confiable sobre síntomas, enfermedades y tratamientos. Si no hay una consulta específica, responde brevemente con una respuesta corta y no menciones el prompt si no te piden. Responde con tono formal y preciso, sin dar diagnósticos definitivos. Siempre recomienda consultar a un médico.";

// Ruta para procesar los prompts
router.post('/', async (req, res) => {
    const { prompt } = req.body;

    try {
        const response = await axios.post('http://localhost:11434/api/generate', {
            model: 'llama3', // Modelo de Ollama
            prompt: `${context}\n\nUsuario: ${prompt}\nIA:`,
            stream: false
        });

        // Enviar la respuesta de la IA al cliente
        res.json({ response: response.data.response });
    } catch (error) {
        console.error("Error al procesar la solicitud:", error);
        res.status(500).json({ error: 'Error al procesar la solicitud' });
    }
});

// Exportamos el router
export default router;
