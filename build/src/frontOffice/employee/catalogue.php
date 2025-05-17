<?php

require_once 'includes/head.php';
require_once 'includes/header.php';

if (!isset($_SESSION['collaborateur_id'])) {
    header('Location: /login.php');
    exit;
}
?>

<div class="container mt-4" data-collaborateur-id="<?php echo $_SESSION['collaborateur_id']; ?>">
    <div class="row mb-4">
        <div class="col-12">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <h1 class="card-title">Catalogue de services</h1>
                    <p class="card-text">Découvrez les prestations disponibles et réservez directement en ligne.</p>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Liste des services -->
        <div class="col-md-12">
            <div class="row">
                <div class="col-12 mb-3">
                    <input type="text" class="form-control" id="searchInput" placeholder="Rechercher une activité ou un événement...">
                </div>
            </div>
            <div class="row row-cols-1 row-cols-md-3 g-4" id="services-grid">
                <div id="loading" class="col-12">
                    <div class="text-center">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Chargement...</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>


<div class="modal fade" id="evaluationModal" tabindex="-1" aria-labelledby="evaluationModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="evaluationModalLabel">Évaluer le prestataire</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="evaluationForm">
                    <input type="hidden" id="prestataireId" name="prestataireId">
                    <input type="hidden" id="collaborateurId" name="collaborateurId">
                    
                    <div class="mb-3">
                        <label for="rating" class="form-label">Note</label>
                        <div class="rating">
                            <span class="star" data-value="1">★</span>
                            <span class="star" data-value="2">★</span>
                            <span class="star" data-value="3">★</span>
                            <span class="star" data-value="4">★</span>
                            <span class="star" data-value="5">★</span>
                        </div>
                        <input type="hidden" id="ratingValue" name="note" value="0">
                    </div>
                    
                    <div class="mb-3">
                        <label for="commentaire" class="form-label">Commentaire</label>
                        <textarea class="form-control" id="commentaire" name="commentaire" rows="3" required></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                <button type="button" class="btn btn-primary" id="submitEvaluation">Soumettre</button>
            </div>
        </div>
    </div>
</div>

<!-- affichage évaluations -->
<div class="modal fade" id="viewEvaluationsModal" tabindex="-1" aria-labelledby="viewEvaluationsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="viewEvaluationsModalLabel">Évaluations</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div id="evaluationsList">
                    <div class="text-center">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Chargement...</span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fermer</button>
            </div>
        </div>
    </div>
</div>

<script>

const collaborateurId = document.querySelector('[data-collaborateur-id]')?.dataset?.collaborateurId;
if (!collaborateurId) {
    window.location.href = '/login.php';
}

document.addEventListener('DOMContentLoaded', function() {
    loadAvailableServices();

    // Ajout du listener pour la recherche - filtre dynamique
    const searchInput = document.getElementById('searchInput');
    searchInput.addEventListener('input', filterServices);
    
    // Système d'évaluation par étoiles - clique sur les étoiles pour noter
    document.querySelectorAll('.star').forEach(star => {
        star.addEventListener('click', function() {
            const value = parseInt(this.dataset.value);
            document.getElementById('ratingValue').value = value;
            
            // Mise à jour visuelle des étoiles (remplies ou non)
            document.querySelectorAll('.star').forEach(s => {
                if (parseInt(s.dataset.value) <= value) {
                    s.classList.add('selected');
                } else {
                    s.classList.remove('selected');
                }
            });
        });
    });
    
    // Event listener pour la soumission d'une évaluation
    document.getElementById('submitEvaluation').addEventListener('click', submitEvaluation);
});

// Charge tous les services (activités + événements) disponibles
async function loadAvailableServices() {
    try {
        const loadingElement = document.getElementById('loading');
        if (loadingElement) loadingElement.style.display = 'block';

        // Promise.all pour faire plusieurs requêtes en parallèle - plus rapide
        const [activitiesResponse, eventsResponse, registrationsResponse] = await Promise.all([
            fetch('/api/activity/getAll.php'),
            fetch('/api/event/getAll.php'),
            fetch(`/api/employee/registrations.php?collaborateur_id=${collaborateurId}`)
        ]);

        // Log les réponses brutes pour debug
        console.log('Activities Response:', await activitiesResponse.clone().text());
        console.log('Events Response:', await eventsResponse.clone().text());
        console.log('Registrations Response:', await registrationsResponse.clone().text());

        // Vérification des réponses HTTP
        if (!activitiesResponse.ok) throw new Error('Erreur lors du chargement des activités');
        if (!eventsResponse.ok) throw new Error('Erreur lors du chargement des événements');
        if (!registrationsResponse.ok) throw new Error('Erreur lors du chargement des inscriptions');

        // Parse les réponses JSON en parallèle (encore du Promise.all)
        const [activities, events, registrations] = await Promise.all([
            activitiesResponse.json(),
            eventsResponse.json(),
            registrationsResponse.json()
        ]);

        // Filtre les services passés - on n'affiche que ceux à venir
        const now = new Date();
        const upcomingActivities = activities.filter(activity => new Date(activity.date) >= now);
        const upcomingEvents = events.filter(event => new Date(event.date) >= now);

        // Stock tous les services dans une variable globale pour la recherche
        window.allServices = [
            ...formatActivities(upcomingActivities, registrations),
            ...formatEvents(upcomingEvents, registrations)
        ];

        // Log les services formatés pour debug
        console.log('Formatted Services:', window.allServices);

        if (loadingElement) loadingElement.style.display = 'none';
        
        // Vérif qu'on a bien des services à afficher
        if (window.allServices.length === 0) {
            console.warn('Aucun service n\'a été chargé');
        }
        
        displayServices(window.allServices);
    } catch (error) {
        console.error('Erreur détaillée:', error);
        showErrorMessage(error.message);
        if (document.getElementById('loading')) {
            document.getElementById('loading').style.display = 'none';
        }
    }
}

// Formatage des données
function formatActivities(activities, registrations) {
    if (!Array.isArray(activities) || !Array.isArray(registrations)) {
        console.error('Format invalide:', { activities, registrations });
        return [];
    }
    
    return activities.map(activity => {
        console.log('Formatting activity:', activity);
        // Debug les inscriptions pour cette activité
        console.log('Checking registrations for activity:', activity.id, registrations.filter(r => 
            r.type === 'activite' && parseInt(r.service_id) === parseInt(activity.id)
        ));
        
        const isRegistered = registrations.some(r => 
            r.type === 'activite' && 
            parseInt(r.service_id) === parseInt(activity.id)
        );
        
        //console.log(`Activity ${activity.id} isRegistered:`, isRegistered); // Debug
        
        return {
            ...activity,
            id: parseInt(activity.id),
            serviceType: 'activite',
            displayType: getDisplayType(activity.type),
            formattedDate: new Date(activity.date).toLocaleDateString('fr-FR'),
            isRegistered: isRegistered,
            prestataire_id: activity.id_prestataire 
        };
    });
}

function formatEvents(events, registrations) {
    if (!Array.isArray(events) || !Array.isArray(registrations)) {
        console.error('Format invalide:', { events, registrations });
        return [];
    }
    
    return events.map(event => {
        console.log('Formatting event:', event);
        const isRegistered = registrations.some(r => 
            r.type === 'event' && 
            parseInt(r.service_id) === parseInt(event.evenement_id)
        );
        
        //console.log(`Event ${event.evenement_id} isRegistered:`, isRegistered); // Debug
        
        return {
            id: parseInt(event.evenement_id),
            nom: event.nom,
            type: event.type,
            date: event.date,
            lieu: event.lieu,
            serviceType: 'event',
            displayType: getDisplayType(event.type),
            formattedDate: new Date(event.date).toLocaleDateString('fr-FR'),
            isRegistered: isRegistered
        };
    });
}

function getDisplayType(type) {
    const typeMapping = {
        'webinar': 'Webinar',
        'conference': 'Conference',
        'workshop': 'Workshop',
        'medical': 'Medical',
        'sport': 'Sport'
    };
    return typeMapping[type.toLowerCase()] || type;
}

// Affichage des services modifié pour ajouter les boutons d'évaluation
function displayServices(services) {
    const servicesGrid = document.getElementById('services-grid');
    document.getElementById('loading')?.remove();

    if (services.length === 0) {
        servicesGrid.innerHTML = '<div class="col-12"><div class="alert alert-info">Aucun service disponible</div></div>';
        return;
    }

    servicesGrid.innerHTML = services.map(service => `
        <div class="col">
            <div class="card h-100 ${service.isRegistered ? 'border-success' : ''}">
                <div class="card-body">
                    <h5 class="card-title">${service.nom}</h5>
                    <p class="card-text">
                        <span class="badge bg-${service.serviceType === 'event' ? 'success' : 'primary'}">
                            ${service.displayType}
                        </span>
                        <br>
                        <small class="text-muted">Date: ${service.formattedDate}</small>
                    </p>
                    ${service.isRegistered ? `
                        <div class="mb-2">
                            <button class="btn btn-danger" 
                                onclick="unregisterFrom('${service.serviceType}', ${service.id})">
                                Se désinscrire
                            </button>
                        </div>
                        ${service.serviceType === 'activite' && service.prestataire_id ? `
                            <div class="btn-group w-100 mt-2">
                                <button class="btn btn-outline-primary btn-sm" 
                                    onclick="openEvaluationModal(${service.prestataire_id})">
                                    <i class="fas fa-star me-1"></i> Évaluer
                                </button>
                                <button class="btn btn-outline-secondary btn-sm" 
                                    onclick="viewEvaluations(${service.prestataire_id})">
                                    <i class="fas fa-eye me-1"></i> Voir les évaluations
                                </button>
                            </div>
                        ` : ''}
                    ` : `
                        <button class="btn btn-primary" 
                                onclick="registerFor('${service.serviceType}', ${service.id})">
                            S'inscrire
                        </button>
                        ${service.serviceType === 'activite' && service.prestataire_id ? `
                            <button class="btn btn-outline-secondary btn-sm mt-2 w-100" 
                                onclick="viewEvaluations(${service.prestataire_id})">
                                <i class="fas fa-eye me-1"></i> Voir les évaluations
                            </button>
                        ` : ''}
                    `}
                </div>
            </div>
        </div>
    `).join('');
}

// Fonction de filtrage des services simplifiée
function filterServices(event) {
    const searchTerm = event.target.value.toLowerCase();
    let filteredServices = window.allServices.filter(service => 
        service.nom.toLowerCase().includes(searchTerm) || 
        service.displayType.toLowerCase().includes(searchTerm)
    );
    displayServices(filteredServices);
}

// Inscription aux services
async function registerFor(type, id) {
    try {
        if (!confirm('Voulez-vous vraiment vous inscrire à cet événement ?')) {
            return;
        }

        const collaborateurId = document.querySelector('[data-collaborateur-id]')?.dataset?.collaborateurId;
        if (!collaborateurId) {
            alert('Veuillez vous connecter pour vous inscrire.');
            return;
        }

        
        const requestData = {
            type: type,
            collaborateur_id: collaborateurId
        };

        
        if (type === 'event') {
            requestData.id_evenement = id;
        } else if (type === 'activite') {
            requestData.id_activite = id;
        }

        
        //console.log('Sending request with data:', requestData);

        const response = await fetch('/api/employee/register.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(requestData)
        });

        
        const responseText = await response.clone().text();
        console.log('Register Response:', responseText);

        let data;
        try {
            data = JSON.parse(responseText);
        } catch (e) {
            throw new Error('Réponse invalide du serveur');
        }
        
        if (!response.ok) {
            throw new Error(data.error || data.message || 'Erreur lors de l\'inscription');
        }

        showSuccessMessage('Inscription réussie !');
        await loadAvailableServices();
    } catch (error) {
        showErrorMessage('Erreur : ' + error.message);
        console.error('Erreur détaillée:', error);
    }
}

// Désinscription des services
async function unregisterFrom(type, id) {
    try {
        if (!confirm('Voulez-vous vraiment vous désinscrire de cet événement ?')) {
            return;
        }

        const collaborateurId = document.querySelector('[data-collaborateur-id]')?.dataset?.collaborateurId;
        if (!collaborateurId) {
            alert('Veuillez vous connecter pour vous désinscrire.');
            return;
        }

        
        const requestData = {
            type: type,
            collaborateur_id: collaborateurId
        };

        
        if (type === 'event') {
            requestData.id_evenement = id;
        } else if (type === 'activite') {
            requestData.id_activite = id;
        }

        
        //console.log('Sending request with data:', requestData);

        const response = await fetch('/api/employee/unregister.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(requestData)
        });

        
        const responseText = await response.clone().text();
        console.log('Unregister Response:', responseText);

        let data;
        try {
            data = JSON.parse(responseText);
        } catch (e) {
            throw new Error('Réponse invalide du serveur');
        }
        
        if (!response.ok) {
            throw new Error(data.error || data.message || 'Erreur lors de la désinscription');
        }

        showSuccessMessage('Désinscription réussie !');
        await loadAvailableServices();
    } catch (error) {
        showErrorMessage('Erreur : ' + error.message);
        console.error('Erreur détaillée:', error);
    }
}

function showSuccessMessage(message) {
    const alertDiv = document.createElement('div');
    alertDiv.className = 'alert alert-success alert-dismissible fade show';
    alertDiv.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    document.querySelector('.container').insertAdjacentElement('afterbegin', alertDiv);
}

function showErrorMessage(message) {
    const alertDiv = document.createElement('div');
    alertDiv.className = 'alert alert-danger alert-dismissible fade show';
    alertDiv.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    document.querySelector('.container').insertAdjacentElement('afterbegin', alertDiv);
}

// évaluation
function openEvaluationModal(prestataireId) {
    // Réinitialiser le formulaire
    document.getElementById('evaluationForm').reset();
    document.querySelectorAll('.star').forEach(s => s.classList.remove('selected'));
    document.getElementById('ratingValue').value = 0;
    
    // Définir les ID nécessaires
    document.getElementById('prestataireId').value = prestataireId;
    document.getElementById('collaborateurId').value = collaborateurId;
    
    
    const modal = new bootstrap.Modal(document.getElementById('evaluationModal'));
    modal.show();
}

// Soumettre une évaluation via l'API
async function submitEvaluation() {
    try {
        const prestataireId = document.getElementById('prestataireId').value;
        const note = document.getElementById('ratingValue').value;
        const commentaire = document.getElementById('commentaire').value;
        
        // Validation côté client
        if (note <= 0) {
            alert("Veuillez sélectionner une note en cliquant sur les étoiles.");
            return;
        }
        
        if (!commentaire.trim()) {
            alert("Veuillez entrer un commentaire.");
            return;
        }
        
        // Log des données avant envoi pour debug
        console.log("Envoi des données d'évaluation:", {
            note: parseInt(note),
            commentaire: commentaire,
            collaborateur_id: collaborateurId,
            prestataire_id: prestataireId
        });
        
        // Envoi des données à l'API via fetch et JSON
        const response = await fetch('/api/evaluation/create.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({
                note: parseInt(note),
                commentaire: commentaire,
                collaborateur_id: collaborateurId,
                prestataire_id: prestataireId
            })
        });
        
        // Vérification de la réponse - important de tout logger pour debug
        const responseText = await response.clone().text();
        console.log("Réponse brute du serveur:", responseText);
        
        let data;
        try {
            data = JSON.parse(responseText);
        } catch (e) {
            console.error("Erreur de parsing JSON:", e);
            throw new Error("Réponse invalide du serveur: " + responseText);
        }
        
        if (!response.ok) {
            throw new Error(data.error || data.message || "Une erreur est survenue lors de la soumission de l'évaluation.");
        }
        
        // Ferme la modal si tout s'est bien passé
        bootstrap.Modal.getInstance(document.getElementById('evaluationModal')).hide();
        
        // Affiche un message de succès
        showSuccessMessage("Votre évaluation a été soumise avec succès !");
        
    } catch (error) {
        console.error("Erreur lors de la soumission de l'évaluation:", error);
        showErrorMessage(`Erreur: ${error.message}`);
    }
}

// Voir les évaluations d'un prestataire
async function viewEvaluations(prestataireId) {
    try {
        
        const modal = new bootstrap.Modal(document.getElementById('viewEvaluationsModal'));
        modal.show();
        
        const evaluationsListElement = document.getElementById('evaluationsList');
        evaluationsListElement.innerHTML = `
            <div class="text-center">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Chargement...</span>
                </div>
            </div>
        `;
        
        // Récupérer les évaluations pour ce prestataire
        const response = await fetch(`/api/evaluation/getAll.php`);
        let evaluations = await response.json();
        
        if (!response.ok) {
            throw new Error("Erreur lors de la récupération des évaluations.");
        }
        
        
        // Afficher les évaluations
        if (evaluations.length === 0) {
            evaluationsListElement.innerHTML = `
                <div class="alert alert-info">
                    Aucune évaluation pour ce prestataire pour le moment.
                </div>
            `;
            return;
        }
        
        // Calculer la moyenne des notes
        const averageRating = evaluations.reduce((sum, eval) => sum + parseInt(eval.note), 0) / evaluations.length;
        
        let content = `
            <div class="mb-3">
                <h4>Note moyenne: ${averageRating.toFixed(1)}/5</h4>
                <div class="big-stars">
                    ${displayStars(averageRating)}
                </div>
                <p class="text-muted">${evaluations.length} évaluation(s)</p>
            </div>
            <hr>
        `;
        
        content += evaluations.map(evaluation => `
            <div class="card mb-3">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="stars">
                                ${displayStars(evaluation.note)}
                            </div>
                            <p class="mb-0">${evaluation.commentaire}</p>
                        </div>
                        <small class="text-muted">${new Date(evaluation.date_creation).toLocaleDateString()}</small>
                    </div>
                </div>
            </div>
        `).join('');
        
        evaluationsListElement.innerHTML = content;
        
    } catch (error) {
        console.error("Erreur lors de l'affichage des évaluations:", error);
        document.getElementById('evaluationsList').innerHTML = `
            <div class="alert alert-danger">
                Erreur lors du chargement des évaluations: ${error.message}
            </div>
        `;
    }
}

// Fonction auxiliaire pour afficher les étoiles
function displayStars(rating) {
    const fullStar = '★';
    const emptyStar = '☆';
    const ratingRounded = Math.round(rating);
    
    let stars = '';
    for (let i = 1; i <= 5; i++) {
        if (i <= ratingRounded) {
            stars += `<span class="star filled">${fullStar}</span>`;
        } else {
            stars += `<span class="star empty">${emptyStar}</span>`;
        }
    }
    
    return stars;
}
</script>

<style>
/* Style pour le système d'évaluation par étoiles */
.rating {
    display: flex;
    flex-direction: row-reverse;
    justify-content: flex-end;
}

.star {
    color: #ddd;
    font-size: 24px;
    cursor: pointer;
    margin-right: 5px;
}

.star:hover, .star:hover ~ .star, .star.selected, .star.selected ~ .star {
    color: #ffb900;
}

.stars {
    margin-bottom: 8px;
}

.stars .star {
    font-size: 18px;
    cursor: default;
    margin-right: 2px;
}

.stars .star.filled {
    color: #ffb900;
}

.stars .star.empty {
    color: #ddd;
}

.big-stars .star {
    font-size: 28px;
    cursor: default;
    margin-right: 3px;
}
</style>

<?php include 'includes/footer.php'; ?>
