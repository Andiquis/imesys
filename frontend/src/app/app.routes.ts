import { Routes } from '@angular/router';

export const routes: Routes = [
  {
    path: '',
    title: 'Imesys Landing',
    loadComponent: () => import('./landing/landing.component').then(m => m.LandingComponent),
  },
  {
    path: 'dashboard',
    loadComponent: () => import('./dashboard/dashboard.component'),
    children: [
      {
        path: 'change-detection',
        title: 'Change Detection',
        loadComponent: () => import('./dashboard/pages/change-detection/change-detection.component'),
      },
      {
        path: 'control-flow',
        title: 'Control Flow',
        loadComponent: () => import('./dashboard/pages/control-flow/control-flow.component'),
      },
      {
        path: 'mod-vision',
        title: 'Vision IA',
        loadComponent: () => import('./dashboard/pages/mod-vision/mod-vision.component'),
      },
      {
        path: 'defer-option',
        title: 'Defer Option',
        loadComponent: () => import('./dashboard/pages/defer-option/defer-option.component'),
      },
      {
        path: 'defer-views',
        title: 'Defer Views',
        loadComponent: () => import('./dashboard/pages/defer-views/defer-views.component'),
      },
      {
        path: 'estudiantes',
        title: 'Estudiantes View',
        loadComponent: () => import('./dashboard/pages/estudiantes/estudiantes.component'),
      },
      {
        path: 'user/:id',
        title: 'User View',
        loadComponent: () => import('./dashboard/pages/user/user.component'),
      },
      {
        path: 'users-list',
        title: 'Users List',
        loadComponent: () => import('./dashboard/pages/users/users.component'),
      },
      {
        path: 'view-transition',
        title: 'View Transition',
        loadComponent: () => import('./dashboard/pages/view-transition/view-transition.component'),
      },
      {
        path: 'chatbot',
        title: 'Chatbot',
        loadComponent: () => import('./dashboard/pages/chatbot/chatbot.component'),
      },
      { path: '', pathMatch: 'full', redirectTo: 'control-flow' },
    ],
  },
  { path: '**', redirectTo: '' } // Redirigir rutas no encontradas al Landing Page
];
