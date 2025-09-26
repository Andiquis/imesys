-- =============================================
-- BASE DE DATOS: IMESYS (Sistema Médico Inteligente)
-- Versión: 2.0
-- Autor: [Tu Nombre]
-- Fecha: [Fecha Actual]
-- Descripción: Sistema integral de gestión médica con IA
-- =============================================

DROP DATABASE IF EXISTS BD_imesysc;
CREATE DATABASE BD_imesysc;
USE BD_imesysc;

-- =============================================
-- TABLAS MAESTRAS
-- =============================================

-- Tabla de Usuarios (Pacientes y Usuarios Generales)
CREATE TABLE usuarios (
    id_usuario INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL COMMENT 'Nombre(s) del usuario',
    apellido VARCHAR(100) NOT NULL COMMENT 'Apellido(s) del usuario',
    dni VARCHAR(8) UNIQUE COMMENT 'Documento Nacional de Identidad (DNI)',
    correo VARCHAR(150) UNIQUE NOT NULL COMMENT 'Correo electrónico único del usuario',
    contrasena VARCHAR(100) NOT NULL COMMENT 'Contraseña hasheada para autenticación',
    telefono VARCHAR(20) COMMENT 'Número de teléfono de contacto',
    direccion TEXT COMMENT 'Dirección completa del usuario',
    fecha_nacimiento DATE COMMENT 'Fecha de nacimiento del usuario',
    genero ENUM('Masculino', 'Femenino', 'Otro') COMMENT 'Género del usuario',
    foto VARCHAR(255) COMMENT 'Ruta de la foto de perfil del usuario',
    fecha_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT 'Fecha y hora de registro en el sistema',
    ultimo_login TIMESTAMP NULL COMMENT 'Fecha y hora del último inicio de sesión',
    activo BOOLEAN DEFAULT TRUE COMMENT 'Indica si la cuenta está activa'
) COMMENT 'Tabla principal de usuarios/pacientes del sistema';



-- Tabla de Especialidades Médicas
CREATE TABLE especialidades (
    id_especialidad INT AUTO_INCREMENT PRIMARY KEY,
    nombre_especialidad VARCHAR(100) NOT NULL UNIQUE COMMENT 'Nombre de la especialidad médica',
    descripcion TEXT COMMENT 'Descripción detallada de la especialidad',
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT 'Fecha de creación del registro'
) COMMENT 'Catálogo de especialidades médicas disponibles en el sistema';
 
-- Tabla de Médicos
CREATE TABLE medicos (
    id_medico INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL COMMENT 'Nombre(s) del médico',
    apellido VARCHAR(100) NOT NULL COMMENT 'Apellido(s) del médico',
    correo VARCHAR(150) UNIQUE NOT NULL COMMENT 'Correo electrónico único del médico',
    contrasena VARCHAR(100) NOT NULL COMMENT 'Contraseña hasheada para autenticación',
    telefono VARCHAR(20) COMMENT 'Número de teléfono de contacto',
    id_especialidad INT NOT NULL COMMENT 'Especialidad principal del médico',
    numero_colegiatura VARCHAR(50) UNIQUE NOT NULL COMMENT 'Número de colegiatura profesional',
    foto VARCHAR(255) COMMENT 'Ruta de la foto de perfil del médico',
    fecha_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT 'Fecha y hora de registro en el sistema',
    direccion_consultorio VARCHAR(200) COMMENT 'Dirección del consultorio principal',
    activo BOOLEAN DEFAULT TRUE COMMENT 'Indica si el médico está activo en el sistema',
    FOREIGN KEY (id_especialidad) REFERENCES especialidades(id_especialidad) ON DELETE CASCADE
) COMMENT 'Tabla de médicos registrados en el sistema';

-- Tabla de Farmacias
CREATE TABLE farmacias (
    id_farmacia INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(150) NOT NULL COMMENT 'Nombre de la farmacia',
    direccion TEXT NOT NULL COMMENT 'Dirección física de la farmacia',
    telefono VARCHAR(20) COMMENT 'Teléfono de contacto',
    correo VARCHAR(150) UNIQUE COMMENT 'Correo electrónico de contacto',
    contrasena VARCHAR(255) NOT NULL COMMENT 'Contraseña hasheada para autenticación',
    activa BOOLEAN DEFAULT TRUE COMMENT 'Indica si la farmacia está activa en el sistema',
    fecha_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT 'Fecha de registro en el sistema',
    fecha_ultimo_login TIMESTAMP NULL COMMENT 'Fecha del último inicio de sesión',
    token_recuperacion VARCHAR(100) NULL COMMENT 'Token para recuperación de contraseña',
    expiracion_token TIMESTAMP NULL COMMENT 'Fecha de expiración del token'
) COMMENT 'Tabla de farmacias afiliadas al sistema';

-- Tabla de Administradores
CREATE TABLE administradores (
    id_admin INT AUTO_INCREMENT PRIMARY KEY,
    usuario VARCHAR(50) UNIQUE NOT NULL COMMENT 'Nombre de usuario para login',
    contrasena VARCHAR(255) NOT NULL COMMENT 'Contraseña hasheada para autenticación',
    nombre VARCHAR(100) NOT NULL COMMENT 'Nombre completo del administrador',
    correo VARCHAR(150) UNIQUE NOT NULL COMMENT 'Correo electrónico del administrador',
    fecha_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT 'Fecha de creación del administrador',
    ultimo_acceso TIMESTAMP NULL COMMENT 'Fecha y hora del último acceso',
    activo BOOLEAN DEFAULT TRUE COMMENT 'Indica si el administrador está activo'
) COMMENT 'Tabla de administradores del sistema';

-- =============================================
-- TABLAS TRANSACCIONALES
-- =============================================

-- Tabla de Citas Médicas
CREATE TABLE citas (
    id_cita INT AUTO_INCREMENT PRIMARY KEY,
    id_usuario INT COMMENT 'Paciente que solicita la cita',
    id_medico INT COMMENT 'Médico asignado para la cita',
    fecha_cita DATETIME NOT NULL COMMENT 'Fecha y hora programada para la cita',
    estado ENUM('Pendiente', 'Confirmada', 'Cancelada', 'Completada') DEFAULT 'Pendiente' COMMENT 'Estado actual de la cita',
    motivo TEXT COMMENT 'Razón de la cita descrita por el paciente',
    respuesta TEXT COMMENT 'Comentarios adicionales del médico',
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT 'Fecha y hora de creación del registro',
    FOREIGN KEY (id_usuario) REFERENCES usuarios(id_usuario) ON DELETE CASCADE,
    FOREIGN KEY (id_medico) REFERENCES medicos(id_medico) ON DELETE CASCADE
) COMMENT 'Registro de citas médicas programadas';

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
    dato_opcional TEXT COMMENT 'Campo adicional para información extra',
    FOREIGN KEY (id_usuario) REFERENCES usuarios(id_usuario) ON DELETE CASCADE,
    FOREIGN KEY (id_medico) REFERENCES medicos(id_medico) ON DELETE CASCADE
) COMMENT 'Registro histórico de consultas médicas realizadas';

-- Tabla de Imágenes Médicas
CREATE TABLE imagenes_medicas (
    id_imagen INT AUTO_INCREMENT PRIMARY KEY,
    id_medico INT COMMENT 'Médico que subió la imagen',
    id_usuario INT COMMENT 'Paciente dueño de la imagen',
    ruta_imagen VARCHAR(255) NOT NULL COMMENT 'Ruta de almacenamiento de la imagen',
    tipo_imagen VARCHAR(50) COMMENT 'Tipo de imagen (Rayos X, Resonancia, etc.)',
    fecha_subida TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT 'Fecha y hora de subida',
    notas TEXT COMMENT 'Notas adicionales sobre la imagen',
    FOREIGN KEY (id_medico) REFERENCES medicos(id_medico) ON DELETE CASCADE,
    FOREIGN KEY (id_usuario) REFERENCES usuarios(id_usuario) ON DELETE CASCADE
) COMMENT 'Registro de imágenes médicas subidas al sistema';

-- Tabla de Resultados IA (Análisis de Imágenes)
CREATE TABLE resultados_ia (
    id_resultado INT AUTO_INCREMENT PRIMARY KEY,
    id_imagen INT COMMENT 'Imagen médica analizada',
    diagnostico TEXT NOT NULL COMMENT 'Diagnóstico generado por la IA',
    probabilidad DECIMAL(5,2) NOT NULL COMMENT 'Probabilidad de acierto del diagnóstico',
    fecha_analisis TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT 'Fecha y hora del análisis',
    modelo_ia VARCHAR(100) COMMENT 'Versión del modelo de IA utilizado',
    FOREIGN KEY (id_imagen) REFERENCES imagenes_medicas(id_imagen) ON DELETE CASCADE
) COMMENT 'Resultados de análisis de imágenes médicas por IA';

-- Tabla de Datos Biométricos
CREATE TABLE datos_biometricos (
    id_dato INT AUTO_INCREMENT PRIMARY KEY,
    id_usuario INT COMMENT 'Paciente asociado a los datos',
    peso DECIMAL(5,2) COMMENT 'Peso en kilogramos',
    altura DECIMAL(5,2) COMMENT 'Altura en metros',
    presion_arterial VARCHAR(50) COMMENT 'Presión arterial (ej: 120/80)',
    frecuencia_cardiaca INT COMMENT 'Pulsaciones por minuto',
    nivel_glucosa DECIMAL(5,2) COMMENT 'Nivel de glucosa en sangre',
    fecha_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT 'Fecha y hora del registro',
    resultado_prediccion TEXT COMMENT 'Predicción o análisis de los datos biométricos',
    FOREIGN KEY (id_usuario) REFERENCES usuarios(id_usuario) ON DELETE CASCADE
) COMMENT 'Registro de datos biométricos de los pacientes';

-- Tabla de Resultados IA Biométricos
CREATE TABLE resultados_ia_biometrico (
    id_resultado_biometrico INT AUTO_INCREMENT PRIMARY KEY,
    id_usuario INT NOT NULL COMMENT 'Paciente asociado al análisis',
    nombre VARCHAR(100) NOT NULL COMMENT 'Nombre del análisis realizado',
    prediccion TEXT NOT NULL COMMENT 'Resultado de la predicción biométrica',
    fecha_hora DATETIME DEFAULT CURRENT_TIMESTAMP COMMENT 'Fecha y hora del análisis',
    modelo_ia VARCHAR(100) COMMENT 'Modelo de IA utilizado para el análisis',
    FOREIGN KEY (id_usuario) REFERENCES usuarios(id_usuario) ON DELETE CASCADE
) COMMENT 'Resultados de análisis biométricos por IA';

-- =============================================
-- TABLAS DE GESTIÓN DE RECOMPENSAS
-- =============================================

-- Tabla de Recompensas (Saldo de Puntos)
CREATE TABLE recompensas (
    id_recompensa INT AUTO_INCREMENT PRIMARY KEY,
    id_usuario INT NOT NULL COMMENT 'Usuario dueño de los puntos',
    puntos INT NOT NULL DEFAULT 0 COMMENT 'Saldo actual de puntos',
    descripcion TEXT COMMENT 'Descripción adicional',
    fecha_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT 'Fecha de creación del registro',
    fecha_actualizacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'Fecha de última actualización',
    FOREIGN KEY (id_usuario) REFERENCES usuarios(id_usuario) ON DELETE CASCADE
) COMMENT 'Saldo de puntos de recompensa por usuario';

-- Tabla de Transacciones de Puntos
CREATE TABLE transacciones_puntos (
    id_transaccion INT AUTO_INCREMENT PRIMARY KEY,
    id_usuario INT NOT NULL COMMENT 'Usuario asociado a la transacción',
    tipo ENUM('ganancia', 'canje') NOT NULL COMMENT 'Tipo de transacción: ganancia o canje',
    puntos INT NOT NULL COMMENT 'Cantidad de puntos (positivo para ganancia, negativo para canje)',
    descripcion VARCHAR(255) NOT NULL COMMENT 'Motivo de la transacción',
    referencia VARCHAR(100) COMMENT 'Referencia externa o ID relacionado',
    fecha_transaccion TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT 'Fecha y hora de la transacción',
    FOREIGN KEY (id_usuario) REFERENCES usuarios(id_usuario) ON DELETE CASCADE
) COMMENT 'Historial de transacciones del sistema de recompensas';

-- Tabla de Canjes (QR Generados)
CREATE TABLE canjes (
    id_canje INT AUTO_INCREMENT PRIMARY KEY,
    id_usuario INT NOT NULL COMMENT 'Usuario que realiza el canje',
    id_transaccion INT NOT NULL COMMENT 'Transacción asociada al canje',
    puntos_canjeados INT NOT NULL COMMENT 'Cantidad de puntos canjeados',
    valor_equivalente DECIMAL(10,2) NOT NULL COMMENT 'Valor monetario equivalente',
    codigo_canje VARCHAR(50) UNIQUE NOT NULL COMMENT 'Código QR/único para el canje',
    estado ENUM('generado', 'utilizado', 'expirado') DEFAULT 'generado' COMMENT 'Estado actual del canje',
    fecha_generacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT 'Fecha de creación del canje',
    fecha_uso TIMESTAMP NULL COMMENT 'Fecha de utilización del canje',
    id_farmacia INT NULL COMMENT 'Farmacia donde se canjeó',
    FOREIGN KEY (id_usuario) REFERENCES usuarios(id_usuario) ON DELETE CASCADE,
    FOREIGN KEY (id_transaccion) REFERENCES transacciones_puntos(id_transaccion) ON DELETE CASCADE,
    FOREIGN KEY (id_farmacia) REFERENCES farmacias(id_farmacia) ON DELETE SET NULL
) COMMENT 'Registro de canjes realizados con puntos de recompensa';

-- Tabla de Configuración de Puntos
CREATE TABLE config_puntos (
    id_config INT AUTO_INCREMENT PRIMARY KEY,
    clave VARCHAR(50) UNIQUE NOT NULL COMMENT 'Clave única de configuración',
    valor VARCHAR(255) NOT NULL COMMENT 'Valor asociado a la clave',
    descripcion TEXT COMMENT 'Descripción del parámetro configurado'
) COMMENT 'Configuración del sistema de puntos de recompensa';

-- =============================================
-- TABLAS ADICIONALES
-- =============================================

-- Tabla de Agenda del Médico
CREATE TABLE agenda_medico (
    id_agenda INT AUTO_INCREMENT PRIMARY KEY,
    id_medico INT NOT NULL COMMENT 'Médico dueño de la agenda',
    fecha_hora DATETIME NOT NULL COMMENT 'Fecha y hora del evento',
    estado ENUM('Disponible', 'No disponible') DEFAULT 'Disponible' COMMENT 'Disponibilidad del médico',
    etiqueta VARCHAR(100) COMMENT 'Etiqueta descriptiva del evento',
    recordatorio TEXT COMMENT 'Notas adicionales sobre el evento',
    fecha_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT 'Fecha de creación del registro',
    FOREIGN KEY (id_medico) REFERENCES medicos(id_medico) ON DELETE CASCADE
) COMMENT 'Agenda personal de los médicos';

-- Tabla de Clasificación de Médicos
CREATE TABLE clasificacion_medicos (
    id_clasificacion INT AUTO_INCREMENT PRIMARY KEY,
    id_usuario INT NOT NULL COMMENT 'Usuario que realiza la clasificación',
    id_medico INT NOT NULL COMMENT 'Médico evaluado',
    puntuacion INT CHECK (puntuacion BETWEEN 1 AND 5) COMMENT 'Puntuación de 1 a 5 estrellas',
    comentario TEXT COMMENT 'Comentario sobre la evaluación',
    fecha_clasificacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT 'Fecha de la evaluación',
    anonimo BOOLEAN DEFAULT FALSE COMMENT 'Indica si la evaluación es anónima',
    FOREIGN KEY (id_usuario) REFERENCES usuarios(id_usuario) ON DELETE CASCADE,
    FOREIGN KEY (id_medico) REFERENCES medicos(id_medico) ON DELETE CASCADE
) COMMENT 'Evaluaciones y reseñas de los médicos por pacientes';

-- Tabla de Recetas Médicas
CREATE TABLE recetas (
    id_receta INT AUTO_INCREMENT PRIMARY KEY,
    id_medico INT NOT NULL COMMENT 'Médico que emite la receta',
    id_paciente INT NOT NULL COMMENT 'Paciente destinatario de la receta',
    fecha_emision DATETIME NOT NULL COMMENT 'Fecha y hora de emisión',
    medicamentos TEXT NOT NULL COMMENT 'JSON con medicamentos recetados',
    instrucciones TEXT NOT NULL COMMENT 'Instrucciones para el paciente',
    observaciones TEXT COMMENT 'Observaciones adicionales',
    FOREIGN KEY (id_medico) REFERENCES medicos(id_medico),
    FOREIGN KEY (id_paciente) REFERENCES usuarios(id_usuario)
) COMMENT 'Recetas médicas electrónicas';

-- Tabla de Auditoría de Farmacias
CREATE TABLE auditoria_farmacias (
    id_auditoria INT AUTO_INCREMENT PRIMARY KEY,
    id_farmacia INT NOT NULL COMMENT 'Farmacia asociada al evento',
    accion VARCHAR(50) NOT NULL COMMENT 'Tipo de acción registrada',
    detalles TEXT COMMENT 'Detalles adicionales del evento',
    direccion_ip VARCHAR(45) COMMENT 'Dirección IP desde donde se realizó la acción',
    user_agent VARCHAR(255) COMMENT 'Agente de usuario (navegador, dispositivo)',
    fecha_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT 'Fecha y hora del evento',
    FOREIGN KEY (id_farmacia) REFERENCES farmacias(id_farmacia) ON DELETE CASCADE
) COMMENT 'Registro de auditoría de actividades de farmacias';

-- =============================================
-- DATOS INICIALES
-- =============================================

-- Insertar especialidades médicas básicas
INSERT INTO especialidades (nombre_especialidad, descripcion) VALUES
('Cardiología', 'Especialidad médica que se ocupa de las enfermedades del corazón y del aparato circulatorio'),
('Neurología', 'Especialidad médica que trata los trastornos del sistema nervioso'),
('Pediatría', 'Especialidad médica que estudia al niño y sus enfermedades');

-- Insertar administrador principal
INSERT INTO administradores (usuario, contrasena, nombre, correo) 
VALUES ('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Administrador Principal', 'admin@imesys.com');

-- Insertar configuración del sistema de puntos
INSERT INTO config_puntos (clave, valor, descripcion) VALUES 
('puntos_por_registro', '50', 'Puntos por registrarse en la plataforma'),
('puntos_por_cita', '30', 'Puntos por cada cita médica completada'),
('puntos_por_comentario', '10', 'Puntos por dejar un comentario válido'),
('puntos_por_biometrico', '20', 'Puntos por completar un análisis biométrico'),
('max_puntos_diarios', '20', 'Máximo de puntos que se pueden canjear por día'),
('valor_por_punto', '0.10', 'Valor en dinero de cada punto (ej: 0.10 = 10 céntimos por punto)'),
('duracion_qr_horas', '24', 'Tiempo en horas que el QR es válido'),
('puntos_por_sesion', '2', 'Puntos por iniciar sesión cada día');

-- Insertar farmacia de ejemplo
INSERT INTO farmacias (nombre, direccion, telefono, correo, contrasena) 
VALUES ('Botica Central Cusco', 'Av. El Sol 123, Cusco', '084-123456', 'boticacentral@correo.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi');

-- =============================================
-- DATOS DE PRUEBA (OPCIONALES)
-- =============================================

-- Usuarios de prueba
INSERT INTO usuarios (nombre, apellido, dni, correo, contrasena, telefono, direccion, fecha_nacimiento, genero) VALUES
('Juan', 'Pérez', '12345678', 'juan.perez@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '987654321', 'Av. Siempre Viva 123', '1985-06-15', 'Masculino'),
('María', 'López', '87654321', 'maria.lopez@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '987654322', 'Calle Falsa 456', '1990-08-20', 'Femenino');

-- Médicos de prueba
INSERT INTO medicos (nombre, apellido, correo, contrasena, telefono, id_especialidad, numero_colegiatura) VALUES  
('Carlos', 'Fernández', 'carlos.fernandez@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '987456789', 1, 'CMP789123'),
('Ana', 'García', 'ana.garcia@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '987654789', 2, 'CMP456789');

-- Recompensas de prueba
INSERT INTO recompensas (id_usuario, puntos, descripcion) VALUES 
(1, 100, 'Puntos iniciales por registro'),
(2, 50, 'Puntos iniciales por registro');

-- Clasificaciones de prueba
INSERT INTO clasificacion_medicos (id_usuario, id_medico, puntuacion, comentario, anonimo) VALUES
(1, 1, 5, 'Excelente atención, muy profesional', FALSE),
(2, 1, 4, 'Buen médico, pero la espera fue larga', TRUE);

-- Agenda de prueba
INSERT INTO agenda_medico (id_medico, fecha_hora, estado, etiqueta) VALUES
(1, DATE_ADD(NOW(), INTERVAL 1 DAY), 'Disponible', 'Consulta general'),
(1, DATE_ADD(NOW(), INTERVAL 2 DAY), 'Disponible', 'Consulta de seguimiento');


select * from farmacias;