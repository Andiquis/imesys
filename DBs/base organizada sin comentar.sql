DROP DATABASE IF EXISTS BD_imesys;
CREATE DATABASE BD_imesys1;
USE BD_imesys1;

-- Tabla de Usuarios (Pacientes y Usuarios Generales)
CREATE TABLE usuarios (
    id_usuario INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    apellido VARCHAR(100) NOT NULL,
    dni VARCHAR(8) UNIQUE,
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
    direccion_consultorio VARCHAR(200),
    fecha_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_especialidad) REFERENCES especialidades(id_especialidad) ON DELETE CASCADE
);

-- Tabla de Agenda del Médico
CREATE TABLE agenda_medico (
    id_agenda INT AUTO_INCREMENT PRIMARY KEY,
    id_medico INT NOT NULL,
    fecha_hora DATETIME NOT NULL,
    estado ENUM('Disponible', 'No disponible') DEFAULT 'Disponible',
    etiqueta VARCHAR(100),
    recordatorio TEXT,
    fecha_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_medico) REFERENCES medicos(id_medico) ON DELETE CASCADE
);

-- Tabla de Clasificación de Médicos
CREATE TABLE clasificacion_medicos (
    id_clasificacion INT AUTO_INCREMENT PRIMARY KEY,
    id_usuario INT NOT NULL,
    id_medico INT NOT NULL,
    puntuacion INT CHECK (puntuacion BETWEEN 1 AND 5),
    comentario TEXT,
    anonimo BOOLEAN DEFAULT FALSE,
    fecha_clasificacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_usuario) REFERENCES usuarios(id_usuario) ON DELETE CASCADE,
    FOREIGN KEY (id_medico) REFERENCES medicos(id_medico) ON DELETE CASCADE
);

-- Tabla de Historial de Consultas
CREATE TABLE historial_consultas (
    id_historial INT AUTO_INCREMENT PRIMARY KEY,
    id_usuario INT NOT NULL,
    id_medico INT NOT NULL,
    motivo TEXT NOT NULL,
    observacion TEXT,
    diagnostico TEXT,
    tratamiento TEXT,
    imagen VARCHAR(255),
    dato_opcional TEXT,
    fecha_hora DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_usuario) REFERENCES usuarios(id_usuario) ON DELETE CASCADE,
    FOREIGN KEY (id_medico) REFERENCES medicos(id_medico) ON DELETE CASCADE
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
    resultado_prediccion TEXT,
    fecha_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
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

-- Tabla de Imágenes Médicas
CREATE TABLE imagenes_medicas (
    id_imagen INT AUTO_INCREMENT PRIMARY KEY,
    id_medico INT,
    id_usuario INT,
    ruta_imagen VARCHAR(255) NOT NULL,
    fecha_subida TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_medico) REFERENCES medicos(id_medico) ON DELETE CASCADE,
    FOREIGN KEY (id_usuario) REFERENCES usuarios(id_usuario) ON DELETE CASCADE
);

-- Tabla de Resultados IA (Radiografías)
CREATE TABLE resultados_ia (
    id_resultado INT AUTO_INCREMENT PRIMARY KEY,
    id_imagen INT,
    diagnostico TEXT NOT NULL,
    probabilidad DECIMAL(5,2) NOT NULL,
    fecha_analisis TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_imagen) REFERENCES imagenes_medicas(id_imagen) ON DELETE CASCADE
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

-- Tabla de Recetas
CREATE TABLE recetas (
    id_receta INT AUTO_INCREMENT PRIMARY KEY,
    id_medico INT NOT NULL,
    id_paciente INT NOT NULL,
    fecha_emision DATETIME NOT NULL,
    medicamentos TEXT NOT NULL,
    instrucciones TEXT NOT NULL,
    observaciones TEXT,
    FOREIGN KEY (id_medico) REFERENCES medicos(id_medico),
    FOREIGN KEY (id_paciente) REFERENCES usuarios(id_usuario)
);

-- Tabla de Recompensas
CREATE TABLE recompensas (
    id_recompensa INT AUTO_INCREMENT PRIMARY KEY,
    id_usuario INT,
    puntos INT NOT NULL DEFAULT 0,
    descripcion TEXT,
    fecha_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    fecha_actualizacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (id_usuario) REFERENCES usuarios(id_usuario) ON DELETE CASCADE
);

-- Tabla de Transacciones de Puntos
CREATE TABLE transacciones_puntos (
    id_transaccion INT AUTO_INCREMENT PRIMARY KEY,
    id_usuario INT NOT NULL,
    tipo ENUM('ganancia', 'canje') NOT NULL,
    puntos INT NOT NULL,
    descripcion VARCHAR(255) NOT NULL,
    referencia VARCHAR(100),
    fecha_transaccion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
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

-- Tabla de Canjes
CREATE TABLE canjes (
    id_canje INT AUTO_INCREMENT PRIMARY KEY,
    id_usuario INT NOT NULL,
    id_transaccion INT NOT NULL,
    puntos_canjeados INT NOT NULL,
    valor_equivalente DECIMAL(10,2) NOT NULL,
    codigo_canje VARCHAR(50) UNIQUE NOT NULL,
    estado ENUM('generado', 'utilizado', 'expirado') DEFAULT 'generado',
    fecha_generacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    fecha_uso TIMESTAMP NULL,
    id_farmacia INT NULL,
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

-- Inserts de configuración de puntos
INSERT INTO config_puntos (clave, valor, descripcion) VALUES 
('puntos_por_registro', '50', 'Puntos por registrarse en la plataforma'),
('puntos_por_cita', '30', 'Puntos por cada cita médica completada'),
('puntos_por_comentario', '10', 'Puntos por dejar un comentario válido'),
('puntos_por_biometrico', '20', 'Puntos por completar un análisis biométrico'),
('max_puntos_diarios', '20', 'Máximo de puntos que se pueden canjear por día'),
('valor_por_punto', '0.10', 'Valor en dinero de cada punto (ej: 0.10 = 10 céntimos por punto)'),
('duracion_qr_horas', '24', 'Tiempo en horas que el QR es válido');

-- Insert de especialidades
INSERT INTO especialidades (nombre_especialidad) VALUES
('Cardiología'),
('Neurología'),
('Pediatría');

-- Insert de farmacia de prueba
INSERT INTO farmacias (nombre, direccion, telefono, correo)
VALUES ('Botica Central Cusco', 'Av. El Sol 123, Cusco', '084-123456', 'boticacentral@correo.com');

-- Insert de clasificaciones de prueba
INSERT INTO clasificacion_medicos (id_usuario, id_medico, puntuacion, comentario, anonimo)
VALUES
(1, 1, 3, 'El doctor fue amable, pero la atención demoró un poco.', FALSE),
(1, 2, 4, 'Buena atención, explicó todo con claridad.', FALSE),
(1, 3, 2, 'No me sentí muy cómodo con la consulta.', TRUE),
(1, 4, 5, 'Excelente médico.', FALSE);


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
select * from canjes