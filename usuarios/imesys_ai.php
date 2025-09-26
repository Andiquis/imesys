<?php
session_start();

// Desactivar salida de errores y habilitar registro
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', 'php_errors.log');
error_reporting(E_ALL);

if (!isset($_SESSION['loggedin'])) {
    header("Location: login.php");
    exit;
}

require 'conexion.php';

// Obtener información del usuario incluyendo la foto
$user_id = $_SESSION['id_usuario'];
$stmt = $conexion->prepare("SELECT nombre, apellido, correo, foto FROM usuarios WHERE id_usuario = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->bind_result($nombre, $apellido, $correo, $foto);
$stmt->fetch();
$stmt->close();

// Definición de la clase Message
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

// Clase ChatService para manejar las peticiones a diferentes APIs de chatbot
class ChatService {
    private $models = [
        'fast1' => 'http://localhost:5000/api/chatbot',
        'local1' => 'http://localhost:5000/api/ollamabot'
    ];
    
    private $currentModel = 'fast1';
    
    public function __construct() {
        if (isset($_SESSION['currentModel'])) {
            $this->currentModel = $_SESSION['currentModel'];
        }
    }
    
    public function setModel($modelName) {
        if (array_key_exists($modelName, $this->models)) {
            $this->currentModel = $modelName;
            $_SESSION['currentModel'] = $modelName;
            return true;
        }
        return false;
    }
    
    public function getCurrentModel() {
        return $this->currentModel;
    }
    
    public function getAvailableModels() {
        return array_keys($this->models);
    }
    
    public function sendMessage($message) {
        $apiUrl = $this->models[$this->currentModel];
        
        if ($this->currentModel === 'fast1') {
            $data = json_encode(['question' => $message]);
        } else {
            $data = json_encode(['prompt' => $message]);
        }
        
        $ch = curl_init($apiUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Content-Length: ' . strlen($data)
        ]);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        
        // Registrar respuesta para depuración
        file_put_contents('debug_response.txt', "HTTP Code: $httpCode\nResponse: $response\n", FILE_APPEND);
        
        if (curl_error($ch)) {
            curl_close($ch);
            throw new Exception('Error en la solicitud: ' . curl_error($ch));
        }
        
        curl_close($ch);
        
        if ($httpCode >= 200 && $httpCode < 300) {
            // Verificar si la respuesta parece HTML
            if (empty($response) || strpos($response, '<br') !== false || strpos($response, '<b>') !== false) {
                throw new Exception('Respuesta no válida: se recibió HTML o respuesta vacía en lugar de JSON');
            }
            $responseData = json_decode($response, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                file_put_contents('debug_response.txt', "Error JSON: " . json_last_error_msg() . "\nResponse: $response\n", FILE_APPEND);
                throw new Exception('Error al decodificar JSON: ' . json_last_error_msg());
            }
            return $responseData['response'] ?? "No se recibió respuesta válida";
        } else {
            throw new Exception('Error en la API: ' . $httpCode);
        }
    }
}

// Inicializar variables de sesión
$jsonFile = 'modulos_ia/geminiBot/chat-data.json';
$chatService = new ChatService();

// Cambiar modelo si es necesario
if (isset($_POST['change_model']) && !empty($_POST['model'])) {
    $chatService->setModel($_POST['model']);
    header('Location: ' . $_SERVER['PHP_SELF']);
    exit;
}

if (!isset($_SESSION['messages'])) {
    if (file_exists($jsonFile)) {
        $jsonContent = file_get_contents($jsonFile);
        $loadedMessages = json_decode($jsonContent, true);
        
        if (is_array($loadedMessages) && !empty($loadedMessages)) {
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
            error_log("Error: No se pudo cargar el archivo JSON $jsonFile. Contenido inválido o vacío.");
        }
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

function getCurrentTime() {
    return date('H:i');
}

function parseMarkdown($text) {
    $text = preg_replace('/\*\*(.*?)\*\*/s', '<strong>$1</strong>', $text);
    $text = preg_replace('/\_\_(.*?)\_\_/s', '<strong>$1</strong>', $text);
    $text = preg_replace('/\*(.*?)\*/s', '<em>$1</em>', $text);
    $text = preg_replace('/\_(.*?)\_/s', '<em>$1</em>', $text);
    $text = preg_replace('/\[(.*?)\]\((.*?)\)/s', '<a href="$2">$1</a>', $text);
    $text = preg_replace('/\`(.*?)\`/s', '<code>$1</code>', $text);
    $text = preg_replace('/```(.*?)```/s', '<pre><code>$1</code></pre>', $text);
    $text = preg_replace('/^\- (.*?)$/m', '<li>$1</li>', $text);
    $text = preg_replace('/<\/li>\n<li>/s', '</li><li>', $text);
    $text = preg_replace('/<li>.*?(<\/li>)/s', '<ul>$0</ul>', $text);
    $text = preg_replace('/\n\n(.*?)\n\n/s', '</p><p>$1</p><p>', $text);
    $text = nl2br($text);
    return $text;
}

// Procesar envío de mensaje AJAX
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ajax_message'])) {
    ob_clean(); // Limpiar cualquier salida previa
    header('Content-Type: application/json');
    
    $userMessage = trim($_POST['ajax_message']);
    
    if (!empty($userMessage)) {
        $userMsg = new Message('Yo', $userMessage, getCurrentTime(), true);
        $_SESSION['messages'][] = $userMsg;
        
        $response = [
            'status' => 'success',
            'userMessage' => [
                'sender' => $userMsg->sender,
                'text' => $userMsg->text,
                'time' => $userMsg->time,
                'isSent' => $userMsg->isSent,
                'html' => nl2br(htmlspecialchars($userMsg->text))
            ],
            'loading' => true
        ];
        
        $messagesForJson = array_map(function($msg) {
            return [
                'sender' => $msg->sender,
                'text' => $msg->text,
                'time' => $msg->time,
                'isSent' => $msg->isSent
            ];
        }, $_SESSION['messages']);
        file_put_contents($jsonFile, json_encode($messagesForJson));
        
        echo json_encode($response);
        
        if (function_exists('fastcgi_finish_request')) {
            session_write_close();
            fastcgi_finish_request();
        }
        
        try {
            $botResponse = $chatService->sendMessage($userMessage);
            $botMsg = new Message('IMESYS', $botResponse, getCurrentTime(), false);
            $_SESSION['messages'][] = $botMsg;
            
            $messagesForJson = array_map(function($msg) {
                return [
                    'sender' => $msg->sender,
                    'text' => $msg->text,
                    'time' => $msg->time,
                    'isSent' => $msg->isSent
                ];
            }, $_SESSION['messages']);
            file_put_contents($jsonFile, json_encode($messagesForJson));
        } catch (Exception $e) {
            $errorMsg = new Message('Sistema', 'Error al conectar con el servidor: ' . $e->getMessage(), getCurrentTime(), false);
            $_SESSION['messages'][] = $errorMsg;
            
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
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Mensaje vacío']);
    }
    exit;
}

// Endpoint para obtener la respuesta del bot
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['check_response'])) {
    ob_clean(); // Limpiar cualquier salida previa
    header('Content-Type: application/json');
    
    $lastMessages = array_slice($_SESSION['messages'], -2);
    $response = ['status' => 'checking'];
    
    if (count($lastMessages) >= 2 && !$lastMessages[1]->isSent) {
        $response = [
            'status' => 'complete',
            'botMessage' => [
                'sender' => $lastMessages[1]->sender,
                'text' => $lastMessages[1]->text,
                'time' => $lastMessages[1]->time,
                'isSent' => $lastMessages[1]->isSent,
                'html' => parseMarkdown(htmlspecialchars($lastMessages[1]->text))
            ]
        ];
    }
    
    file_put_contents('debug_check_response.txt', "Response: " . json_encode($response) . "\n", FILE_APPEND);
    
    echo json_encode($response);
    exit;
}

// Manejar envío de mensaje tradicional (fallback)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['message'])) {
    $userMessage = trim($_POST['message']);
    
    if (!empty($userMessage)) {
        $_SESSION['messages'][] = new Message('Yo', $userMessage, getCurrentTime(), true);
        
        try {
            $botResponse = $chatService->sendMessage($userMessage);
            $_SESSION['messages'][] = new Message('IMESYS', $botResponse, getCurrentTime(), false);
        } catch (Exception $e) {
            $_SESSION['messages'][] = new Message('Sistema', 'Error al conectar con el servidor: ' . $e->getMessage(), getCurrentTime(), false);
        }
        
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
    
    header('Location: ' . $_SERVER['PHP_SELF']);
    exit;
}

// Limpiar historial
if (isset($_POST['clear_chat'])) {
    $_SESSION['messages'] = [
        new Message(
            'IMESYS',
            '¡Hola! Soy IMESYS, tu asistente virtual. ¿En qué puedo ayudarte hoy?',
            getCurrentTime(),
            false
        )
    ];
    
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
    <title>IMESYS - Chat IA</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="estilos_inicio.css">
</head>
<body class="bg-gray-100">
    <!-- Barra superior -->
    <?php include 'header_usuarios.php'; ?>

    <!-- Contenido principal -->
    <div id="contentArea" class="content-area">
        <div class="container mx-auto max-w-4xl px-4 py-8">
            <div class="bg-white rounded-lg shadow-md p-6">
                <div class="chat-container max-w-2xl mx-auto">
                    <div class="chat-header flex items-center justify-between bg-gradient-to-r from-[#5DD9FC] to-[#0052CC] text-white p-4 rounded-t-lg">
                        <div class="flex items-center">
                            <i class="fas fa-robot text-xl mr-3"></i>
                            <h2 class="text-lg font-semibold">IMESYS Asistente Virtual</h2>
                        </div>
                        <div class="flex items-center space-x-2">
                            <form method="post" id="modelForm" class="inline">
                                <input type="hidden" name="change_model" value="1">
                                <select name="model" id="modelSelect" onchange="this.form.submit();" class="p-2 border border-gray-300 rounded-lg text-gray-800 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                    <?php foreach ($chatService->getAvailableModels() as $model): ?>
                                        <option value="<?php echo $model; ?>" <?php echo ($chatService->getCurrentModel() === $model) ? 'selected' : ''; ?>>
                                            <?php echo ucfirst($model); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </form>
                            <form method="post" class="inline">
                                <input type="hidden" name="clear_chat" value="1">
                                <button type="submit" class="text-white hover:text-gray-200" title="Limpiar historial">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M3 6h18M8 6V4a2 2 0 012-2h4a2 2 0 012 2v2m-4 5v6m-4 0v-6m10 6V9a1 1 0 00-1-1H5a1 1 0 00-1 1v10a1 1 0 001 1h14a1 1 0 001-1z" />
                                    </svg>
                                </button>
                            </form>
                        </div>
                    </div>
                    <div class="chat-body h-96 overflow-y-auto p-4 bg-gray-50 rounded-b-lg" id="chatBody">
                        <div class="chat-messages space-y-4" id="chatMessages">
                            <?php foreach ($_SESSION['messages'] as $message): ?>
                                <div class="message-container">
                                    <div class="message flex flex-col <?php echo $message->isSent ? 'items-end' : 'items-start'; ?>">
                                        <div class="message-content <?php echo $message->isSent ? 'bg-green-100 text-green-800' : 'bg-blue-100 text-blue-800'; ?> p-3 rounded-lg max-w-xs">
                                            <?php echo $message->isSent ? nl2br(htmlspecialchars($message->text)) : parseMarkdown(htmlspecialchars($message->text)); ?>
                                        </div>
                                        <div class="message-time text-xs text-gray-500 mt-1"><?php echo htmlspecialchars($message->time); ?></div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <div class="chat-footer mt-4">
                        <form method="post" id="messageForm" class="flex items-center space-x-2">
                            <input 
                                id="messageInput" 
                                type="text" 
                                name="message" 
                                class="flex-grow p-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" 
                                placeholder="Escribe tu mensaje aquí..." 
                                required
                                autocomplete="off"
                            >
                            <button type="submit" id="sendButton" class="bg-blue-600 text-white p-2 rounded-lg hover:bg-blue-700">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M2.01 21L23 12 2.01 3 2 10l15 2-15 2z"></path>
                                </svg>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php include 'footer_usuario.php'; ?>

    <script>
        // Elementos del DOM para el chat
        const chatMessages = document.getElementById('chatMessages');
        const messageInput = document.getElementById('messageInput');
        const messageForm = document.getElementById('messageForm');
        const sendButton = document.getElementById('sendButton');
        const chatBody = document.getElementById('chatBody');

        function scrollToBottom() {
            if (chatBody) {
                chatBody.scrollTop = chatBody.scrollHeight;
            }
        }

        function addMessage(message, isUser) {
            const msgContainer = document.createElement('div');
            msgContainer.className = 'message-container';
            
            const msgDiv = document.createElement('div');
            msgDiv.className = `message flex flex-col ${isUser ? 'items-end' : 'items-start'}`;
            
            const contentDiv = document.createElement('div');
            contentDiv.className = `message-content ${isUser ? 'bg-green-100 text-green-800' : 'bg-blue-100 text-blue-800'} p-3 rounded-lg max-w-xs`;
            contentDiv.innerHTML = message.html || message.text.replace(/\n/g, '<br>');
            
            const timeDiv = document.createElement('div');
            timeDiv.className = 'message-time text-xs text-gray-500 mt-1';
            timeDiv.textContent = message.time;
            
            msgDiv.appendChild(contentDiv);
            msgDiv.appendChild(timeDiv);
            msgContainer.appendChild(msgDiv);
            
            chatMessages.appendChild(msgContainer);
            scrollToBottom();
            
            setTimeout(() => {
                msgDiv.style.opacity = '1';
                msgDiv.style.transform = 'translateY(0)';
            }, 10);
        }

        function addLoadingIndicator() {
            const loadingDiv = document.createElement('div');
            loadingDiv.className = 'loading-indicator';
            loadingDiv.id = 'loadingIndicator';
            
            const spinner = document.createElement('div');
            spinner.className = 'animate-spin h-5 w-5 border-2 border-blue-500 border-t-transparent rounded-full';
            
            const span = document.createElement('span');
            span.className = 'ml-2 text-gray-600';
            span.textContent = 'Generando respuesta...';
            
            loadingDiv.appendChild(spinner);
            loadingDiv.appendChild(span);
            
            chatMessages.appendChild(loadingDiv);
            setTimeout(() => {
                loadingDiv.className = 'loading-indicator flex items-center space-x-2 p-2';
            }, 10);
            scrollToBottom();
        }

        function removeLoadingIndicator() {
            const indicator = document.getElementById('loadingIndicator');
            if (indicator) {
                indicator.className = 'loading-indicator';
                setTimeout(() => indicator.remove(), 300);
            }
        }

        messageForm.addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const userMessage = messageInput.value.trim();
            if (!userMessage) return;
            
            messageInput.disabled = true;
            sendButton.disabled = true;
            
            // Mostrar mensaje del usuario inmediatamente
            const userMsg = {
                text: userMessage,
                time: new Date().toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'}),
                html: userMessage.replace(/\n/g, '<br>')
            };
            addMessage(userMsg, true);
            
            // Mostrar indicador de carga inmediatamente
            addLoadingIndicator();
            
            // Limpiar input
            messageInput.value = '';
            
            const formData = new FormData();
            formData.append('ajax_message', userMessage);
            
            try {
                const response = await fetch(window.location.href, {
                    method: 'POST',
                    body: formData
                });
                
                if (!response.ok) {
                    const text = await response.text();
                    console.error('Error HTTP:', response.status, 'Contenido:', text);
                    throw new Error(`Error HTTP: ${response.status}`);
                }
                
                const contentType = response.headers.get('content-type');
                if (!contentType || !contentType.includes('application/json')) {
                    const text = await response.text();
                    console.error('Respuesta no es JSON:', text);
                    throw new Error('Respuesta no es JSON válida');
                }
                
                const data = await response.json();
                
                if (data.status === 'success') {
                    // Iniciar polling para la respuesta del bot
                    checkForBotResponse();
                } else {
                    throw new Error(data.message || 'Error en la respuesta del servidor');
                }
            } catch (error) {
                console.error('Error:', error);
                removeLoadingIndicator();
                const errorMsg = {
                    text: 'Error al enviar el mensaje: ' + error.message,
                    time: new Date().toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'}),
                    html: 'Error al enviar el mensaje: ' + error.message
                };
                addMessage(errorMsg, false);
            } finally {
                messageInput.disabled = false;
                sendButton.disabled = false;
                messageInput.focus();
            }
        });

        async function checkForBotResponse() {
            try {
                const response = await fetch(`${window.location.href}?check_response=1`, {
                    headers: {
                        'Accept': 'application/json'
                    }
                });
                
                if (!response.ok) {
                    const text = await response.text();
                    console.error('Error HTTP:', response.status, 'Contenido:', text);
                    throw new Error(`Error HTTP: ${response.status}`);
                }
                
                const contentType = response.headers.get('content-type');
                if (!contentType || !contentType.includes('application/json')) {
                    const text = await response.text();
                    console.error('Respuesta no es JSON:', text);
                    throw new Error('Respuesta no es JSON válida');
                }
                
                const data = await response.json();
                
                if (data.status === 'complete') {
                    removeLoadingIndicator();
                    addMessage(data.botMessage, false);
                } else {
                    setTimeout(checkForBotResponse, 500);
                }
            } catch (error) {
                console.error('Error al verificar respuesta:', error);
                removeLoadingIndicator();
                const errorMsg = {
                    text: 'Error al obtener la respuesta: ' + error.message,
                    time: new Date().toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'}),
                    html: 'Error al obtener la respuesta: ' + error.message
                };
                addMessage(errorMsg, false);
            }
        }

        messageInput.addEventListener('keydown', function(e) {
            if (e.key === 'Enter' && !e.shiftKey) {
                e.preventDefault();
                if (messageInput.value.trim()) {
                    messageForm.dispatchEvent(new Event('submit'));
                }
            }
        });

        document.addEventListener('DOMContentLoaded', function() {
            scrollToBottom();
            const messages = document.querySelectorAll('.message');
            messages.forEach((message, index) => {
                setTimeout(() => {
                    message.style.opacity = '1';
                    message.style.transform = 'translateY(0)';
                }, index * 100);
            });
            messageInput.focus();
        });

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