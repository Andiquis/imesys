<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro de Médicos - iMESYS</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f0f8ff;
            margin: 0;
            padding: 0;
            color: #333;
        }
        .container {
            max-width: 800px;
            margin: 40px auto;
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
        }
        h1 {
            color: #2c3e50;
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #3498db;
            padding-bottom: 10px;
        }
        .form-group {
            margin-bottom: 20px;
        }
        label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #2c3e50;
        }
        input[type="text"],
        input[type="email"],
        input[type="password"],
        input[type="tel"],
        input[type="date"],
        input[type="file"],
        select,
        textarea {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 16px;
            box-sizing: border-box;
        }
        .form-row {
            display: flex;
            gap: 20px;
        }
        .form-row .form-group {
            flex: 1;
        }
        .btn-submit {
            background-color: #3498db;
            color: white;
            border: none;
            padding: 12px 20px;
            font-size: 16px;
            border-radius: 5px;
            cursor: pointer;
            width: 100%;
            font-weight: 600;
            transition: background-color 0.3s;
        }
        .btn-submit:hover {
            background-color: #2980b9;
        }
        .required:after {
            content: " *";
            color: red;
        }
        .photo-preview {
            width: 150px;
            height: 150px;
            border-radius: 50%;
            object-fit: cover;
            border: 3px solid #3498db;
            margin: 10px auto;
            display: block;
        }
        .photo-upload {
            text-align: center;
        }
        .login-link {
            text-align: center;
            margin-top: 20px;
        }
        .login-link a {
            color: #3498db;
            text-decoration: none;
        }
        .login-link a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Registro de Médico</h1>
        <form id="registroMedico" action="registrar_medico.php" method="POST" enctype="multipart/form-data">
            <!-- Sección de Información Personal -->
            <h2>Información Personal</h2>
            <div class="form-row">
                <div class="form-group">
                    <label for="nombre" class="required">Nombre</label>
                    <input type="text" id="nombre" name="nombre" required>
                </div>
                <div class="form-group">
                    <label for="apellido" class="required">Apellido</label>
                    <input type="text" id="apellido" name="apellido" required>
                </div>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="correo" class="required">Correo Electrónico</label>
                    <input type="email" id="correo" name="correo" required>
                </div>
                <div class="form-group">
                    <label for="telefono">Teléfono</label>
                    <input type="tel" id="telefono" name="telefono">
                </div>
            </div>
            
            <div class="form-group">
                <label for="contrasena" class="required">Contraseña</label>
                <input type="password" id="contrasena" name="contrasena" required>
            </div>
            
            <div class="form-group">
                <label for="confirmar_contrasena" class="required">Confirmar Contraseña</label>
                <input type="password" id="confirmar_contrasena" name="confirmar_contrasena" required>
            </div>
            
            <!-- Sección de Información Profesional -->
            <h2>Información Profesional</h2>
            <div class="form-group">
                <label for="especialidad" class="required">Especialidad Médica</label>
                <select id="especialidad" name="id_especialidad" required>
                    <option value="">Seleccione una especialidad</option>
                    <option value="1">Cardiología</option>
                    <option value="2">Dermatología</option>
                    <option value="3">Endocrinología</option>
                    <option value="4">Gastroenterología</option>
                    <option value="5">Neurología</option>
                    <option value="6">Oftalmología</option>
                    <option value="7">Pediatría</option>
                    <option value="8">Psiquiatría</option>
                    <option value="9">Radiología</option>
                    <option value="10">Cirugía General</option>
                    <!-- Otras especialidades según tu base de datos -->
                </select>
            </div>
            
            <div class="form-group">
                <label for="numero_colegiatura" class="required">Número de Colegiatura</label>
                <input type="text" id="numero_colegiatura" name="numero_colegiatura" required>
            </div>
            
            <div class="form-group">
                <label for="direccion_consultorio">Dirección del Consultorio</label>
                <textarea id="direccion_consultorio" name="direccion_consultorio" rows="3"></textarea>
            </div>
            
            <!-- Foto de perfil -->
            <div class="form-group photo-upload">
                <label for="foto">Foto de Perfil</label>
                <img id="photoPreview" src="https://via.placeholder.com/150" alt="Vista previa de la foto" class="photo-preview">
                <input type="file" id="foto" name="foto" accept="image/*">
            </div>
            
            <div class="form-group">
                <button type="submit" class="btn-submit">Registrarse como Médico</button>
            </div>
            
            <div class="login-link">
                ¿Ya tienes una cuenta? <a href="login_medico.php">Inicia sesión aquí</a>
            </div>
        </form>
    </div>

    <script>
        // Vista previa de la foto
        document.getElementById('foto').addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    document.getElementById('photoPreview').src = e.target.result;
                }
                reader.readAsDataURL(file);
            }
        });
        
        // Validación del formulario
        document.getElementById('registroMedico').addEventListener('submit', function(e) {
            const password = document.getElementById('contrasena').value;
            const confirmPassword = document.getElementById('confirmar_contrasena').value;
            
            if (password !== confirmPassword) {
                alert('Las contraseñas no coinciden');
                e.preventDefault();
            }
            
            // Aquí puedes agregar más validaciones según sea necesario
        });
    </script>
</body>
</html>