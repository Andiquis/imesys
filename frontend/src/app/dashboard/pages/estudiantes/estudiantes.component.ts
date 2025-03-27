import { Component, OnInit } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormsModule } from '@angular/forms';
import { EstudiantesService } from '../../../services/estudiantes.service';

@Component({
  selector: 'app-estudiantes',
  standalone: true,
  imports: [CommonModule, FormsModule],
  templateUrl: './estudiantes.component.html',
  styleUrls: ['./estudiantes.component.css']
})
export default class EstudiantesComponent implements OnInit {
  estudiantes: any[] = [];
  mostrarFormulario = false;
  editando = false;
  estudianteSeleccionadoId: number | null = null;

  estudianteForm = {
    nombres: '',
    apellidos: '',
    dni: '',
    correo: '',
    telefono: '',
    fecha_nacimiento: '',
    direccion: ''
  };

  constructor(private estudiantesService: EstudiantesService) {}

  ngOnInit() {
    this.obtenerEstudiantes();
  }

  obtenerEstudiantes(): void {
    this.estudiantesService.obtenerEstudiantes().subscribe(data => {
      this.estudiantes = data;
    });
  }

  abrirFormulario(editar = false, estudiante: any = null): void {
    this.mostrarFormulario = true;
    this.editando = editar;
    
    if (editar && estudiante) {
      this.estudianteSeleccionadoId = estudiante.id;
      this.estudianteForm = { ...estudiante };
    } else {
      this.limpiarFormulario();
    }
  }

  cerrarFormulario(): void {
    this.mostrarFormulario = false;
    this.limpiarFormulario();
  }

  guardarEstudiante(): void {
    if (this.editando) {
      this.estudiantesService.actualizarEstudiante(this.estudianteSeleccionadoId!, this.estudianteForm).subscribe(
        () => {
          this.obtenerEstudiantes();
          this.cerrarFormulario();
        },
        error => {
          console.error('Error al actualizar estudiante', error);
        }
      );
    } else {
      this.estudiantesService.crearEstudiante(this.estudianteForm).subscribe(
        (respuesta) => {
          this.estudiantes.push(respuesta);
          this.cerrarFormulario();
        },
        error => {
          console.error('Error al guardar el estudiante', error);
        }
      );
    }
  }

  eliminarEstudiante(id: number): void {
    if (confirm('¿Estás seguro de que deseas eliminar este estudiante?')) {
      this.estudiantesService.eliminarEstudiante(id).subscribe(
        () => {
          this.estudiantes = this.estudiantes.filter(est => est.id !== id);
        },
        error => {
          console.error('Error al eliminar estudiante', error);
        }
      );
    }
  }

  limpiarFormulario(): void {
    this.estudianteForm = {
      nombres: '',
      apellidos: '',
      dni: '',
      correo: '',
      telefono: '',
      fecha_nacimiento: '',
      direccion: ''
    };
    this.estudianteSeleccionadoId = null;
    this.editando = false;
  }
}
