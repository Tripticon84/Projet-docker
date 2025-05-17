<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/frontOffice/employee/includes/head.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/frontOffice/employee/includes/header.php';
?>

<div class="container mt-4">
    <div class="row">
        <div class="col-md-12">
            <h2>Chatbot Assistance</h2>
            <p class="text-muted">Choisissez parmi les questions disponibles et obtenez rapidement des réponses</p>
        </div>
    </div>

    <div class="row mt-4">
        <div class="col-md-8 mx-auto">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">BusinessCare - Chatbot Assistant</h5>
                </div>
                <div class="card-body">
                    <div id="chat-window" class="bg-light p-3 rounded mb-3" style="height: 300px; overflow-y: auto;">
                        <div class="text-center">
                            <span class="badge bg-secondary">Démarrer une conversation</span>
                        </div>
                    </div>
                    
                    <div id="chat-history" class="d-flex mb-3 flex-wrap" style="gap: 5px;">
                        <!-- Historique de navigation ici -->
                    </div>

                    <div class="card">
                        <div class="card-header bg-light">
                            <h6 class="mb-0">Questions disponibles</h6>
                        </div>
                        <div id="chat-options" class="list-group list-group-flush">
                            <!-- Questions disponibles ici -->
                            <div class="text-center p-3">
                                <div class="spinner-border text-primary" role="status">
                                    <span class="visually-hidden">Loading...</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-footer">
                    <button id="reset-chat" class="btn btn-outline-secondary">Recommencer</button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Variables pour suivre l'état du chatbot
    let currentQuestionId = null;
    let chatHistory = [];

    // Chargement initial des questions
    loadInitialQuestions();

    // Gérer le bouton "Recommencer"
    document.getElementById('reset-chat').addEventListener('click', function() {
        resetChat();
    });

    // Fonction pour charger les questions initiales (sans parent)
    async function loadInitialQuestions() {
        const chatOptions = document.getElementById('chat-options');
        chatOptions.innerHTML = '<div class="text-center p-3"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div></div>';
        
        try {
            const response = await fetch('/api/chatbot/getAll.php');
            const data = await response.json();
            
            // Filtrer les questions sans parent_id (questions initiales)
            const initialQuestions = data.filter(q => !q.parent_id);
            
            // Afficher les questions initiales
            displayQuestionOptions(initialQuestions);
            
            // Ajouter un message d'accueil
            addChatMessage('BusinessCare', 'Bonjour! Comment puis-je vous aider aujourd\'hui? Veuillez choisir une question ci-dessous.');
            
        } catch (error) {
            console.error('Erreur lors du chargement des questions:', error);
            chatOptions.innerHTML = '<div class="alert alert-danger">Erreur de chargement des questions. Veuillez réessayer plus tard.</div>';
        }
    }

    // Fonction pour afficher les options de questions
    function displayQuestionOptions(questions) {
        const chatOptions = document.getElementById('chat-options');
        chatOptions.innerHTML = '';
        
        if (questions.length === 0) {
            chatOptions.innerHTML = '<div class="list-group-item text-center">Il n\'y a plus de questions disponibles sur ce sujet.</div>';
            return;
        }
        
        questions.forEach(question => {
            const button = document.createElement('button');
            button.type = 'button';
            button.className = 'list-group-item list-group-item-action';
            button.textContent = question.question;
            button.dataset.questionId = question.chatbot_id;
            
            button.addEventListener('click', function() {
                handleQuestionClick(question);
            });
            
            chatOptions.appendChild(button);
        });
    }

    // Fonction pour gérer le clic sur une question
    async function handleQuestionClick(question) {
        // Ajouter la question à l'historique
        addToHistory(question);
        
        // Afficher la question de l'utilisateur
        addChatMessage('Vous', question.question, 'user');
        
        // Afficher la réponse du chatbot
        addChatMessage('BusinessCare', question.answer);
        
        // Définir la question actuelle
        currentQuestionId = question.chatbot_id;
        
        // Charger les sous-questions si elles existent
        loadSubQuestions(question.chatbot_id);
    }

    // Fonction pour charger les sous-questions
    async function loadSubQuestions(parentId) {
        const chatOptions = document.getElementById('chat-options');
        chatOptions.innerHTML = '<div class="text-center p-3"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div></div>';
        
        try {
            const response = await fetch(`/api/chatbot/getSubQuestion.php?parent_id=${parentId}`);
            
            // Si le statut HTTP est 404, cela signifie qu'il n'y a pas de sous-questions
            if (response.status === 404) {
                chatOptions.innerHTML = '<div class="list-group-item text-center">Il n\'y a plus d\'informations disponibles sur ce sujet.</div>';
                return;
            }
            
            const data = await response.json();
            displayQuestionOptions(data);
            
        } catch (error) {
            console.error('Erreur lors du chargement des sous-questions:', error);
            
            if (error.message.includes('404')) {
                chatOptions.innerHTML = '<div class="list-group-item text-center">Il n\'y a plus d\'informations disponibles sur ce sujet.</div>';
            } else {
                chatOptions.innerHTML = '<div class="alert alert-danger">Erreur de chargement des questions. Veuillez réessayer plus tard.</div>';
            }
        }
    }

    // Fonction pour ajouter un message au chat
    function addChatMessage(sender, message, type = 'bot') {
        const chatWindow = document.getElementById('chat-window');
        const messageDiv = document.createElement('div');
        messageDiv.className = `chat-message mb-2 ${type === 'user' ? 'text-end' : ''}`;
        
        const badge = document.createElement('span');
        badge.className = `badge ${type === 'user' ? 'bg-primary' : 'bg-secondary'}`;
        badge.textContent = sender;
        
        const messageContent = document.createElement('div');
        messageContent.className = `${type === 'user' ? 'text-end' : ''}`;
        messageContent.innerHTML = `<p class="mb-0">${message}</p>`;
        
        messageDiv.appendChild(badge);
        messageDiv.appendChild(messageContent);
        chatWindow.appendChild(messageDiv);
        
        // Scroll to bottom
        chatWindow.scrollTop = chatWindow.scrollHeight;
    }

    // Fonction pour ajouter à l'historique
    function addToHistory(question) {
        chatHistory.push(question);
        updateHistoryUI();
    }

    // Fonction pour mettre à jour l'UI de l'historique
    function updateHistoryUI() {
        const historyContainer = document.getElementById('chat-history');
        historyContainer.innerHTML = '';
        
        // Limiter l'historique aux 3 derniers éléments pour l'interface
        const displayHistory = chatHistory.slice(-3);
        
        displayHistory.forEach((item, index) => {
            const historyBadge = document.createElement('span');
            historyBadge.className = 'badge bg-info text-dark';
            historyBadge.style.cursor = 'pointer';
            historyBadge.textContent = item.question.substring(0, 15) + (item.question.length > 15 ? '...' : '');
            
            historyBadge.addEventListener('click', function() {
                navigateToHistory(index);
            });
            
            historyContainer.appendChild(historyBadge);
        });
    }

    // Fonction pour naviguer dans l'historique
    function navigateToHistory(index) {
        // Récupérer l'index réel basé sur l'historique complet
        const historyIndex = chatHistory.length - 3 + index;
        
        if (historyIndex >= 0 && historyIndex < chatHistory.length) {
            const question = chatHistory[historyIndex];
            
            // Réinitialiser l'historique jusqu'à ce point
            chatHistory = chatHistory.slice(0, historyIndex + 1);
            updateHistoryUI();
            
            // Simuler un clic sur cette question
            handleQuestionClick(question);
        }
    }

    // Fonction pour réinitialiser le chat
    function resetChat() {
        // Vider l'historique
        chatHistory = [];
        currentQuestionId = null;
        
        // Vider l'UI de l'historique
        document.getElementById('chat-history').innerHTML = '';
        
        // Vider la fenêtre de chat sauf le message d'accueil
        const chatWindow = document.getElementById('chat-window');
        chatWindow.innerHTML = '<div class="text-center"><span class="badge bg-secondary">Démarrer une conversation</span></div>';
        
        // Recharger les questions initiales
        loadInitialQuestions();
    }
});
</script>

<style>
.chat-message {
    margin-bottom: 10px;
}
.chat-message .badge {
    margin-bottom: 3px;
}
.list-group-item {
    cursor: pointer;
}
</style>

<?php require_once $_SERVER['DOCUMENT_ROOT'] . '/frontOffice/employee/includes/footer.php'; ?>