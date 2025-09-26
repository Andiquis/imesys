<?php
require_once 'conexion.php';

class SistemaPuntos {
    public $conexion;
    
    public function __construct($conexion) {
        $this->conexion = $conexion;
    }
    
    /**
     * Obtiene el saldo de puntos de un usuario
     */
    public function obtenerSaldo($id_usuario) {
        $stmt = $this->conexion->prepare("SELECT puntos FROM recompensas WHERE id_usuario = ?");
        $stmt->bind_param("i", $id_usuario);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            return $row['puntos'];
        } else {
            $this->crearRegistroInicial($id_usuario);
            return 0;
        }
    }
    
    /**
     * Verifica puntos asignados hoy por una acción específica
     */
    public function verificarPuntosHoy($id_usuario, $tipo_accion) {
        $stmt = $this->conexion->prepare("
            SELECT COALESCE(SUM(puntos), 0) as total 
            FROM transacciones_puntos 
            WHERE id_usuario = ? 
            AND descripcion LIKE ?
            AND DATE(fecha_transaccion) = CURDATE()
        ");
        $like_param = "%$tipo_accion%";
        $stmt->bind_param("is", $id_usuario, $like_param);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        return $row['total'];
    }
    
    /**
     * Crea registro inicial de puntos
     */
    private function crearRegistroInicial($id_usuario) {
        $stmt = $this->conexion->prepare("INSERT INTO recompensas (id_usuario, puntos) VALUES (?, 0)");
        $stmt->bind_param("i", $id_usuario);
        $stmt->execute();
    }
    
    /**
     * Añade puntos y registra la transacción
     */
    public function agregarPuntos($id_usuario, $puntos, $descripcion, $referencia = null) {
        $saldo_actual = $this->obtenerSaldo($id_usuario);
        $nuevo_saldo = $saldo_actual + $puntos;
        
        $stmt = $this->conexion->prepare("UPDATE recompensas SET puntos = ? WHERE id_usuario = ?");
        $stmt->bind_param("ii", $nuevo_saldo, $id_usuario);
        $stmt->execute();
        
        $this->registrarTransaccion($id_usuario, 'ganancia', $puntos, $descripcion, $referencia);
        return $nuevo_saldo;
    }
    
    /**
     * Registra una transacción
     */
    private function registrarTransaccion($id_usuario, $tipo, $puntos, $descripcion, $referencia = null) {
        $stmt = $this->conexion->prepare("
            INSERT INTO transacciones_puntos 
            (id_usuario, tipo, puntos, descripcion, referencia) 
            VALUES (?, ?, ?, ?, ?)
        ");
        $stmt->bind_param("isiss", $id_usuario, $tipo, $puntos, $descripcion, $referencia);
        $stmt->execute();
        return $stmt->insert_id;
    }
    
    /**
     * Canjea puntos por un código
     */
    public function canjearPuntos($id_usuario, $puntos_a_canjear) {
        // Validar máximo diario
        $max_diario = $this->obtenerConfig('max_puntos_diarios');
        $canjeados_hoy = $this->puntosCanjeadosHoy($id_usuario);
        
        if (($canjeados_hoy + $puntos_a_canjear) > $max_diario) {
            throw new Exception("No puedes canjear más de $max_diario puntos por día. Ya has canjeado $canjeados_hoy puntos hoy.");
        }
        
        // Verificar saldo
        $saldo_actual = $this->obtenerSaldo($id_usuario);
        if ($saldo_actual < $puntos_a_canjear) {
            throw new Exception("No tienes suficientes puntos para realizar este canje.");
        }
        
        // Calcular valor equivalente
        $valor_por_punto = $this->obtenerConfig('valor_por_punto');
        $valor_equivalente = $puntos_a_canjear * $valor_por_punto;
        
        // Generar código único de 16 caracteres
        $codigo_canje = $this->generarCodigoUnico();
        
        // Actualizar saldo
        $nuevo_saldo = $saldo_actual - $puntos_a_canjear;
        $stmt = $this->conexion->prepare("UPDATE recompensas SET puntos = ? WHERE id_usuario = ?");
        $stmt->bind_param("ii", $nuevo_saldo, $id_usuario);
        $stmt->execute();
        
        // Registrar transacción
        $id_transaccion = $this->registrarTransaccion(
            $id_usuario, 
            'canje', 
            -$puntos_a_canjear, 
            'Canje de puntos', 
            $codigo_canje
        );
        
        // Registrar canje con estado inicial 'generado'
        $stmt = $this->conexion->prepare("
            INSERT INTO canjes 
            (id_usuario, id_transaccion, puntos_canjeados, valor_equivalente, codigo_canje, estado, fecha_generacion) 
            VALUES (?, ?, ?, ?, ?, 'generado', NOW())
        ");
        $stmt->bind_param("iiids", $id_usuario, $id_transaccion, $puntos_a_canjear, $valor_equivalente, $codigo_canje);
        $stmt->execute();
        
        return [
            'id_canje' => $stmt->insert_id,
            'codigo_canje' => $codigo_canje,
            'puntos_canjeados' => $puntos_a_canjear,
            'valor_equivalente' => $valor_equivalente,
            'nuevo_saldo' => $nuevo_saldo
        ];
    }
    
    /**
     * Obtiene puntos canjeados hoy
     */
    private function puntosCanjeadosHoy($id_usuario) {
        $stmt = $this->conexion->prepare("
            SELECT COALESCE(SUM(ABS(puntos)), 0) as total 
            FROM transacciones_puntos 
            WHERE id_usuario = ? 
            AND tipo = 'canje' 
            AND DATE(fecha_transaccion) = CURDATE()
        ");
        $stmt->bind_param("i", $id_usuario);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        return $row['total'];
    }
    
    /**
     * Genera código único para canje (16 caracteres alfanuméricos)
     */
    private function generarCodigoUnico() {
        do {
            $codigo = strtoupper(bin2hex(random_bytes(8))); // 16 caracteres
            $stmt = $this->conexion->prepare("SELECT id_canje FROM canjes WHERE codigo_canje = ?");
            $stmt->bind_param("s", $codigo);
            $stmt->execute();
            $stmt->store_result();
        } while ($stmt->num_rows > 0);
        
        return $codigo;
    }
    
    /**
     * Obtiene valor de configuración
     */
    private function obtenerConfig($clave) {
        $stmt = $this->conexion->prepare("SELECT valor FROM config_puntos WHERE clave = ?");
        $stmt->bind_param("s", $clave);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            return is_numeric($row['valor']) ? (float)$row['valor'] : $row['valor'];
        }
        
        throw new Exception("Configuración '$clave' no encontrada");
    }
    
    /**
     * Obtiene historial de transacciones
     */
    public function obtenerHistorial($id_usuario, $limite = 10) {
        $stmt = $this->conexion->prepare("
            SELECT tipo, puntos, descripcion, fecha_transaccion
            FROM transacciones_puntos
            WHERE id_usuario = ?
            ORDER BY fecha_transaccion DESC
            LIMIT ?
        ");
        $stmt->bind_param("ii", $id_usuario, $limite);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_all(MYSQLI_ASSOC);
    }
    
    /**
     * Verifica un código de canje (para farmacias)
     */
    public function verificarCanje($codigo_canje, $id_farmacia) {
        // Validación básica del código
        if (empty($codigo_canje) || strlen($codigo_canje) != 16) {
            return ['error' => 'El código debe tener exactamente 16 caracteres'];
        }

        // Obtener el canje
        $stmt = $this->conexion->prepare("
            SELECT c.*, u.nombre, u.apellido
            FROM canjes c
            JOIN usuarios u ON c.id_usuario = u.id_usuario
            WHERE c.codigo_canje = ?
            AND c.estado = 'generado'
        ");
        
        if (!$stmt || !$stmt->bind_param("s", $codigo_canje) || !$stmt->execute()) {
            return ['error' => 'Error en la consulta: ' . $this->conexion->error];
        }
        
        $result = $stmt->get_result();
        
        if ($result->num_rows === 0) {
            return ['error' => 'Código no encontrado o ya utilizado'];
        }
        
        $canje = $result->fetch_assoc();
        
        // Verificar expiración (24 horas por defecto)
        $horas_expiracion = $this->obtenerConfig('duracion_qr_horas') ?? 24;
        $fecha_expiracion = strtotime($canje['fecha_generacion']) + ($horas_expiracion * 3600);
        
        if (time() > $fecha_expiracion) {
            $this->marcarCanjeExpirado($canje['id_canje']);
            return ['error' => 'Este código ha expirado'];
        }
        
        // Marcar como utilizado
        $this->marcarCanjeUtilizado($canje['id_canje'], $id_farmacia);
        
        return [
            'success' => true,
            'canje' => $canje,
            'usuario' => "{$canje['nombre']} {$canje['apellido']}",
            'valor_equivalente' => $canje['valor_equivalente'],
            'fecha_uso' => date('Y-m-d H:i:s')
        ];
    }
    
    /**
     * Marca un canje como utilizado
     */
    private function marcarCanjeUtilizado($id_canje, $id_farmacia) {
        $stmt = $this->conexion->prepare("
            UPDATE canjes 
            SET estado = 'utilizado', 
                fecha_uso = NOW(), 
                id_farmacia = ?
            WHERE id_canje = ?
        ");
        $stmt->bind_param("ii", $id_farmacia, $id_canje);
        $stmt->execute();
    }
    
    /**
     * Marca un canje como expirado
     */
    private function marcarCanjeExpirado($id_canje) {
        $stmt = $this->conexion->prepare("
            UPDATE canjes 
            SET estado = 'expirado'
            WHERE id_canje = ?
        ");
        $stmt->bind_param("i", $id_canje);
        $stmt->execute();
    }
}
?>