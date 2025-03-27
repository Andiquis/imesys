import { Component } from '@angular/core';
import { CommonModule } from '@angular/common';

@Component({
  selector: 'app-control-flow',
  standalone: true,
  imports: [CommonModule], // Agregar esto para usar *ngIf y *ngFor
  templateUrl: './control-flow.component.html',
  styleUrls: ['./control-flow.component.css']
})
export default class ControlFlowComponent {
  usuarios: any[] = [];
}
