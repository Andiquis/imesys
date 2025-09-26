<!--obtener usuarios o listar usuarios-->

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mostrar Usuarios</title>
    <style>
        table {
            border-collapse: collapse;
            width: 80%;
            margin: 20px auto;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
        }
    </style>
</head>
<body>
    <h1>Lista de Usuarios</h1>
    <button onclick="obtenerUsuarios()">Obtener Usuarios</button>
    <div id="resultado"></div>

    <script>
        async function obtenerUsuarios() {
            try {
                const response = await fetch('http://localhost:5000/api/usuarios');
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                const usuarios = await response.json();
                mostrarUsuarios(usuarios);
            } catch (error) {
                document.getElementById('resultado').innerText = 'Error al obtener usuarios: ' + error;
            }
        }

        function mostrarUsuarios(usuarios) {
            let tablaHTML = '<table>';
            tablaHTML += '<thead><tr><th>ID</th><th>Nombre</th><th>Apellido</th><th>Correo</th><th>Teléfono</th><th>Dirección</th><th>Fecha de Nacimiento</th><th>Género</th><th>Foto</th><th>Fecha de Registro</th></tr></thead><tbody>';

            usuarios.forEach(usuario => {
                tablaHTML += `<tr>
                    <td>${usuario.id_usuario}</td>
                    <td>${usuario.nombre}</td>
                    <td>${usuario.apellido}</td>
                    <td>${usuario.correo}</td>
                    <td>${usuario.telefono || ''}</td>
                    <td>${usuario.direccion || ''}</td>
                    <td>${usuario.fecha_nacimiento || ''}</td>
                    <td>${usuario.genero || ''}</td>
                    <td>${usuario.foto || ''}</td>
                    <td>${usuario.fecha_registro}</td>
                </tr>`;
            });

            tablaHTML += '</tbody></table>';
            document.getElementById('resultado').innerHTML = tablaHTML;
        }
    </script>
</body>
</html>