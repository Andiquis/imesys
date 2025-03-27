import { Injectable } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Observable } from 'rxjs';

@Injectable({
  providedIn: 'root'
})
export class EstudiantesService {
  private apiUrl = 'http://localhost:5000/api/estudiantes';

  constructor(private http: HttpClient) {}

  obtenerEstudiantes(): Observable<any> {
    return this.http.get(this.apiUrl);
  }

  obtenerEstudiantePorId(id: number): Observable<any> {
    return this.http.get(`${this.apiUrl}/${id}`);
  }

  crearEstudiante(estudiante: any): Observable<any> {
    return this.http.post(this.apiUrl, estudiante);
  }

  actualizarEstudiante(id: number, estudiante: any): Observable<any> {
    return this.http.put(`${this.apiUrl}/${id}`, estudiante);
  }

  eliminarEstudiante(id: number): Observable<any> {
    return this.http.delete(`${this.apiUrl}/${id}`);
  }
}


