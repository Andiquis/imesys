// routes/biometricAnalysis.routes.js
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

/*const GEMINI_URL = "https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-pro-latest:generateContent";*/
const GEMINI_URL = "https://generativelanguage.googleapis.com/v1beta/models/gemini-2.0-flash:generateContent";


class BiometricAnalyzer {
  async analyzeBiometrics(biometricData) {
    try {
      // Construir el prompt específico para análisis biométrico
      const prompt = `Actúa como un sistema de análisis de salud predictivo basado en datos biométricos. 
      Analiza los siguientes datos biométricos del paciente y proporciona una evaluación breve, directa y clara 
      sin rodeos ni explicaciones extensas. Enfócate en posibles condiciones de salud, riesgos e indicadores clave.
      Mantén tu respuesta corta, con máximo 3-4 oraciones concisas.

      Datos biométricos del paciente:
      - Peso: ${biometricData.peso || 'No proporcionado'} kg
      - Altura: ${biometricData.altura || 'No proporcionada'} cm
      - Presión arterial: ${biometricData.presion_arterial || 'No proporcionada'} mmHg
      - Frecuencia cardíaca: ${biometricData.frecuencia_cardiaca || 'No proporcionada'} bpm
      - Nivel de glucosa: ${biometricData.nivel_glucosa || 'No proporcionado'} mg/dL

      Basado únicamente en estos datos, proporciona:
      1. Evaluación breve del estado general de salud
      2. Posibles riesgos o condiciones a vigilar
      3. Una recomendación simple si es necesaria`;

      const response = await axios.post(`${GEMINI_URL}?key=${API_KEY}`, {
        contents: [{ parts: [{ text: prompt }] }],
        generationConfig: {
          temperature: 0.2, // Temperatura baja para respuestas más precisas/predecibles
          topP: 0.8,
          topK: 40,
          maxOutputTokens: 150, // Limitado para forzar respuestas cortas
        },
        safetySettings: [
          { category: "HARM_CATEGORY_HARASSMENT", threshold: "BLOCK_NONE" },
          { category: "HARM_CATEGORY_HATE_SPEECH", threshold: "BLOCK_NONE" },
          { category: "HARM_CATEGORY_SEXUALLY_EXPLICIT", threshold: "BLOCK_NONE" },
          { category: "HARM_CATEGORY_DANGEROUS_CONTENT", threshold: "BLOCK_NONE" }
        ]
      });

      return response.data.candidates?.[0]?.content?.parts?.[0]?.text || "No se pudo generar un análisis con los datos proporcionados.";
    } catch (error) {
      console.error("Error en análisis biométrico:", error.response?.data || error.message);
      return "Ocurrió un error al analizar los datos biométricos.";
    }
  }
}

const biometricAnalyzer = new BiometricAnalyzer();

// Ruta para verificar el estado del servicio
router.get("/", (req, res) => {
  res.json({ message: "API de Análisis Biométrico con Gemini está funcionando." });
});

// Ruta principal para analizar datos biométricos
router.post("/", async (req, res) => {
  const { message } = req.body;
  
  // Si el mensaje no es un objeto, tratar de parsearlo
  let biometricData;
  
  try {
    if (typeof message === 'string') {
      try {
        biometricData = JSON.parse(message);
      } catch (e) {
        // Si no se puede parsear como JSON, intentar parsearlo como string con formato
        const dataLines = message.split(',').map(line => line.trim());
        biometricData = {};
        
        dataLines.forEach(line => {
          if (line.includes('peso')) {
            biometricData.peso = line.split(':')[1]?.trim() || line;
          } else if (line.includes('altura')) {
            biometricData.altura = line.split(':')[1]?.trim() || line;
          } else if (line.includes('presion')) {
            biometricData.presion_arterial = line.split(':')[1]?.trim() || line;
          } else if (line.includes('frecuencia')) {
            biometricData.frecuencia_cardiaca = line.split(':')[1]?.trim() || line;
          } else if (line.includes('glucosa')) {
            biometricData.nivel_glucosa = line.split(':')[1]?.trim() || line;
          }
        });
      }
    } else if (typeof message === 'object') {
      biometricData = message;
    } else {
      // Si llegan directamente los datos en el body
      biometricData = req.body;
    }

    // Verificar que tenemos al menos algunos datos
    if (!biometricData || Object.keys(biometricData).length === 0) {
      return res.status(400).json({ error: "No se han proporcionado datos biométricos válidos." });
    }

    const analysis = await biometricAnalyzer.analyzeBiometrics(biometricData);
    res.json({ response: analysis });
  } catch (error) {
    console.error("Error al procesar la solicitud:", error);
    res.status(500).json({ error: "Ocurrió un error al procesar los datos biométricos." });
  }
});

export default router;