<?php
session_start();

if (!isset($_SESSION['loggedin'])) {
    header("Location: login_medico.php");
    exit;
}

require 'conexion.php';

// Obtener información del médico (misma lógica que perfil_medico.php)
$id_medico = $_SESSION['id_medico'];

// Obtener información del médico
$query_medico = "SELECT m.*, e.nombre_especialidad 
                FROM medicos m 
                JOIN especialidades e ON m.id_especialidad = e.id_especialidad 
                WHERE m.id_medico = ?";
$stmt_medico = $conexion->prepare($query_medico);
$stmt_medico->bind_param("i", $id_medico);
$stmt_medico->execute();
$result_medico = $stmt_medico->get_result();
$medico = $result_medico->fetch_assoc();

// Extraer variables individuales para compatibilidad
$nombre = $medico['nombre'];
$apellido = $medico['apellido'];
$correo = $medico['correo'];
$foto = $medico['foto'];
$especialidad = $medico['nombre_especialidad'];
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>IMESYS - Panel Médico</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="estilos_inicio.css">
    <style>
        /* Sobrescribir estilos externos para el layout correcto */
        .content-area {
            padding-top: 80px !important; /* Espacio para el header fijo */
            min-height: calc(100vh - 80px) !important;
            background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%) !important;
            margin-left: 0 !important; /* Sobrescribir margin-left del CSS externo */
            margin-top: 0 !important; /* Sobrescribir margin-top del CSS externo */
        }
        
        /* Desktop: Ajustar para sidebar permanente */
        @media (min-width: 1024px) {
            .content-area {
                margin-left: 280px !important; /* Respetar ancho del sidebar */
                padding-left: 0 !important;
            }
            
            /* Asegurar que el navbar también se ajuste */
            #topNavbar {
                left: 280px !important;
                width: calc(100% - 280px) !important;
            }
        }
        
        /* Móvil y tablet: Sin margin-left */
        @media (max-width: 1023px) {
            .content-area {
                margin-left: 0 !important;
            }
            
            #topNavbar {
                left: 0 !important;
                width: 100% !important;
            }
        }
        
        /* Ajustes adicionales del contenedor */
        .container {
            max-width: 100% !important;
            margin: 0 auto !important;
            padding-left: 1rem !important;
            padding-right: 1rem !important;
        }
        
        /* Desktop: Contenedor más ajustado */
        @media (min-width: 1024px) {
            .container {
                max-width: calc(100vw - 320px) !important; /* Ancho disponible menos sidebar y margen */
                padding-left: 2rem !important;
                padding-right: 2rem !important;
            }
        }
        
        /* Suavizar transiciones */
        .content-area * {
            transition: all 0.3s ease;
        }
        
        /* Mejorar la integración visual manteniendo el tema claro */
        body {
            background: #ffffff !important;
        }

        /* Estilos para el buscador y resultados - Versión Elegante */
.search-bar {
    position: relative;
    margin: 20px auto 0 auto;
    width: 100%;
    max-width: 500px;
}

#searchInput {
    width: 100%;
    padding: 14px 25px;
    border: 2px solid #a0c4ff; /* Celeste suave */
    border-radius: 30px;
    font-size: 16px;
    outline: none;
    transition: all 0.3s ease;
    box-shadow: 0 4px 15px rgba(160, 196, 255, 0.2);
    background-color: rgba(255, 255, 255, 0.8);
    backdrop-filter: blur(5px);
}


#searchButton {
    position: absolute;
    right: 6px;
    top: 6px;
    background: linear-gradient(135deg, #7fb2ff 0%, #5a9cff 100%); /* <-- ya en hover */
    color: white;
    border: none;
    border-radius: 50%;
    width: 42px;
    height: 42px;
    cursor: pointer;
    transition: all 0.3s ease;
    box-shadow: 0 2px 10px rgba(160, 196, 255, 0.3);
    transform: scale(1.05); /* <-- efecto hover aplicado siempre */
}


.search-results {
    position: absolute;
    top: 110%;
    left: 0;
    width: 100%;
    max-height: 400px;
    overflow-y: auto;
    background: linear-gradient(to bottom, #f0f9ff 0%, #e0f2fe 100%); /* Degradado celeste pastel */
    border: 1px solid #bfdbfe;
    border-radius: 18px;
    box-shadow: 0 10px 30px rgba(165, 207, 255, 0.2);
    z-index: 1000;
    display: none;
    animation: fadeIn 0.3s ease;
}

@keyframes fadeIn {
    from { opacity: 0; transform: translateY(-10px); }
    to { opacity: 1; transform: translateY(0); }
}

.search-result-item {
    padding: 16px 24px;
    border-bottom: 1px solid #dbeafe;
    cursor: pointer;
    transition: all 0.25s ease;
    background-color: rgba(255, 255, 255, 0.7);
    backdrop-filter: blur(3px);
}

.search-result-item:first-child {
    border-top-left-radius: 17px;
    border-top-right-radius: 17px;
}

.search-result-item:last-child {
    border-bottom-left-radius: 17px;
    border-bottom-right-radius: 17px;
    border-bottom: none;
}

.search-result-item:hover {
    background-color: rgba(224, 242, 254, 0.9);
    transform: translateX(5px);
    box-shadow: 3px 0 15px rgba(165, 207, 255, 0.2);
}

.search-result-item h4 {
    margin: 0 0 6px 0;
    color: #1e40af;
    font-weight: 600;
    font-size: 17px;
}

.search-result-item p {
    margin: 0;
    color: #4b5563;
    font-size: 14px;
    line-height: 1.5;
}

.search-result-item p strong {
    color: #3b82f6;
    font-weight: 500;
}

/* Efecto especial para el mensaje de carga */
.search-results .fa-spinner {
    color: #7fb2ff;
    font-size: 20px;
    margin-bottom: 10px;
}

/* Efecto para cuando no hay resultados */
.search-results .fa-user-slash {
    color: #93c5fd;
    font-size: 20px;
    margin-bottom: 10px;
}
</style>
    
</head>
<body class="bg-white">
    <!-- Barra superior -->
    <?php include 'header_medico.php'; ?>

    <!-- Contenido principal -->
    <div id="contentArea" class="content-area">
        <div class="container mx-auto px-4 lg:px-6 py-6 lg:py-8">
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
    <div id="searchResults" class="search-results"></div>
</div>
                    </div>
                </div>
            </div>

         
            
            <!-- Resumen de actividad -->
            <div class="health-cards">
                <div class="health-card blue-card">
                    <h3>Citas Hoy</h3>
                    <i class="fas fa-calendar-day text-4xl mb-3"></i>
                    <p class="text-2xl font-bold">12</p>
                    <a href="modulo_citas.php" class="card-link">
                        Ver agenda <i class="fas fa-chevron-right"></i>
                    </a>
                </div>
                
                <div class="health-card green-card">
                    <h3>Pacientes Nuevos</h3>
                    <i class="fas fa-user-plus text-4xl mb-3"></i>
                    <p class="text-2xl font-bold">5</p>
                    <a href="registrar_paciente.php" class="card-link">
                        Ver pacientes <i class="fas fa-chevron-right"></i>
                    </a>
                </div>
                
                <div class="health-card purple-card">
                    <h3>Registrar una Consulta</h3>
                    <i class="fas fa-prescription-bottle-alt text-4xl mb-3"></i>
                    <p class="text-2xl font-bold">3</p>
                    <a href="buscador_pacientes.php" class="card-link">
                        Completar <i class="fas fa-chevron-right"></i>
                    </a>
                </div>
                
                <div class="health-card yellow-card">
                    <h3>Calificación Mensual</h3>
                    <i class="fas fa-star text-4xl mb-3"></i>
                    <p class="text-2xl font-bold">4.8</p>
                    <div class="doctor-stats">
                        <span>45 valoraciones</span>
                    </div>
                </div>
            </div>
            
            <!-- Próximas citas -->
            <div class="specialists-section">
                <h2>Próximas Citas</h2>
                <div class="doctors-grid">
                    <div class="doctor-card">
                        <div class="doctor-header">
                            <img src="../img/paciente1.jpg" alt="Paciente" class="doctor-photo">
                            <div class="doctor-title">
                                <h3>Fer</h3>
                                <p>10:30 AM - Consulta de seguimiento</p>
                            </div>
                        </div>
                        <p class="doctor-description">Motivo: Control de presión arterial</p>
                        <div class="doctor-footer">
                            <span class="rating">Historial completo</span>
                            <a href="#" class="profile-link">Ver detalles</a>
                        </div>
                    </div>
                    
                    <div class="doctor-card">
                        <div class="doctor-header">
                            <img src="../img/paciente2.png" alt="Paciente" class="doctor-photo">
                            <div class="doctor-title">
                                <h3>Diana Rh</h3>
                                <p>11:45 AM - Primera consulta</p>
                            </div>
                        </div>
                        <p class="doctor-description">Motivo: Dolor abdominal recurrente</p>
                        <div class="doctor-footer">
                            <span class="rating">Nuevo paciente</span>
                            <a href="#" class="profile-link">Ver detalles</a>
                        </div>
                    </div>
                    
                    <div class="doctor-card">
                        <div class="doctor-header">
                            <img src="../img/paciente3.png" alt="Paciente" class="doctor-photo">
                            <div class="doctor-title">
                                <h3>Max Quispe</h3>
                                <p>2:15 PM - Resultados de exámenes</p>
                            </div>
                        </div>
                        <p class="doctor-description">Motivo: Revisión de análisis de sangre</p>
                        <div class="doctor-footer">
                            <span class="rating">Exámenes listos</span>
                            <a href="#" class="profile-link">Ver detalles</a>
                        </div>
                    </div>
                </div>
                <div class="text-center mt-4">
                    <a href="modulo_citas.php" class="boton">
                        <i class="fas fa-calendar-alt mr-2"></i> Ver agenda completa
                    </a>
                </div>
            </div>
            
            <!-- Sección de contacto -->
            <div class="bg-white rounded-lg shadow-md p-6 mb-8">
                <h2 class="text-xl font-semibold mb-4 text-gray-800">Soporte Técnico</h2>
                <p class="text-gray-600 mb-4">¿Necesitas ayuda con el sistema o tienes alguna sugerencia?</p>
                <a href="https://wa.me/51930173314" target="_blank">
    <button class="boton">
        <i class="fas fa-headset mr-2"></i> Contactar soporte
    </button>
</a>
            </div>
        </div> <!-- Cierre del container -->
    </div> <!-- Cierre del content-area -->

    <?php include 'footer_medico.php'; ?>
    <script>
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('searchInput');
    const searchResults = document.getElementById('searchResults');
    const searchButton = document.getElementById('searchButton');
    
    // Función para buscar pacientes
    function searchPatients(query) {
        if (query.length < 2) {
            searchResults.style.display = 'none';
            return;
        }
        
        // Mostrar loader mientras se busca
        searchResults.innerHTML = '<div class="search-result-item" style="text-align: center;"><i class="fas fa-spinner fa-spin"></i> Buscando...</div>';
        searchResults.style.display = 'block';
        
        fetch('buscar_pacientes.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'query=' + encodeURIComponent(query)
        })
        .then(response => response.json())
        .then(data => {
            if (data.length > 0) {
                searchResults.innerHTML = '';
                data.forEach(patient => {
                    const item = document.createElement('div');
                    item.className = 'search-result-item';
                    item.innerHTML = `
                        <h4>${patient.nombre} ${patient.apellido}</h4>
                        <p><strong>DNI:</strong> ${patient.dni} | <strong>Teléfono:</strong> ${patient.telefono || 'N/A'}</p>
                        <p><strong>Edad:</strong> ${patient.edad || 'N/A'} | <strong>Género:</strong> ${patient.genero || 'N/A'}</p>
                    `;
                    item.addEventListener('click', function() {
                        // Efecto al seleccionar
                        item.style.backgroundColor = '#dbeafe';
                        setTimeout(() => {
                            window.location.href = 'perfil_paciente.php?id=' + patient.id_usuario;
                        }, 200);
                    });
                    searchResults.appendChild(item);
                });
            } else {
                searchResults.innerHTML = '<div class="search-result-item" style="text-align: center; color: #6b7280;"><i class="fas fa-user-slash"></i> No se encontraron pacientes</div>';
            }
        })
        .catch(error => {
            console.error('Error:', error);
            searchResults.innerHTML = '<div class="search-result-item" style="text-align: center; color: #ef4444;"><i class="fas fa-exclamation-circle"></i> Error en la búsqueda</div>';
        });
    }
    
    // Eventos
    searchInput.addEventListener('input', function() {
        searchPatients(this.value);
    });
    
    searchButton.addEventListener('click', function() {
        searchPatients(searchInput.value);
        searchInput.focus();
    });
    
    // Ocultar resultados al hacer clic fuera
    document.addEventListener('click', function(e) {
        if (!searchResults.contains(e.target) && e.target !== searchInput && e.target !== searchButton) {
            searchResults.style.display = 'none';
        }
    });
    
    // Efecto al presionar Enter
    searchInput.addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            searchPatients(this.value);
        }
    });
});
</script>
</body>
</html>