import express from "express";
import axios from "axios";

const router = express.Router();

const API_KEY = process.env.GEMINI_API_KEY;
const GEMINI_URL = "https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-pro-latest:generateContent";

router.post("/", async (req, res) => {
    const { message } = req.body;

    if (!message) {
        return res.status(400).json({ error: "El mensaje es requerido." });
    }

    try {
        const response = await axios.post(`${GEMINI_URL}?key=${API_KEY}`, {
            contents: [{ parts: [{ text: message }] }]
        });

        const reply = response.data.candidates?.[0]?.content?.parts?.[0]?.text || "No se pudo generar una respuesta.";
        res.json({ response: reply });

    } catch (error) {
        console.error("Error en la API de Gemini:", error);
        res.status(500).json({ error: "Ocurrió un error al procesar la solicitud." });
    }
});

export default router;
