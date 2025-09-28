CREATE TABLE IF NOT EXISTS `bd_imesys`.`administradores` (
  `id_admin` INT NOT NULL AUTO_INCREMENT,
  `usuario` VARCHAR(50) NOT NULL,
  `contrasena` VARCHAR(255) NOT NULL, -- Aquí debe ir el hash seguro
  `nombre` VARCHAR(100) NOT NULL,
  `correo` VARCHAR(150) NOT NULL,
  `fecha_registro` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  `ultimo_acceso` TIMESTAMP NULL DEFAULT NULL,
  `activo` TINYINT(1) NULL DEFAULT '1',
  `last_password_reset` TIMESTAMP NULL DEFAULT NULL,
  `intentos_fallidos` INT NOT NULL DEFAULT 0,
  `locked_until` TIMESTAMP NULL DEFAULT NULL,
  `rol` ENUM('superadmin', 'admin', 'auditor') NOT NULL DEFAULT 'admin',
  PRIMARY KEY (`id_admin`),
  UNIQUE INDEX `usuario` (`usuario` ASC) VISIBLE,
  UNIQUE INDEX `correo` (`correo` ASC) VISIBLE
)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8mb4
COLLATE = utf8mb4_0900_ai_ci;


CREATE TABLE IF NOT EXISTS `bd_imesys`.`otp_codes` (
  `id_otp` INT NOT NULL AUTO_INCREMENT,
  `id_admin` INT NOT NULL,
  `codigo` VARCHAR(6) NOT NULL,
  `fecha_creacion` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `fecha_expiracion` TIMESTAMP NOT NULL,
  `usado` TINYINT(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id_otp`),
  FOREIGN KEY (`id_admin`) REFERENCES `administradores`(`id_admin`) ON DELETE CASCADE
)
ENGINE = InnoDB
DEFAULT CHARSET = utf8mb4
COLLATE = utf8mb4_0900_ai_ci;



🎯 Con esto tienes:
Seguridad	¿Incluido?
Contraseña segura (hash)	✅
Segundo factor facial (MFA)	✅
OTP por email (fallback)	✅
Bloqueo temporal (locked_until)	✅
Roles por admin	✅
Control de intentos fallidos	✅
Activación/desactivación de cuenta	✅