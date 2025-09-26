<?php
session_start();

if (!isset($_SESSION['farmacia_loggedin'])) {
    header("Location: login_farmacia.php");
    exit;
}

require 'conexion.php';
require 'puntos.php';

$sistemaPuntos = new SistemaPuntos($conexion);
$farmacia_id = $_SESSION['farmacia_id'];
$mensaje = '';
$resultado = null;

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['codigo'])) {
    $codigo = trim($_POST['codigo']);
    $resultado = $sistemaPuntos->verificarCanje($codigo, $farmacia_id);
    
    if (isset($resultado['error'])) {
        $mensaje = $resultado['error'];
    } else {
        $mensaje = "¡Canje verificado exitosamente!";
        // Refrescar el historial después de un canje exitoso
        header("Location: verificar_codigo.php");
        exit;
    }
}

// Obtener últimos canjes verificados por esta farmacia
$historial = [];
$stmt = $conexion->prepare("
    SELECT c.codigo_canje, c.puntos_canjeados, c.valor_equivalente, c.fecha_uso,
           CONCAT(u.nombre, ' ', u.apellido) as usuario
    FROM canjes c
    JOIN usuarios u ON c.id_usuario = u.id_usuario
    WHERE c.id_farmacia = ?
    ORDER BY c.fecha_uso DESC
    LIMIT 10
");
$stmt->bind_param("i", $farmacia_id);
$stmt->execute();
$historial = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>IMESYS - Validar Código</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #3b82f6;
            --primary-hover: #2563eb;
            --gradient-start: #f8fafc;
            --gradient-end: #e2e8f0;
        }
        
        body {
            background: linear-gradient(135deg, var(--gradient-start) 0%, var(--gradient-end) 100%);
            min-height: 100vh;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(8px);
            border-radius: 14px;
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
            transition: all 0.3s ease;
            border: 1px solid rgba(255, 255, 255, 0.2);
        }
        
        .card:hover {
            transform: translateY(-2px);
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
        }
        
        .btn-primary {
            background-color: var(--primary-color);
            transition: all 0.3s ease;
        }
        
        .btn-primary:hover {
            background-color: var(--primary-hover);
            transform: translateY(-1px);
        }
        
        .header {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(8px);
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
        }
        
        input:focus {
            border-color: var(--primary-color);
            ring-color: var(--primary-color);
        }
        
        table {
            border-collapse: separate;
            border-spacing: 0;
        }
        
        th {
            background: rgba(243, 244, 246, 0.8);
        }
        
        tr:last-child td {
            border-bottom: 0;
        }
    </style>
</head>
<body class="antialiased">
    <!-- Barra superior -->
    <header class="bg-gradient-to-r from-[#5DD9FC] to-[#0052CC] ">
        <div class="container mx-auto px-4 py-3 flex justify-between items-center">
            <div class="flex items-center space-x-3">
                <img src="../img/logo.png" alt="Logo IMESYS" class="h-10">
                <span class="text-lg font-semibold text-gray-800"><?php echo htmlspecialchars($_SESSION['farmacia_nombre']); ?></span>
            </div>
            <div>
                <a href="logout_farmacia.php" 
   class="flex items-center text-white border border-white px-4 py-2 rounded-lg hover:bg-white hover:text-blue-700 transition-all duration-300">
    <i class="fas fa-sign-out-alt mr-2"></i> Cerrar Sesión
</a>

            </div>
        </div>
    </header>

    <!-- Contenido principal -->
    <div class="container mx-auto px-4 py-8 max-w-6xl">
        <div class="flex items-center justify-between mb-8">
            <h1 class="text-3xl font-bold text-gray-800">Validar Código de Canje</h1>
        </div>
        
        <?php if ($mensaje): ?>
            <div class="mb-6 p-4 rounded-xl <?php echo isset($resultado['error']) ? 'bg-red-100 text-red-800 border border-red-200' : 'bg-green-100 text-green-800 border border-green-200'; ?>">
                <div class="flex items-center">
                    <i class="fas <?php echo isset($resultado['error']) ? 'fa-exclamation-circle' : 'fa-check-circle'; ?> mr-3"></i>
                    <span><?php echo htmlspecialchars($mensaje); ?></span>
                </div>
            </div>
        <?php endif; ?>
        
        <!-- Formulario de verificación -->
        <div class="card p-6 mb-8">
            <h2 class="text-xl font-semibold mb-4 text-gray-800 flex items-center">
                <i class="fas fa-qrcode mr-2 text-blue-500"></i> Verificar Código
            </h2>
            
            <form method="POST" class="flex flex-col md:flex-row gap-4 items-end">
                <div class="flex-1 w-full">
                    <label for="codigo" class="block text-gray-700 font-medium mb-2">Código de canje del paciente</label>
                    <input type="text" id="codigo" name="codigo" 
                           class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all" 
                           placeholder="Ingrese el código de 16 caracteres" 
                           pattern="[A-Za-z0-9]{16}" 
                           title="El código debe tener exactamente 16 caracteres alfanuméricos"
                           required>
                </div>
                <button type="submit" class="btn-primary text-white font-medium py-3 px-8 rounded-xl flex items-center">
                    Validar <i class="fas fa-check-circle ml-2"></i>
                </button>
            </form>
            
            <?php if ($resultado && !isset($resultado['error'])): ?>
                <div class="mt-6 p-5 bg-blue-50 rounded-xl border border-blue-100">
                    <h3 class="font-semibold text-lg mb-3 text-blue-800 flex items-center">
                        <i class="fas fa-info-circle mr-2"></i> Detalles del Canje
                    </h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="space-y-2">
                            <p class="text-gray-700"><span class="font-medium">Paciente:</span> <?php echo htmlspecialchars($resultado['usuario']); ?></p>
                            <p class="text-gray-700"><span class="font-medium">Puntos canjeados:</span> <span class="font-bold"><?php echo htmlspecialchars($resultado['canje']['puntos_canjeados']); ?></span></p>
                        </div>
                        <div class="space-y-2">
                            <p class="text-gray-700"><span class="font-medium">Valor equivalente:</span> <span class="font-bold text-blue-600"><?php echo number_format($resultado['canje']['valor_equivalente'], 2); ?> €</span></p>
                            <p class="text-gray-700"><span class="font-medium">Fecha/hora:</span> <?php echo date('d/m/Y H:i', strtotime($resultado['fecha_uso'])); ?></p>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
        
        <!-- Historial de canjes -->
        <div class="card p-6">
            <h2 class="text-xl font-semibold mb-4 text-gray-800 flex items-center">
                <i class="fas fa-history mr-2 text-blue-500"></i> Historial Reciente
            </h2>
            
            <?php if (!empty($historial)): ?>
                <div class="overflow-x-auto rounded-xl border border-gray-100">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider rounded-tl-xl">Fecha</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Paciente</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Código</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Puntos</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider rounded-tr-xl">Valor</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <?php foreach ($historial as $canje): ?>
                                <tr class="hover:bg-gray-50 transition-colors">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                        <?php echo date('d/m/Y H:i', strtotime($canje['fecha_uso'])); ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                        <?php echo htmlspecialchars($canje['usuario']); ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600 font-mono">
                                        <?php echo htmlspecialchars($canje['codigo_canje']); ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                        <?php echo htmlspecialchars($canje['puntos_canjeados']); ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-blue-600">
                                        <?php echo number_format($canje['valor_equivalente'], 2); ?> €
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="text-center py-12 rounded-xl bg-gray-50 border border-gray-200">
                    <i class="fas fa-exchange-alt text-5xl text-gray-300 mb-4"></i>
                    <p class="text-gray-500 text-lg">No hay canjes registrados</p>
                    <p class="text-gray-400 mt-2">Los canjes aparecerán aquí una vez verificados</p>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script>
        // Validación del formulario antes de enviar
        document.querySelector('form').addEventListener('submit', function(e) {
            const codigo = document.getElementById('codigo').value.trim();
            if (codigo.length !== 16) {
                alert('El código debe tener exactamente 16 caracteres');
                e.preventDefault();
            }
        });
        
        // Mejorar la experiencia de usuario
        document.getElementById('codigo').addEventListener('input', function(e) {
            this.value = this.value.toUpperCase();
        });
    </script>
</body>
</html>