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
            // Si no existe registro, lo creamos con 0 puntos
            $this->crearRegistroInicial($id_usuario);
            return 0;
        }
    }
    
    /**
 * Verifica cuántos puntos se han asignado hoy por una acción específica
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
     * Crea registro inicial de puntos para un usuario
     */
    public function crearRegistroInicial($id_usuario) {
        $stmt = $this->conexion->prepare("INSERT INTO recompensas (id_usuario, puntos) VALUES (?, 0)");
        $stmt->bind_param("i", $id_usuario);
        $stmt->execute();
    }
    
    /**
     * Añade puntos a un usuario y registra la transacción
     */
    public function agregarPuntos($id_usuario, $puntos, $descripcion, $referencia = null) {
        // Verificar si existe registro
        $saldo_actual = $this->obtenerSaldo($id_usuario);
        
        // Actualizar saldo
        $nuevo_saldo = $saldo_actual + $puntos;
        $stmt = $this->conexion->prepare("UPDATE recompensas SET puntos = ? WHERE id_usuario = ?");
        $stmt->bind_param("ii", $nuevo_saldo, $id_usuario);
        $stmt->execute();
        
        // Registrar transacción
        $this->registrarTransaccion($id_usuario, 'ganancia', $puntos, $descripcion, $referencia);
        
        return $nuevo_saldo;
    }
    
    /**
     * Registra una transacción de puntos
     */
    public function registrarTransaccion($id_usuario, $tipo, $puntos, $descripcion, $referencia = null) {
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
     * Canjea puntos por un QR con valor equivalente
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
        
        // Generar código único
        $codigo_canje = $this->generarCodigoUnico();
        
        // Registrar transacción (restar puntos)
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
            'canje_'.$codigo_canje
        );
        
        // Registrar canje
        $stmt = $this->conexion->prepare("
            INSERT INTO canjes 
            (id_usuario, id_transaccion, puntos_canjeados, valor_equivalente, codigo_canje) 
            VALUES (?, ?, ?, ?, ?)
        ");
        $stmt->bind_param("iiids", $id_usuario, $id_transaccion, $puntos_a_canjear, $valor_equivalente, $codigo_canje);
        $stmt->execute();
        $id_canje = $stmt->insert_id;
        
        return [
            'id_canje' => $id_canje,
            'codigo_canje' => $codigo_canje,
            'puntos_canjeados' => $puntos_a_canjear,
            'valor_equivalente' => $valor_equivalente,
            'nuevo_saldo' => $nuevo_saldo
        ];
    }
    
    /**
     * Obtiene puntos canjeados hoy por un usuario
     */
    public function puntosCanjeadosHoy($id_usuario) {
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
     * Genera un código único para el canje
     */
    public function generarCodigoUnico() {
        $codigo = strtoupper(bin2hex(random_bytes(8))); // Genera un código de 16 caracteres
        
        // Verificar que no exista
        $stmt = $this->conexion->prepare("SELECT id_canje FROM canjes WHERE codigo_canje = ?");
        $stmt->bind_param("s", $codigo);
        $stmt->execute();
        $stmt->store_result();
        
        if ($stmt->num_rows > 0) {
            // Si existe, generar otro
            return $this->generarCodigoUnico();
        }
        
        return $codigo;
    }
    
    /**
     * Obtiene valor de configuración
     */
    public function obtenerConfig($clave) {
        $stmt = $this->conexion->prepare("SELECT valor FROM config_puntos WHERE clave = ?");
        $stmt->bind_param("s", $clave);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            return is_numeric($row['valor']) ? (float)$row['valor'] : $row['valor'];
        }
        
        return null;
    }
    
    /**
     * Obtiene historial de transacciones
     */
    public function obtenerHistorial($id_usuario, $limite = 10) {
        $stmt = $this->conexion->prepare("
            SELECT id_transaccion, tipo, puntos, descripcion, referencia, fecha_transaccion
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
     * Verifica un código de canje para farmacias
     */
    public function verificarCanje($codigo_canje, $id_farmacia) {
        // Obtener canje
        $stmt = $this->conexion->prepare("
            SELECT c.*, u.nombre, u.apellido
            FROM canjes c
            JOIN usuarios u ON c.id_usuario = u.id_usuario
            WHERE c.codigo_canje = ?
            AND c.estado = 'generado'
        ");
        $stmt->bind_param("s", $codigo_canje);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 0) {
            return ['error' => 'Código no válido o ya utilizado'];
        }
        
        $canje = $result->fetch_assoc();
        
        // Verificar expiración (24 horas)
        $fecha_generacion = new DateTime($canje['fecha_generacion']);
        $fecha_actual = new DateTime();
        $diferencia = $fecha_actual->getTimestamp() - $fecha_generacion->getTimestamp();
        $horas_expiracion = $this->obtenerConfig('duracion_qr_horas');
        
        if ($diferencia > ($horas_expiracion * 3600)) {
            // Marcar como expirado
            $this->marcarCanjeExpirado($canje['id_canje']);
            return ['error' => 'El código ha expirado'];
        }
        
        // Marcar como utilizado
        $this->marcarCanjeUtilizado($canje['id_canje'], $id_farmacia);
        
        return [
            'success' => true,
            'canje' => $canje,
            'usuario' => $canje['nombre'] . ' ' . $canje['apellido'],
            'fecha_uso' => date('Y-m-d H:i:s')
        ];
    }
    
    public function marcarCanjeUtilizado($id_canje, $id_farmacia) {
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
    
    public function marcarCanjeExpirado($id_canje) {
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