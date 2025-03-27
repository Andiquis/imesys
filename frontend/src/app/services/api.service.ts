import { Injectable } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Observable } from 'rxjs';

@Injectable({
  providedIn: 'root'
})
export class ApiService {
  private apiUrl = 'http://127.0.0.1:8000/predict/';
 // Cambia por tu endpoint real

  constructor(private http: HttpClient) {}

  detectarNeumonia(file: File): Observable<any> {
    const formData = new FormData();
    formData.append('file', file);  // Envía la imagen como FormData

    return this.http.post<any>(this.apiUrl, formData);
  }
}
