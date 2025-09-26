<?php
include 'header_medico.php';
?>
<br><br>
<!-- Contenido principal -->
<div id="contentArea" class="main-content">
    <div class="container">
        <!-- Sección de bienvenida -->
        <div class="search-container">
            <div class="search-inner">
                <div class="welcome-section">
                    <h1>BIENVENIDO DR. <?php echo strtoupper(htmlspecialchars($nombre)); ?></h1>
                    <p>Especialidad: <?php echo htmlspecialchars($especialidad); ?></p>
                    <div class="search-bar">
                        <input type="text" id="searchInput" placeholder="Buscar paciente por DNI o Nombre..." autocomplete="off">
                        <button id="searchButton">
                            <i class="fas fa-search"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Imagen solo visible al inicio -->
        <center>
            <div class="imagen-container" id="imagenInicio">
                <img src="../img/pacientes.png" alt="Imagen centrada">
            </div>
        </center>

        <!-- Resultados de búsqueda -->
        <div id="searchResults" class="search-results mt-6 grid grid-cols-1 gap-4"></div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('searchInput');
    const searchButton = document.getElementById('searchButton');
    const searchResults = document.getElementById('searchResults');
    const imagenInicio = document.getElementById('imagenInicio');

    // Función para buscar pacientes
    function buscarPacientes(termino) {
        if (imagenInicio) {
            imagenInicio.style.display = 'none'; // Oculta la imagen cuando se realiza una búsqueda
        }

        if (termino.length < 2) {
            searchResults.innerHTML = '<p class="text-gray-500 text-center py-4">Ingrese al menos 2 caracteres para buscar</p>';
            return;
        }

        fetch('buscar_pacientes_ajax.php?q=' + encodeURIComponent(termino))
            .then(response => response.json())
            .then(data => {
                if (data.error) {
                    searchResults.innerHTML = '<p class="text-red-500 text-center py-4">' + data.error + '</p>';
                } else if (data.length === 0) {
                    searchResults.innerHTML = '<p class="text-gray-500 text-center py-4">No se encontraron pacientes</p>';
                } else {
                    let html = '';
                    data.forEach(paciente => {
                        const fechaNac = new Date(paciente.fecha_nacimiento);
                        const hoy = new Date();
                        let edad = hoy.getFullYear() - fechaNac.getFullYear();
                        const m = hoy.getMonth() - fechaNac.getMonth();
                        if (m < 0 || (m === 0 && hoy.getDate() < fechaNac.getDate())) {
                            edad--;
                        }

                        const fotoUrl = paciente.foto ? 
                            'uploads/pacientes/' + encodeURIComponent(paciente.foto) : 
                            'data:image/svg+xml;charset=UTF-8,%3Csvg xmlns="http://www.w3.org/2000/svg" width="100" height="100" viewBox="0 0 100 100"%3E%3Crect fill="%23ddd" width="100" height="100" rx="10"/%3E%3Ctext fill="%23666" font-family="Arial" font-size="40" x="50" y="60" text-anchor="middle"%3E' + 
                            paciente.nombre.charAt(0).toUpperCase() + '%3C/text%3E%3C/svg%3E';

                        html += `
                        <div class="bg-white rounded-lg shadow-md overflow-hidden">
                            <div class="p-4 flex items-start">
                                <div class="flex-shrink-0 mr-4">
                                    <img src="${fotoUrl}" alt="Foto de ${paciente.nombre}" 
                                         class="h-16 w-16 rounded-full object-cover border-2 border-blue-100">
                                </div>
                                <div class="flex-1 min-w-0">
                                    <h3 class="text-lg font-semibold text-gray-800 truncate">
                                        ${paciente.nombre} ${paciente.apellido}
                                    </h3>
                                    <div class="grid grid-cols-2 md:grid-cols-4 gap-2 mt-2 text-sm">
                                        <div>
                                            <span class="text-gray-500">DNI:</span>
                                            <span class="font-medium">${paciente.dni}</span>
                                        </div>
                                        <div>
                                            <span class="text-gray-500">Teléfono:</span>
                                            <span class="font-medium">${paciente.telefono || 'No registrado'}</span>
                                        </div>
                                        <div>
                                            <span class="text-gray-500">Edad:</span>
                                            <span class="font-medium">${edad} años</span>
                                        </div>
                                        <div>
                                            <span class="text-gray-500">Registro:</span>
                                            <span class="font-medium">${new Date(paciente.fecha_registro).toLocaleDateString()}</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="flex flex-col space-y-2 ml-4">
                                    <a href="perfil_paciente.php?id=${paciente.id_usuario}" 
                                       class="boton-outline py-1 px-3 text-sm">
                                        <i class="fas fa-user-md mr-1"></i> Ver Perfil
                                    </a>
                                    <a href="registrar_consulta.php?id_paciente=${paciente.id_usuario}" 
                                       class="boton py-1 px-3 text-sm">
                                        <i class="fas fa-notes-medical mr-1"></i> Consulta
                                    </a>
                                    <a href="historial_medico.php?id=${paciente.id_usuario}" 
                                       class="boton-outline py-1 px-3 text-sm">
                                        <i class="fas fa-user-md mr-1"></i> Ver Historial
                                    </a>
                                </div>
                            </div>
                        </div>
                        `;
                    });
                    searchResults.innerHTML = html;
                }
            })
            .catch(error => {
                searchResults.innerHTML = '<p class="text-red-500 text-center py-4">Error al buscar pacientes</p>';
                console.error('Error:', error);
            });
    }

    // Eventos
    searchButton.addEventListener('click', function() {
        buscarPacientes(searchInput.value.trim());
    });

    searchInput.addEventListener('keyup', function(e) {
        const valor = searchInput.value.trim();

        // Mostrar imagen si el campo está vacío
        if (valor.length === 0 && imagenInicio) {
            imagenInicio.style.display = 'block';
            searchResults.innerHTML = ''; // Limpiar resultados si se borra todo
        }

        if (e.key === 'Enter') {
            buscarPacientes(valor);
        }
    });

    // Búsqueda inicial si hay un término en la URL
    const urlParams = new URLSearchParams(window.location.search);
    const initialSearch = urlParams.get('q');
    if (initialSearch) {
        searchInput.value = initialSearch;
        buscarPacientes(initialSearch);
    }
});
</script>

<?php
include 'footer_medico.php';
?>
