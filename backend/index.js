import express from "express";
import cors from "cors";
import dotenv from "dotenv";
import bodyParser from "body-parser";
import estudiantesRoutes from "./routes/estudiantes.routes.js"; // Ruta para estudiantes

dotenv.config();

const app = express();

// Middleware
app.use(cors());
app.use(bodyParser.json());

// Rutas
app.use("/api/estudiantes", estudiantesRoutes);

const PORT = process.env.PORT || 5000;
app.listen(PORT, () => {
  console.log(`🚀 Servidor corriendo en http://localhost:${PORT}`);
});
