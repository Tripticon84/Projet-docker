<?php
$title = "Modifier une société";
include_once "../includes/head.php";
?>

<body>
    <div class="container-fluid">
        <div class="row">
            <?php include_once "../includes/sidebar.php"; ?>
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 main-content">
                <!-- Entête -->
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <div class="d-flex align-items-center">
                        <a href="/backoffice/event/event.php" class="btn btn-link text-decoration-none me-2">
                            <i class="fas fa-arrow-left"></i>
                        </a>
                        <h1 class="h2">Modifier un évènement</h1>
                    </div>
                </div>

                <div id="loadingIndicator" class="text-center my-5">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Chargement...</span>
                    </div>
                    <p class="mt-2">Chargement des données de la société...</p>
                </div>

                <!-- Form -->
                <div id="eventForm" class="card" style="display: none;">
                    <div class="card-header bg-white">
                        <h5 class="card-title mb-0">Informations de l'évènement</h5>
                    </div>
                    <div class="card-body">
                        <form id="modifyEventForm">
                            <input type="hidden" id="evenement_id" name="evenement_id" value="">

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="nom" class="form-label">Nom <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="nom" name="nom" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="date" class="form-label">Date <span class="text-danger">*</span></label>
                                    <input type="date" class="form-control" id="date" name="date" required>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="id_lieu" class="form-label">Lieu <span class="text-danger">*</span></label>
                                    <select class="form-control" id="id_lieu" name="id_lieu" required>
                                        <option value="">Sélectionner un lieu</option>
                                    </select>
                                    <div id="lieu_details" class="mt-2 small text-muted"></div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="type" class="form-label">Type<span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="type" name="type" required>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="statut" class="form-label">Statut <span class="text-danger">*</span></label>
                                    <select class="form-control" id="statut" name="statut" required>
                                        <option value="">Sélectionner un statut</option>
                                        <option value="en_cours">En cours</option>
                                        <option value="a_venir">À venir</option>
                                        <option value="termine">Terminé</option>
                                    </select>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="association" class="form-label">Association</label>
                                    <select class="form-control" id="association" name="association"></select>
                                </div>
                            </div>
                            <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                                <button type="submit" class="btn btn-primary">Enregistrer les modifications</button>
                            </div>
                        </form>
                    </div>
                </div>

                <div id="notFoundMessage" class="alert alert-danger my-4" style="display: none;">
                    <h4 class="alert-heading">Evènement non trouvée</h4>
                    <p>L'évènement demandée n'existe pas ou a été supprimée.</p>
                    <hr>
                    <div class="d-flex justify-content-end">
                        <a href="event.php" class="btn btn-outline-danger">Retour à la liste</a>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Récupération de l'ID de la société depuis l'URL
            const urlParams = new URLSearchParams(window.location.search);
            const eventId = urlParams.get('id');


            if (!eventId) {
                document.getElementById('loadingIndicator').style.display = 'none';
                document.getElementById('notFoundMessage').style.display = 'block';
                return;
            }

            // Chargement des lieux
            loadLocations();

            // Récupération des données de la société
            fetch(`../../api/event/getOne.php?evenement_id=${eventId}`, {
                    headers: {
                        'Authorization': 'Bearer ' + getToken()
                    }
                })
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Event non trouvée');
                    }
                    return response.json();
                })
                .then(event => {
                    if (!event) {
                        throw new Error('Event non trouvée');
                    }
                    console.log(event);

                    // Remplir le formulaire avec les données de la société
                    document.getElementById('evenement_id').value = event.evenement_id;
                    document.getElementById('nom').value = event.nom || '';
                    document.getElementById('date').value = event.date || '';
                    document.getElementById('type').value = event.type || '';
                    document.getElementById('statut').value = event.statut || '';
                    document.getElementById('association').value = event.association || '';

                    // Gestion du lieu si un ID est disponible
                    if (event.id_lieu) {
                        const lieuInterval = setInterval(() => {
                            const lieuSelect = document.getElementById('id_lieu');
                            if (lieuSelect.options.length > 1) {
                                lieuSelect.value = event.id_lieu;
                                showLocationDetails(event.id_lieu);
                                clearInterval(lieuInterval);
                            }
                        }, 100);
                    } else if (event.lieu) {
                        // Pour la compatibilité - si on a seulement le nom du lieu sans ID
                        // Rechercher le lieu correspondant dans la liste
                        const lieuNom = event.lieu;
                        const lieuInterval = setInterval(() => {
                            if (window.locationDetails) {
                                for (const id in window.locationDetails) {
                                    const lieu = window.locationDetails[id];
                                    if (lieu.adresse && lieu.adresse.includes(lieuNom)) {
                                        document.getElementById('id_lieu').value = id;
                                        showLocationDetails(id);
                                        break;
                                    }
                                }
                                clearInterval(lieuInterval);
                            }
                        }, 100);
                    }

                    // Afficher le formulaire
                    document.getElementById('loadingIndicator').style.display = 'none';
                    document.getElementById('eventForm').style.display = 'block';
                })
                .catch(error => {
                    console.error('Erreur:', error);
                    document.getElementById('loadingIndicator').style.display = 'none';
                    document.getElementById('notFoundMessage').style.display = 'block';
                });

            association();
            
            // Écouteur de changement pour lieu sélectionné
            document.getElementById('id_lieu').addEventListener('change', function() {
                showLocationDetails(this.value);
            });

            // Récupération des associations pour le select
            function association() {
                const associationSelect = document.getElementById('association');

                fetch('../../api/association/getAll.php', {
                        headers: {
                            'Authorization': 'Bearer ' + getToken()
                        }
                    })
                    .then(response => response.json())
                    .then(data => {
                        data.forEach(association => {
                            const option = document.createElement('option');
                            option.value = association.id;
                            option.textContent = association.name; // Ajout du nom de l'association
                            associationSelect.appendChild(option);
                        });
                    })
                    .catch(error => {
                        console.error('Erreur lors du chargement des associations:', error);
                        alert('Erreur lors du chargement des associations');
                    });
            }

            // Gestion de la soumission du formulaire
            const form = document.getElementById('modifyEventForm');
            form.addEventListener('submit', function(e) {
                e.preventDefault();

                const formData = new FormData(form);
                const eventData = {};

                formData.forEach((value, key) => {
                    eventData[key] = value;
                    console.log(value);
                });

                fetch('../../api/event/update.php', {
                        method: 'PATCH',
                        headers: {
                            'Content-Type': 'application/json',
                            'Authorization': 'Bearer ' + getToken(),
                        },
                        body: JSON.stringify(eventData),
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (!data.empty) {
                            alert('Event modifiée avec succès!');
                            // Recharger les données pour afficher les modifications
                            window.location.reload();
                        } else {
                            alert('Erreur: ' + (data.error || 'Impossible de modifier l\'event'));
                        }
                    })
                    .catch(error => {
                        console.error('Erreur:', error);
                        alert('Une erreur est survenue lors de la modification de la société.');
                    });
            });
        });

        // Fonction pour charger les lieux depuis l'API
        function loadLocations() {
            const locationSelect = document.getElementById('id_lieu');

            fetch('../../api/place/getAll.php', {
                headers: {
                    'Authorization': 'Bearer ' + getToken()
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data && data.length > 0) {
                    locationSelect.innerHTML = '<option value="">Sélectionnez un lieu</option>';

                    // Stocker les détails du lieu pour affichage ultérieur
                    window.locationDetails = {};

                    data.forEach(location => {
                        const option = document.createElement('option');
                        option.value = location.lieu_id;
                        option.textContent = `${location.adresse}, ${location.ville} (${location.code_postal})`;
                        locationSelect.appendChild(option);

                        // Stocker les détails pour référence
                        window.locationDetails[location.lieu_id] = location;
                    });
                } else {
                    locationSelect.innerHTML = '<option value="" disabled>Aucun lieu disponible</option>';
                }
            })
            .catch(error => {
                console.error('Erreur lors du chargement des lieux:', error);
                locationSelect.innerHTML = '<option value="" disabled>Erreur de chargement</option>';
            });
        }

        // Fonction pour afficher les détails du lieu sélectionné
        function showLocationDetails(locationId) {
            const detailsDiv = document.getElementById('lieu_details');

            if (!locationId || !window.locationDetails || !window.locationDetails[locationId]) {
                detailsDiv.innerHTML = '';
                return;
            }

            const location = window.locationDetails[locationId];
            detailsDiv.innerHTML = `
                <div class="card p-2 bg-light">
                    <p class="m-0"><strong>Adresse complète:</strong> ${location.adresse}</p>
                    <p class="m-0"><strong>Ville:</strong> ${location.ville}</p>
                    <p class="m-0"><strong>Code Postal:</strong> ${location.code_postal}</p>
                </div>
            `;
        }
    </script>

    <style>
        #lieu_details {
            margin-top: 0.5rem;
        }
        #lieu_details .card {
            box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
        }
    </style>
</body>

</html>