-- MySQL Workbench Forward Engineering

SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0;
SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0;
SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION';

-- -----------------------------------------------------
-- Schema mydb
-- -----------------------------------------------------
-- -----------------------------------------------------
-- Schema bd_imesys
-- -----------------------------------------------------

-- -----------------------------------------------------
-- Schema bd_imesys
-- -----------------------------------------------------
CREATE SCHEMA IF NOT EXISTS `bd_imesys` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci ;
USE `bd_imesys` ;

-- -----------------------------------------------------
-- Table `bd_imesys`.`administradores`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `bd_imesys`.`administradores` (
  `id_admin` INT NOT NULL AUTO_INCREMENT,
  `usuario` VARCHAR(50) NOT NULL,
  `contrasena` VARCHAR(255) NOT NULL,
  `template_facial` VARCHAR(2000) NULL DEFAULT NULL,
  `reconocimiento_facial_activo` TINYINT(1) NOT NULL DEFAULT '1',
  `nombre` VARCHAR(100) NOT NULL,
  `correo` VARCHAR(150) NOT NULL,
  `fecha_registro` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  `ultimo_acceso` TIMESTAMP NULL DEFAULT NULL,
  `activo` TINYINT(1) NULL DEFAULT '1',
  `last_password_reset` TIMESTAMP NULL DEFAULT NULL,
  `intentos_fallidos` INT NOT NULL DEFAULT '0',
  `locked_until` TIMESTAMP NULL DEFAULT NULL,
  `rol` ENUM('superadmin', 'admin', 'auditor') NOT NULL DEFAULT 'admin',
  PRIMARY KEY (`id_admin`),
  UNIQUE INDEX `usuario` (`usuario` ASC) VISIBLE,
  UNIQUE INDEX `correo` (`correo` ASC) VISIBLE)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8mb4
COLLATE = utf8mb4_0900_ai_ci;


-- -----------------------------------------------------
-- Table `bd_imesys`.`especialidades`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `bd_imesys`.`especialidades` (
  `id_especialidad` INT NOT NULL AUTO_INCREMENT,
  `nombre_especialidad` VARCHAR(100) NOT NULL,
  PRIMARY KEY (`id_especialidad`),
  UNIQUE INDEX `nombre_especialidad` (`nombre_especialidad` ASC) VISIBLE)
ENGINE = InnoDB
AUTO_INCREMENT = 4
DEFAULT CHARACTER SET = utf8mb4
COLLATE = utf8mb4_0900_ai_ci;


-- -----------------------------------------------------
-- Table `bd_imesys`.`medicos`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `bd_imesys`.`medicos` (
  `id_medico` INT NOT NULL AUTO_INCREMENT,
  `nombre` VARCHAR(100) NOT NULL,
  `apellido` VARCHAR(100) NOT NULL,
  `correo` VARCHAR(150) NOT NULL,
  `contrasena` VARCHAR(100) NOT NULL,
  `telefono` VARCHAR(20) NULL DEFAULT NULL,
  `id_especialidad` INT NOT NULL,
  `numero_colegiatura` VARCHAR(50) NOT NULL,
  `foto` VARCHAR(255) NULL DEFAULT NULL,
  `fecha_registro` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  `direccion_consultorio` VARCHAR(200) NULL DEFAULT NULL,
  PRIMARY KEY (`id_medico`),
  UNIQUE INDEX `correo` (`correo` ASC) VISIBLE,
  UNIQUE INDEX `numero_colegiatura` (`numero_colegiatura` ASC) VISIBLE,
  INDEX `fk_especialidad_medicos` (`id_especialidad` ASC) VISIBLE,
  CONSTRAINT `fk_especialidad_medicos`
    FOREIGN KEY (`id_especialidad`)
    REFERENCES `bd_imesys`.`especialidades` (`id_especialidad`))
ENGINE = InnoDB
AUTO_INCREMENT = 13
DEFAULT CHARACTER SET = utf8mb4
COLLATE = utf8mb4_0900_ai_ci;


-- -----------------------------------------------------
-- Table `bd_imesys`.`agenda_medico`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `bd_imesys`.`agenda_medico` (
  `id_agenda` INT NOT NULL AUTO_INCREMENT,
  `id_medico` INT NOT NULL,
  `fecha_hora` DATETIME NOT NULL,
  `estado` ENUM('Disponible', 'No disponible') NULL DEFAULT 'Disponible',
  `etiqueta` VARCHAR(100) NULL DEFAULT NULL,
  `recordatorio` TEXT NULL DEFAULT NULL,
  `fecha_registro` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_agenda`),
  INDEX `id_medico` (`id_medico` ASC) VISIBLE,
  CONSTRAINT `agenda_medico_ibfk_1`
    FOREIGN KEY (`id_medico`)
    REFERENCES `bd_imesys`.`medicos` (`id_medico`)
    ON DELETE CASCADE)
ENGINE = InnoDB
AUTO_INCREMENT = 485
DEFAULT CHARACTER SET = utf8mb4
COLLATE = utf8mb4_0900_ai_ci;


-- -----------------------------------------------------
-- Table `bd_imesys`.`farmacias`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `bd_imesys`.`farmacias` (
  `id_farmacia` INT NOT NULL AUTO_INCREMENT,
  `nombre` VARCHAR(150) NOT NULL,
  `direccion` TEXT NOT NULL,
  `telefono` VARCHAR(20) NULL DEFAULT NULL,
  `correo` VARCHAR(150) NULL DEFAULT NULL,
  `fecha_registro` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_farmacia`),
  UNIQUE INDEX `correo` (`correo` ASC) VISIBLE)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8mb4
COLLATE = utf8mb4_0900_ai_ci;


-- -----------------------------------------------------
-- Table `bd_imesys`.`auditoria_farmacias`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `bd_imesys`.`auditoria_farmacias` (
  `id_auditoria` INT NOT NULL AUTO_INCREMENT,
  `id_farmacia` INT NOT NULL,
  `accion` VARCHAR(50) NOT NULL COMMENT 'login, canje, etc.',
  `detalles` TEXT NULL DEFAULT NULL,
  `direccion_ip` VARCHAR(45) NULL DEFAULT NULL,
  `user_agent` VARCHAR(255) NULL DEFAULT NULL,
  `fecha_registro` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_auditoria`),
  INDEX `id_farmacia` (`id_farmacia` ASC) VISIBLE,
  CONSTRAINT `auditoria_farmacias_ibfk_1`
    FOREIGN KEY (`id_farmacia`)
    REFERENCES `bd_imesys`.`farmacias` (`id_farmacia`)
    ON DELETE CASCADE)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8mb4
COLLATE = utf8mb4_0900_ai_ci;


-- -----------------------------------------------------
-- Table `bd_imesys`.`usuarios`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `bd_imesys`.`usuarios` (
  `id_usuario` INT NOT NULL AUTO_INCREMENT,
  `nombre` VARCHAR(100) NOT NULL,
  `apellido` VARCHAR(100) NOT NULL,
  `correo` VARCHAR(150) NOT NULL,
  `contrasena` VARCHAR(100) NOT NULL,
  `telefono` VARCHAR(20) NULL DEFAULT NULL,
  `direccion` TEXT NULL DEFAULT NULL,
  `fecha_nacimiento` DATE NULL DEFAULT NULL,
  `genero` ENUM('Masculino', 'Femenino', 'Otro') NULL DEFAULT NULL,
  `foto` VARCHAR(255) NULL DEFAULT NULL,
  `fecha_registro` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  `dni` VARCHAR(20) NULL DEFAULT NULL,
  PRIMARY KEY (`id_usuario`),
  UNIQUE INDEX `correo` (`correo` ASC) VISIBLE,
  UNIQUE INDEX `unique_dni` (`dni` ASC) VISIBLE)
ENGINE = InnoDB
AUTO_INCREMENT = 20
DEFAULT CHARACTER SET = utf8mb4
COLLATE = utf8mb4_0900_ai_ci;


-- -----------------------------------------------------
-- Table `bd_imesys`.`transacciones_puntos`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `bd_imesys`.`transacciones_puntos` (
  `id_transaccion` INT NOT NULL AUTO_INCREMENT,
  `id_usuario` INT NOT NULL,
  `tipo` ENUM('ganancia', 'canje') NOT NULL COMMENT 'ganancia: puntos obtenidos, canje: puntos usados',
  `puntos` INT NOT NULL COMMENT 'Cantidad de puntos (positivo para ganancia, negativo para canje)',
  `descripcion` VARCHAR(255) NOT NULL COMMENT 'Motivo de la transacción',
  `referencia` VARCHAR(100) NULL DEFAULT NULL COMMENT 'Referencia externa o ID relacionado',
  `fecha_transaccion` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_transaccion`),
  INDEX `id_usuario` (`id_usuario` ASC) VISIBLE,
  CONSTRAINT `transacciones_puntos_ibfk_1`
    FOREIGN KEY (`id_usuario`)
    REFERENCES `bd_imesys`.`usuarios` (`id_usuario`)
    ON DELETE CASCADE)
ENGINE = InnoDB
AUTO_INCREMENT = 30
DEFAULT CHARACTER SET = utf8mb4
COLLATE = utf8mb4_0900_ai_ci;


-- -----------------------------------------------------
-- Table `bd_imesys`.`canjes`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `bd_imesys`.`canjes` (
  `id_canje` INT NOT NULL AUTO_INCREMENT,
  `id_usuario` INT NOT NULL,
  `id_transaccion` INT NOT NULL COMMENT 'Relación con la transacción de puntos',
  `puntos_canjeados` INT NOT NULL,
  `valor_equivalente` DECIMAL(10,2) NOT NULL COMMENT 'Valor en dinero equivalente',
  `codigo_canje` VARCHAR(50) NOT NULL COMMENT 'Código QR/único',
  `estado` ENUM('generado', 'utilizado', 'expirado') NULL DEFAULT 'generado',
  `fecha_generacion` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  `fecha_uso` TIMESTAMP NULL DEFAULT NULL COMMENT 'Cuando se canjea en farmacia',
  `id_farmacia` INT NULL DEFAULT NULL COMMENT 'Farmacia donde se canjeó',
  PRIMARY KEY (`id_canje`),
  UNIQUE INDEX `codigo_canje` (`codigo_canje` ASC) VISIBLE,
  INDEX `id_usuario` (`id_usuario` ASC) VISIBLE,
  INDEX `id_transaccion` (`id_transaccion` ASC) VISIBLE,
  INDEX `id_farmacia` (`id_farmacia` ASC) VISIBLE,
  CONSTRAINT `canjes_ibfk_1`
    FOREIGN KEY (`id_usuario`)
    REFERENCES `bd_imesys`.`usuarios` (`id_usuario`)
    ON DELETE CASCADE,
  CONSTRAINT `canjes_ibfk_2`
    FOREIGN KEY (`id_transaccion`)
    REFERENCES `bd_imesys`.`transacciones_puntos` (`id_transaccion`)
    ON DELETE CASCADE,
  CONSTRAINT `canjes_ibfk_3`
    FOREIGN KEY (`id_farmacia`)
    REFERENCES `bd_imesys`.`farmacias` (`id_farmacia`)
    ON DELETE SET NULL)
ENGINE = InnoDB
AUTO_INCREMENT = 5
DEFAULT CHARACTER SET = utf8mb4
COLLATE = utf8mb4_0900_ai_ci;


-- -----------------------------------------------------
-- Table `bd_imesys`.`citas`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `bd_imesys`.`citas` (
  `id_cita` INT NOT NULL AUTO_INCREMENT,
  `id_usuario` INT NULL DEFAULT NULL,
  `id_medico` INT NULL DEFAULT NULL,
  `fecha_cita` DATETIME NOT NULL,
  `estado` ENUM('Pendiente', 'Confirmada', 'Cancelada', 'Completada') NULL DEFAULT 'Pendiente',
  `motivo` TEXT NULL DEFAULT NULL,
  `respuesta` TEXT NULL DEFAULT NULL,
  PRIMARY KEY (`id_cita`),
  INDEX `id_usuario` (`id_usuario` ASC) VISIBLE,
  INDEX `id_medico` (`id_medico` ASC) VISIBLE,
  CONSTRAINT `citas_ibfk_1`
    FOREIGN KEY (`id_usuario`)
    REFERENCES `bd_imesys`.`usuarios` (`id_usuario`)
    ON DELETE CASCADE,
  CONSTRAINT `citas_ibfk_2`
    FOREIGN KEY (`id_medico`)
    REFERENCES `bd_imesys`.`medicos` (`id_medico`)
    ON DELETE CASCADE)
ENGINE = InnoDB
AUTO_INCREMENT = 334
DEFAULT CHARACTER SET = utf8mb4
COLLATE = utf8mb4_0900_ai_ci;


-- -----------------------------------------------------
-- Table `bd_imesys`.`clasificacion_medicos`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `bd_imesys`.`clasificacion_medicos` (
  `id_clasificacion` INT NOT NULL AUTO_INCREMENT,
  `id_usuario` INT NOT NULL,
  `id_medico` INT NOT NULL,
  `puntuacion` INT NULL DEFAULT NULL,
  `comentario` TEXT NULL DEFAULT NULL,
  `fecha_clasificacion` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  `anonimo` TINYINT(1) NULL DEFAULT '0',
  PRIMARY KEY (`id_clasificacion`),
  INDEX `id_usuario` (`id_usuario` ASC) VISIBLE,
  INDEX `id_medico` (`id_medico` ASC) VISIBLE,
  CONSTRAINT `clasificacion_medicos_ibfk_1`
    FOREIGN KEY (`id_usuario`)
    REFERENCES `bd_imesys`.`usuarios` (`id_usuario`)
    ON DELETE CASCADE,
  CONSTRAINT `clasificacion_medicos_ibfk_2`
    FOREIGN KEY (`id_medico`)
    REFERENCES `bd_imesys`.`medicos` (`id_medico`)
    ON DELETE CASCADE)
ENGINE = InnoDB
AUTO_INCREMENT = 39
DEFAULT CHARACTER SET = utf8mb4
COLLATE = utf8mb4_0900_ai_ci;


-- -----------------------------------------------------
-- Table `bd_imesys`.`config_puntos`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `bd_imesys`.`config_puntos` (
  `id_config` INT NOT NULL AUTO_INCREMENT,
  `clave` VARCHAR(50) NOT NULL,
  `valor` VARCHAR(255) NOT NULL,
  `descripcion` TEXT NULL DEFAULT NULL,
  PRIMARY KEY (`id_config`),
  UNIQUE INDEX `clave` (`clave` ASC) VISIBLE)
ENGINE = InnoDB
AUTO_INCREMENT = 8
DEFAULT CHARACTER SET = utf8mb4
COLLATE = utf8mb4_0900_ai_ci;


-- -----------------------------------------------------
-- Table `bd_imesys`.`datos_biometricos`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `bd_imesys`.`datos_biometricos` (
  `id_dato` INT NOT NULL AUTO_INCREMENT,
  `id_usuario` INT NULL DEFAULT NULL,
  `peso` DECIMAL(5,2) NULL DEFAULT NULL,
  `altura` DECIMAL(5,2) NULL DEFAULT NULL,
  `presion_arterial` VARCHAR(50) NULL DEFAULT NULL,
  `frecuencia_cardiaca` INT NULL DEFAULT NULL,
  `nivel_glucosa` DECIMAL(5,2) NULL DEFAULT NULL,
  `fecha_registro` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  `descripcion_resultado` VARCHAR(255) NULL DEFAULT 'Mi Resultado',
  `resultado_prediccion` TEXT NULL DEFAULT NULL,
  PRIMARY KEY (`id_dato`),
  INDEX `id_usuario` (`id_usuario` ASC) VISIBLE,
  CONSTRAINT `datos_biometricos_ibfk_1`
    FOREIGN KEY (`id_usuario`)
    REFERENCES `bd_imesys`.`usuarios` (`id_usuario`)
    ON DELETE CASCADE)
ENGINE = InnoDB
AUTO_INCREMENT = 33
DEFAULT CHARACTER SET = utf8mb4
COLLATE = utf8mb4_0900_ai_ci;


-- -----------------------------------------------------
-- Table `bd_imesys`.`departamentos`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `bd_imesys`.`departamentos` (
  `departamento_id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `nombre` VARCHAR(100) NOT NULL,
  `ubicacion` VARCHAR(100) NULL DEFAULT NULL,
  `presupuesto` DECIMAL(12,2) NULL DEFAULT NULL,
  PRIMARY KEY (`departamento_id`),
  UNIQUE INDEX `departamento_id` (`departamento_id` ASC) VISIBLE)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8mb4
COLLATE = utf8mb4_0900_ai_ci;


-- -----------------------------------------------------
-- Table `bd_imesys`.`empleados`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `bd_imesys`.`empleados` (
  `empleado_id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `nombre` VARCHAR(100) NOT NULL,
  `apellido` VARCHAR(100) NOT NULL,
  `email` VARCHAR(100) NULL DEFAULT NULL,
  `telefono` VARCHAR(20) NULL DEFAULT NULL,
  `fecha_contratacion` DATE NOT NULL,
  `salario` DECIMAL(10,2) NULL DEFAULT NULL,
  `departamento_id` INT NULL DEFAULT NULL,
  PRIMARY KEY (`empleado_id`),
  UNIQUE INDEX `empleado_id` (`empleado_id` ASC) VISIBLE,
  UNIQUE INDEX `email` (`email` ASC) VISIBLE)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8mb4
COLLATE = utf8mb4_0900_ai_ci;


-- -----------------------------------------------------
-- Table `bd_imesys`.`historial_consultas`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `bd_imesys`.`historial_consultas` (
  `id_historial` INT NOT NULL AUTO_INCREMENT,
  `id_usuario` INT NOT NULL,
  `id_medico` INT NOT NULL,
  `motivo` TEXT NOT NULL,
  `observacion` TEXT NULL DEFAULT NULL,
  `tratamiento` TEXT NULL DEFAULT NULL,
  `imagen` VARCHAR(255) NULL DEFAULT NULL,
  `fecha_hora` DATETIME NULL DEFAULT CURRENT_TIMESTAMP,
  `dato_opcional` TEXT NULL DEFAULT NULL,
  `diagnostico` TEXT NULL DEFAULT NULL,
  PRIMARY KEY (`id_historial`),
  INDEX `id_usuario` (`id_usuario` ASC) VISIBLE,
  INDEX `id_medico` (`id_medico` ASC) VISIBLE,
  CONSTRAINT `historial_consultas_ibfk_1`
    FOREIGN KEY (`id_usuario`)
    REFERENCES `bd_imesys`.`usuarios` (`id_usuario`)
    ON DELETE CASCADE,
  CONSTRAINT `historial_consultas_ibfk_2`
    FOREIGN KEY (`id_medico`)
    REFERENCES `bd_imesys`.`medicos` (`id_medico`)
    ON DELETE CASCADE)
ENGINE = InnoDB
AUTO_INCREMENT = 6
DEFAULT CHARACTER SET = utf8mb4
COLLATE = utf8mb4_0900_ai_ci;


-- -----------------------------------------------------
-- Table `bd_imesys`.`historial_paciente`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `bd_imesys`.`historial_paciente` (
  `id_historial_paciente` INT NOT NULL AUTO_INCREMENT,
  `id_usuario` INT NOT NULL,
  `fecha_hora` DATETIME NOT NULL,
  `motivo` TEXT NOT NULL,
  `nombre_medico` VARCHAR(100) NOT NULL,
  `especialidad` VARCHAR(100) NOT NULL,
  PRIMARY KEY (`id_historial_paciente`),
  INDEX `id_usuario` (`id_usuario` ASC) VISIBLE,
  CONSTRAINT `historial_paciente_ibfk_1`
    FOREIGN KEY (`id_usuario`)
    REFERENCES `bd_imesys`.`usuarios` (`id_usuario`)
    ON DELETE CASCADE)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8mb4
COLLATE = utf8mb4_0900_ai_ci;


-- -----------------------------------------------------
-- Table `bd_imesys`.`imagenes_medicas`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `bd_imesys`.`imagenes_medicas` (
  `id_imagen` INT NOT NULL AUTO_INCREMENT,
  `id_medico` INT NULL DEFAULT NULL,
  `id_usuario` INT NULL DEFAULT NULL,
  `ruta_imagen` VARCHAR(255) NOT NULL,
  `fecha_subida` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_imagen`),
  INDEX `id_medico` (`id_medico` ASC) VISIBLE,
  CONSTRAINT `imagenes_medicas_ibfk_1`
    FOREIGN KEY (`id_medico`)
    REFERENCES `bd_imesys`.`medicos` (`id_medico`)
    ON DELETE CASCADE)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8mb4
COLLATE = utf8mb4_0900_ai_ci;


-- -----------------------------------------------------
-- Table `bd_imesys`.`proyectos`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `bd_imesys`.`proyectos` (
  `proyecto_id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `nombre` VARCHAR(200) NOT NULL,
  `descripcion` TEXT NULL DEFAULT NULL,
  `fecha_inicio` DATE NULL DEFAULT NULL,
  `fecha_fin_estimada` DATE NULL DEFAULT NULL,
  `presupuesto` DECIMAL(12,2) NULL DEFAULT NULL,
  `departamento_responsable` INT NULL DEFAULT NULL,
  PRIMARY KEY (`proyecto_id`),
  UNIQUE INDEX `proyecto_id` (`proyecto_id` ASC) VISIBLE)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8mb4
COLLATE = utf8mb4_0900_ai_ci;


-- -----------------------------------------------------
-- Table `bd_imesys`.`recompensas`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `bd_imesys`.`recompensas` (
  `id_recompensa` INT NOT NULL AUTO_INCREMENT,
  `id_usuario` INT NULL DEFAULT NULL,
  `puntos` INT NULL DEFAULT '0',
  `descripcion` TEXT NULL DEFAULT NULL,
  `fecha_registro` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_recompensa`),
  INDEX `id_usuario` (`id_usuario` ASC) VISIBLE,
  CONSTRAINT `recompensas_ibfk_1`
    FOREIGN KEY (`id_usuario`)
    REFERENCES `bd_imesys`.`usuarios` (`id_usuario`)
    ON DELETE CASCADE)
ENGINE = InnoDB
AUTO_INCREMENT = 5
DEFAULT CHARACTER SET = utf8mb4
COLLATE = utf8mb4_0900_ai_ci;


-- -----------------------------------------------------
-- Table `bd_imesys`.`resultados_ia`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `bd_imesys`.`resultados_ia` (
  `id_resultado` INT NOT NULL AUTO_INCREMENT,
  `id_imagen` INT NULL DEFAULT NULL,
  `diagnostico` TEXT NOT NULL,
  `probabilidad` DECIMAL(5,2) NOT NULL,
  `fecha_analisis` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_resultado`),
  INDEX `id_imagen` (`id_imagen` ASC) VISIBLE,
  CONSTRAINT `resultados_ia_ibfk_1`
    FOREIGN KEY (`id_imagen`)
    REFERENCES `bd_imesys`.`imagenes_medicas` (`id_imagen`)
    ON DELETE CASCADE)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8mb4
COLLATE = utf8mb4_0900_ai_ci;


-- -----------------------------------------------------
-- Table `bd_imesys`.`v2_actividades_personales_medico`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `bd_imesys`.`v2_actividades_personales_medico` (
  `id_actividad` INT NOT NULL AUTO_INCREMENT,
  `id_medico` INT NOT NULL,
  `titulo` VARCHAR(255) NOT NULL,
  `descripcion` TEXT NULL DEFAULT NULL,
  `fecha_inicio` DATETIME NOT NULL,
  `fecha_fin` DATETIME NOT NULL,
  `tipo` ENUM('Reunión', 'Evento', 'Descanso', 'Otro') NULL DEFAULT 'Otro',
  `estado` ENUM('Programada', 'Cancelada', 'Completada') NULL DEFAULT 'Programada',
  `fecha_registro` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_actividad`),
  INDEX `v2_actividades_ibfk_1` (`id_medico` ASC) VISIBLE,
  CONSTRAINT `v2_actividades_ibfk_1`
    FOREIGN KEY (`id_medico`)
    REFERENCES `bd_imesys`.`medicos` (`id_medico`)
    ON DELETE CASCADE)
ENGINE = InnoDB
AUTO_INCREMENT = 5
DEFAULT CHARACTER SET = utf8mb4
COLLATE = utf8mb4_0900_ai_ci;


-- -----------------------------------------------------
-- Table `bd_imesys`.`v2_citas`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `bd_imesys`.`v2_citas` (
  `id_cita` INT NOT NULL AUTO_INCREMENT,
  `id_usuario` INT NULL DEFAULT NULL,
  `id_medico` INT NULL DEFAULT NULL,
  `fecha_cita` DATETIME NOT NULL,
  `estado` ENUM('Pendiente', 'Confirmada', 'Cancelada', 'Completada') NULL DEFAULT 'Pendiente',
  `motivo` TEXT NULL DEFAULT NULL,
  `respuesta` TEXT NULL DEFAULT NULL,
  PRIMARY KEY (`id_cita`),
  INDEX `id_usuario` (`id_usuario` ASC) VISIBLE,
  INDEX `id_medico` (`id_medico` ASC) VISIBLE,
  CONSTRAINT `v2_citas_ibfk_1`
    FOREIGN KEY (`id_usuario`)
    REFERENCES `bd_imesys`.`usuarios` (`id_usuario`)
    ON DELETE CASCADE,
  CONSTRAINT `v2_citas_ibfk_2`
    FOREIGN KEY (`id_medico`)
    REFERENCES `bd_imesys`.`medicos` (`id_medico`)
    ON DELETE CASCADE)
ENGINE = InnoDB
AUTO_INCREMENT = 6
DEFAULT CHARACTER SET = utf8mb4
COLLATE = utf8mb4_0900_ai_ci;


-- -----------------------------------------------------
-- Table `bd_imesys`.`v2_disponibilidad_medico`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `bd_imesys`.`v2_disponibilidad_medico` (
  `id_disponibilidad` INT NOT NULL AUTO_INCREMENT,
  `id_medico` INT NOT NULL,
  `fecha_hora` DATETIME NOT NULL,
  `estado` ENUM('Disponible', 'No disponible') NULL DEFAULT 'Disponible',
  `observaciones` TEXT NULL DEFAULT NULL,
  `fecha_registro` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_disponibilidad`),
  INDEX `v2_disponibilidad_ibfk_1` (`id_medico` ASC) VISIBLE,
  CONSTRAINT `v2_disponibilidad_ibfk_1`
    FOREIGN KEY (`id_medico`)
    REFERENCES `bd_imesys`.`medicos` (`id_medico`)
    ON DELETE CASCADE)
ENGINE = InnoDB
AUTO_INCREMENT = 186
DEFAULT CHARACTER SET = utf8mb4
COLLATE = utf8mb4_0900_ai_ci;


SET SQL_MODE=@OLD_SQL_MODE;
SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS;
SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS;
