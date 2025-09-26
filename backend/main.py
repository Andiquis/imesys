from fastapi import FastAPI, File, UploadFile, HTTPException
from fastapi.middleware.cors import CORSMiddleware
import numpy as np
import cv2
from tensorflow.keras.models import load_model
from tensorflow.keras.preprocessing.image import img_to_array
from PIL import Image
import io

app = FastAPI()

# Configuración de CORS
app.add_middleware(
    CORSMiddleware,
    allow_origins=["*"],
    allow_credentials=True,
    allow_methods=["*"],
    allow_headers=["*"],
)

# Cargar el modelo en el evento de inicio
@app.on_event("startup")
def load_model_on_startup():
    global model
    model_path = "model1_MobileNetV2.h5"
    try:
        model = load_model(model_path)
        print("Modelo cargado correctamente.")
    except Exception as e:
        print(f"Error al cargar el modelo: {str(e)}")


# Etiquetas de clases
class_labels = ["Neumonía", "No Neumonía"]
@app.post("/predict/")
async def predict(file: UploadFile = File(...)):
    try:
        # Leer imagen
        contents = await file.read()
        image = Image.open(io.BytesIO(contents)).convert("RGB")
        img_size = (224, 224)
        image = image.resize(img_size)
        img = img_to_array(image) / 255.0
        img = np.expand_dims(img, axis=0)

        # Predicción
        prediction = model.predict(img)[0][0].item()  # Convertir numpy.float32 a float
        confidence = round(prediction * 100, 2)

        if prediction < 0.5:
            result = {
                "prediction": "Neumonía",
                "confidence": round(100 - confidence, 2),
                "confidence_scores": {"Neumonía": round(100 - confidence, 2), "No Neumonía": confidence}
            }
        else:
            result = {
                "prediction": "No Neumonía",
                "confidence": confidence,
                "confidence_scores": {"Neumonía": round(100 - confidence, 2), "No Neumonía": confidence}
            }

        return result

    except Exception as e:
        raise HTTPException(status_code=500, detail=f"Error al procesar la imagen: {str(e)}")