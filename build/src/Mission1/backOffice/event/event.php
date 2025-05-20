<?php
$title = "Gestion des Événements";
include_once "../includes/head.php";
?>

<body>
    <div class="container-fluid">
        <div class="row">
            <?php include_once "../includes/sidebar.php"; ?>
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 main-content">
                <!-- Header -->
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">Gestion des Événements</h1>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <a href="create.php" class="btn btn-sm btn-primary">
                            <i class="fas fa-plus"></i> Ajouter un événement
                        </a>
                    </div>
                </div>

                <!-- Statistiques rapides -->
                <div class="row mb-4">
                    <div class="col-md-4">
                        <div class="stat-card">
                            <div class="stat-icon bg-primary-subtle text-primary">
                                <i class="fas fa-calendar"></i>
                            </div>
                            <p class="text-muted mb-0">Événements totaux</p>
                            <h3 id="eventsCount">-</h3>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="stat-card">
                            <div class="stat-icon bg-success-subtle text-success">
                                <i class="fas fa-calendar-check"></i>
                            </div>
                            <p class="text-muted mb-0">Événements à venir</p>
                            <h3 id="upcomingEventsCount">-</h3>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="stat-card">
                            <div class="stat-icon bg-info-subtle text-info">
                                <i class="fas fa-users"></i>
                            </div>
                            <p class="text-muted mb-0">Participants totaux</p>
                            <h3 id="participantsCount">-</h3>
                        </div>
                    </div>
                </div>

                <!-- Events Table -->
                <div class="card mt-4">
                    <div class="card-header bg-white d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0">Liste des Événements</h5>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th scope="col">ID</th>
                                        <th scope="col">Nom</th>
                                        <th scope="col">Date</th>
                                        <th scope="col">Lieu</th>
                                        <th scope="col">Type</th>
                                        <th scope="col">Statut</th>
                                        <th scope="col">Association</th>
                                        <th scope="col" class="text-end">Actions</th>
                                    </tr>
                                </thead>
                                <tbody id="eventsList">
                                    <tr>
                                        <td colspan="8" class="text-center">Chargement des événements...</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="card-footer bg-white d-flex justify-content-between align-items-center">
                        <nav aria-label="Table navigation">
                            <ul class="pagination pagination-sm mb-0" id="paginationList"></ul>
                        </nav>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <!-- Modal pour les participants -->
    <div class="modal fade" id="participantsModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="participantsModalLabel">Participants à l'événement</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th scope="col">ID</th>
                                    <th scope="col">Nom</th>
                                    <th scope="col">Prénom</th>
                                    <th scope="col">Email</th>
                                    <th scope="col">Téléphone</th>
                                    <th scope="col">Actions</th>
                                </tr>
                            </thead>
                            <tbody id="participantsList">
                                <!-- Les participants seront insérés ici par JavaScript -->
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fermer</button>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            fetchEvents();
            fetchEventStats();
        });

        function fetchEvents() {
            fetch('../../api/event/getAll.php', {
                headers: {
                    'Authorization': 'Bearer ' + getToken()
                }
            })
            .then(response => response.json())
            .then(data => {
                const eventsList = document.getElementById('eventsList');
                eventsList.innerHTML = '';

                if (data.length > 0) {
                    data.forEach(event => {
                        const row = document.createElement('tr');
                        row.innerHTML = `
                            <td>${event.evenement_id}</td>
                            <td>${event.nom}</td>
                            <td>${formatDate(event.date)}</td>
                            <td>${event.lieu || '-'}</td>
                            <td>${event.type || '-'}</td>
                            <td><span class="badge bg-${getStatusBadgeClass(event.statut)}">${event.statut}</span></td>
                            <td>${event.id_association || '-'}</td>
                            <td class="text-end">
                                <button class="btn btn-sm btn-outline-primary me-1" onclick="viewParticipants(${event.evenement_id})">
                                    <i class="fas fa-users"></i>
                                </button>
                                <a href="modify.php?id=${event.evenement_id}" class="btn btn-sm btn-outline-secondary me-1">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <button class="btn btn-sm btn-outline-danger" onclick="deleteEvent(${event.evenement_id})">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </td>
                        `;
                        eventsList.appendChild(row);
                    });
                } else {
                    eventsList.innerHTML = '<tr><td colspan="8" class="text-center">Aucun événement trouvé</td></tr>';
                }
            })
            .catch(error => {
                console.error('Erreur:', error);
                document.getElementById('eventsList').innerHTML =
                    '<tr><td colspan="8" class="text-center text-danger">Erreur lors du chargement des événements</td></tr>';
            });
        }

        function fetchEventStats() {
            fetch('../../api/event/getStats.php', {
                headers: {
                    'Authorization': 'Bearer ' + getToken()
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    document.getElementById('eventsCount').textContent = data.stats.total;
                    document.getElementById('upcomingEventsCount').textContent = data.stats.upcoming;
                    document.getElementById('participantsCount').textContent = data.stats.participants;
                } else {
                    console.error('Erreur:', data.error);
                }
            })
            .catch(error => {
                console.error('Erreur:', error);
                document.getElementById('eventsCount').textContent = 'Erreur';
                document.getElementById('upcomingEventsCount').textContent = 'Erreur';
                document.getElementById('participantsCount').textContent = 'Erreur';
            });
        }

        function getStatusBadgeClass(status) {
            switch(status) {
                case 'en_cours': return 'primary';
                case 'a_venir': return 'success';
                case 'termine': return 'secondary';
                default: return 'secondary';
            }
        }

        function formatDate(dateStr) {
            if (!dateStr) return '-';
            return new Date(dateStr).toLocaleDateString('fr-FR');
        }

        function viewParticipants(eventId) {
            const participantsList = document.getElementById('participantsList');
            participantsList.innerHTML = '<tr><td colspan="6" class="text-center">Chargement des participants...</td></tr>';

            const modal = new bootstrap.Modal(document.getElementById('participantsModal'));
            modal.show();

            fetch(`../../api/event/getParticipants.php?id=${eventId}`, {
                headers: {
                    'Authorization': 'Bearer ' + getToken()
                }
            })
            .then(response => response.json())
            .then(data => {
                participantsList.innerHTML = '';
                if (data && data.length > 0) {
                    data.forEach(participant => {
                        const row = document.createElement('tr');
                        row.innerHTML = `
                            <td>${participant.collaborateur_id}</td>
                            <td>${participant.nom || '-'}</td>
                            <td>${participant.prenom || '-'}</td>
                            <td>${participant.email || '-'}</td>
                            <td>${participant.telephone || '-'}</td>
                            <td>
                                <button class="btn btn-sm btn-outline-danger" onclick="removeParticipant(${eventId}, ${participant.collaborateur_id})">
                                    <i class="fas fa-user-minus"></i> Retirer
                                </button>
                            </td>
                        `;
                        participantsList.appendChild(row);
                    });
                } else {
                    participantsList.innerHTML = '<tr><td colspan="6" class="text-center">Aucun participant trouvé pour cet événement</td></tr>';
                }
            })
            .catch(error => {
                console.error('Erreur:', error);
                participantsList.innerHTML = '<tr><td colspan="6" class="text-center text-danger">Erreur lors du chargement des participants</td></tr>';
            });
        }

        function removeParticipant(eventId, collaboratorId) {
            if (confirm('Êtes-vous sûr de vouloir retirer ce participant de l\'événement ?')) {
                const data = {
                    id_evenement: parseInt(eventId),
                    id_collaborateur: parseInt(collaboratorId)
                };
                console.log('Sending data:', data); // Debug log

                fetch('../../api/event/removeParticipant.php', {
                    method: 'DELETE',
                    headers: {
                        'Content-Type': 'application/json',
                        'Authorization': 'Bearer ' + getToken()
                    },
                    body: JSON.stringify(data)
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('Participant retiré avec succès.');
                        viewParticipants(eventId);
                    } else {
                        alert('Erreur: ' + (data.error || 'Erreur inconnue'));
                        console.error('Error data:', data);
                    }
                })
                .catch(error => {
                    console.error('Erreur:', error);
                    alert('Une erreur est survenue lors du retrait du participant.');
                });
            }
        }

        function deleteEvent(eventId) {
            if (confirm('Êtes-vous sûr de vouloir supprimer cet événement ? Cette action est irréversible.')) {
                fetch('../../api/event/delete.php', {
                    method: 'DELETE',
                    headers: {
                        'Content-Type': 'application/json',
                        'Authorization': 'Bearer ' + getToken()
                    },
                    body: JSON.stringify({ 'evenement_id': eventId })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.evenement_id) {
                        alert('Événement supprimé avec succès.');
                        fetchEvents(); // Refresh the events list
                    } else {
                        alert('Erreur: ' + (data.error || 'Erreur inconnue'));
                        console.error('Error data:', data);
                    }
                })
                .catch(error => {
                    console.error('Erreur:', error);
                    alert('Une erreur est survenue lors de la suppression de l\'événement.');
                });
            }
        }
    </script>

    <style>
        .stat-card {
            position: relative;
            padding: 1rem;
            background-color: white;
            border-radius: 0.5rem;
            box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
            margin-bottom: 1rem;
            overflow: hidden;
        }

        .stat-icon {
            position: absolute;
            top: 1rem;
            right: 1rem;
            width: 2.5rem;
            height: 2.5rem;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.25rem;
        }

        .stat-card h3 {
            font-size: 1.5rem;
            font-weight: 700;
            margin-bottom: 0.25rem;
            margin-top: 0.5rem;
        }

        .table tr:hover {
            background-color: rgba(0, 123, 255, 0.05);
        }
    </style>
</body>
</html>
