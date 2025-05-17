<?php require_once 'includes/head.php'; ?>

<!-- Ajout de FullCalendar -->
<link href="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.js"></script>

<!-- Styles spécifiques à cette page -->
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
        border-left: 4px solid transparent;
        margin-bottom: 8px;
    }

    .activity-item:hover {
        transform: translateX(5px);
        background-color: #f8f9fa;
    }

    /* Styles pour différencier les événements des activités */
    .activity-item[data-type="event"] {
        border-left-color: #28a745;
    }

    .activity-item[data-type="activity"] {
        border-left-color: #007bff;
    }

    /* Badges des types */
    .badge.bg-primary {
        background-color: #007bff !important;
    }

    .badge.bg-success {
        background-color: #28a745 !important;
    }

    @media (max-width: 768px) {
        .fc-toolbar {
            flex-direction: column;
        }
        .fc-toolbar-chunk {
            margin-bottom: 10px;
        }
    }

    .activity-event {
        background-color: #007bff;
        border: none;
    }
    .regular-event {
        background-color: #28a745;
        border: none;
    }

    /* Styles pour les boutons de filtre */
    .filter-buttons {
        margin-bottom: 15px;
        padding: 10px;
    }
    .filter-buttons button {
        margin-right: 10px;
    }
    .filter-buttons button.active {
        background-color: #0056b3;
        color: white;
    }

    /* Styles pour la modal */
    .event-modal .modal-header {
        background-color: #f8f9fa;
        border-bottom: 3px solid;
    }
    
    .event-modal.activity .modal-header {
        border-color: #007bff;
    }
    
    .event-modal.event .modal-header {
        border-color: #28a745;
    }
    
    .event-details dt {
        font-weight: 600;
        color: #495057;
    }
</style>

<!-- Script spécifique à cette page -->
<script>
// Add this at the beginning of the script section
const collaborateurId = <?php echo isset($_SESSION['collaborateur_id']) ? $_SESSION['collaborateur_id'] : 'null'; ?>;
if (!collaborateurId) {
    console.error('Collaborateur ID not found in session');
}

async function getEmployeeActivities() {
    try {
        if (!collaborateurId) {
            throw new Error('Collaborateur ID manquant - Veuillez vous connecter.');
        }

        const response = await Promise.all([
            fetch(`/api/employee/getActivity.php?collaborateur_id=${collaborateurId}`),
            fetch(`/api/employee/getEvent.php?collaborateur_id=${collaborateurId}`)
        ]);

        const [activitiesResponse, eventsResponse] = response;
        const activities = await activitiesResponse.json();
        const events = await eventsResponse.json();

        if (!Array.isArray(activities) || !Array.isArray(events)) {
            console.error('Invalid response format:', { activities, events });
            return [];
        }

        // Utiliser un Set pour stocker les identifiants uniques
        const processedIds = new Set();
        const allActivities = [];

        // Traiter les activités
        activities.forEach(activity => {
            const key = `activity-${activity.activity_id}`;
            if (!processedIds.has(key)) {
                processedIds.add(key);
                allActivities.push({
                    id: activity.activity_id,
                    title: activity.type,
                    start: activity.date,
                    type: activity.type,
                    devis: activity.is_devis,
                    prestataire: activity.id_prestataire,
                    lieu: activity.id_lieu,
                    itemType: 'activity'
                });
            }
        });

        // Traiter les événements
        events.forEach(event => {
            const key = `event-${event.id_association}`;
            if (!processedIds.has(key)) {
                processedIds.add(key);
                allActivities.push({
                    id: event.id_association,
                    title: event.nom,
                    start: event.date,
                    lieu: event.lieu,
                    type: event.type,
                    statut: event.statut,
                    id_association: event.id_association,
                    itemType: 'event'
                });
            }
        });

        return allActivities;
    } catch (error) {
        console.error('Erreur lors du chargement des données:', error);
        return [];
    }
}

let currentFilter = 'all'; // Variable globale pour suivre le filtre actif

function initializeFilters() {
    const filterButtons = document.querySelectorAll('.filter-btn');
    filterButtons.forEach(button => {
        button.addEventListener('click', function() {
            currentFilter = this.dataset.filter;
            // Mettre à jour l'apparence des boutons
            filterButtons.forEach(btn => btn.classList.remove('active'));
            this.classList.add('active');
            // Rafraîchir le calendrier
            calendar.refetchEvents();
        });
    });
}

function initializeCalendar() {
    const calendarEl = document.getElementById('calendar');
    if (!calendarEl) return;

    const calendar = new FullCalendar.Calendar(calendarEl, {
        initialView: 'dayGridMonth',
        headerToolbar: {
            left: 'prev,next today',
            center: 'title',
            right: 'dayGridMonth,timeGridWeek,timeGridDay'
        },
        eventSources: [{
            events: function(info, successCallback, failureCallback) {
                getEmployeeActivities()
                    .then(activities => {
                        const formattedEvents = activities
                            .filter(activity => {
                                if (currentFilter === 'all') return true;
                                if (currentFilter === 'activities') return activity.itemType === 'activity';
                                if (currentFilter === 'events') return activity.itemType === 'event';
                                return true;
                            })
                            .map(activity => ({
                                id: activity.id,
                                title: activity.itemType === 'activity' ? `Activité: ${activity.title}` : `Événement: ${activity.title}`,
                                start: activity.start,
                                className: [
                                    activity.itemType === 'activity' ? 'activity-event' : 'regular-event',
                                    activity.type.toLowerCase()
                                ],
                                extendedProps: {
                                    type: activity.type,
                                    itemType: activity.itemType,
                                    lieu: activity.lieu,
                                    devis: activity.devis,
                                    prestataire: activity.prestataire,
                                    statut: activity.statut
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
        eventClick: function(info) {
            const event = info.event;
            const modal = document.getElementById('eventDetailsModal');
            const modalInstance = new bootstrap.Modal(modal);
            
            // Reset modal classes
            modal.classList.remove('activity', 'event');
            // Add appropriate class based on item type
            modal.classList.add(event.extendedProps.itemType);
            
            // Update modal content
            modal.querySelector('.modal-title').textContent = event.title;
            modal.querySelector('.event-date').textContent = event.start.toLocaleString('fr-FR', {
                weekday: 'long',
                year: 'numeric',
                month: 'long',
                day: 'numeric',
                hour: '2-digit',
                minute: '2-digit'
            });
            modal.querySelector('.event-type').textContent = event.extendedProps.type;
            modal.querySelector('.event-location').textContent = event.extendedProps.lieu || 'Non spécifié';
            
            // Additional details based on type
            let additionalDetails = '';
            if (event.extendedProps.itemType === 'activity') {
                if (event.extendedProps.devis) additionalDetails += 'Devis requis\n';
                if (event.extendedProps.prestataire) additionalDetails += 'Prestataire assigné\n';
            } else {
                if (event.extendedProps.statut) additionalDetails += `Statut: ${event.extendedProps.statut}\n`;
            }
            modal.querySelector('.event-additional').textContent = additionalDetails || 'Aucun détail supplémentaire';
            
            modalInstance.show();
        },
        locale: 'fr'
    });

    window.calendar = calendar; // Rendre le calendrier accessible globalement
    calendar.render();
}

function loadActivities() {
    const activitiesList = document.getElementById('activities-list');
    if (!activitiesList) return;

    // Vider la liste avant d'ajouter de nouveaux éléments
    activitiesList.innerHTML = '<div class="loading">Chargement des activités...</div>';

    getEmployeeActivities()
        .then(activities => {
            activitiesList.innerHTML = ''; // Vider le message de chargement
            
            if (activities.length === 0) {
                activitiesList.innerHTML = '<div class="text-center">Aucune activité trouvée</div>';
                return;
            }

            // Créer une Map pour éviter les doublons
            const uniqueActivities = new Map();
            const now = new Date();
            
            // Trier et filtrer les activités à venir par date
            activities
                .filter(activity => new Date(activity.start) >= now) // Filtre uniquement les activités à venir
                .sort((a, b) => new Date(a.start) - new Date(b.start))
                .forEach(activity => {
                    const key = `${activity.itemType}-${activity.id}`;
                    if (!uniqueActivities.has(key)) {
                        uniqueActivities.set(key, activity);
                        const activityElement = createActivityElement(activity);
                        activitiesList.appendChild(activityElement);
                    }
                });

            // Afficher un message si aucune activité à venir
            if (uniqueActivities.size === 0) {
                activitiesList.innerHTML = '<div class="text-center">Aucune activité à venir</div>';
            }
        })
        .catch(error => {
            console.error('Erreur lors du chargement des activités:', error);
            activitiesList.innerHTML = '<div class="text-danger">Erreur lors du chargement des activités</div>';
        });
}

function createActivityElement(item) {
    const element = document.createElement('div');
    element.className = `list-group-item list-group-item-action activity-item`;
    element.setAttribute('data-id', `${item.itemType}-${item.id}`);
    element.setAttribute('data-type', item.itemType);
    
    const date = new Date(item.start);
    const formattedDate = date.toLocaleDateString('fr-FR', {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric'
    });
    
    element.innerHTML = `
        <div class="d-flex w-100 justify-content-between">
            <h5 class="mb-1">${item.title}</h5>
            <small>${formattedDate}</small>
        </div>
        <p class="mb-1">
            <span class="badge bg-${item.itemType === 'activity' ? 'primary' : 'success'}">
                ${item.itemType === 'activity' ? 'Activité' : 'Événement'}
            </span>
        </p>
    `;
    return element;
}

// Initialisation une fois que le DOM est chargé
document.addEventListener('DOMContentLoaded', function() {
    initializeCalendar();
    loadActivities();
    initializeFilters();
});
</script>

<?php require_once 'includes/header.php'; ?>

<div class="container mt-4">
    <div class="row mb-4">
        <div class="col-12">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <h1 class="card-title">Mon Planning</h1>
                    <p class="card-text">Consultez vos activités et événements à venir.</p>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Calendrier et liste des activités -->
        <div class="col-md-12">
            <div class="card mb-4">
                <div class="card-body">
                    <div class="filter-buttons">
                        <button class="btn btn-outline-primary filter-btn active" data-filter="all">Tout afficher</button>
                        <button class="btn btn-outline-primary filter-btn" data-filter="activities">Activités uniquement</button>
                        <button class="btn btn-outline-primary filter-btn" data-filter="events">Événements uniquement</button>
                    </div>
                    <div id="calendar"></div>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Mes activités à venir</h5>
                </div>
                <div class="card-body">
                    <div id="activities-list" class="list-group">
                        <!-- Les activités seront chargées dynamiquement -->
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Ajout de la modal pour les détails -->
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

<?php include 'includes/footer.php'; ?>
