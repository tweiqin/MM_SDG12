<footer class="bg-dark text-white py-4">
    <div class="container">
        <div class="row">
            <div class="col-md-4 mb-3">
                <h5>Contact Us</h5>
                <p>Email: <a href="mailto:qinqintay03@gmail.com"
                        class="text-warning text-decoration-none">qinqintay03@gmail.com</a></p>
                <p>Phone: <a href="https://api.whatsapp.com/send?phone=60166896283"
                        class="text-warning text-decoration-none">+60 16-689 6283</a></p>
                <p>Address: Jalan Teknologi 5, Technology Park Malaysia, 57000 Bukit Jalil, Kuala Lumpur, Malaysia.</p>
            </div>

            <div class="col-md-4 mb-3">
                <h5>Quick Links</h5>
                <ul class="list-unstyled">
                    <li><a href="../pages/index.php" class="text-white text-decoration-none">Home</a></li>
                    <li><a href="../pages/faq.php" class="text-white text-decoration-none">FAQs</a></li>
                    <li><a href="#" class="text-white text-decoration-none">Privacy Policy</a></li>
                    <li><a href="#" class="text-white text-decoration-none">Contact</a></li>
                </ul>
            </div>

            <div class="col-md-4 mb-3">
                <h5>Follow Us</h5>
                <a href="#" class="text-white me-3">
                    <i class="fab fa-facebook"></i>
                </a>
                <a href="#" class="text-white me-3">
                    <i class="fab fa-twitter"></i>
                </a>
                <a href="#" class="text-white me-3">
                    <i class="fab fa-instagram"></i>
                </a>
                <a href="https://www.linkedin.com/in/wei-qin-3a8b29188/" class="text-white">
                    <i class="fab fa-linkedin"></i>
                </a>
            </div>
        </div>

        <hr>
        <div class="text-center mt-3">
            <p class="mb-0">&copy; <?php echo date('Y'); ?> Makan Mystery. All rights reserved.</p>
        </div>
</footer>

<div id="chatbot-icon-container" style="position: fixed; bottom: 30px; right: 30px; z-index: 1000;">
    <button class="btn btn-lg btn-primary shadow-lg" id="open-chat-btn" data-bs-toggle="modal"
        data-bs-target="#chatbotModal"
        style="border-radius: 50%; width: 60px; height: 60px; background-color: #00a650; border: none;">
        <i class="fas fa-robot fa-lg text-white"></i>
    </button>
</div>
<div class="modal fade" id="chatbotModal" tabindex="-1" aria-labelledby="chatbotModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-sm chatbot-bottom-right">
        <div class="modal-content">
            <div class="modal-header bg-dark text-white" style="background-color: #274081 !important;">
                <h5 class="modal-title" id="chatbotModalLabel">MakanMystery Support</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                    aria-label="Close"></button>
            </div>
            <div class="modal-body" id="chat-messages" style="height: 300px; overflow-y: auto;">
                <div class="alert alert-info p-2 mb-2">Hello! I'm your AI assistant. How can I help you save
                    food
                    today?</div>
            </div>
            <div class="modal-footer">
                <input type="text" id="chat-input" class="form-control" placeholder="Type your question..." autofocus>
                <button type="button" class="btn btn-primary" id="send-chat-btn"
                    style="background-color: #00a650; border-color: #00a650;">Send</button>
            </div>
        </div>
    </div>
</div>

<style>
    .chatbot-bottom-right {
        position: fixed !important;
        bottom: 20px;
        right: 30px;
        margin: 0;
        transform: none !important;
    }

    #chatbotModal .modal-dialog {
        pointer-events: auto;
    }
</style>


<?php
// Ensure API key is available
// Ensure API key is available
if (!defined('GEMINI_API_KEY')) {
    // Try to load it if not defined (path depends on where footer is included from)
    // Assuming standard structure pages/ -> ../config/
    if (file_exists(__DIR__ . '/../config/api-keys.php')) {
        include_once __DIR__ . '/../config/api-keys.php';
    } elseif (file_exists(__DIR__ . '/../../config/api-keys.php')) {
        // Fallback if included from deeper level
        include_once __DIR__ . '/../../config/api-keys.php';
    }
}
// We do NOT echo the key to the frontend anymore for security.
// The proxy script handles the key. Use a flag to indicate availability if needed.
$chatbotAvailable = defined('GEMINI_API_KEY') && !empty(GEMINI_API_KEY);
?>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const chatMessages = document.getElementById('chat-messages');
        const chatInput = document.getElementById('chat-input');
        const sendChatBtn = document.getElementById('send-chat-btn');
        const chatModal = document.getElementById('chatbotModal');

        // OPENROUTER API CONFIG
        // API Key is now handled securely on the server side (api/chat-handler.php)
        const CHATBOT_AVAILABLE = <?php echo $chatbotAvailable ? 'true' : 'false'; ?>;

        // Function to append message to the chat window
        function appendMessage(sender, text, isError = false) {
            const msgDiv = document.createElement('div');

            let baseClass = 'p-2 mb-2 rounded shadow-sm small ';

            if (sender === 'user') {
                msgDiv.className = baseClass + ' text-end text-dark';
                msgDiv.style.backgroundColor = '#E9F2F9';
            } else {
                msgDiv.className = baseClass + ' text-start text-white';
                msgDiv.style.backgroundColor = isError ? '#dc3545' : '#00a650';
            }

            // Simple markdown-like Bold parsing
            let formattedText = text.replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>');

            msgDiv.innerHTML = `<strong>${sender === 'user' ? 'You' : 'MakanMystery Bot'}</strong>: ${formattedText}`;
            chatMessages.appendChild(msgDiv);
            chatMessages.scrollTop = chatMessages.scrollHeight;
        }

        // Function to send message to the API
        async function sendMessage() {
            const message = chatInput.value.trim();
            if (!message) return;

            if (!CHATBOT_AVAILABLE) {
                appendMessage('bot', 'System Error: API Key missing in server configuration.', true);
                return;
            }

            appendMessage('user', message);
            chatInput.value = '';
            sendChatBtn.disabled = true;

            // Show typing indicator
            const loadingDiv = document.createElement('div');
            loadingDiv.id = 'loading-indicator';
            loadingDiv.className = 'p-2 mb-2 rounded shadow-sm small text-success text-start';
            loadingDiv.innerHTML = '<strong>MakanMystery Bot</strong>: Typing...';
            chatMessages.appendChild(loadingDiv);
            chatMessages.scrollTop = chatMessages.scrollHeight;

            try {
                // Call Local Proxy Script (Secure)
                // Determine path based on current location
                const apiPath = window.location.pathname.includes('/pages/') ? '../api/chat-handler.php' : 'api/chat-handler.php';

                const formData = new FormData();
                formData.append('message', message);

                const response = await fetch(apiPath, {
                    method: "POST",
                    body: formData
                });

                loadingDiv.remove();

                if (!response.ok) {
                    const errData = await response.json().catch(() => ({}));
                    console.error("API Error:", errData);
                    const errMsg = errData.reply ? errData.reply : (errData.error || response.statusText);
                    appendMessage('bot', `Error: ${errMsg}`, true);
                    return;
                }

                const data = await response.json();
                const reply = data.reply || "Sorry, I couldn't understand that.";

                appendMessage('bot', reply);

            } catch (error) {
                loadingDiv.remove();
                console.error("Network Error:", error);
                appendMessage('bot', 'Network error occurred. Please check your connection.', true);
            } finally {
                sendChatBtn.disabled = false;
            }
        }

        // Event listeners
        sendChatBtn.addEventListener('click', sendMessage);
        chatInput.addEventListener('keypress', function (e) {
            if (e.key === 'Enter') {
                sendMessage();
            }
        });

        // Focus input when modal opens
        chatModal.addEventListener('shown.bs.modal', function () {
            chatInput.focus();
        });
    });
</script>