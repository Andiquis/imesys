// routes/buscador.routes.js
import { Router } from "express";
import axios from "axios";

const router = Router();

// Configuración de la API de Google Custom Search
const API_KEY = 'AIzaSyC410TATtejgU633r2ummEkH2TP-Nx9lyE';
const SEARCH_ENGINE_ID = 'f05c3413b666740ee';

// Endpoint de búsqueda
router.post('/', async (req, res) => {
  const { query } = req.body;

  if (!query || !query.trim()) {
    return res.status(400).json({ error: 'La consulta está vacía' });
  }

  try {
    const url = `https://www.googleapis.com/customsearch/v1?q=${encodeURIComponent(query)}&key=${API_KEY}&cx=${SEARCH_ENGINE_ID}`;
    const response = await axios.get(url);
    const data = response.data;

    if (data.items) {
      // Limitar a 5 resultados
      const results = data.items.slice(0, 5).map(item => ({
        title: item.title,
        snippet: item.snippet,
        link: item.link
      }));
      res.json(results);
    } else {
      res.json([]);  // Devolver array vacío para mantener consistencia
    }
  } catch (error) {
    console.error('Error en la búsqueda:', error.message);
    res.status(500).json({ error: 'Error al realizar la búsqueda. Por favor, intenta nuevamente.' });
  }
});

export default router;