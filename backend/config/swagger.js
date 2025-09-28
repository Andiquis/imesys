// config/swagger.js
import swaggerJSDoc from 'swagger-jsdoc';

const options = {
  definition: {
    openapi: '3.0.0',
    info: {
      title: 'IMESYS API',
      version: '1.0.0',
      description: 'Documentación de la API de IMESYS',
    },
    servers: [
      {
        url: 'http://localhost:5000',
      },
    ],
    tags: [
      {
        name: 'usuarios',
        description: 'Operaciones relacionadas con los usuarios del sistema',
      },
      {
        name: 'medicos',
        description: 'Gestión de información de médicos',
      },
      {
        name: 'citas',
        description: 'Manejo de citas médicas',
      },
      {
        name: 'ias',
        description: 'Consultas con inteligencia artificial',
      },
      {
        name: 'login',
        description: 'Autenticación y autorización de usuarios',
      },
        {
            name: 'especialistas',
            description: 'Gestión de especialidades médicas y médicos',
        },
        {
            name: 'calificaciones',
            description: 'Gestión de calificaciones y comentarios de médicos',
        },
        {
            name: 'buscador',
            description: 'Funcionalidad de búsqueda en la aplicación',
        },
        {
            name: 'MisPacientes',
            description: 'Gestión de pacientes por parte de médicos',
        },
    ],
  },
  apis: ['./routes/*.js'], // Ajusta esta ruta si es necesario
  apis: ['./routes/**/*.js'], // Ajusta esta ruta si es necesario

};

const swaggerSpec = swaggerJSDoc(options);

export default swaggerSpec;
