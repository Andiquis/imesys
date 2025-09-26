<!--chatbot con gemini-->

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chat con Gemini</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f5f5f5;
        }
        .chat-container {
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            padding: 20px;
            margin-bottom: 20px;
        }
        .chat-messages {
            height: 400px;
            overflow-y: auto;
            margin-bottom: 20px;
            padding: 10px;
            border: 1px solid #e0e0e0;
            border-radius: 5px;
        }
        .message {
            margin-bottom: 15px;
            padding: 10px;
            border-radius: 5px;
            max-width: 80%;
        }
        .user-message {
            background-color: #e3f2fd;
            margin-left: auto;
            text-align: right;
        }
        .bot-message {
            background-color: #f1f1f1;
        }
        .message-form {
            display: flex;
            gap: 10px;
        }
        input {
            flex-grow: 1;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 16px;
        }
        button {
            background-color: #4285f4;
            color: white;
            border: none;
            border-radius: 5px;
            padding: 10px 20px;
            font-size: 16px;
            cursor: pointer;
            transition: background-color 0.3s;
        }
        button:hover {
            background-color: #356ac3;
        }
        .loading {
            text-align: center;
            margin: 10px 0;
            font-style: italic;
            color: #666;
        }
    </style>
</head>
<body>
    <div class="chat-container">
        <h1>Chat con Gemini</h1>
        <div class="chat-messages" id="chat-messages">
            <div class="message bot-message">
                Hola, soy un asistente impulsado por Gemini. ¿En qué puedo ayudarte hoy?
            </div>
        </div>
        <div id="loading-indicator" class="loading" style="display: none;">
            Pensando...
        </div>
        <form id="message-form" class="message-form">
            <input 
                type="text" 
                id="user-input" 
                placeholder="Escribe tu mensaje aquí..." 
                required
                autocomplete="off"
            >
            <button type="submit">Enviar</button>
        </form>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const messageForm = document.getElementById('message-form');
            const userInput = document.getElementById('user-input');
            const chatMessages = document.getElementById('chat-messages');
            const loadingIndicator = document.getElementById('loading-indicator');

            messageForm.addEventListener('submit', async (e) => {
                e.preventDefault();
                
                const message = userInput.value.trim();
                if (!message) return;

                // Agregar mensaje del usuario al chat
                addMessage(message, 'user');
                userInput.value = '';
                
                // Mostrar indicador de carga
                loadingIndicator.style.display = 'block';
                
                try {
                    // Realizar petición a la API
                    const response = await fetch('http://localhost:5000/api/chatbot', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify({ message })
                    });

                    if (!response.ok) {
                        throw new Error('Error en la respuesta del servidor');
                    }

                    const data = await response.json();
                    
                    // Agregar respuesta del bot al chat
                    addMessage(data.response, 'bot');
                } catch (error) {
                    console.error('Error:', error);
                    addMessage('Lo siento, ha ocurrido un error al procesar tu solicitud.', 'bot');
                } finally {
                    // Ocultar indicador de carga
                    loadingIndicator.style.display = 'none';
                    
                    // Scroll al último mensaje
                    chatMessages.scrollTop = chatMessages.scrollHeight;
                }
            });

            function addMessage(content, sender) {
                const messageDiv = document.createElement('div');
                messageDiv.classList.add('message');
                messageDiv.classList.add(sender === 'user' ? 'user-message' : 'bot-message');
                
                // Convertir saltos de línea a <br>
                const formattedContent = content.replace(/\n/g, '<br>');
                messageDiv.innerHTML = formattedContent;
                
                chatMessages.appendChild(messageDiv);
                chatMessages.scrollTop = chatMessages.scrollHeight;
            }
        });
    </script>
</body>
</html>