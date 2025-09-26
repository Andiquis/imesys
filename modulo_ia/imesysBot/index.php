<?php
session_start();

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
        
        if (curl_error($ch)) {
            curl_close($ch);
            throw new Exception('Error en la solicitud: ' . curl_error($ch));
        }
        
        curl_close($ch);
        
        if ($httpCode >= 200 && $httpCode < 300) {
            $responseData = json_decode($response, true);
            if ($this->currentModel === 'fast1') {
                return $responseData['response'] ?? "No se recibió respuesta válida";
            } else {
                return $responseData['response'] ?? "No se recibió respuesta válida";
            }
        } else {
            throw new Exception('Error en la API: ' . $httpCode);
        }
    }
}

// Inicializar variables de sesión
$jsonFile = 'chat-data.json';
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
        
        header('Content-Type: application/json');
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
    }
    exit;
}

// Endpoint para obtener la respuesta del bot
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['check_response'])) {
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
    
    header('Content-Type: application/json');
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
    <title>IMESYS Asistente Virtual</title>
    <link rel="stylesheet" href="styles.scss">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

</head>
<body>
    <div class="chat-container">
        <div class="chat-header">
            <div class="header-content">
                <i class="fas fa-robot header-icon"></i>
                <h2>IMESYS Asistente Virtual</h2>
                
                <div class="model-selector">
                    <form method="post" id="modelForm">
                        <input type="hidden" name="change_model" value="1">
                        <select name="model" id="modelSelect" onchange="this.form.submit();">
                            <?php foreach ($chatService->getAvailableModels() as $model): ?>
                                <option value="<?php echo $model; ?>" <?php echo ($chatService->getCurrentModel() === $model) ? 'selected' : ''; ?>>
                                    <?php echo ucfirst($model); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </form>
                </div>
                
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
        
        <div class="chat-body" id="chatBody">
            <div class="chat-messages" id="chatMessages">
                <?php foreach ($_SESSION['messages'] as $message): ?>
                    <div class="message-container">
                        <div class="message <?php echo $message->isSent ? 'user-message' : 'bot-message'; ?>">
                            <div class="message-content">
                                <?php echo $message->isSent ? nl2br(htmlspecialchars($message->text)) : parseMarkdown(htmlspecialchars($message->text)); ?>
                            </div>
                            <div class="message-time"><?php echo htmlspecialchars($message->time); ?></div>
                        </div>
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
                <button type="submit" id="sendButton" class="send-button">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M2.01 21L23 12 2.01 3 2 10l15 2-15 2z"></path>
                    </svg>
                </button>
            </form>
        </div>
    </div>

    <script>
        const chatMessages = document.getElementById('chatMessages');
        const messageInput = document.getElementById('messageInput');
        const messageForm = document.getElementById('messageForm');
        const sendButton = document.getElementById('sendButton');
        const chatBody = document.getElementById('chatBody');

        function scrollToBottom() {
            chatBody.scrollTop = chatBody.scrollHeight;
        }

        function addMessage(message, isUser) {
            const msgContainer = document.createElement('div');
            msgContainer.className = 'message-container';
            
            const msgDiv = document.createElement('div');
            msgDiv.className = `message ${isUser ? 'user-message' : 'bot-message'}`;
            
            const contentDiv = document.createElement('div');
            contentDiv.className = 'message-content';
            contentDiv.innerHTML = message.html || message.text.replace(/\n/g, '<br>');
            
            const timeDiv = document.createElement('div');
            timeDiv.className = 'message-time';
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
            spinner.className = 'spinner';
            
            const span = document.createElement('span');
            span.textContent = 'Generando respuesta...';
            
            loadingDiv.appendChild(spinner);
            loadingDiv.appendChild(span);
            
            chatMessages.appendChild(loadingDiv);
            setTimeout(() => {
                loadingDiv.className = 'loading-indicator visible';
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
                
                const data = await response.json();
                
                if (data.status === 'success') {
                    // Iniciar polling para la respuesta del bot
                    checkForBotResponse();
                } else {
                    throw new Error('Error en la respuesta del servidor');
                }
            } catch (error) {
                console.error('Error:', error);
                removeLoadingIndicator();
                const errorMsg = {
                    text: 'Error al enviar el mensaje. Por favor, inténtalo de nuevo.',
                    time: new Date().toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'}),
                    html: 'Error al enviar el mensaje. Por favor, inténtalo de nuevo.'
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
                const response = await fetch(`${window.location.href}?check_response=1`);
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
                    text: 'Error al obtener la respuesta. Por favor, inténtalo de nuevo.',
                    time: new Date().toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'}),
                    html: 'Error al obtener la respuesta. Por favor, inténtalo de nuevo.'
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