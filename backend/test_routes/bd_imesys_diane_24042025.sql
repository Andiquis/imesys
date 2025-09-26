
CREATE DATABASE BD_imesys;
USE BD_imesys;

-- Tabla de Usuarios (Pacientes y Usuarios Generales)
CREATE TABLE usuarios (
    id_usuario INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    apellido VARCHAR(100) NOT NULL,
    correo VARCHAR(150) UNIQUE NOT NULL,
    contrasena VARCHAR(100) NOT NULL,
    telefono VARCHAR(20),
    direccion TEXT,
    fecha_nacimiento DATE,
    genero ENUM('Masculino', 'Femenino', 'Otro'),
    foto VARCHAR(255),
    fecha_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabla de Especialidades
CREATE TABLE especialidades (
    id_especialidad INT AUTO_INCREMENT PRIMARY KEY,
    nombre_especialidad VARCHAR(100) NOT NULL UNIQUE
);

-- Tabla de Médicos
CREATE TABLE medicos (
    id_medico INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    apellido VARCHAR(100) NOT NULL,
    correo VARCHAR(150) UNIQUE NOT NULL,
    contrasena VARCHAR(100) NOT NULL,
    telefono VARCHAR(20),
    id_especialidad INT NOT NULL,
    numero_colegiatura VARCHAR(50) UNIQUE NOT NULL,
    foto VARCHAR(255),
    fecha_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    direccion_consultorio VARCHAR(200),
    FOREIGN KEY (id_especialidad) REFERENCES especialidades(id_especialidad) ON DELETE CASCADE
);
-- Tabla de Historial de Consultas
CREATE TABLE historial_consultas (
    id_historial INT AUTO_INCREMENT PRIMARY KEY,
    id_usuario INT NOT NULL,
    id_medico INT NOT NULL,
    motivo TEXT NOT NULL,
    observacion TEXT,
    imagen VARCHAR(255),
    fecha_hora DATETIME DEFAULT CURRENT_TIMESTAMP,
    dato_opcional TEXT,
    FOREIGN KEY (id_usuario) REFERENCES usuarios(id_usuario) ON DELETE CASCADE,
    FOREIGN KEY (id_medico) REFERENCES medicos(id_medico) ON DELETE CASCADE
);

CREATE TABLE historial_paciente (
    id_historial_paciente INT AUTO_INCREMENT PRIMARY KEY,
    id_usuario INT NOT NULL,
    fecha_hora DATETIME NOT NULL,
    motivo TEXT NOT NULL,
    nombre_medico VARCHAR(100) NOT NULL,
    especialidad VARCHAR(100) NOT NULL,
    FOREIGN KEY (id_usuario) REFERENCES usuarios(id_usuario) ON DELETE CASCADE
);

-- Tabla de Resultados IA Biométricos
CREATE TABLE resultados_ia_biometrico (
    id_resultado_biometrico INT AUTO_INCREMENT PRIMARY KEY,
    id_usuario INT NOT NULL,
    nombre VARCHAR(100) NOT NULL,
    prediccion TEXT NOT NULL,
    fecha_hora DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_usuario) REFERENCES usuarios(id_usuario) ON DELETE CASCADE
);

-- Tabla de Citas
CREATE TABLE citas (
    id_cita INT AUTO_INCREMENT PRIMARY KEY,
    id_usuario INT,
    id_medico INT,
    fecha_cita DATETIME NOT NULL,
    estado ENUM('Pendiente', 'Confirmada', 'Cancelada', 'Completada') DEFAULT 'Pendiente',
    motivo TEXT,
    respuesta TEXT,
    FOREIGN KEY (id_usuario) REFERENCES usuarios(id_usuario) ON DELETE CASCADE,
    FOREIGN KEY (id_medico) REFERENCES medicos(id_medico) ON DELETE CASCADE
);

-- Tabla de Imágenes Médicas
CREATE TABLE imagenes_medicas (
    id_imagen INT AUTO_INCREMENT PRIMARY KEY,
    id_medico INT,
    id_usuario INT,
    ruta_imagen VARCHAR(255) NOT NULL,
    fecha_subida TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_medico) REFERENCES medicos(id_medico) ON DELETE CASCADE
);

-- Tabla de Resultados IA
CREATE TABLE resultados_ia (
    id_resultado INT AUTO_INCREMENT PRIMARY KEY,
    id_imagen INT,
    diagnostico TEXT NOT NULL,
    probabilidad DECIMAL(5,2) NOT NULL,
    fecha_analisis TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_imagen) REFERENCES imagenes_medicas(id_imagen) ON DELETE CASCADE
);

-- Tabla de Datos Biométricos
CREATE TABLE datos_biometricos (
    id_dato INT AUTO_INCREMENT PRIMARY KEY,
    id_usuario INT,
    peso DECIMAL(5,2),
    altura DECIMAL(5,2),
    presion_arterial VARCHAR(50),
    frecuencia_cardiaca INT,
    nivel_glucosa DECIMAL(5,2),
    fecha_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    resultado_prediccion TEXT,
    FOREIGN KEY (id_usuario) REFERENCES usuarios(id_usuario) ON DELETE CASCADE
);

-- Tabla de Recompensas
CREATE TABLE recompensas (
    id_recompensa INT AUTO_INCREMENT PRIMARY KEY,
    id_usuario INT,
    puntos INT DEFAULT 0,
    descripcion TEXT,
    fecha_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_usuario) REFERENCES usuarios(id_usuario) ON DELETE CASCADE
);

-- Tabla de Farmacias
CREATE TABLE farmacias (
    id_farmacia INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(150) NOT NULL,
    direccion TEXT NOT NULL,
    telefono VARCHAR(20),
    correo VARCHAR(150) UNIQUE,
    fecha_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabla de Agenda del Médico
CREATE TABLE agenda_medico (
    id_agenda INT AUTO_INCREMENT PRIMARY KEY,
    id_medico INT NOT NULL,
    fecha_hora DATETIME NOT NULL,
    estado ENUM('Disponible', 'No disponible') DEFAULT 'Disponible',
    etiqueta VARCHAR(100), -- opcional, para etiquetas como “Reunión”, “Cirugía”, “Día libre”, etc.
    recordatorio TEXT,     -- opcional, para notas adicionales
    fecha_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_medico) REFERENCES medicos(id_medico) ON DELETE CASCADE
);

-- Tabla de Clasificación de Médicos
CREATE TABLE clasificacion_medicos (
    id_clasificacion INT AUTO_INCREMENT PRIMARY KEY,
    id_usuario INT NOT NULL,
    id_medico INT NOT NULL,
    puntuacion INT CHECK (puntuacion BETWEEN 1 AND 5), -- de 1 a 5 estrellas
    comentario TEXT,
    fecha_clasificacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    anonimo BOOLEAN DEFAULT FALSE, -- si el paciente quiere que su comentario sea anónimo
    FOREIGN KEY (id_usuario) REFERENCES usuarios(id_usuario) ON DELETE CASCADE,
    FOREIGN KEY (id_medico) REFERENCES medicos(id_medico) ON DELETE CASCADE
);


-- Datos de prueba para la tabla clasificacion_medicos
INSERT INTO clasificacion_medicos (id_usuario, id_medico, puntuacion, comentario, anonimo)
VALUES
(1, 1, 3, 'El doctor fue amable, pero la atención demoró un poco.', FALSE),
(1, 2, 4, 'Buena atención, explicó todo con claridad.', FALSE),
(1, 3, 2, 'No me sentí muy cómodo con la consulta.', TRUE),
(1, 4, 5, 'Excelente médico, muy profesional y puntual.', FALSE),
(1, 4, 5, 'Me ayudó muchísimo con mi tratamiento.', TRUE);

INSERT INTO agenda_medico (id_medico, fecha_hora, estado, etiqueta, recordatorio)
VALUES
(4, '2025-04-23 09:00:00', 'Disponible', 'Consulta general', 'Mañana disponible para consultas regulares'),
(4, '2025-04-24 15:30:00', 'No disponible', 'Reunión interna', 'Reunión con equipo médico para revisión de casos');


-- Datos de prueba para Usuarios
INSERT INTO usuarios (nombre, apellido, correo, contrasena, telefono, direccion, fecha_nacimiento, genero, foto) VALUES
('Juan', 'Pérez', 'juan.perez@example.com', 'hashed_password', '987654321', 'Av. Siempre Viva 123', '1985-06-15', 'Masculino', 'juan.jpg'),
('María', 'López', 'maria.lopez@example.com', 'hashed_password', '987654322', 'Calle Falsa 456', '1990-08-20', 'Femenino', 'maria.jpg');

-- Datos de prueba para Especialidades
INSERT INTO especialidades (nombre_especialidad) VALUES
('Cardiología'),
('Neurología'),
('Pediatría');

INSERT INTO medicos (nombre, apellido, correo, contrasena, telefono, id_especialidad, numero_colegiatura, foto, direccion_consultorio)  
VALUES  
('Juan', 'Pérez', 'juan.perez@example.com', '123', '987654321', 1, 'CMP123456', 'juan_perez.jpg', 'Av. Salud 123, Cusco'),  
('María', 'Gómez', 'maria.gomez@example.com', '456', '987123456', 2, 'CMP654321', 'maria_gomez.jpg', 'Jr. Medicinal 456, Cusco'),  
('Carlos', 'Fernández', 'carlos.fernandez@example.com', '789', '987456789', 3, 'CMP789123', 'carlos_fernandez.jpg', 'Calle Bienestar 789, Cusco');  

INSERT INTO clasificacion_medicos (id_usuario, id_medico, puntuacion, comentario, anonimo)
VALUES (1, 4, 4, 'Muy buena atención, aunque esperé un poco más de lo previsto.', FALSE);

select * from usuarios;

select * from medicos  