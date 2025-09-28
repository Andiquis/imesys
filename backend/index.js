//index.js

import express from "express";
import cors from "cors";
import dotenv from "dotenv";
import bodyParser from "body-parser";
import path from "path";
import { exec } from "child_process";
import { fileURLToPath } from "url";
import swaggerUi from 'swagger-ui-express';
import swaggerSpec from './config/swagger.js';


// Configuración inicial
dotenv.config();
const app = express();

// Middleware
app.use('/api-docs', swaggerUi.serve, swaggerUi.setup(swaggerSpec));
app.use(cors());
app.use(bodyParser.json());
/*app.use(cors({
  origin: ["http://localhost:4200", "http://localhost:3000"], // Permitir frontend de Angular y la página HTML local
  credentials: true
}));
app.use(cors({
  origin: '*', // Permite solicitudes desde cualquier origen
  credentials: true
}));
*/
/*app.use(cors());*/ // Esto permitirá todas las solicitudes sin restricciones de origen
app.use(cors({
  origin: '*', // O el dominio de tu frontend
  methods: ['GET', 'POST', 'PUT', 'DELETE'],
  allowedHeaders: ['Content-Type', 'Authorization']
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
import andiBot from './routes/andiBot.routes.js'; // Importar la ruta del chatbot ANDI
import especialistasRoutes from './routes/especialistas.routes.js'; // Importar la ruta de especialistas
import reservarCitaRoutes from "./routes/reservar-cita.routes.js";
import agendarCitaRoutes from "./routes/agenda-citas.routes.js"; // Importar la ruta de agendar cita
import buscadorRoutes from './routes/buscador.routes.js'; // Importar la ruta del buscador
import calificacionRoutes from './routes/calificacion.routes.js'; // Importar la ruta de calificaciones
//import modelRoutes from './routes/model.routes.js'; // Importar la ruta de predicción de neumonía

//////////////////////
// Modulo de medicos
/////////////////////
import misPacientesRoutes from "./routes/medicos/mis.pacientes.routes.js"; // Importar la ruta de mis pacientes
import miHistorialRoutes from "./routes/usuarios/mi.historial.routes.js"; // Importar la ruta del historial médico
import neumoniaRoutes from "./routes/medicos/neumonia.routes.js"; // Importar la ruta de neumonía




// Ruta de inicio
//app.get("/", (req, res) => {res.send("Servidor API de Imesys funcionando correctamente");});





 // Rutas de la API
app.use("/api/usuarios", loginUsuarios);
app.use("/api/login_medicos", loginMedicos);
app.use("/api/datos_biometricos", datosBio);
app.use("/api/ollamabot", ollamaBot);
app.use("/api/chatbot", chatBot); // Usar la ruta del chatbot de Gemini
app.use("/api/prediccion_bio", prediccionBio); // Usar la ruta de predicción de neumonía
app.use("/api/andibot", andiBot); // Usar la ruta del chatbot ANDI
app.use("/api/especialistas", especialistasRoutes); // Usar la ruta de especialistas
app.use("/api/reservar-cita", reservarCitaRoutes); // New route
app.use("/api/agenda-citas", agendarCitaRoutes); // Usar la ruta de agendar cita
app.use("/api/buscador", buscadorRoutes); // Usar la ruta del buscador
app.use("/api/rating", calificacionRoutes); // Usar la ruta de calificaciones
//app.use("/api/predict", modelRoutes); // Usar la ruta de predicción de neumonía

//////////////////////
// Modulo de medicos
/////////////////////
app.use("/api/mis-pacientes", misPacientesRoutes); // Usar la ruta de mis pacientes
app.use("/api/mi-historial", miHistorialRoutes); // Usar la ruta del historial médico
app.use("/api/neumonia", neumoniaRoutes); // Usar la ruta de neumonía




// Servir archivos estáticos de Angular en la raíz
app.use(express.static(path.join(__dirname, '../frontend/dist/frontend/browser')));

// Redirigir todas las rutas (SPA) a index.html

app.get('*', (req, res) => {
  res.sendFile(path.join(__dirname, '../frontend/dist/frontend/browser/index.html'));
});




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
