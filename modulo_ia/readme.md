# Proyecto de IA con Ollama, LLaMA 3 y NodeJS

## Requisitos Previos

Antes de ejecutar el proyecto, asegúrate de:

1. **Instalar Ollama**: Descarga e instala desde [ollama.ai](https://ollama.ai)
2. **Descargar el modelo LLaMA 3**: Ejecuta `ollama pull llama3` en tu terminal
3. **Instalar Node.js**: Versión recomendada 18 o superior

## Estructura del Proyecto

Este proyecto contiene varios módulos de IA:

- **imesysBot**: Módulo principal que integra tanto ollamaBot como geminiBot
- **datosBio**: Módulo para análisis de datos biométricos
- **neumonia**: Módulo para detección de neumonía

## Cómo ejecutar el proyecto

### Iniciar el servidor backend

```bash
# Navega a la carpeta backend
cd backend

# Inicia el servidor
node index.js
# O alternativamente
npm start
```

### Notas importantes

- Utiliza solamente el módulo **imesysBot** para interactuar con los bots de IA, ya que este contiene la funcionalidad integrada de ollamaBot y geminiBot.
- Los módulos **datosBio** y **neumonia** son específicos para sus respectivas tareas de análisis biométrico y detección de neumonía.

## Solución de problemas

Si encuentras algún problema durante la instalación o ejecución, asegúrate de:

1. Verificar que Ollama está correctamente instalado y ejecutándose
2. Confirmar que el modelo LLaMA 3 se ha descargado correctamente
3. Comprobar las versiones de Node.js y dependencias