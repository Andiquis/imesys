<?php
include 'header_medico.php';

require 'conexion.php';

// Obtener recetas recientes
$id_medico = $_SESSION['id_medico'];
$query = "SELECT r.*, u.nombre as paciente_nombre, u.apellido as paciente_apellido, u.dni
          FROM recetas r
          JOIN usuarios u ON r.id_paciente = u.id_usuario
          WHERE r.id_medico = ?
          ORDER BY r.fecha_emision DESC
          LIMIT 5";
$stmt = $conexion->prepare($query);
$stmt->bind_param("i", $id_medico);
$stmt->execute();
$result = $stmt->get_result();
$recetas = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();
$conexion->close();
?>

<!-- Contenido principal -->
<div id="contentArea" class="content-area">
    <div class="container mx-auto px-4 py-6">
        <!-- Encabezado -->
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-bold text-gray-800">Recetas Electrónicas</h1>
            <a href="nueva_receta.php" class="boton">
                <i class="fas fa-plus mr-2"></i> Nueva Receta
            </a>
        </div>
        
        <!-- Resumen rápido -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <div class="bg-white rounded-lg shadow-md p-6 border-l-4 border-blue-500">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-500">Recetas este mes</p>
                        <p class="text-3xl font-bold text-gray-800">15</p>
                    </div>
                    <div class="bg-blue-100 p-3 rounded-full">
                        <i class="fas fa-prescription-bottle-alt text-blue-600 text-xl"></i>
                    </div>
                </div>
            </div>
            
            <div class="bg-white rounded-lg shadow-md p-6 border-l-4 border-green-500">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-500">Pacientes atendidos</p>
                        <p class="text-3xl font-bold text-gray-800">12</p>
                    </div>
                    <div class="bg-green-100 p-3 rounded-full">
                        <i class="fas fa-user-injured text-green-600 text-xl"></i>
                    </div>
                </div>
            </div>
            
            <div class="bg-white rounded-lg shadow-md p-6 border-l-4 border-purple-500">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-500">Medicamentos recetados</p>
                        <p class="text-3xl font-bold text-gray-800">28</p>
                    </div>
                    <div class="bg-purple-100 p-3 rounded-full">
                        <i class="fas fa-pills text-purple-600 text-xl"></i>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Recetas recientes -->
        <div class="bg-white rounded-lg shadow-md overflow-hidden mb-8">
            <div class="p-6">
                <h2 class="text-lg font-semibold text-gray-800 mb-4">Recetas Recientes</h2>
                
                <?php if (empty($recetas)): ?>
                    <div class="text-center py-8">
                        <i class="fas fa-prescription text-4xl text-gray-300 mb-3"></i>
                        <p class="text-gray-500">No hay recetas registradas</p>
                    </div>
                <?php else: ?>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Fecha</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Paciente</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">DNI</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Medicamentos</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Acciones</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <?php foreach ($recetas as $receta): ?>
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            <?= date('d/m/Y', strtotime($receta['fecha_emision'])) ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                            <?= htmlspecialchars($receta['paciente_nombre'] . ' ' . $receta['paciente_apellido']) ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            <?= htmlspecialchars($receta['dni']) ?>
                                        </td>
                                        <td class="px-6 py-4 text-sm text-gray-500">
                                            <?= substr($receta['medicamentos'], 0, 50) ?>...
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            <a href="ver_receta.php?id=<?= $receta['id_receta'] ?>" class="text-blue-600 hover:text-blue-800 mr-3">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="generar_pdf_receta.php?id=<?= $receta['id_receta'] ?>" class="text-green-600 hover:text-green-800">
                                                <i class="fas fa-file-pdf"></i>
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php include 'footer_medico.php'; ?>