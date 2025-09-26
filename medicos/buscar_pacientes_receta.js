// Buscar pacientes con AJAX
document.getElementById('btn_buscar_paciente').addEventListener('click', buscarPacientes);
document.getElementById('buscar_paciente').addEventListener('keyup', function(e) {
    if (e.key === 'Enter') {
        buscarPacientes();
    }
});

function buscarPacientes() {
    const termino = document.getElementById('buscar_paciente').value.trim();
    const resultados = document.getElementById('resultados_pacientes');
    
    if (termino.length < 2) {
        resultados.innerHTML = '<p class="text-gray-500 p-2">Ingrese al menos 2 caracteres</p>';
        resultados.classList.remove('hidden');
        return;
    }
    
    // Mostrar loader
    resultados.innerHTML = '<p class="text-gray-500 p-2">Buscando pacientes...</p>';
    resultados.classList.remove('hidden');
    
    // Realizar petición AJAX
    fetch('../ajax/buscar_pacientes_receta.php?q=' + encodeURIComponent(termino))
        .then(response => {
            if (!response.ok) {
                throw new Error('Error en la respuesta del servidor');
            }
            return response.json();
        })
        .then(data => {
            if (data.error) {
                resultados.innerHTML = '<p class="text-red-500 p-2">' + data.error + '</p>';
            } else if (data.length === 0) {
                resultados.innerHTML = '<p class="text-gray-500 p-2">No se encontraron pacientes</p>';
            } else {
                let html = '';
                data.forEach(paciente => {
                    html += `
                        <div class="p-2 hover:bg-gray-100 rounded cursor-pointer paciente-item" 
                             data-id="${paciente.id_usuario}" 
                             data-nombre="${paciente.nombre}" 
                             data-apellido="${paciente.apellido}" 
                             data-dni="${paciente.dni}">
                            ${paciente.nombre} ${paciente.apellido} - DNI: ${paciente.dni}
                        </div>
                    `;
                });
                resultados.innerHTML = html;
            }
        })
        .catch(error => {
            resultados.innerHTML = '<p class="text-red-500 p-2">Error en la búsqueda</p>';
            console.error('Error:', error);
        });
}

// Seleccionar paciente
document.addEventListener('click', function(e) {
    if (e.target.classList.contains('paciente-item')) {
        const id = e.target.getAttribute('data-id');
        const nombre = e.target.getAttribute('data-nombre');
        const apellido = e.target.getAttribute('data-apellido');
        const dni = e.target.getAttribute('data-dni');
        
        document.getElementById('id_paciente_seleccionado').value = id;
        document.getElementById('buscar_paciente').value = `${nombre} ${apellido} - DNI: ${dni}`;
        document.getElementById('resultados_pacientes').classList.add('hidden');
        
        // Mostrar información del paciente seleccionado
        document.getElementById('nombre_paciente').textContent = nombre;
        document.getElementById('apellido_paciente').textContent = apellido;
        document.getElementById('dni_paciente').textContent = dni;
        document.getElementById('info_paciente').classList.remove('hidden');
    }
});