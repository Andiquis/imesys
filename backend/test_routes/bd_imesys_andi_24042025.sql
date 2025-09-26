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
  PRIMARY KEY (`id_usuario`),
  UNIQUE INDEX `correo` (`correo` ASC) VISIBLE)
ENGINE = InnoDB
AUTO_INCREMENT = 14
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
AUTO_INCREMENT = 15
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
-- Table `bd_imesys`.`historial_consultas`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `bd_imesys`.`historial_consultas` (
  `id_historial` INT NOT NULL AUTO_INCREMENT,
  `id_usuario` INT NOT NULL,
  `id_medico` INT NOT NULL,
  `motivo` TEXT NOT NULL,
  `observacion` TEXT NULL DEFAULT NULL,
  `imagen` VARCHAR(255) NULL DEFAULT NULL,
  `fecha_hora` DATETIME NULL DEFAULT CURRENT_TIMESTAMP,
  `dato_opcional` TEXT NULL DEFAULT NULL,
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


SET SQL_MODE=@OLD_SQL_MODE;
SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS;
SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS;
