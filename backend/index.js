import express from "express";
import cors from "cors";
import dotenv from "dotenv";
import bodyParser from "body-parser";
import path from "path";
import { exec } from "child_process";
import { fileURLToPath } from "url";

// Configuración inicial
dotenv.config();
const app = express();

// Middleware
app.use(cors());
app.use(bodyParser.json());
app.use(cors({
  origin: ["http://localhost:4200", "http://localhost:3000"], // Permitir frontend de Angular y la página HTML local
  credentials: true
}));

// Archivos estáticos
const __filename = fileURLToPath(import.meta.url);
const __dirname = path.dirname(__filename);
app.use('/uploads', express.static(path.join(__dirname, 'uploads')));

// Rutas importadas
import loginUsuarios from "./routes/usuarios.routes.js";
import loginMedicos from "./routes/loginMedicos.routes.js";
import datosBio from "./routes/datosBio.routes.js";
import ollamaBot from './routes/ollamabot.routes.js';
import chatBot from './routes/geminibot.routes.js'; // Importar la ruta del chatbot
import prediccionBio from './routes/prediccionBio.routes.js'; // Importar la ruta de predicción de neumonía

import especialistasRoutes from './routes/especialistas.routes.js'; // Importar la ruta de especialistas
import reservarCitaRoutes from "./routes/reservar-cita.routes.js";
//import modelRoutes from './routes/model.routes.js'; // Importar la ruta de predicción de neumonía

// Rutas API
app.get("/", (req, res) => {
  res.send("Servidor de API Gemini funcionando correctamente");
});
app.use("/api/usuarios", loginUsuarios);
app.use("/api/login_medicos", loginMedicos);
app.use("/api/datos_biometricos", datosBio);
app.use("/api/ollamabot", ollamaBot);
app.use("/api/chatbot", chatBot); // Usar la ruta del chatbot de Gemini
app.use("/api/prediccion_bio", prediccionBio); // Usar la ruta de predicción de neumonía
app.use("/api/especialistas", especialistasRoutes); // Usar la ruta de especialistas
app.use("/api/reservar-cita", reservarCitaRoutes); // New route
//app.use("/api/predict", modelRoutes); // Usar la ruta de predicción de neumonía

// Función asincrónica para ejecutar comandos individualmente
function runCommandAsync(command, label) {
  return new Promise((resolve, reject) => {
    console.log(`\n🔧 Iniciando "${label}" con el comando:\n> ${command}\n`);

    const subprocess = exec(command, { shell: true });

    subprocess.stdout.on("data", (data) => {
      process.stdout.write(`📥 [${label}] stdout: ${data}`);
    });

    subprocess.stderr.on("data", (data) => {
      process.stderr.write(`⚠️  [${label}] stderr: ${data}`);
    });

    subprocess.on("close", (code) => {
      if (code === 0) {
        console.log(`✅ [${label}] finalizó correctamente con código ${code}.\n`);
        resolve();
      } else {
        console.error(`❌ [${label}] terminó con errores. Código de salida: ${code}\n`);
        reject(new Error(`Error en ${label}. Código: ${code}`));
      }
    });
  });
}

// Arranque del servidor principal
const PORT = process.env.PORT || 5000;
app.listen(PORT, () => {
  console.log(`🚀 Servidor Express corriendo en http://localhost:${PORT}`);

  // Lanzamiento de servicios externos asincrónicamente
  launchModules();
});

// Función para lanzar los servicios externos en paralelo
async function launchModules() {
  const modules = [
    {
      command: "python3.10 -m uvicorn main:app --host 0.0.0.0 --port 8000 --reload",
      label: "Servidor de análisis de neumonía"
    },
    /*{
      command: "python3.10 -m uvicorn main_chat_ia:app --host 0.0.0.0 --port 8001 --reload",
      label: "Servidor de chatbot online"
    },*/
    {
      command: "ollama run llama3",
      label: "Servidor Ollama"
    }
  ];

  for (const mod of modules) {
    // Ejecutar de forma independiente sin detener el resto si uno falla
    runCommandAsync(mod.command, mod.label).catch((err) =>
      console.error(`🛑 Falló el módulo "${mod.label}":`, err.message)
    );
  }
}
