<?php
$title = "Communauté - Espace Salarié";
require_once $_SERVER['DOCUMENT_ROOT'] . '/frontOffice/employee/includes/head.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/frontOffice/employee/includes/header.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/api/dao/chat.php';

// Vérification de la session
if (!isset($_SESSION['collaborateur_id'])) {
    header('Location: /login.php');
    exit;
}

// Récupérer tous les salons
$allChats = getAllChats();

// Récupérer les salons auxquels l'utilisateur est déjà inscrit
$userChats = getUserChats($_SESSION['collaborateur_id']);

// Transformer les salons de l'utilisateur en tableau indexé par salon_id pour faciliter les recherches
$userChatsById = [];
$userChatAdmin = [];
$isUserAdmin = false; // Variable pour déterminer si l'utilisateur est administrateur d'au moins un salon

foreach ($userChats as $chat) {
    $userChatsById[$chat['salon_id']] = $chat;
    
    // Vérifier si l'utilisateur est admin du salon
    $isAdmin = isUserChatAdmin($chat['salon_id'], $_SESSION['collaborateur_id']);
    $userChatAdmin[$chat['salon_id']] = $isAdmin;
    
    // Si l'utilisateur est admin d'au moins un salon, on le considère comme administrateur
    if ($isAdmin) {
        $isUserAdmin = true;
    }
}

// Vérifier si l'utilisateur est un admin système (si nécessaire)
// Cette partie dépendrait de comment vous identifiez un admin système
// Par exemple, on pourrait le faire en vérifiant un rôle spécifique dans la table collaborateur
// Pour cet exemple, on utilise uniquement les admins de salon
?>
<style>
    .message {
        margin: 10px 0;
    }
    .message-bubble {
        max-width: 70%;
        padding: 10px;
        border-radius: 15px;
    }
    .message-own {
        background-color: #007bff;
        color: white;
        margin-left: auto;
    }
    .message-other {
        background-color: #f0f2f5;
        margin-right: auto;
    }
    #chat-messages {
        display: flex;
        flex-direction: column;
        gap: 10px;
        padding: 15px;
    }
    .admin-badge {
        font-size: 0.8rem;
        padding: 0.2rem 0.5rem;
    }
    .salon-actions {
        display: flex;
        gap: 5px;
        margin-top: 10px;
    }
    .salon-actions .btn {
        flex-grow: 1;
    }
</style>

<div class="container mt-4">
    <div class="row mb-4">
        <div class="col-12">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <h1 class="card-title">Communauté</h1>
                    <p class="card-text">Rejoignez les différents salons pour échanger avec vos collègues.</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Vue d'ensemble des salons -->
    <div class="row" id="salons-overview">
        <!-- Section pour afficher mes salons -->
        <div class="col-12 mb-4">
            <h3>Mes salons</h3>
            <div class="row">
                <?php if (count($userChats) > 0): ?>
                    <?php foreach ($userChats as $chat): ?>
                        <div class="col-md-4 mb-4">
                            <div class="card h-100 shadow-sm">
                                <div class="card-header bg-light d-flex justify-content-between align-items-center">
                                    <h5 class="card-title mb-0"><?= htmlspecialchars($chat['nom']) ?></h5>
                                    <?php if ($userChatAdmin[$chat['salon_id']]): ?>
                                        <span class="badge bg-warning admin-badge">Admin</span>
                                    <?php endif; ?>
                                </div>
                                <div class="card-body">
                                    <p class="card-text text-muted"><?= htmlspecialchars($chat['description']) ?></p>
                                    <div class="salon-actions">
                                        <button class="btn btn-success" onclick="joinChat(<?= $chat['salon_id'] ?>, '<?= htmlspecialchars($chat['nom']) ?>')">
                                            <i class="fas fa-comments me-2"></i>Discuter
                                        </button>
                                        <button class="btn btn-outline-danger" onclick="quitSalon(<?= $chat['salon_id'] ?>, '<?= htmlspecialchars($chat['nom']) ?>')">
                                            <i class="fas fa-sign-out-alt me-2"></i>Quitter
                                        </button>
                                    </div>
                                    <?php if ($userChatAdmin[$chat['salon_id']]): ?>
                                        <div class="mt-2">
                                            <button class="btn btn-outline-danger w-100" onclick="deleteSalon(<?= $chat['salon_id'] ?>, '<?= htmlspecialchars($chat['nom']) ?>')">
                                                <i class="fas fa-trash-alt me-2"></i>Supprimer le salon
                                            </button>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="col-12">
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i>Vous n'avez pas encore rejoint de salon.
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Section pour afficher les autres salons -->
        <div class="col-12">
            <h3>Autres salons disponibles</h3>
            <div class="row">
                <?php 
                $hasSalons = false;
                foreach ($allChats as $chat): 
                    if (!isset($userChatsById[$chat['salon_id']])):
                        $hasSalons = true; 
                ?>
                    <div class="col-md-4 mb-4">
                        <div class="card h-100 shadow-sm">
                            <div class="card-body">
                                <h5 class="card-title"><?= htmlspecialchars($chat['nom']) ?></h5>
                                <p class="card-text text-muted"><?= htmlspecialchars($chat['description']) ?></p>
                                <button class="btn btn-primary w-100" 
                                        onclick="joinSalon(<?= $chat['salon_id'] ?>, '<?= htmlspecialchars($chat['nom']) ?>')">
                                    <i class="fas fa-sign-in-alt me-2"></i>Rejoindre le salon
                                </button>
                            </div>
                        </div>
                    </div>
                <?php 
                    endif;
                endforeach; 
                
                if (!$hasSalons): 
                ?>
                    <div class="col-12">
                        <div class="alert alert-info">
                            <i class="fas fa-check-circle me-2"></i>Vous avez déjà rejoint tous les salons disponibles.
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Bouton pour créer un nouveau salon (visible uniquement pour les admins) -->
        <?php if ($isUserAdmin): ?>
        <div class="col-12 mt-4 mb-5">
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createSalonModal">
                <i class="fas fa-plus me-2"></i>Créer un nouveau salon
            </button>
        </div>
        <?php endif; ?>
    </div>

    <!-- Zone de chat (initialement cachée) -->
    <div class="row d-none" id="chat-area">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header bg-light d-flex justify-content-between align-items-center">
                    <h5 class="mb-0" id="chat-title">Salon</h5>
                    <button class="btn btn-outline-secondary btn-sm" onclick="leaveChatRoom()">
                        <i class="fas fa-arrow-left me-2"></i>Retour aux salons
                    </button>
                </div>
                <div class="card-body chat-messages" id="chat-messages" style="height: 400px; overflow-y: auto;">
                    <div class="text-center text-muted">
                        <i class="fas fa-comments fa-3x mb-3"></i>
                        <p>Les messages s'afficheront ici</p>
                    </div>
                </div>
                <div class="card-footer bg-light">
                    <form id="message-form" class="mb-0">
                        <div class="input-group">
                            <input type="text" class="form-control" id="message-input" 
                                   placeholder="Votre message..." autocomplete="off">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-paper-plane"></i>
                                <span class="ms-1">Envoyer</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal pour créer un nouveau salon (accessible uniquement pour les admins) -->
<?php if ($isUserAdmin): ?>
<div class="modal fade" id="createSalonModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Créer un nouveau salon</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="createSalonForm">
                    <div class="mb-3">
                        <label for="salonName" class="form-label">Nom du salon</label>
                        <input type="text" class="form-control" id="salonName" required>
                    </div>
                    <div class="mb-3">
                        <label for="salonDescription" class="form-label">Description</label>
                        <textarea class="form-control" id="salonDescription" rows="3" required></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                <button type="button" class="btn btn-primary" onclick="createSalon()">Créer</button>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<script>
    const collaborateurId = <?= json_encode($_SESSION['collaborateur_id']) ?>;

    // Fonction pour rejoindre un salon et discuter directement
    async function joinChat(salonId, salonName) {
        try {
            // Masquer la vue d'ensemble des salons
            document.getElementById('salons-overview').classList.add('d-none');
            // Afficher la zone de chat
            document.getElementById('chat-area').classList.remove('d-none');
            // Mettre à jour le titre
            document.getElementById('chat-title').textContent = salonName;
            // Initialiser le chat
            currentChatId = salonId;
            
            await loadMessages();
            startMessagePolling();
        } catch (error) {
            console.error('Erreur lors du chargement du chat:', error);
            const chatMessages = document.getElementById('chat-messages');
            chatMessages.innerHTML = `
                <div class="alert alert-danger">
                    Une erreur est survenue lors du chargement des messages. 
                    Veuillez réessayer plus tard.
                </div>
            `;
        }
    }

    // Fonction pour rejoindre un salon (s'inscrire)
    async function joinSalon(salonId, salonName) {
        try {
            // Vérifier si le salon a déjà des membres
            const usersResponse = await fetch(`/api/chat/getUsers.php?salon_id=${salonId}`);
            const usersData = await usersResponse.json();
            
            const isFirstUser = !usersResponse.ok || !Array.isArray(usersData) || usersData.length === 0;
            
            // Ajouter l'utilisateur au salon
            const response = await addUserToSalon(salonId, isFirstUser);
            
            if (response) {
                // Rafraîchir la page pour voir les changements
                window.location.reload();
            }
        } catch (error) {
            console.error('Erreur lors de l\'inscription au salon:', error);
            alert('Une erreur est survenue lors de l\'inscription au salon.');
        }
    }
    
    // Fonction pour quitter un salon
    async function quitSalon(salonId, salonName) {
        if (!confirm(`Voulez-vous vraiment quitter le salon "${salonName}" ?`)) {
            return;
        }
        
        try {
            const response = await fetch('/api/chat/removeUser.php', {
                method: 'DELETE',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    salon_id: salonId,
                    collaborateur_id: collaborateurId
                })
            });
            
            const data = await response.json();
            
            if (response.ok) {
                // Rafraîchir la page pour voir les changements
                window.location.reload();
            } else {
                throw new Error(data.message || 'Erreur lors de la désinscription du salon');
            }
        } catch (error) {
            console.error('Erreur:', error);
            alert('Une erreur est survenue lors de la désinscription du salon.');
        }
    }
    
    // Fonction pour supprimer un salon (admin uniquement)
    async function deleteSalon(salonId, salonName) {
        if (!confirm(`Voulez-vous vraiment supprimer le salon "${salonName}" ? Cette action est irréversible.`)) {
            return;
        }
        
        try {
            const response = await fetch('/api/chat/delete.php', {
                method: 'DELETE',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    salon_id: salonId
                })
            });
            
            const data = await response.json();
            
            if (response.ok) {
                // Rafraîchir la page pour voir les changements
                window.location.reload();
            } else {
                throw new Error(data.message || 'Erreur lors de la suppression du salon');
            }
        } catch (error) {
            console.error('Erreur:', error);
            alert('Une erreur est survenue lors de la suppression du salon.');
        }
    }
    
    // Fonction pour créer un nouveau salon
    async function createSalon() {
        const nom = document.getElementById('salonName').value.trim();
        const description = document.getElementById('salonDescription').value.trim();
        
        if (!nom || !description) {
            alert('Veuillez remplir tous les champs.');
            return;
        }
        
        try {
            const response = await fetch('/api/chat/create.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    nom: nom,
                    description: description
                })
            });
            
            const data = await response.json();
            
            if (response.ok) {
                // Fermer le modal
                const modal = bootstrap.Modal.getInstance(document.getElementById('createSalonModal'));
                modal.hide();
                
                // Si la création a réussi et que nous avons un ID de salon
                if (data && data.salon_id) {
                    // Ajouter automatiquement l'utilisateur comme admin du nouveau salon
                    await addUserToSalon(data.salon_id, true);
                }
                
                // Rafraîchir la page pour voir les changements
                window.location.reload();
            } else {
                throw new Error(data.message || 'Erreur lors de la création du salon');
            }
        } catch (error) {
            console.error('Erreur:', error);
            alert('Une erreur est survenue lors de la création du salon.');
        }
    }

    // Fonction pour ajouter l'utilisateur au salon
    async function addUserToSalon(salonId, isAdmin = false) {
        try {
            const response = await fetch('/api/chat/addUser.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    salon_id: salonId,
                    collaborateur_id: collaborateurId,
                    is_admin: isAdmin
                })
            });
            
            const data = await response.json();
            
            if (!response.ok) {
                console.warn('Avertissement:', data.message || 'Erreur lors de l\'ajout au salon');
                // Si l'erreur est que l'utilisateur est déjà dans le salon, on continue
                if (response.status === 400 && data.message && data.message.includes('déjà dans le salon')) {
                    return true;
                }
                // Pour les autres erreurs, on les affiche mais on continue quand même
                return true;
            }
            
            console.log('Ajouté au salon avec succès:', data.message);
            return true;
        } catch (error) {
            console.warn('Note: Erreur lors de l\'ajout au salon:', error);
            // On continue même en cas d'erreur car l'utilisateur pourrait déjà être dans le salon
            return true;
        }
    }

    function leaveChatRoom() {
        if (messagePollingInterval) {
            clearInterval(messagePollingInterval);
        }
        document.getElementById('chat-area').classList.add('d-none');
        document.getElementById('salons-overview').classList.remove('d-none');
        currentChatId = null;
        lastMessageTimestamp = 0;
    }
</script>
<script src="/data/static/js/chat.js"></script>

<?php require_once $_SERVER['DOCUMENT_ROOT'] . '/frontOffice/employee/includes/footer.php'; ?>
