Drop DATABASE BD_imesys;
CREATE DATABASE BD_imesys;
USE BD_imesys;

-- Tabla de Usuarios (Pacientes y Usuarios Generales)
CREATE TABLE usuarios (
    id_usuario INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    apellido VARCHAR(100) NOT NULL,
    dni VARCHAR(8) unique,
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
    id_usuario INT NOT NULL COMMENT 'Paciente asociado a la consulta',
    id_medico INT NOT NULL COMMENT 'Médico que atendió la consulta',
    motivo TEXT NOT NULL COMMENT 'Razón principal de la consulta',
    observacion TEXT COMMENT 'Notas médicas durante la consulta',
    diagnostico TEXT COMMENT 'Diagnóstico establecido',
    tratamiento TEXT COMMENT 'Tratamiento recomendado',
    imagen VARCHAR(255) COMMENT 'Ruta de imágenes médicas asociadas',
    fecha_hora DATETIME DEFAULT CURRENT_TIMESTAMP COMMENT 'Fecha y hora de la consulta',
    dato_opcional TEXT,
    FOREIGN KEY (id_usuario) REFERENCES usuarios(id_usuario) ON DELETE CASCADE,
    FOREIGN KEY (id_medico) REFERENCES medicos(id_medico) ON DELETE CASCADE
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

-- Crear tabla recompensas con campos completos
CREATE TABLE recompensas (
    id_recompensa INT AUTO_INCREMENT PRIMARY KEY,
    id_usuario INT,
    puntos INT NOT NULL DEFAULT 0 COMMENT 'Saldo actual de puntos',
    descripcion TEXT,
    fecha_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    fecha_actualizacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'Fecha de última actualización',
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
INSERT INTO farmacias (nombre, direccion, telefono, correo)
VALUES ('Botica Central Cusco', 'Av. El Sol 123, Cusco', '084-123456', 'boticacentral@correo.com');


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

CREATE TABLE recetas (
    id_receta INT AUTO_INCREMENT PRIMARY KEY,
    id_medico INT NOT NULL,
    id_paciente INT NOT NULL,
    fecha_emision DATETIME NOT NULL,
    medicamentos TEXT NOT NULL COMMENT 'JSON con medicamentos recetados',
    instrucciones TEXT NOT NULL,
    observaciones TEXT,
    FOREIGN KEY (id_medico) REFERENCES medicos(id_medico),
    FOREIGN KEY (id_paciente) REFERENCES usuarios(id_usuario)
);

-- Tabla de Transacciones de Puntos
CREATE TABLE transacciones_puntos (
    id_transaccion INT AUTO_INCREMENT PRIMARY KEY,
    id_usuario INT NOT NULL,
    tipo ENUM('ganancia', 'canje') NOT NULL COMMENT 'ganancia: puntos obtenidos, canje: puntos usados',
    puntos INT NOT NULL COMMENT 'Cantidad de puntos (positivo para ganancia, negativo para canje)',
    descripcion VARCHAR(255) NOT NULL COMMENT 'Motivo de la transacción',
    referencia VARCHAR(100) COMMENT 'Referencia externa o ID relacionado',
    fecha_transaccion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_usuario) REFERENCES usuarios(id_usuario) ON DELETE CASCADE
);

-- Tabla de Canjes (para los QR generados)
CREATE TABLE canjes (
    id_canje INT AUTO_INCREMENT PRIMARY KEY,
    id_usuario INT NOT NULL,
    id_transaccion INT NOT NULL COMMENT 'Relación con la transacción de puntos',
    puntos_canjeados INT NOT NULL,
    valor_equivalente DECIMAL(10,2) NOT NULL COMMENT 'Valor en dinero equivalente',
    codigo_canje VARCHAR(50) UNIQUE NOT NULL COMMENT 'Código QR/único',
    estado ENUM('generado', 'utilizado', 'expirado') DEFAULT 'generado',
    fecha_generacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    fecha_uso TIMESTAMP NULL COMMENT 'Cuando se canjea en farmacia',
    id_farmacia INT NULL COMMENT 'Farmacia donde se canjeó',
    FOREIGN KEY (id_usuario) REFERENCES usuarios(id_usuario) ON DELETE CASCADE,
    FOREIGN KEY (id_transaccion) REFERENCES transacciones_puntos(id_transaccion) ON DELETE CASCADE,
    FOREIGN KEY (id_farmacia) REFERENCES farmacias(id_farmacia) ON DELETE SET NULL
);

-- Tabla de Configuración de Puntos
CREATE TABLE config_puntos (
    id_config INT AUTO_INCREMENT PRIMARY KEY,
    clave VARCHAR(50) UNIQUE NOT NULL,
    valor VARCHAR(255) NOT NULL,
    descripcion TEXT
);

-- Insertar valores por defecto para la configuración
INSERT INTO config_puntos (clave, valor, descripcion) VALUES 
('puntos_por_registro', '50', 'Puntos por registrarse en la plataforma'),
('puntos_por_cita', '30', 'Puntos por cada cita médica completada'),
('puntos_por_comentario', '10', 'Puntos por dejar un comentario válido'),
('puntos_por_biometrico', '20', 'Puntos por completar un análisis biométrico'),
('max_puntos_diarios', '20', 'Máximo de puntos que se pueden canjear por día'),
('valor_por_punto', '0.10', 'Valor en dinero de cada punto (ej: 0.10 = 10 céntimos por punto)'),
('duracion_qr_horas', '24', 'Tiempo en horas que el QR es válido');


-- Datos de prueba para Especialidades
INSERT INTO especialidades (nombre_especialidad) VALUES
('Cardiología'),
('Neurología'),
('Pediatría');


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
VALUES (1, 5, 5, 'Muy buena atención, aunque esperé un poco más de lo previsto.', FALSE);

select * from usuarios;

select * from clasificacion_medicos ;

select * from medicos  ;

select * from historial_consultas  ;

select * from recompensas;
TRUNCATE TABLE recompensas;






ALTER TABLE usuarios ADD COLUMN dni VARCHAR(8) unique AFTER apellido;


-- Tabla actualizada de Recompensas (para el saldo actual de usuarios)
ALTER TABLE recompensas 
MODIFY COLUMN puntos INT DEFAULT 0 NOT NULL COMMENT 'Saldo actual de puntos',
ADD COLUMN fecha_actualizacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'Fecha de última actualización';

-- Tabla de Transacciones de Puntos
CREATE TABLE transacciones_puntos (
    id_transaccion INT AUTO_INCREMENT PRIMARY KEY,
    id_usuario INT NOT NULL,
    tipo ENUM('ganancia', 'canje') NOT NULL COMMENT 'ganancia: puntos obtenidos, canje: puntos usados',
    puntos INT NOT NULL COMMENT 'Cantidad de puntos (positivo para ganancia, negativo para canje)',
    descripcion VARCHAR(255) NOT NULL COMMENT 'Motivo de la transacción',
    referencia VARCHAR(100) COMMENT 'Referencia externa o ID relacionado',
    fecha_transaccion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_usuario) REFERENCES usuarios(id_usuario) ON DELETE CASCADE
);

-- Tabla de Canjes (para los QR generados)
CREATE TABLE canjes (
    id_canje INT AUTO_INCREMENT PRIMARY KEY,
    id_usuario INT NOT NULL,
    id_transaccion INT NOT NULL COMMENT 'Relación con la transacción de puntos',
    puntos_canjeados INT NOT NULL,
    valor_equivalente DECIMAL(10,2) NOT NULL COMMENT 'Valor en dinero equivalente',
    codigo_canje VARCHAR(50) UNIQUE NOT NULL COMMENT 'Código QR/único',
    estado ENUM('generado', 'utilizado', 'expirado') DEFAULT 'generado',
    fecha_generacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    fecha_uso TIMESTAMP NULL COMMENT 'Cuando se canjea en farmacia',
    id_farmacia INT NULL COMMENT 'Farmacia donde se canjeó',
    FOREIGN KEY (id_usuario) REFERENCES usuarios(id_usuario) ON DELETE CASCADE,
    FOREIGN KEY (id_transaccion) REFERENCES transacciones_puntos(id_transaccion) ON DELETE CASCADE,
    FOREIGN KEY (id_farmacia) REFERENCES farmacias(id_farmacia) ON DELETE SET NULL
);

-- Tabla de Configuración de Puntos
CREATE TABLE config_puntos (
    id_config INT AUTO_INCREMENT PRIMARY KEY,
    clave VARCHAR(50) UNIQUE NOT NULL,
    valor VARCHAR(255) NOT NULL,
    descripcion TEXT
);

-- Insertar valores por defecto para la configuración
INSERT INTO config_puntos (clave, valor, descripcion) VALUES 
('puntos_por_registro', '50', 'Puntos por registrarse en la plataforma'),
('puntos_por_cita', '30', 'Puntos por cada cita médica completada'),
('puntos_por_comentario', '10', 'Puntos por dejar un comentario válido'),
('puntos_por_biometrico', '20', 'Puntos por completar un análisis biométrico'),
('max_puntos_diarios', '20', 'Máximo de puntos que se pueden canjear por día'),
('valor_por_punto', '0.10', 'Valor en dinero de cada punto (ej: 0.10 = 10 céntimos por punto)'),
('duracion_qr_horas', '24', 'Tiempo en horas que el QR es válido');

INSERT INTO config_puntos (clave, valor, descripcion) VALUES 
('puntos_por_sesion', '2', 'Puntos por iniciar sesión cada día');

select * from recompensas;


INSERT INTO recompensas (id_usuario, puntos) VALUES (5, 100);



ALTER TABLE farmacias
ADD COLUMN contrasena VARCHAR(255) NOT NULL COMMENT 'Contraseña hasheada para el login',
ADD COLUMN activa BOOLEAN DEFAULT TRUE COMMENT 'Indica si la farmacia está activa en el sistema',
ADD COLUMN fecha_ultimo_login TIMESTAMP NULL COMMENT 'Fecha del último inicio de sesión',
ADD COLUMN token_recuperacion VARCHAR(100) NULL COMMENT 'Token para recuperación de contraseña',
ADD COLUMN expiracion_token TIMESTAMP NULL COMMENT 'Fecha de expiración del token';

SHOW COLUMNS FROM farmacias;


CREATE TABLE auditoria_farmacias (
    id_auditoria INT AUTO_INCREMENT PRIMARY KEY,
    id_farmacia INT NOT NULL,
    accion VARCHAR(50) NOT NULL COMMENT 'login, canje, etc.',
    detalles TEXT,
    direccion_ip VARCHAR(45),
    user_agent VARCHAR(255),
    fecha_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_farmacia) REFERENCES farmacias(id_farmacia) ON DELETE CASCADE
);

-- Actualizar la farmacia existente con una contraseña por defecto (ejemplo: "farmacia123")
UPDATE farmacias 
SET contrasena = '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi' -- hash de "farmacia123"
WHERE id_farmacia = 1;


select * from farmacias;

select * from config_puntos;
select * from canjes;


-- Tabla de Administradores
CREATE TABLE administradores (
    id_admin INT AUTO_INCREMENT PRIMARY KEY,
    usuario VARCHAR(50) UNIQUE NOT NULL,
    contrasena VARCHAR(255) NOT NULL,
    nombre VARCHAR(100) NOT NULL,
    correo VARCHAR(150) UNIQUE NOT NULL,
    fecha_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    ultimo_acceso TIMESTAMP NULL,
    activo BOOLEAN DEFAULT TRUE
);

INSERT INTO administradores (usuario, contrasena, nombre, correo) 
VALUES ('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Administrador Principal', 'admin@imesys.com');
select * from administradores;


-- Insertar el administrador principal (credenciales fijas)
INSERT INTO administradores (usuario, contrasena, nombre, correo) 
VALUES ('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Administrador Principal', 'admin@imesys.com');
-- La contraseña es "password" (hasheada)
