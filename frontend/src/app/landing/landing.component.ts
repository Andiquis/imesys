import { Component } from '@angular/core';
import { Router } from '@angular/router';

@Component({
  selector: 'app-landing',
  standalone: true,
  templateUrl: './landing.component.html',
  styleUrls: ['./landing.component.css']
})
export class LandingComponent {
  constructor(private router: Router) {}

  goToDashboard() {
    this.router.navigate(['/dashboard']);
  }

  showIframe = true;

  // Llamar este método cuando la vista se active
  reloadIframe() {
    this.showIframe = false;
    setTimeout(() => this.showIframe = true, 100);
  }
}


