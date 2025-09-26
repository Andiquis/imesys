CREATE TABLE IF NOT EXISTS datos_biometricos (
  id_dato INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
  id_usuario INT NULL DEFAULT NULL,
  peso DECIMAL(5,2) NULL DEFAULT NULL,
  altura DECIMAL(5,2) NULL DEFAULT NULL,
  presion_arterial VARCHAR(50) NULL DEFAULT NULL,
  frecuencia_cardiaca INT NULL DEFAULT NULL,
  nivel_glucosa DECIMAL(5,2) NULL DEFAULT NULL,
  fecha_registro TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  descripcion_resultado TEXT NULL DEFAULT 'mi resultado',
  resultado_prediccion TEXT NULL DEFAULT NULL,
  INDEX id_usuario (id_usuario ASC) VISIBLE,
  CONSTRAINT datos_biometricos_ibfk_1
    FOREIGN KEY (id_usuario)
    REFERENCES usuarios (id_usuario)
    ON DELETE CASCADE
) ENGINE = InnoDB DEFAULT CHARACTER SET = utf8mb4 COLLATE = utf8mb4_0900_ai_ci;
