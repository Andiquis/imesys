import { exec } from "child_process";

// Función para ejecutar procesos con estado
function runCommand(command, message) {
  console.log(`Ejecutando: ${command}...`);
  const process = exec(command);

  process.stdout.on("data", (data) => console.log(`${message}: ${data}`));
  process.stderr.on("data", (data) => console.error(`Error en ${message}: ${data}`));

  process.on("exit", (code) => {
    if (code === 0) {
      console.log(`✅ ${message} iniciado correctamente.\n`);
    } else {
      console.error(`❌ Error en ${message}. Código de salida: ${code}\n`);
    }
  });
}

// Ejecutar servidores
runCommand("node index.js", "Servidor de pacientes");
runCommand("node chat_off.js", "Servidor de chatbot offline");
runCommand("python3.10 -m uvicorn main:app --host 0.0.0.0 --port 8000 --reload", "Servidor de análisis de neumonía");
runCommand("python3.10 -m uvicorn main_chat_ia:app --host 0.0.0.0 --port 8001 --reload", "Servidor de chatbot online");
runCommand("ollama serve", "Servidor Ollama");
