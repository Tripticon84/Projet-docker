// Initialisation du calendrier
document.addEventListener('DOMContentLoaded', async function() {
    // Ajout du chargement des données pour la page d'accueil
    const upcomingEventsElement = document.getElementById('upcoming-events');
    const myActivitiesElement = document.getElementById('my-activities');
    const adviceForm = document.getElementById('adviceForm');
    
    if (upcomingEventsElement || myActivitiesElement) {
        await loadDashboardData();
    } else if (adviceForm) {
        // Si nous sommes sur la page de conseils, charger les conseils
        await loadEmployeeAdvice();
    } else {
        await initializeCalendar();
        await loadActivities();
        initializeFilters();
    }

    // Ajouter l'écouteur pour le formulaire de mot de passe s'il existe
    const passwordForm = document.getElementById('passwordForm');
    if (passwordForm) {
        passwordForm.addEventListener('submit', handlePasswordUpdate);
    }
    
    // Ajouter l'écouteur pour le formulaire de conseil s'il existe
    if (adviceForm) {
        adviceForm.addEventListener('submit', function(e) {
            e.preventDefault();
            submitAdviceRequest();
        });
    }
});

async function initializeCalendar() {
    const calendarEl = document.getElementById('calendar');
    if (!calendarEl) return;

    const calendar = new FullCalendar.Calendar(calendarEl, {
        initialView: 'dayGridMonth',
        headerToolbar: {
            left: 'prev,next today',
            center: 'title',
            right: 'dayGridMonth,timeGridWeek,timeGridDay'
        },
        events: await getEmployeeActivities(),
        locale: 'fr'
    });

    calendar.render();
}

function getCollaborateurId() {
    // Vérifier d'abord si la variable globale est définie dans la page
    if (typeof collaborateurId !== 'undefined' && collaborateurId) {
        return collaborateurId;
    }

    // Essayer plusieurs sources pour l'ID
    const sources = {
        bodyData: document.body.dataset.collaborateurId,
        containerData: document.querySelector('.container')?.dataset?.collaborateurId,
        elementData: document.querySelector('[data-collaborateur-id]')?.dataset?.collaborateurId,
        urlParam: new URLSearchParams(window.location.search).get('collaborateur_id'),
        hiddenInput: document.querySelector('input[name="collaborateur_id"]')?.value
    };

    const id = sources.bodyData || sources.containerData || sources.elementData || sources.urlParam || sources.hiddenInput;
    
    if (!id) {
        console.error('ID du collaborateur non trouvé. Redirection vers la page de connexion...');
        window.location.href = '/login.php';
        return null;
    }
    
    return id;
}

async function getEmployeeActivities() {
    try {
        const collaborateurId = getCollaborateurId();
        
        if (!collaborateurId) {
            throw new Error('Collaborateur ID manquant - Veuillez vous connecter.');
        }

        const [activities, events] = await Promise.all([
            fetch(`/api/employee/getActivity.php?collaborateur_id=${collaborateurId}`).then(r => r.json()),
            fetch(`/api/employee/getEvent.php?collaborateur_id=${collaborateurId}`).then(r => r.json())
        ]);

        // Formater correctement les dates pour FullCalendar
        const formattedActivities = activities.map(activity => ({
            title: activity.nom,
            type: activity.type,
            date: activity.date,
            devis: activity.is_devis,
            prestataire: activity.id_prestataire,
            lieu: activity.id_lieu
        }));

        const formattedEvents = events.map(event => ({
            title: event.nom,
            date: event.date,
            lieu: event.lieu,
            type: event.type,
            satatut: event.statut,
            id_association: event.id_association
        }));
        return [...formattedActivities, ...formattedEvents];
    } catch (error) {
        console.error('Erreur détaillée:', {
            message: error.message,
            stack: error.stack,
            collaborateurId: getCollaborateurId()
        });
        return [];
    }
}

async function loadActivities() {
    const activitiesList = document.getElementById('activities-list');
    if (!activitiesList) return;

    const activities = await getEmployeeActivities();
    activities.forEach(activity => {
        const activityElement = createActivityElement(activity);
        activitiesList.appendChild(activityElement);
    });
}

function createActivityElement(item) {
    const element = document.createElement('div');
    element.className = `list-group-item list-group-item-action activity-item ${item.type}`;
    
    const date = new Date(item.start);
    const typeLabel = item.itemType === 'activity' ? 'Activité' : 'Événement';
    
    element.innerHTML = `
        <div class="d-flex w-100 justify-content-between">
            <h5 class="mb-1">${item.title}</h5>
            <small>${date.toLocaleDateString()}</small>
        </div>
        <p class="mb-1">
            <span class="badge bg-${item.itemType === 'activity' ? 'primary' : 'success'}">${typeLabel}</span>
            ${date.toLocaleTimeString()}
        </p>
    `;
    return element;
}

function initializeFilters() {
    const filters = document.querySelectorAll('.filter-activity');
    filters.forEach(filter => {
        filter.addEventListener('change', function() {
            updateActivityVisibility();
        });
    });
}

function updateActivityVisibility() {
    const selectedTypes = Array.from(document.querySelectorAll('.filter-activity:checked'))
        .map(checkbox => checkbox.value);
    
    document.querySelectorAll('.activity-item').forEach(item => {
        const type = Array.from(item.classList)
            .find(className => ['webinar', 'conference', 'workshop', 'medical', 'sport'].includes(className));
        
        item.style.display = selectedTypes.includes(type) ? 'block' : 'none';
    });
}

async function registerForService(type, id) {
    try {
        const collaborateurId = getCollaborateurId();
        
        // Utiliser le bon nom de champ en fonction du type
        const requestData = {
            type,
            collaborateur_id: collaborateurId
        };
        
        // Ajouter le bon champ ID en fonction du type
        if (type === 'event') {
            requestData.id_evenement = id;
        } else if (type === 'activity') {
            requestData.id_activite = id;
        }

        const response = await fetch('/api/employee/register.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(requestData)
        });

        const data = await response.json();
        if (!response.ok) throw new Error(data.message);
        
        // Rafraîchir le calendrier si on est sur la page calendrier
        const calendar = document.querySelector('#calendar');
        if (calendar) {
            await initializeCalendar();
        }
        
        return data;
    } catch (error) {
        console.error('Erreur d\'inscription:', error);
        throw error;
    }
}

async function unregisterFrom(type, id) {
    try {
        if (!confirm('Voulez-vous vraiment vous désinscrire de cet événement ?')) {
            return;
        }

        const collaborateurId = getCollaborateurId();
        
        const requestData = {
            type,
            collaborateur_id: collaborateurId
        };
        
        if (type === 'event') {
            requestData.id_evenement = id;
        } else if (type === 'activite') {
            requestData.id_activite = id;
        }

        const response = await fetch('/api/employee/unregister.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(requestData)
        });

        const data = await response.json();
        if (!response.ok) throw new Error(data.message);
        
        // Rafraîchir la liste des services
        await loadAvailableServices();
        
        return data;
    } catch (error) {
        console.error('Erreur de désinscription:', error);
        throw error;
    }
}

async function unsubscribeFromAssociation(collaborateurId, associationId) {
    if (!confirm('Êtes-vous sûr de vouloir vous désinscrire de cette association ?')) {
        return;
    }

    try {
        const response = await fetch('/api/employee/unsubscribeAssociation.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                collaborateur_id: collaborateurId,
                association_id: associationId
            })
        });

        const data = await response.json();
        
        if (!response.ok) {
            throw new Error(data.message || 'Erreur lors de la désinscription');
        }

        // Recharger la page pour actualiser la liste
        window.location.reload();

    } catch (error) {
        alert('Erreur: ' + error.message);
    }
}

async function subscribeToAssociation(collaborateurId, associationId) {
    try {
        const response = await fetch('/api/employee/subscribeAssociation.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                collaborateur_id: collaborateurId,
                association_id: associationId
            })
        });

        const data = await response.json();
        
        if (!response.ok) {
            throw new Error(data.message || 'Erreur lors de l\'inscription');
        }

        // Recharger la page pour actualiser la liste
        window.location.reload();

    } catch (error) {
        alert('Erreur: ' + error.message);
    }
}

async function loadDashboardData() {
    try {
        const allActivities = await getEmployeeActivities();
        
        if (!Array.isArray(allActivities)) {
            throw new Error('Les données reçues ne sont pas au bon format');
        }

        // Trier les éléments par date
        const sortedActivities = allActivities.sort((a, b) => new Date(a.date) - new Date(b.date));
        
        // Séparer les événements des activités
        const now = new Date();
        const events = sortedActivities.filter(item => 
            item.type === 'event' && new Date(item.date) >= now
        ).slice(0, 3);
        
        const activities = sortedActivities.filter(item => 
            item.type !== 'event' && new Date(item.date) >= now
        ).slice(0, 3);

        // Mise à jour des événements à venir
        const upcomingEventsElement = document.getElementById('upcoming-events');
        if (upcomingEventsElement) {
            upcomingEventsElement.innerHTML = events.length > 0 
                ? events.map(event => `
                    <div class="mb-2 p-2 border-bottom">
                        <div class="d-flex justify-content-between align-items-center">
                            <strong>${event.title || 'Sans titre'}</strong>
                            <small class="text-muted">${new Date(event.date).toLocaleDateString('fr-FR')}</small>
                        </div>
                        ${event.lieu ? `<div class="text-muted small"><i class="fas fa-map-marker-alt"></i> ${event.lieu}</div>` : ''}
                    </div>
                `).join('')
                : '<p class="text-muted">Aucun événement à venir pour le moment.</p>';
        }

        // Mise à jour des activités
        const myActivitiesElement = document.getElementById('my-activities');
        if (myActivitiesElement) {
            myActivitiesElement.innerHTML = activities.length > 0
                ? activities.map(activity => `
                    <div class="mb-2 p-2 border-bottom">
                        <div class="d-flex justify-content-between align-items-center">
                            <strong>${activity.title || 'Sans titre'}</strong>
                            <small class="text-muted">${new Date(activity.date).toLocaleDateString('fr-FR')}</small>
                        </div>
                        <div class="text-muted small">
                            <i class="fas fa-tag"></i> ${activity.type || 'Activité'}
                        </div>
                    </div>
                `).join('')
                : '<p class="text-muted">Vous n\'avez pas de réservations en cours.</p>';
        }
    } catch (error) {
        console.error('Erreur lors du chargement des données:', error);
        
        // Afficher un message d'erreur dans l'interface
        const elements = ['upcoming-events', 'my-activities'];
        elements.forEach(id => {
            const element = document.getElementById(id);
            if (element) {
                element.innerHTML = '<p class="text-danger"><i class="fas fa-exclamation-circle"></i> Erreur lors du chargement des données</p>';
            }
        });
    }
}

function toggleEditMode() {
    const inputs = document.querySelectorAll('#profileForm input:not([type="hidden"])');
    const editButtons = document.getElementById('editButtons');
    const isDisabled = inputs[0].disabled;

    inputs.forEach(input => {
        if (input.id !== 'role') { // Ne pas permettre la modification du rôle
            input.disabled = !isDisabled;
        }
    });

    editButtons.style.display = isDisabled ? 'block' : 'none';
}

function showMessage(type, message) {
    let messageDiv = document.getElementById('updateMessage');
    
    // Si le div de message n'existe pas, le créer
    if (!messageDiv) {
        messageDiv = document.createElement('div');
        messageDiv.id = 'updateMessage';
        const form = document.getElementById('profileForm');
        if (form) {
            form.insertBefore(messageDiv, form.firstChild);
        } else {
            document.querySelector('.container')?.prepend(messageDiv);
        }
    }
    
    messageDiv.className = `alert alert-${type}`;
    messageDiv.textContent = message;
    messageDiv.style.display = 'block';
    
    setTimeout(() => {
        messageDiv.style.display = 'none';
    }, 3000);
}

async function updateProfile(event) {
    event.preventDefault();
    
    try {
        const formData = {
            collaborateur_id: document.getElementById('collaborateur_id').value,
            nom: document.getElementById('nom').value,
            prenom: document.getElementById('prenom').value,
            email: document.getElementById('email').value,
            telephone: document.getElementById('telephone').value
        };

        const response = await fetch('/api/employee/updateProfile.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(formData)
        });

        const data = await response.json();
        
        if (!response.ok) throw new Error(data.message || 'Erreur lors de la mise à jour');

        showMessage('success', 'Profil mis à jour avec succès');
        toggleEditMode();

    } catch (error) {
        showMessage('danger', error.message || 'Une erreur est survenue');
    }
}

async function handlePasswordUpdate(event) {
    event.preventDefault();
    
    const currentPassword = document.getElementById('currentPassword').value;
    const newPassword = document.getElementById('newPassword').value;
    const confirmPassword = document.getElementById('confirmPassword').value;
    
    if (newPassword !== confirmPassword) {
        alert('Les nouveaux mots de passe ne correspondent pas');
        return;
    }
    
    try {
        const response = await fetch('/api/employee/updatePassword.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                collaborateur_id: document.querySelector('input[name="collaborateur_id"]').value,
                currentPassword: currentPassword,
                newPassword: newPassword
            })
        });

        const data = await response.json();
        
        if (response.ok) {
            alert('Mot de passe modifié avec succès');
            document.getElementById('passwordForm').reset();
        } else {
            throw new Error(data.message || 'Erreur lors de la modification du mot de passe');
        }
    } catch (error) {
        alert(error.message);
    }
}

// Fonctions pour l'espace conseils
async function loadEmployeeAdvice() {
    try {
        const collaborateurId = getCollaborateurId();
        
        if (!collaborateurId) {
            throw new Error('Collaborateur ID manquant - Veuillez vous connecter');
        }
        
        const response = await fetch(`/api/advice/getAll.php`);
        const advices = await response.json();
        
        if (!response.ok) {
            throw new Error('Erreur lors de la récupération des conseils');
        }
        
        const pendingContainer = document.getElementById('pendingAdvice');
        const answeredContainer = document.getElementById('answeredAdvice');
        
        // Filtrer les conseils pour ce collaborateur
        const myAdvices = advices.filter(advice => Number(advice.id_collaborateur) === Number(collaborateurId));
        
        // Séparer les conseils en attente et répondus
        const pendingAdvices = myAdvices.filter(advice => !advice.reponse);
        const answeredAdvices = myAdvices.filter(advice => advice.reponse);
        
        // Fonction pour formater les dates
        function formatDate(dateString) {
            if (!dateString) return 'N/A';
            const date = new Date(dateString);
            return isNaN(date.getTime()) ? 'Date invalide' : 
                `${date.toLocaleDateString()} à ${date.toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'})}`;
        }
        
        // Afficher les conseils en attente
        if (pendingAdvices.length > 0) {
            pendingContainer.innerHTML = pendingAdvices.map(advice => `
                <div class="list-group-item list-group-item-action">
                    <div class="d-flex w-100 justify-content-between">
                        <h5 class="mb-1">Question: ${advice.question}</h5>
                        <small>Envoyé le: ${formatDate(advice.date_creation)}</small>
                    </div>
                    <div class="mt-3 d-flex justify-content-end">
                        <button class="btn btn-sm btn-danger" onclick="deleteAdvice(${advice.conseil_id})">
                            <i class="fas fa-trash me-1"></i> Supprimer
                        </button>
                    </div>
                </div>
            `).join('');
        } else {
            pendingContainer.innerHTML = '<div class="alert alert-info">Vous n\'avez aucune demande de conseil en attente.</div>';
        }
        
        // Afficher les conseils répondus
        if (answeredAdvices.length > 0) {
            answeredContainer.innerHTML = answeredAdvices.map(advice => `
                <div class="list-group-item">
                    <div class="d-flex w-100 justify-content-between">
                        <h5 class="mb-1">Question: ${advice.question}</h5>
                        <small>Posé le: ${formatDate(advice.date_creation)}</small>
                    </div>
                    <p class="mt-2 mb-1"><strong>Réponse de ${advice.admin_username || 'l\'administration'} (${formatDate(advice.date_reponse)}):</strong></p>
                    <p class="mb-1">${advice.reponse}</p>
                </div>
            `).join('');
        } else {
            answeredContainer.innerHTML = '<div class="alert alert-info">Vous n\'avez aucun conseil répondu pour le moment.</div>';
        }
    } catch (error) {
        console.error('Erreur de chargement des conseils:', error);
        
        const containers = ['pendingAdvice', 'answeredAdvice'];
        containers.forEach(id => {
            const container = document.getElementById(id);
            if (container) {
                container.innerHTML = '<div class="alert alert-danger">Erreur lors du chargement des conseils.</div>';
            }
        });
        
        showAdviceMessage('danger', `Erreur: ${error.message}`);
    }
}

async function submitAdviceRequest() {
    try {
        const questionInput = document.getElementById('question');
        const question = questionInput.value.trim();
        
        if (!question) {
            throw new Error('Veuillez saisir votre question');
        }
        
        const collaborateurId = getCollaborateurId();
        
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
        showAdviceMessage('success', 'Votre demande de conseil a été envoyée avec succès');
        
        // Recharger la liste des conseils
        setTimeout(() => loadEmployeeAdvice(), 1000);
        
    } catch (error) {
        console.error('Erreur d\'envoi de conseil:', error);
        showAdviceMessage('danger', `Erreur: ${error.message}`);
    }
}

async function deleteAdvice(adviceId) {
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
        
        showAdviceMessage('success', 'Demande de conseil supprimée avec succès');
        
        // Recharger la liste des conseils
        setTimeout(() => loadEmployeeAdvice(), 1000);
        
    } catch (error) {
        console.error('Erreur de suppression de conseil:', error);
        showAdviceMessage('danger', `Erreur: ${error.message}`);
    }
}

function showAdviceMessage(type, message) {
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
