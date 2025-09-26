<?php
session_start();

// Definición de la clase Message equivalente a la interfaz en Angular
class Message {
    public $sender;
    public $text;
    public $time;
    public $isSent;

    public function __construct($sender, $text, $time, $isSent) {
        $this->sender = $sender;
        $this->text = $text;
        $this->time = $time;
        $this->isSent = $isSent;
    }
}

// Clase ChatService para manejar las peticiones a la API
class ChatService {
    private $apiUrl = 'http://localhost:5000/api/ollamabot';

    public function sendMessage($message) {
        $data = json_encode(['prompt' => $message]);
        
        $ch = curl_init($this->apiUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Content-Length: ' . strlen($data)
        ]);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        
        if (curl_error($ch)) {
            curl_close($ch);
            throw new Exception('Error en la solicitud: ' . curl_error($ch));
        }
        
        curl_close($ch);
        
        if ($httpCode >= 200 && $httpCode < 300) {
            $responseData = json_decode($response, true);
            return $responseData['response'] ?? "No se recibió respuesta válida";
        } else {
            throw new Exception('Error en la API: ' . $httpCode);
        }
    }
}

// Inicializar las variables de sesión para mensajes
$jsonFile = 'chat-data.json';
if (!isset($_SESSION['messages'])) {
    // Intentar cargar desde el archivo JSON si existe
    if (file_exists($jsonFile)) {
        $loadedMessages = json_decode(file_get_contents($jsonFile), true);
        $_SESSION['messages'] = array_map(function($msg) {
            return new Message($msg['sender'], $msg['text'], $msg['time'], $msg['isSent']);
        }, $loadedMessages);
    } else {
        $_SESSION['messages'] = [
            new Message(
                'IMESYS',
                '¡Hola! Soy IMESYS, tu asistente virtual. ¿En qué puedo ayudarte hoy?',
                getCurrentTime(),
                false
            )
        ];
    }
}

// Función para obtener la hora actual
function getCurrentTime() {
    return date('H:i');
}

// Función para convertir Markdown a HTML
function parseMarkdown($text) {
    // Implementación básica - para una implementación completa, considera usar Parsedown
    
    // Negrita
    $text = preg_replace('/\*\*(.*?)\*\*/s', '<strong>$1</strong>', $text);
    $text = preg_replace('/\_\_(.*?)\_\_/s', '<strong>$1</strong>', $text);
    
    // Cursiva
    $text = preg_replace('/\*(.*?)\*/s', '<em>$1</em>', $text);
    $text = preg_replace('/\_(.*?)\_/s', '<em>$1</em>', $text);
    
    // Enlaces
    $text = preg_replace('/\[(.*?)\]\((.*?)\)/s', '<a href="$2">$1</a>', $text);
    
    // Código
    $text = preg_replace('/\`(.*?)\`/s', '<code>$1</code>', $text);
    
    // Listas
    $text = preg_replace('/^\- (.*?)$/m', '<li>$1</li>', $text);
    $text = preg_replace('/<\/li>\n<li>/s', '</li><li>', $text);
    $text = preg_replace('/<li>.*?(<\/li>)/s', '<ul>$0</ul>', $text);
    
    // Párrafos
    $text = preg_replace('/\n\n(.*?)\n\n/s', '</p><p>$1</p><p>', $text);
    
    // Líneas nuevas
    $text = nl2br($text);
    
    return $text;
}

// Manejar la solicitud de envío de mensaje
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['message'])) {
    $userMessage = trim($_POST['message']);
    
    if (!empty($userMessage)) {
        // Agregar mensaje del usuario
        $_SESSION['messages'][] = new Message(
            'Yo',
            $userMessage,
            getCurrentTime(),
            true
        );
        
        try {
            // Instanciar el servicio de chat y enviar mensaje
            $chatService = new ChatService();
            $botResponse = $chatService->sendMessage($userMessage);
            
            // Agregar respuesta del bot
            $_SESSION['messages'][] = new Message(
                'IMESYS',
                $botResponse,
                getCurrentTime(),
                false
            );
        } catch (Exception $e) {
            // Manejar errores
            $_SESSION['messages'][] = new Message(
                'Sistema',
                'Error al conectar con el servidor: ' . $e->getMessage(),
                getCurrentTime(),
                false
            );
        }
        
        // Guardar en el archivo JSON
        $messagesForJson = array_map(function($msg) {
            return [
                'sender' => $msg->sender,
                'text' => $msg->text,
                'time' => $msg->time,
                'isSent' => $msg->isSent
            ];
        }, $_SESSION['messages']);
        file_put_contents($jsonFile, json_encode($messagesForJson));
    }
    
    // Redirigir para evitar reenvíos
    header('Location: ' . $_SERVER['PHP_SELF']);
    exit;
}

// Función para limpiar el historial de chat si es necesario
if (isset($_POST['clear_chat'])) {
    $_SESSION['messages'] = [
        new Message(
            'IMESYS',
            '¡Hola! Soy IMESYS, tu asistente virtual. ¿En qué puedo ayudarte hoy?',
            getCurrentTime(),
            false
        )
    ];
    
    // Guardar en el archivo JSON
    $messagesForJson = [
        [
            'sender' => 'IMESYS',
            'text' => '¡Hola! Soy IMESYS, tu asistente virtual. ¿En qué puedo ayudarte hoy?',
            'time' => getCurrentTime(),
            'isSent' => false
        ]
    ];
    file_put_contents($jsonFile, json_encode($messagesForJson));
    
    header('Location: ' . $_SERVER['PHP_SELF']);
    exit;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>IMESYS Asistente Virtual</title>
    <!-- Enlace a archivo de estilos externo -->
    <link rel="stylesheet" href="../geminiBot/styles.scss">
    <!-- Font Awesome para iconos -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <div class="chat-container">
        <div class="chat-header">
            <div class="header-content">
                <i class="fas fa-robot header-icon"></i>
                <h2>IMESYS Asistente Virtual</h2>
                <form method="post" style="display: inline;">
                    <input type="hidden" name="clear_chat" value="1">
                    <button type="submit" class="clear-button" title="Limpiar historial">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M3 6h18M8 6V4a2 2 0 012-2h4a2 2 0 012 2v2m-4 5v6m-4 0v-6m10 6V9a1 1 0 00-1-1H5a1 1 0 00-1 1v10a1 1 0 001 1h14a1 1 0 001-1z" />
                        </svg>
                    </button>
                </form>
            </div>
        </div>
        
        <div class="chat-body">
            <div class="chat-messages" id="chatMessages">
                <?php foreach ($_SESSION['messages'] as $index => $message): ?>
                    <div class="message-container">
                        <?php if ($message->isSent): ?>
                            <div class="message user-message">
                                <div class="message-content"><?php echo nl2br(htmlspecialchars($message->text)); ?></div>
                                <div class="message-time"><?php echo htmlspecialchars($message->time); ?></div>
                            </div>
                        <?php else: ?>
                            <?php if ($message->text === 'typing'): ?>
                                <div class="loading-indicator">
                                    <div class="spinner"></div>
                                    <span>Escribiendo...</span>
                                </div>
                            <?php else: ?>
                                <div class="message bot-message">
                                    <div class="message-content"><?php echo parseMarkdown(htmlspecialchars($message->text)); ?></div>
                                    <div class="message-time"><?php echo htmlspecialchars($message->time); ?></div>
                                </div>
                            <?php endif; ?>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
        
        <div class="chat-footer">
            <form method="post" id="messageForm" class="input-container">
                <input 
                    id="messageInput" 
                    type="text" 
                    name="message" 
                    class="chat-input"
                    placeholder="Escribe tu mensaje aquí..." 
                    required
                    autocomplete="off"
                >
                <button type="submit" class="send-button">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M2.01 21L23 12 2.01 3 2 10l15 2-15 2z"></path>
                    </svg>
                </button>
            </form>
        </div>
    </div>

    <script>
        // Variables
        const chatMessages = document.getElementById('chatMessages');
        const messageInput = document.getElementById('messageInput');
        const messageForm = document.getElementById('messageForm');
        
        // Función para desplazarse al final del chat
        function scrollToBottom() {
            chatMessages.scrollTop = chatMessages.scrollHeight;
        }
        
        // Permitir enviar con Enter
        messageInput.addEventListener('keydown', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                messageForm.submit();
            }
        });
        
        // Inicialización
        document.addEventListener('DOMContentLoaded', function() {
            scrollToBottom();

            // Agregar animaciones suaves a los mensajes
            const messages = document.querySelectorAll('.message');
            messages.forEach((message, index) => {
                setTimeout(() => {
                    message.style.opacity = '1';
                    message.style.transform = 'translateY(0)';
                }, index * 100);
            });
        });

        // Agregar una pequeña animación al botón de enviar
        const sendButton = document.querySelector('.send-button');
    sendButton.addEventListener('mousedown', function() {
            this.style.transform = 'scale(0.95)';
        });
        
        sendButton.addEventListener('mouseup', function() {
            this.style.transform = 'scale(1)';
        });
        
        sendButton.addEventListener('mouseleave', function() {
            this.style.transform = 'scale(1)';
        });
    </script>
</body>
</html>