<?php
session_start();

if (!isset($_SESSION['loggedin'])) {
    header("Location: login.php");
    exit;
}

require 'conexion.php';
require 'puntos.php';

$sistemaPuntos = new SistemaPuntos($conexion);
$user_id = $_SESSION['id_usuario'];

// Procesar canje de puntos
$mensaje = '';
$codigo_data = null; // Cambiamos el nombre de qr_data a codigo_data

// Por esto:
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['canjear_puntos'])) {
    $puntos_a_canjear = intval($_POST['puntos']);
    
    try {
        $resultado_canje = $sistemaPuntos->canjearPuntos($user_id, $puntos_a_canjear);
        
        $codigo_data = [
            'codigo' => $resultado_canje['codigo_canje'], // Usamos el código real de la base de datos
            'valor' => $resultado_canje['valor_equivalente'],
            'puntos' => $resultado_canje['puntos_canjeados'],
            'fecha' => date('d/m/Y H:i')
        ];
        
        $mensaje = "¡Canje exitoso! Se han descontado {$resultado_canje['puntos_canjeados']} puntos.";
    } catch (Exception $e) {
        $mensaje = "Error: " . $e->getMessage();
    }
}


// Obtener datos del usuario
$saldo_puntos = $sistemaPuntos->obtenerSaldo($user_id);
$historial = $sistemaPuntos->obtenerHistorial($user_id, 10);
$valor_por_punto = $sistemaPuntos->obtenerConfig('valor_por_punto');
$max_diario = $sistemaPuntos->obtenerConfig('max_puntos_diarios');
$canjeados_hoy = $sistemaPuntos->puntosCanjeadosHoy($user_id);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>IMESYS - Mis Recompensas</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/qrcode@1.5.1/build/qrcode.min.js"></script>
    <style>
        .progress-bar {
            height: 10px;
            background-color: #e5e7eb;
            border-radius: 5px;
            overflow: hidden;
        }
        .progress {
            height: 100%;
            background-color: #3b82f6;
            transition: width 0.3s ease;
        }
        /* Eliminar cualquier margen/ancho forzado en el footer */
    </style>
</head>
<body class="bg-gray-100">
    <!-- Barra superior -->
    <?php include 'header_usuarios.php'; ?>

    <!-- Contenido principal -->
    <div class="w-full flex justify-center">
      <div class="w-full max-w-4xl px-4 py-8">
        <h1 class="text-3xl font-bold text-gray-800 mb-6">Mis Recompensas</h1>
        
        <!-- Tarjeta de saldo -->
        <div class="bg-blue-600 text-white rounded-lg shadow-md p-6 mb-8">
            <div class="flex justify-between items-center">
                <div>
                    <h2 class="text-xl font-semibold mb-2">Tus Puntos IMESYS</h2>
                    <p class="text-4xl font-bold"><?php echo number_format($saldo_puntos); ?></p>
                    <p class="mt-2">1 punto = <?php echo number_format($valor_por_punto, 2); ?> S/.</p>
                </div>
                <div class="text-right">
                    <i class="fas fa-coins text-5xl opacity-30"></i>
                </div>
            </div>
            <!-- Progreso de canje diario -->
            <div class="mt-6">
                <p class="text-sm mb-1">
                    Puntos canjeados hoy: <?php echo $canjeados_hoy; ?> / <?php echo $max_diario; ?>
                </p>
                <div class="progress-bar">
                    <div class="progress" style="width: <?php echo min(100, ($canjeados_hoy / $max_diario) * 100); ?>%"></div>
                </div>
            </div>
        </div>
        
        <?php if ($mensaje): ?>
            <div class="mb-6 p-4 rounded-lg <?php echo strpos($mensaje, 'Error') === false ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'; ?>">
                <?php echo $mensaje; ?>
            </div>
        <?php endif; ?>
        
        <!-- Sección para canjear puntos -->
        <div class="bg-white rounded-lg shadow-md p-6 mb-8">
            <h2 class="text-xl font-semibold mb-4 text-gray-800">Canjear Puntos</h2>
            <form method="POST" class="flex flex-col md:flex-row gap-4 items-end">
                <div class="flex-1">
                    <label for="puntos" class="block text-gray-700 font-medium mb-2">Puntos a canjear (máx. <?php echo $max_diario; ?> por día)</label>
                    <input type="number" id="puntos" name="puntos" min="1" max="<?php echo min($saldo_puntos, $max_diario - $canjeados_hoy); ?>" 
                           class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                </div>
                <button type="submit" name="canjear_puntos" class="bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-6 rounded-lg transition duration-300">
                    Generar Codigo <i class="fas fa-qrcode ml-2"></i>
                </button>
            </form>
            <?php if ($codigo_data): ?>
<div class="mt-8 p-6 border-2 border-dashed border-blue-300 rounded-lg flex flex-col items-center">
    <h3 class="text-lg font-semibold mb-4 text-gray-800">Código de Canje</h3>
    <div class="mb-4 p-4 bg-gray-100 rounded-lg text-center">
        <span class="text-3xl font-mono font-bold tracking-wider text-blue-600">
            <?php echo $codigo_data['codigo']; ?>
        </span>
    </div>
    <div class="text-center">
        <p class="text-gray-600">Valor: <span class="font-semibold"><?php echo number_format($codigo_data['valor'], 2); ?> S/.</span></p>
        <p class="text-gray-600">Puntos: <span class="font-semibold"><?php echo $codigo_data['puntos']; ?></span></p>
        <p class="text-gray-500 text-sm mt-2">Generado: <?php echo $codigo_data['fecha']; ?></p>
    </div>
    <div class="mt-4 bg-yellow-50 border border-yellow-200 text-yellow-700 px-4 py-3 rounded text-sm">
        <i class="fas fa-exclamation-circle mr-2"></i>
        Este código expirará en <?php echo $sistemaPuntos->obtenerConfig('duracion_qr_horas'); ?> horas
    </div>
    <button onclick="window.print()" class="mt-4 bg-gray-100 hover:bg-gray-200 text-gray-800 font-medium py-2 px-4 rounded-lg transition duration-300">
        <i class="fas fa-print mr-2"></i> Imprimir o Guardar
    </button>
</div>
<?php endif; ?>
        </div>
        <!-- Sección de cómo ganar puntos -->
        <div class="bg-white rounded-lg shadow-md p-6 mb-8">
            <h2 class="text-xl font-semibold mb-4 text-gray-800">¿Cómo ganar más puntos?</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="flex items-start p-3 border border-gray-200 rounded-lg">
                    <div class="bg-blue-100 p-3 rounded-full mr-4">
                        <i class="fas fa-calendar-check text-blue-600"></i>
                    </div>
                    <div>
                        <h3 class="font-medium text-gray-800">Completar citas médicas</h3>
                        <p class="text-gray-600 text-sm">Gana <?php echo $sistemaPuntos->obtenerConfig('puntos_por_cita'); ?> puntos por cada cita completada</p>
                    </div>
                </div>
                <div class="flex items-start p-3 border border-gray-200 rounded-lg">
                    <div class="bg-green-100 p-3 rounded-full mr-4">
                        <i class="fas fa-comment-medical text-green-600"></i>
                    </div>
                    <div>
                        <h3 class="font-medium text-gray-800">Dejar comentarios</h3>
                        <p class="text-gray-600 text-sm">Gana <?php echo $sistemaPuntos->obtenerConfig('puntos_por_comentario'); ?> puntos por cada comentario válido</p>
                    </div>
                </div>
                <div class="flex items-start p-3 border border-gray-200 rounded-lg">
                    <div class="bg-purple-100 p-3 rounded-full mr-4">
                        <i class="fas fa-heartbeat text-purple-600"></i>
                    </div>
                    <div>
                        <h3 class="font-medium text-gray-800">Análisis biométricos</h3>
                        <p class="text-gray-600 text-sm">Gana <?php echo $sistemaPuntos->obtenerConfig('puntos_por_biometrico'); ?> puntos por cada análisis</p>
                    </div>
                </div>
                <div class="flex items-start p-3 border border-gray-200 rounded-lg">
                    <div class="bg-yellow-100 p-3 rounded-full mr-4">
                        <i class="fas fa-user-plus text-yellow-600"></i>
                    </div>
                    <div>
                        <h3 class="font-medium text-gray-800">Registro inicial</h3>
                        <p class="text-gray-600 text-sm">Ganaste <?php echo $sistemaPuntos->obtenerConfig('puntos_por_registro'); ?> puntos por registrarte</p>
                    </div>
                </div>
            </div>
        </div>
        <!-- Historial de puntos -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <h2 class="text-xl font-semibold mb-4 text-gray-800">Historial de Puntos</h2>
            <?php if (!empty($historial)): ?>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Fecha</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Descripción</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Puntos</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <?php foreach ($historial as $transaccion): ?>
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        <?php echo date('d/m/Y H:i', strtotime($transaccion['fecha_transaccion'])); ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        <?php echo htmlspecialchars($transaccion['descripcion']); ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium <?php echo $transaccion['tipo'] === 'ganancia' ? 'text-green-600' : 'text-red-600'; ?>">
                                        <?php echo $transaccion['tipo'] === 'ganancia' ? '+' : '-'; ?>
                                        <?php echo abs($transaccion['puntos']); ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="text-center py-8">
                    <i class="fas fa-history text-4xl text-gray-300 mb-4"></i>
                    <p class="text-gray-500">Aún no tienes historial de puntos</p>
                </div>
            <?php endif; ?>
        </div>
      </div>
    </div>
    <!-- Footer -->
    <?php include 'footer_usuario.php'; ?>

    <script>
        // Elementos del DOM
        const sidebar = document.getElementById('sidebar');
        const menuToggle = document.getElementById('menuToggle');
        const overlay = document.getElementById('overlay');
        const menuItems = document.querySelectorAll('.menu-item');
        const chatButton = document.getElementById('chatButton');
        
        // Estado del menú
        let menuOpen = false;
        
        // Función para alternar el menú
        function toggleMenu() {
            menuOpen = !menuOpen;
            
            if (menuOpen) {
                sidebar.classList.add('open');
                overlay.classList.add('show');
                menuToggle.classList.add('rotated');
            } else {
                sidebar.classList.remove('open');
                overlay.classList.remove('show');
                menuToggle.classList.remove('rotated');
            }
        }
        
        // Eventos
        menuToggle.addEventListener('click', toggleMenu);
        overlay.addEventListener('click', toggleMenu);
        
        // Evento para items del menú
        menuItems.forEach(item => {
            item.addEventListener('click', function() {
                menuItems.forEach(i => i.classList.remove('active'));
                this.classList.add('active');
                toggleMenu();
            });
        });
        
        // Actualizar máximo de puntos a canjear en tiempo real
        const inputPuntos = document.getElementById('puntos');
        if (inputPuntos) {
            inputPuntos.addEventListener('input', function() {
                const max = parseInt(this.max);
                if (parseInt(this.value) > max) {
                    this.value = max;
                }
            });
        }
    </script>
</body>
</html>