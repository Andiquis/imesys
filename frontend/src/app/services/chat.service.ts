import { Injectable } from '@angular/core';
import { Message } from '../dashboard/pages/change-detection/models/message.model';
import { HttpClient } from '@angular/common/http';
import { Observable } from 'rxjs';

@Injectable({
  providedIn: 'root'
})
export class ChatService {
  private apiUrl = 'http://localhost:3000/chat';

  constructor(private http: HttpClient) { }

  sendMessage(message: string): Promise<string> {
    return this.http.post<any>(this.apiUrl, { prompt: message })
      .toPromise()
      .then(response => response?.response || "No se recibió respuesta válida")
      .catch(error => {
        console.error('Error en la solicitud:', error);
        throw error;
      });
  }
}