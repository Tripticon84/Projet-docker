<?php
$title = "Espace Conseils";
require_once $_SERVER['DOCUMENT_ROOT'] . '/frontOffice/employee/includes/head.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/frontOffice/employee/includes/header.php';

if (!isset($_SESSION['collaborateur_id'])) {
    header('Location: /login.php');
    exit;
}

$collaborateur_id = $_SESSION['collaborateur_id'];
?>

<div class="container mt-4" data-collaborateur-id="<?= $collaborateur_id ?>">
    <div class="row mb-4">
        <div class="col-12">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <h1 class="card-title">Espace Conseils</h1>
                    <p class="card-text">Demandez des conseils aux administrateurs et consultez vos réponses.</p>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Formulaire de demande de conseil -->
    <div class="card mb-5">
        <div class="card-header bg-primary text-white">
            <h4>Demander un conseil</h4>
        </div>
        <div class="card-body">
            <form id="adviceForm">
                <div class="mb-3">
                    <label for="question" class="form-label">Votre question</label>
                    <textarea class="form-control" id="question" name="question" rows="3" required></textarea>
                    <div class="form-text">Pendant que vous tapez, nous recherchons des questions similaires...</div>
                </div>
                
                <!-- Zone pour afficher les conseils similaires -->
                <div id="similarAdvice" class="mb-3 d-none">
                    <div class="alert alert-info">
                        <h5><i class="fas fa-lightbulb me-2"></i>Conseils similaires trouvés</h5>
                        <p>Des questions similaires existent déjà. Consultez-les avant de soumettre votre question.</p>
                        <div id="similarAdviceList" class="mt-3"></div>
                    </div>
                </div>
                
                <button type="submit" class="btn btn-primary">Envoyer ma demande</button>
            </form>
        </div>
    </div>
    
    <!-- Messages de confirmation/erreur -->
    <div id="adviceMessage" class="alert d-none"></div>
    
    <!-- Mes conseils en attente -->
    <div class="card mb-4">
        <div class="card-header bg-warning text-dark">
            <h4>Mes demandes en attente</h4>
        </div>
        <div class="card-body">
            <div id="pendingAdvice" class="row">
                <!-- Les conseils en attente seront chargés ici dynamiquement -->
                <div class="text-center">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Chargement...</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Conseils répondus -->
    <div class="card mb-4">
        <div class="card-header bg-success text-white">
            <h4>Conseils répondus</h4>
        </div>
        <div class="card-body">
            <div id="answeredAdvice" class="row">
                <!-- Les conseils répondus seront chargés ici dynamiquement -->
                <div class="text-center">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Chargement...</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="/data/static/js/employee.js"></script>

<script>
    // Fonctions spécifiques à la page des conseils
    async function loadEmployeeAdvicePage() {
        try {
            // Récupérer l'ID du collaborateur
            const collaborateurId = document.querySelector('[data-collaborateur-id]')?.dataset?.collaborateurId;
            
            if (!collaborateurId) {
                throw new Error('ID du collaborateur non trouvé. Veuillez vous connecter.');
            }
            
            // Charger les conseils depuis l'API
            const response = await fetch(`/api/advice/getAll.php`);
            if (!response.ok) throw new Error('Erreur lors du chargement des conseils');
            
            const advices = await response.json();
            
            // Récupérer les conteneurs HTML
            const pendingContainer = document.getElementById('pendingAdvice');
            const answeredContainer = document.getElementById('answeredAdvice');
            
            // Filtrer les conseils pour ce collaborateur (uniquement pour les demandes en attente)
            const myPendingAdvices = advices.filter(advice => 
                Number(advice.id_collaborateur) === Number(collaborateurId) && !advice.reponse
            );
            
            // Récupérer tous les conseils qui ont une réponse (sans filtre par collaborateur)
            const allAnsweredAdvices = advices.filter(advice => advice.reponse);
            
            // Afficher les conseils en attente du collaborateur connecté
            if (pendingContainer) {
                if (myPendingAdvices.length > 0) {
                    pendingContainer.innerHTML = myPendingAdvices.map(advice => `
                        <div class="col-md-6 col-lg-4 mb-3">
                            <div class="card h-100 border-warning shadow-sm">
                                <div class="card-header bg-warning text-dark d-flex justify-content-between align-items-center">
                                    <h5 class="mb-0 card-title">Question en attente</h5>
                                    <span class="badge bg-light text-dark">
                                        ${formatDate(advice.date_creation)}
                                    </span>
                                </div>
                                <div class="card-body">
                                    <p class="card-text">${advice.question}</p>
                                </div>
                                <div class="card-footer bg-transparent d-flex justify-content-end">
                                    <button class="btn btn-sm btn-danger" onclick="deleteAdviceItem(${advice.conseil_id})">
                                        <i class="fas fa-trash me-1"></i> Supprimer
                                    </button>
                                </div>
                            </div>
                        </div>
                    `).join('');
                } else {
                    pendingContainer.innerHTML = '<div class="col-12"><div class="alert alert-info">Vous n\'avez aucune demande de conseil en attente.</div></div>';
                }
            }
            
            // Afficher tous les conseils répondus 
            if (answeredContainer) {
                if (allAnsweredAdvices.length > 0) {
                    answeredContainer.innerHTML = allAnsweredAdvices.map(advice => {
                        // Déterminer si ce conseil appartient au collaborateur connecté
                        const isMyAdvice = Number(advice.id_collaborateur) === Number(collaborateurId);
                        
                        return `
                        <div class="col-md-6 col-lg-4 mb-3">
                            <div class="card h-100 ${isMyAdvice ? 'border-primary' : ''} shadow-sm">
                                <div class="card-header bg-success text-white d-flex justify-content-between align-items-center">
                                    <h5 class="mb-0 card-title">${isMyAdvice ? 'Ma question' : 'Question'}</h5>
                                    <span class="badge bg-light text-dark">
                                        ${formatDate(advice.date_reponse)}
                                    </span>
                                </div>
                                <div class="card-body">
                                    ${!isMyAdvice ? 
                                        `<div class="mb-2">
                                            <span class="badge bg-secondary">
                                                <i class="fas fa-user me-1"></i>${advice.collaborateur_prenom || ''} ${advice.collaborateur_nom || 'Anonyme'}
                                            </span>
                                        </div>` : ''
                                    }
                                    <div class="mb-3">
                                        <small class="text-muted">Posé le: ${formatDate(advice.date_creation)}</small>
                                    </div>
                                    <h6 class="card-subtitle mb-2 text-muted">Question:</h6>
                                    <p class="card-text mb-3">${advice.question}</p>
                                    
                                    <h6 class="card-subtitle mb-2 text-success">
                                        Réponse de ${advice.admin_username || 'l\'administration'}:
                                    </h6>
                                    <p class="card-text">${advice.reponse}</p>
                                </div>
                            </div>
                        </div>
                    `}).join('');
                } else {
                    answeredContainer.innerHTML = '<div class="col-12"><div class="alert alert-info">Aucun conseil répondu pour le moment.</div></div>';
                }
            }
        } catch (error) {
            console.error('Erreur de chargement des conseils:', error);
            
            // Afficher un message d'erreur dans les conteneurs
            ['pendingAdvice', 'answeredAdvice'].forEach(id => {
                const container = document.getElementById(id);
                if (container) {
                    container.innerHTML = '<div class="col-12"><div class="alert alert-danger">Erreur lors du chargement des conseils.</div></div>';
                }
            });
            
            showAdvicePageMessage('danger', `Erreur: ${error.message}`);
        }
    }
    
    // Fonction pour envoyer une demande de conseil
    async function submitAdvicePageRequest() {
        try {
            const questionInput = document.getElementById('question');
            const question = questionInput.value.trim();
            
            if (!question) {
                throw new Error('Veuillez saisir votre question');
            }
            
            const collaborateurId = document.querySelector('[data-collaborateur-id]')?.dataset?.collaborateurId;
            
            if (!collaborateurId) {
                throw new Error('ID du collaborateur non trouvé. Veuillez vous connecter.');
            }
            
            const response = await fetch('/api/advice/create.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    question: question,
                    id_collaborateur: collaborateurId
                })
            });
            
            const data = await response.json();
            
            if (!response.ok) {
                throw new Error(data.message || 'Erreur lors de l\'envoi de la demande');
            }
            
            // Réinitialiser le formulaire et afficher un message de succès
            questionInput.value = '';
            showAdvicePageMessage('success', 'Votre demande de conseil a été envoyée avec succès');
            
            // Recharger la liste des conseils
            setTimeout(() => loadEmployeeAdvicePage(), 1000);
            
        } catch (error) {
            console.error('Erreur d\'envoi de conseil:', error);
            showAdvicePageMessage('danger', `Erreur: ${error.message}`);
        }
    }
    
    // Fonction pour supprimer une demande de conseil
    async function deleteAdviceItem(adviceId) {
        if (!confirm('Êtes-vous sûr de vouloir supprimer cette demande de conseil ?')) {
            return;
        }
        
        try {
            const response = await fetch('/api/advice/delete.php', {
                method: 'DELETE',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    conseil_id: adviceId
                })
            });
            
            const data = await response.json();
            
            if (!response.ok) {
                throw new Error(data.message || 'Erreur lors de la suppression');
            }
            
            showAdvicePageMessage('success', 'Demande de conseil supprimée avec succès');
            
            // Recharger la liste des conseils
            setTimeout(() => loadEmployeeAdvicePage(), 1000);
            
        } catch (error) {
            console.error('Erreur de suppression de conseil:', error);
            showAdvicePageMessage('danger', `Erreur: ${error.message}`);
        }
    }
    
    // Fonction pour rechercher des conseils similaires
    let searchTimeout;
    async function searchSimilarAdvice(query) {
        // Attendre que l'utilisateur arrête de taper
        clearTimeout(searchTimeout);
        
        // Ne rechercher que si la requête est suffisamment longue
        if (query.length < 5) {
            document.getElementById('similarAdvice').classList.add('d-none');
            return;
        }
        
        searchTimeout = setTimeout(async () => {
            try {
                const response = await fetch('/api/advice/findSimilar.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        query: query
                    })
                });
                
                if (!response.ok) {
                    throw new Error('Erreur lors de la recherche de conseils similaires');
                }
                
                const data = await response.json();
                const similarAdviceContainer = document.getElementById('similarAdvice');
                const similarAdviceList = document.getElementById('similarAdviceList');
                
                // Afficher les conseils similaires s'il y en a
                if (data.results && data.results.length > 0) {
                    similarAdviceList.innerHTML = data.results.map(advice => `
                        <div class="card mb-2 border-info">
                            <div class="card-header bg-info text-white d-flex justify-content-between align-items-center">
                                <h6 class="mb-0">Question similaire</h6>
                                <small>${formatDate(advice.date_creation)}</small>
                            </div>
                            <div class="card-body">
                                <p class="mb-1"><strong>Q:</strong> ${advice.question}</p>
                                ${advice.reponse ? `
                                    <hr>
                                    <p class="mb-0"><strong>R:</strong> ${advice.reponse}</p>
                                ` : '<p class="text-muted mb-0"><i>Pas encore de réponse</i></p>'}
                            </div>
                        </div>
                    `).join('');
                    
                    similarAdviceContainer.classList.remove('d-none');
                } else {
                    similarAdviceContainer.classList.add('d-none');
                }
                
            } catch (error) {
                console.error('Erreur lors de la recherche:', error);
                document.getElementById('similarAdvice').classList.add('d-none');
            }
        }, 500); // Délai avant recherche
    }
    
    // Fonction pour formater les dates pour l'affichage
    function formatDate(dateString) {
        if (!dateString) return 'N/A';
        
        const date = new Date(dateString);
        
        // Vérifier si la date est valide
        if (isNaN(date.getTime())) return 'Date invalide';
        
        // Format: JJ/MM/AAAA à HH:MM
        return `${date.toLocaleDateString()} à ${date.toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'})}`;
    }
    
    // Fonction pour afficher les messages
    function showAdvicePageMessage(type, message) {
        const messageElement = document.getElementById('adviceMessage');
        if (messageElement) {
            messageElement.className = `alert alert-${type}`;
            messageElement.textContent = message;
            messageElement.classList.remove('d-none');
            
            // Masquer le message après quelques secondes
            setTimeout(() => {
                messageElement.classList.add('d-none');
            }, 5000);
        }
    }
    
    // Initialiser la page au chargement
    document.addEventListener('DOMContentLoaded', function() {
        // Charger les conseils
        loadEmployeeAdvicePage();
        
        // Gestionnaire pour le formulaire de conseil
        const adviceForm = document.getElementById('adviceForm');
        if (adviceForm) {
            adviceForm.addEventListener('submit', function(e) {
                e.preventDefault();
                submitAdvicePageRequest();
            });
        }
        
        // Ajouter l'écouteur d'événement pour détecter les changements dans le champ de question
        const questionInput = document.getElementById('question');
        if (questionInput) {
            questionInput.addEventListener('input', function(e) {
                searchSimilarAdvice(e.target.value.trim());
            });
        }
    });
</script>

<?php require_once $_SERVER['DOCUMENT_ROOT'] . '/frontOffice/employee/includes/footer.php'; ?>
