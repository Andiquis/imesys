import { Component } from '@angular/core';
import { ApiService } from '../../../services/api.service';
import { CommonModule } from '@angular/common';

@Component({
  standalone: true,
  imports: [CommonModule],
  selector: 'app-mod-vision',
  templateUrl: './mod-vision.component.html',
  styleUrls: ['./mod-vision.component.css']
})
export default class ModVisionComponent {
  resultado: string = '';
  procesando: boolean = false;
  imagenUrl: string | null = null; // Guarda la imagen seleccionada

  constructor(private apiService: ApiService) {}

  onFileSelected(event: any) {
    const file: File = event.target.files[0];

    // Validar que el archivo sea una imagen
    if (!file || !file.type.startsWith('image/')) {
      this.resultado = '⚠️ Solo se permiten imágenes en formato JPG o PNG.';
      return;
    }

    // Convertir imagen a URL para previsualización
    const reader = new FileReader();
    reader.onload = () => {
      this.imagenUrl = reader.result as string;
    };
    reader.readAsDataURL(file);

    this.procesarImagen(file);
  }

  procesarImagen(file: File) {
    this.procesando = true;
    this.resultado = '';

    this.apiService.detectarNeumonia(file).subscribe(
      response => {
        this.resultado = `🩺 Resultado: ${response.prediction} - 📊 Confianza: ${response.confidence}%`;
        this.procesando = false;
      },
      error => {
        console.error('Error en la predicción', error);
        this.resultado = '⚠️ Error al procesar la imagen. Inténtalo de nuevo.';
        this.procesando = false;
      }
    );
  }

  reset(inputFile: HTMLInputElement) {
    this.resultado = '';
    this.procesando = false;
    this.imagenUrl = null;
    inputFile.value = ''; // Limpia el input del archivo
  }
}
