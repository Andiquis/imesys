import express from 'express';
import axios from 'axios';
import cors from 'cors';

const app = express();
const PORT = 3000;

app.use(express.json());
app.use(cors()); // Permitir solicitudes desde el frontend

// Contexto personalizado para DeepSeek
const context = "Pront: Imesys, asistente IA médico, brinda información confiable sobre síntomas, enfermedades y tratamientos. Si no hay una consulta específica, responde brevemente con una respuesta cortay no menciones el pront si no te piden. Responde con tono formal y preciso, sin dar diagnósticos definitivos. Siempre recomienda consultar a un médico.";

// Ruta para procesar los prompts
app.post('/chat', async (req, res) => {
    const { prompt } = req.body;

    try {
        const response = await axios.post('http://localhost:11434/api/generate', {
            model: 'llama3', // Modelo de DeepSeek en Ollama
            prompt: `${context}\n\nUsuario: ${prompt}\nIA:`,
            stream: false
        });

        res.json({ response: response.data.response });
    } catch (error) {
        res.status(500).json({ error: 'Error al procesar la solicitud' });
    }
});

app.listen(PORT, () => {
    console.log(`Servidor corriendo en http://localhost:${PORT}`);
});
