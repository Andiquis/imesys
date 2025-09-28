-- Tabla: v2_citas
CREATE TABLE IF NOT EXISTS bd_imesys.v2_citas (
  id_cita INT NOT NULL AUTO_INCREMENT,
  id_usuario INT DEFAULT NULL,
  id_medico INT DEFAULT NULL,
  fecha_cita DATETIME NOT NULL,
  estado ENUM('Pendiente', 'Confirmada', 'Cancelada', 'Completada') DEFAULT 'Pendiente',
  motivo TEXT DEFAULT NULL,
  respuesta TEXT DEFAULT NULL,
  PRIMARY KEY (id_cita),
  INDEX (id_usuario),
  INDEX (id_medico),
  CONSTRAINT v2_citas_ibfk_1 FOREIGN KEY (id_usuario) REFERENCES bd_imesys.usuarios (id_usuario) ON DELETE CASCADE,
  CONSTRAINT v2_citas_ibfk_2 FOREIGN KEY (id_medico) REFERENCES bd_imesys.medicos (id_medico) ON DELETE CASCADE
) ENGINE=InnoDB
DEFAULT CHARSET=utf8mb4
COLLATE=utf8mb4_0900_ai_ci;

-- Tabla: v2_actividades_personales_medico
CREATE TABLE IF NOT EXISTS bd_imesys.v2_actividades_personales_medico (
  id_actividad INT NOT NULL AUTO_INCREMENT,
  id_medico INT NOT NULL,
  titulo VARCHAR(255) NOT NULL,
  descripcion TEXT,
  fecha_inicio DATETIME NOT NULL,
  fecha_fin DATETIME NOT NULL,
  tipo ENUM('Reunión', 'Evento', 'Descanso', 'Otro') DEFAULT 'Otro',
  estado ENUM('Programada', 'Cancelada', 'Completada') DEFAULT 'Programada',
  fecha_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id_actividad),
  CONSTRAINT v2_actividades_ibfk_1 FOREIGN KEY (id_medico) REFERENCES bd_imesys.medicos (id_medico) ON DELETE CASCADE
) ENGINE=InnoDB
DEFAULT CHARSET=utf8mb4
COLLATE=utf8mb4_0900_ai_ci;

-- Tabla: v2_disponibilidad_medico
CREATE TABLE IF NOT EXISTS bd_imesys.v2_disponibilidad_medico (
  id_disponibilidad INT NOT NULL AUTO_INCREMENT,
  id_medico INT NOT NULL,
  fecha_hora DATETIME NOT NULL,
  estado ENUM('Disponible', 'No disponible') DEFAULT 'Disponible',
  observaciones TEXT,
  fecha_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id_disponibilidad),
  CONSTRAINT v2_disponibilidad_ibfk_1 FOREIGN KEY (id_medico) REFERENCES bd_imesys.medicos (id_medico) ON DELETE CASCADE
) ENGINE=InnoDB
DEFAULT CHARSET=utf8mb4
COLLATE=utf8mb4_0900_ai_ci;
