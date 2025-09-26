<?php
session_start();

// Inicializar historial de chat si no existe
if (!isset($_SESSION['chatHistory'])) {
    // Intentar cargar desde un archivo JSON si existe
    $jsonFile = 'chat-data.json';
    if (file_exists($jsonFile)) {
        $_SESSION['chatHistory'] = json_decode(file_get_contents($jsonFile), true);
    } else {
        $_SESSION['chatHistory'] = [];
    }
}

// Manejar envío de mensajes
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'sendMessage' && !empty($_POST['userMessage'])) {
        $userMessage = htmlspecialchars($_POST['userMessage']);
        
        // Preparar la solicitud a la API
        $apiUrl = 'http://localhost:5000/api/chatbot';
        $postData = json_encode(['question' => $userMessage]);
        
        // Configurar opciones de cURL
        $ch = curl_init($apiUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Content-Length: ' . strlen($postData)
        ]);
        
        // Ejecutar la solicitud
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        // Procesar la respuesta
        if ($httpCode === 200) {
            $responseData = json_decode($response, true);
            $botMessage = $responseData['response'] ?? 'Error al procesar la respuesta.';
        } else {
            $botMessage = 'Error en la respuesta. Inténtalo de nuevo.';
        }
        
        // Añadir al historial
        $_SESSION['chatHistory'][] = [
            'id' => count($_SESSION['chatHistory']) + 1,
            'user' => $userMessage,
            'bot' => $botMessage
        ];
        
        // Guardar en el archivo JSON
        file_put_contents('chat-data.json', json_encode($_SESSION['chatHistory']));
        
        // Redirigir para evitar reenvío del formulario
        header('Location: ' . $_SERVER['PHP_SELF']);
        exit;
    } elseif ($_POST['action'] === 'clearChat') {
        // Limpiar historial
        $_SESSION['chatHistory'] = [];
        file_put_contents('chat-data.json', json_encode([]));
        
        // Redirigir
        header('Location: ' . $_SERVER['PHP_SELF']);
        exit;
    }
}

// Función para obtener la hora actual
function getCurrentTime() {
    return date('H:i');
}

// Función para convertir Markdown a HTML
function parseMarkdown($text) {
    // Implementación básica - esta es una versión simplificada
    // Para una implementación completa, considere usar una biblioteca como Parsedown
    
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
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ImesysBot - Asistente Médico</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="styles.scss">
</head>
<body>
    <div class="chat-container">
        <div class="chat-header">
            <div class="header-content">
                <i class="fas fa-user-doctor header-icon"></i>
                <h1>ImesysBot - Asistente Médico</h1>
                <form method="post" style="display: inline;">
                    <input type="hidden" name="action" value="clearChat">
                    <button type="submit" class="clear-button" title="Limpiar historial">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M3 6h18M8 6V4a2 2 0 012-2h4a2 2 0 012 2v2m-4 5v6m-4 0v-6m10 6V9a1 1 0 00-1-1H5a1 1 0 00-1 1v10a1 1 0 001 1h14a1 1 0 001-1z" />
                        </svg>
                    </button>
                </form>
            </div>
        </div>
        <div class="chat-body" id="chatBody">
            <div class="chat-messages" id="chatMessages">
                <?php if (empty($_SESSION['chatHistory'])): ?>
                    <div class="bot-message message">
                        <div class="message-content markdown-content">Hazme una pregunta...</div>
                        <div class="message-time"><?php echo getCurrentTime(); ?></div>
                    </div>
                <?php else: ?>
                    <?php foreach ($_SESSION['chatHistory'] as $message): ?>
                        <div class="message-container">
                            <div class="user-message message">
                                <div class="message-content"><?php echo $message['user']; ?></div>
                                <div class="message-time"><?php echo getCurrentTime(); ?></div>
                            </div>
                            <div class="bot-message message">
                                <div class="message-content markdown-content">
                                    <?php echo parseMarkdown($message['bot']); ?>
                                </div>
                                <div class="message-time"><?php echo getCurrentTime(); ?></div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
        <div class="chat-footer">
            <form method="post" class="input-container">
                <input type="hidden" name="action" value="sendMessage">
                <input 
                    type="text" 
                    name="userMessage" 
                    placeholder="Escribe tu consulta médica..." 
                    class="chat-input" 
                    required
                    autocomplete="off"
                >
                <button type="submit" class="send-button">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M5 12h14M12 5l7 7-7 7" />
                    </svg>
                </button>
            </form>
        </div>
    </div>

    <script>
        // Función para desplazarse al final del chat
        function scrollToBottom() {
            const chatBody = document.getElementById('chatBody');
            chatBody.scrollTop = chatBody.scrollHeight;
        }

        // Ejecutar al cargar la página
        document.addEventListener('DOMContentLoaded', function() {
            scrollToBottom();
        });
    </script>
</body>
</html>