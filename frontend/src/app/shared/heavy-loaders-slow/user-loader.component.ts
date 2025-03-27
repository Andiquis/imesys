
import { Component } from '@angular/core';

@Component({
  selector: 'app-user-loader',
  standalone: true,
  imports: [],
  template: `
    <h1>Hola Mundo heavy loaders slow</h1>
  `
})
export class UserLoaderComponent {
  constructor(){}
 }