'''
import os
import google.generativeai as genai
from fastapi import FastAPI, HTTPException
from fastapi.middleware.cors import CORSMiddleware
from pydantic import BaseModel
from dotenv import load_dotenv

# Cargar las variables de entorno desde el archivo .env
load_dotenv()
API_KEY = os.getenv("GEMINI_API_KEY")

if not API_KEY:
    raise ValueError("La clave de la API de Gemini no está configurada.")

# Configurar la API de Gemini
genai.configure(api_key=API_KEY)

class Chatbot:
    def __init__(self):
        self.model = genai.GenerativeModel(
            model_name="gemini-1.5-pro-latest",
            generation_config={
                "temperature": 0.7,
                "top_p": 0.9,
                "top_k": 50,
                "max_output_tokens": 512,
            },
            safety_settings=[
                {"category": "HARM_CATEGORY_HARASSMENT", "threshold": "BLOCK_NONE"},
                {"category": "HARM_CATEGORY_HATE_SPEECH", "threshold": "BLOCK_NONE"},
                {"category": "HARM_CATEGORY_SEXUALLY_EXPLICIT", "threshold": "BLOCK_NONE"},
                {"category": "HARM_CATEGORY_DANGEROUS_CONTENT", "threshold": "BLOCK_NONE"},
            ]
        )

    def get_response(self, query: str) -> str:
        try:
            prompt = (
                "Usted es Imesys, un asistente de inteligencia artificial especializado en el ámbito médico. "
                "Su propósito es proporcionar información confiable sobre síntomas, enfermedades, tratamientos "
                "Si el usuario no realiza una consulta específica, responde con mensajes breves y concisos."
                "y recomendaciones médicas basadas en información validada. Su tono de respuesta debe ser formal, "
                "preciso y profesional. No proporciona diagnósticos definitivos y siempre recomienda la consulta con un médico.\n\n"
                f"Ahora, analice y responda la siguiente consulta médica:\nConsulta: {query}"
            )
            response = self.model.generate_content(prompt)
            return response.text if response else "No se pudo generar una respuesta."
        except Exception as e:
            print(f"Error en Gemini: {e}")
            return "Ocurrió un error al procesar la solicitud."

# Inicializar FastAPI
app = FastAPI()
chatbot = Chatbot()

# Configurar CORS para permitir solicitudes desde Angular
app.add_middleware(
    CORSMiddleware,
    allow_origins=["http://localhost:4200"],  # Permitir el frontend de Angular
    allow_credentials=True,
    allow_methods=["*"],  # Permitir todos los métodos (GET, POST, etc.)
    allow_headers=["*"],  # Permitir todos los encabezados
)

# Modelo de solicitud
class ChatRequest(BaseModel):
    question: str

@app.get("/")
def home():
    return {"message": "API de Chatbot con Gemini en FastAPI está corriendo."}

@app.post("/ask")
def ask_question(request: ChatRequest):
    if not request.question.strip():
        raise HTTPException(status_code=400, detail="La pregunta no puede estar vacía.")

    response = chatbot.get_response(request.question)
    return {"response": response}
'''
# This code is a FastAPI application that serves as a backend for a medical chatbot using the Gemini API.   