<?php
$title = "Mon Planning";
require_once $_SERVER['DOCUMENT_ROOT'] . '/frontOffice/prestataire/includes/head.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/frontOffice/prestataire/includes/header.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/api/dao/provider.php';

// Vérifier si une session est déjà active
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['prestataire_id'])) {
    header("Location: login/login.php?message=Veuillez vous connecter.");
    exit();
}

$prestataire_id = $_SESSION['prestataire_id'];
?>

<!-- Ajout de FullCalendar -->
<link href="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/locales/fr.js"></script>


<style>
    /* Styles pour le calendrier */
    #calendar {
        min-height: 600px;
        padding: 15px;
    }

    .fc-toolbar-title {
        font-size: 1.5em !important;
        font-weight: bold;
        color: #333;
    }

    .fc-event {
        cursor: pointer;
        padding: 4px;
        margin: 2px 0;
        border-radius: 4px;
    }

    /* Styles des activités */
    .activity-item {
        transition: all 0.3s ease;
        border-left: 4px solid #007bff;
        margin-bottom: 8px;
    }

    .activity-item:hover {
        transform: translateX(5px);
        background-color: #f8f9fa;
    }

    /* Événements passés vs futurs */
    .past-activity {
        opacity: 0.7;
    }

    .future-activity {
        font-weight: bold;
    }

    .activity-event {
        background-color: #007bff;
        border: none;
    }

    @media (max-width: 768px) {
        .fc-toolbar {
            flex-direction: column;
        }
        .fc-toolbar-chunk {
            margin-bottom: 10px;
        }
    }

    /* Styles pour la modal */
    .event-modal .modal-header {
        background-color: #f8f9fa;
        border-bottom: 3px solid #007bff;
    }
    
    .event-details dt {
        font-weight: 600;
        color: #495057;
    }
</style>

<script>

const prestataireId = <?php echo isset($_SESSION['prestataire_id']) ? $_SESSION['prestataire_id'] : 'null'; ?>;
if (!prestataireId) {
    console.error('Prestataire ID not found in session');
}


// Fonction utile pour debug - affiche un objet dans la console proprement
function debugObject(obj, label) {
    console.log(`Debug ${label}:`, JSON.stringify(obj));
}

// Récupère les activités du prestataire depuis l'API
async function getProviderActivities() {
    try {
        if (!prestataireId) {
            throw new Error('Prestataire ID manquant - Veuillez vous connecter.');
        }

        // Fetch API - bien mieux que XMLHttpRequest, ça supporte les Promises
        const response = await fetch(`/api/provider/getActivite.php?id=${prestataireId}`);
        if (!response.ok) {
            throw new Error('Erreur lors de la récupération des activités');
        }

        const activities = await response.json();
        debugObject(activities, 'Raw activities from API'); // Debug 

        if (!Array.isArray(activities)) {
            console.error('Format de réponse invalide:', activities);
            return [];
        }

        // Map pour transformer les données au format attendu par le calendrier
        return activities.map(activity => {
            
            const dateValue = activity.date || null;
            debugObject(activity, 'Single activity object');
            
            return {
                id: activity.activite_id,
                title: activity.name,
                date: dateValue,
                start: dateValue, // FullCalendar a besoin de 'start'
                type: activity.type,
                lieu: activity.place,
                devis: activity.id_estimate
            };
        });
    } catch (error) {
        console.error('Erreur lors du chargement des données:', error);
        return [];
    }
}

// Initialise le calendrier avec FullCalendar (super lib JS)
function initializeCalendar() {
    const calendarEl = document.getElementById('calendar');
    if (!calendarEl) return;

    const calendar = new FullCalendar.Calendar(calendarEl, {
        initialView: 'dayGridMonth', // Vue par défaut: mois
        headerToolbar: {
            left: 'prev,next today',
            center: 'title',
            right: 'dayGridMonth,timeGridWeek,timeGridDay'
        },
        eventSources: [{
            events: function(info, successCallback, failureCallback) {
                // Callback pour charger les événements dynamiquement
                getProviderActivities()
                    .then(activities => {
                        const formattedEvents = activities.map(activity => ({
                            id: activity.id,
                            title: `${activity.title}`,
                            start: activity.date,
                            className: [
                                'activity-event',
                                activity.type.toLowerCase().replace(' ', '-')
                            ],
                            extendedProps: {
                                type: activity.type,
                                lieu: activity.lieu,
                                devis: activity.devis
                            }
                        }));
                        successCallback(formattedEvents);
                    })
                    .catch(error => {
                        console.error('Erreur lors du chargement des événements:', error);
                        failureCallback(error);
                    });
            }
        }],
        // Gestion du clic sur un événement - ouvre une modal avec les détails
        eventClick: function(info) {
            const event = info.event;
            const modal = document.getElementById('eventDetailsModal');
            const modalInstance = new bootstrap.Modal(modal);
            
            // Format de la date en français pour l'affichage
            const eventDate = new Date(event.start);
            const formattedDate = !isNaN(eventDate.getTime()) ? 
                eventDate.toLocaleDateString('fr-FR', {
                    weekday: 'long',
                    year: 'numeric',
                    month: 'long',
                    day: 'numeric'
                }) : 'Date non spécifiée';
            
            // Remplissage des champs de la modal
            modal.querySelector('.modal-title').textContent = event.title;
            modal.querySelector('.event-date').textContent = formattedDate;
            modal.querySelector('.event-type').textContent = event.extendedProps.type;
            modal.querySelector('.event-location').textContent = event.extendedProps.lieu || 'Non spécifié';
            
            // Infos additionnelles si présentes
            let additionalDetails = '';
            if (event.extendedProps.devis) {
                additionalDetails += `Devis associé: #${event.extendedProps.devis}`;
            }
            
            modal.querySelector('.event-additional').textContent = additionalDetails || 'Aucun détail supplémentaire';
            
            modalInstance.show();
        },
        locale: 'fr' // Calendrier en français - locale doit être chargée
    });

    window.calendar = calendar; // Stocke le calendrier dans une var globale pour y accéder ailleurs
    calendar.render();
}

function loadActivities() {
    const activitiesList = document.getElementById('activities-list');
    if (!activitiesList) return;

    
    activitiesList.innerHTML = '<div class="text-center">Chargement des activités...</div>';

    getProviderActivities()
        .then(activities => {
            debugObject(activities, 'Processed activities'); // Debug 
            activitiesList.innerHTML = ''; 
            
            if (activities.length === 0) {
                activitiesList.innerHTML = '<div class="alert alert-info">Aucune activité programmée</div>';
                return;
            }

            const now = new Date();
            
            
            const futureActivities = [];
            const pastActivities = [];
            
            activities.forEach(activity => {
                
                if (!activity.date) {
                    console.warn('Activity missing date:', activity);
                    
                    futureActivities.push(activity);
                    return;
                }
                
                
                let activityDate;
                try {
                   
                    activityDate = new Date(activity.date);
                    
                   
                    if (isNaN(activityDate.getTime())) {
                        activityDate = new Date(activity.date + 'T00:00:00');
                    }
                    
                    
                    if (isNaN(activityDate.getTime())) {
                        console.warn('Could not parse date:', activity.date);
                        futureActivities.push(activity);
                        return;
                    }
                } catch (e) {
                    console.error('Error parsing date:', e);
                    futureActivities.push(activity);
                    return;
                }
                
               
                console.log('Valid activity date comparison:', 
                            activity.date, activityDate, now, activityDate >= now);
                
                if (activityDate >= now) {
                    futureActivities.push(activity);
                } else {
                    pastActivities.push(activity);
                }
            });
            
            
            futureActivities.sort((a, b) => new Date(a.date) - new Date(b.date));
            pastActivities.sort((a, b) => new Date(b.date) - new Date(a.date)); 
            
           
            if (futureActivities.length > 0) {
                activitiesList.innerHTML += '<h6 class="text-primary mb-3">Activités à venir</h6>';
                futureActivities.forEach(activity => {
                    const element = createActivityElement(activity, 'future');
                    activitiesList.appendChild(element);
                });
            } else {
                activitiesList.innerHTML += '<div class="alert alert-info">Aucune activité à venir</div>';
            }
            
            
            if (pastActivities.length > 0) {
                activitiesList.innerHTML += '<h6 class="text-secondary mt-4 mb-3">Activités passées</h6>';
               
                pastActivities.slice(0, 5).forEach(activity => {
                    const element = createActivityElement(activity, 'past');
                    activitiesList.appendChild(element);
                });
            }
        })
        .catch(error => {
            console.error('Erreur lors du chargement des activités:', error);
            activitiesList.innerHTML = '<div class="alert alert-danger">Erreur lors du chargement des activités</div>';
        });
}


// createActivityElement crée un élément DOM pour afficher une activité dans la liste
function createActivityElement(item, timeStatus) {
    const element = document.createElement('div');
    element.className = `list-group-item list-group-item-action activity-item ${timeStatus}-activity`;
    element.setAttribute('data-id', item.id);
    
    // Debug l'item qu'on va afficher
    debugObject(item, 'Item for element creation');
    
    // Format de la date pour l'affichage
    let formattedDate = "Date non spécifiée";
    if (item.date) {
        try {
            const date = new Date(item.date);
            if (!isNaN(date.getTime())) {
                formattedDate = date.toLocaleDateString('fr-FR', {
                    day: '2-digit',
                    month: '2-digit',
                    year: 'numeric'
                });
            }
        } catch (e) {
            console.error('Error formatting date:', e);
        }
    }
    
    // Génère le HTML avec template string 
    element.innerHTML = `
        <div class="d-flex w-100 justify-content-between">
            <h5 class="mb-1">${item.title || ''}</h5>
            <small>${formattedDate}</small>
        </div>
        <p class="mb-1">
            <span class="badge bg-primary">${item.type || 'Non spécifié'}</span>
            ${item.lieu ? `<small class="text-muted ms-2"><i class="fas fa-map-marker-alt"></i> ${item.lieu}</small>` : ''}
        </p>
    `;
    return element;
}


// Exécute initializeCalendar et loadActivities quand le DOM est chargé
document.addEventListener('DOMContentLoaded', function() {
    initializeCalendar();
    loadActivities();
});
</script>

<div class="container mt-4">
    <div class="row mb-4">
        <div class="col-12">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <h1 class="card-title">Mon Planning</h1>
                    <p class="card-text">Consultez vos activités et prestations à venir.</p>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        
        <div class="col-md-8">
            <div class="card mb-4">
                <div class="card-header bg-white">
                    <h5 class="card-title mb-0">Calendrier des prestations</h5>
                </div>
                <div class="card-body">
                    <div id="calendar"></div>
                </div>
            </div>
        </div>
        
        
        <div class="col-md-4">
            <div class="card">
                <div class="card-header bg-white">
                    <h5 class="card-title mb-0">Mes prestations</h5>
                </div>
                <div class="card-body">
                    <div id="activities-list">
                        
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>


<div class="modal fade event-modal" id="eventDetailsModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <dl class="event-details">
                    <dt>Date</dt>
                    <dd class="event-date"></dd>
                    <dt>Type</dt>
                    <dd class="event-type"></dd>
                    <dt>Lieu</dt>
                    <dd class="event-location"></dd>
                    <dt>Détails additionnels</dt>
                    <dd class="event-additional"></dd>
                </dl>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fermer</button>
            </div>
        </div>
    </div>
</div>

<?php require_once $_SERVER['DOCUMENT_ROOT'] . '/frontOffice/prestataire/includes/footer.php'; ?>
