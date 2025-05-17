let messagePollingInterval;
let currentChatId = null;
let lastMessageTimestamp = 0;

// Remplacer la fonction getToken par une version améliorée
function getAuthToken() {
    return sessionStorage.getItem('token') || '';
}

async function switchChat(salonId) {
    currentChatId = salonId;
    lastMessageTimestamp = 0;
    
    // Mettre à jour l'UI
    const chatHeader = document.getElementById('chat-header');
    const selectedChat = document.querySelector(`[data-salon-id="${salonId}"]`);
    chatHeader.innerHTML = `<h5 class="mb-0">${selectedChat.querySelector('h6').textContent}</h5>`;
    
    // Afficher le formulaire de message
    document.getElementById('message-form').classList.remove('d-none');
    
    // Charger les messages initiaux
    await loadMessages();
    
    // Démarrer le polling des messages
    if (messagePollingInterval) {
        clearInterval(messagePollingInterval);
    }
    messagePollingInterval = setInterval(loadNewMessages, 3000);
}

async function loadMessages() {
    try {
        const response = await fetch(`/api/chat/getMessages.php?salon_id=${currentChatId}`);
        if (!response.ok) throw new Error('Erreur de communication avec le serveur');
        
        // La réponse est un tableau direct, pas un objet avec success
        const messages = await response.json();
        
        const chatMessages = document.getElementById('chat-messages');
        chatMessages.innerHTML = '';
        
        if (Array.isArray(messages)) {
            messages.forEach(message => {
                const messageElement = createMessageElement(message);
                chatMessages.appendChild(messageElement);
            });
            
            chatMessages.scrollTop = chatMessages.scrollHeight;
            
            if (messages.length > 0) {
                lastMessageTimestamp = Math.max(...messages.map(m => m.timestamp));
            }
        } else {
            console.warn('Format de réponse inattendu:', messages);
        }
    } catch (error) {
        console.error('Erreur:', error);
        throw new Error('Erreur lors du chargement des messages');
    }
}

async function loadNewMessages() {
    if (!currentChatId) return;
    
    try {
        const response = await fetch(`/api/chat/getLatestMessages.php?salon_id=${currentChatId}&since=${lastMessageTimestamp}`);
        if (!response.ok) throw new Error('Erreur de communication avec le serveur');
        
        const messages = await response.json();
        
        // Vérifier si nous avons de nouveaux messages
        if (Array.isArray(messages) && messages.length > 0) {
            const chatMessages = document.getElementById('chat-messages');
            
            // Filtrer pour n'afficher que les messages que nous n'avons pas déjà
            const newMessages = messages.filter(message => {
                // Convertir en string pour comparer de façon fiable
                const messageId = `${message.collaborateur_id}-${message.timestamp}`;
                // Vérifier si ce message existe déjà dans le DOM
                const exists = document.querySelector(`[data-message-id="${messageId}"]`);
                return !exists;
            });
            
            // Ajouter uniquement les nouveaux messages
            newMessages.forEach(message => {
                const messageElement = createMessageElement(message);
                chatMessages.appendChild(messageElement);
            });
            
            // Mettre à jour le timestamp seulement s'il y a de nouveaux messages
            if (newMessages.length > 0) {
                lastMessageTimestamp = Math.max(...messages.map(m => m.timestamp));
                chatMessages.scrollTop = chatMessages.scrollHeight;
            }
        }
    } catch (error) {
        console.error('Erreur lors du chargement des nouveaux messages:', error);
    }
}

function createMessageElement(message) {
    const isOwnMessage = message.collaborateur_id === collaborateurId;
    const div = document.createElement('div');
    div.className = `message d-flex ${isOwnMessage ? 'justify-content-end' : 'justify-content-start'}`;
    
    // Ajouter un attribut data pour identifier ce message de façon unique
    const messageId = `${message.collaborateur_id}-${message.timestamp}`;
    div.setAttribute('data-message-id', messageId);
    
    div.innerHTML = `
        <div class="message-bubble ${isOwnMessage ? 'message-own' : 'message-other'}">
            <div class="message-header">
                <small class="fw-bold">${escapeHtml(message.username || 'Utilisateur inconnu')}</small>
            </div>
            <div class="message-content">
                ${escapeHtml(message.message)}
            </div>
            <div class="message-footer">
                <small class="opacity-75">
                    ${new Date(message.timestamp * 1000).toLocaleTimeString()}
                </small>
            </div>
        </div>
    `;
    
    return div;
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

// Gestion de l'envoi des messages
document.getElementById('message-form').addEventListener('submit', async (e) => {
    e.preventDefault();
    
    const input = document.getElementById('message-input');
    const message = input.value.trim();
    
    if (!message || !currentChatId) return;
    
    try {
        const response = await fetch('/api/chat/sendMessage.php', {
            method: 'POST',  // On utilise directement POST
            headers: {
                'Content-Type': 'application/json'
                // Suppression du token car il n'est peut-être pas nécessaire
            },
            body: JSON.stringify({
                salon_id: currentChatId,
                collaborateur_id: collaborateurId,
                message: message
            })
        });
        
        if (!response.ok) {
            const errorData = await response.json();
            throw new Error(errorData.message || `Erreur ${response.status}: ${response.statusText}`);
        }
        
        input.value = '';
        await loadMessages();
    } catch (error) {
        console.error('Erreur:', error);
        showError('Erreur lors de l\'envoi du message: ' + error.message);
    }
});

function showError(message) {
    const alertDiv = document.createElement('div');
    alertDiv.className = 'alert alert-danger alert-dismissible fade show';
    alertDiv.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    
    document.querySelector('.container').prepend(alertDiv);
    
    setTimeout(() => {
        alertDiv.remove();
    }, 5000);
}

// Initialiser le polling des messages
function startMessagePolling() {
    // Arrêter l'intervalle existant s'il y en a un
    if (messagePollingInterval) {
        clearInterval(messagePollingInterval);
    }
    
    // Démarrer un nouvel intervalle
    messagePollingInterval = setInterval(loadNewMessages, 3000);
}
