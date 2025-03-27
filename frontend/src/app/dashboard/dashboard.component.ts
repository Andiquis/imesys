import { Component } from '@angular/core';
import { Router, NavigationEnd, RouterModule } from '@angular/router';
import { CommonModule } from '@angular/common';

@Component({
  standalone: true,
  selector: 'app-dashboard',
  templateUrl: './dashboard.component.html',
  styleUrls: ['./dashboard.component.css'],
  imports: [CommonModule, RouterModule]
})
export default class DashboardComponent {
  moduleTitle: string = 'Dashboard';
  currentRoute: string = ''; // Guarda la ruta activa

  constructor(private router: Router) {
    this.router.events.subscribe(event => {
      if (event instanceof NavigationEnd) {
        this.currentRoute = event.url; // Guarda la ruta activa
        this.setModuleTitle(this.currentRoute); // Actualiza el título del módulo
      }
    });
  }

  setModuleTitle(url: string) {
    const titles: { [key: string]: string } = {
      '/dashboard/estudiantes': 'Estudiantes',
      '/dashboard/users-list': 'Usuarios',
      '/dashboard/mod-vision': 'Análisis Médico',
      '/dashboard/change-detection': 'Change Detection',
      '/dashboard/chatbot': 'Chatbot',
      '/dashboard/control-flow': 'WELCOME TO IMESYS',
      '/dashboard/defer-option': 'Defer Option',
      '/dashboard/defer-views': 'Defer Views',
      '/dashboard/view-transition': 'View Transition',
    };
    this.moduleTitle = titles[url] || 'Dashboard';
  }

  isActiveRoute(route: string): boolean {
    return this.currentRoute === route;
  }
}
