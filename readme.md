## comandos para iniciar el servidor

para el chatbot
# backend/python3.10 -m uvicorn main_chat_ia:app --host 0.0.0.0 --port 8001 --reload  

para modelo de neumonia
# backend/python3.10 -m uvicorn main:app --host 0.0.0.0 --port 8000 --reload 

para iniciar el backend
# backend/node index.js

para iniciar el frontend
# frontend/ng serve -o

para el iniciar el modulo de chatbot ofline
# backend/node chat_off.js

boton a dashboard: (click)="goToDashboard()"