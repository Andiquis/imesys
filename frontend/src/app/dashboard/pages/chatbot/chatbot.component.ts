import { Component, OnInit } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { CommonModule } from '@angular/common';
import { FormsModule } from '@angular/forms';

import chatData from './chat-data.json';
 // Importar el JSON directamente

@Component({
  selector: 'app-chatbot',
  standalone: true,
  imports: [CommonModule, FormsModule],
  templateUrl: './chatbot.component.html',
  styleUrls: ['./chatbot.component.css']
})
export default class ChatbotComponent implements OnInit {
  userMessage: string = "";
  botResponse: string = "Hazme una pregunta...";
  chatHistory: any[] = [];
  userId: number = 1; // Identificador por defecto
  loading: boolean = false;
  apiUrl: string = 'http://localhost:8001/ask'; // URL del backend

  constructor(private http: HttpClient) {}

  ngOnInit() {
    this.loadChatHistory();
  }

  // Cargar historial de chat desde el JSON importado
  loadChatHistory() {
    this.chatHistory = chatData || [];
// Cargar los datos
  }

  sendMessage() {
    if (!this.userMessage.trim()) return;

    this.loading = true;
    const userMessage = this.userMessage;
    this.userMessage = "";

    // Agregar mensaje del usuario al historial
    this.chatHistory.push({ id: this.userId, user: userMessage, bot: "Pensando... 🤔" });

    const requestBody = { question: userMessage };

    this.http.post<any>(this.apiUrl, requestBody).subscribe(
      (response) => {
        const botMessage = response.response;

        // Actualizar el mensaje del bot en el historial
        this.chatHistory[this.chatHistory.length - 1].bot = botMessage;
        this.saveChatHistory(); // Guardar en JSON (simulado)
        this.loading = false;
      },
      (error) => {
        this.chatHistory[this.chatHistory.length - 1].bot = "Error en la respuesta. Inténtalo de nuevo.";
        this.loading = false;
      }
    );
  }

  // Simulación de guardado en JSON (realmente no se puede escribir en archivos locales desde Angular)
  saveChatHistory() {
    console.log("Historial actualizado:", this.chatHistory);
  }
}
