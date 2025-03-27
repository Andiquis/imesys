import { CommonModule } from '@angular/common';
import { Component } from '@angular/core';

@Component({
  selector: 'app-heavy-loaders-slow',
  standalone: true,
  imports: [CommonModule],
  template: `
    <h1>Hola Mundo heavy loaders slow</h1>
  `
})
export class HeavyLoadersSlowComponent { }