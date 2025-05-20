<?php
$title = "Détail de la facture";
include_once "../includes/head.php";
?>

<body>
    <div class="container-fluid">
        <div class="row">
            <?php include_once "../includes/sidebar.php"; ?>
            <!-- Main Content -->
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 main-content">
                <!-- Header avec bouton retour -->
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <div>
                        <a href="invoice.php" class="btn btn-sm btn-outline-secondary me-2">
                            <i class="fas fa-arrow-left"></i> Retour
                        </a>
                        <h1 class="h2 d-inline">Détail de la facture</h1>
                    </div>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <a href="#" id="modifyBtn" class="btn btn-sm btn-outline-success">
                            <i class="fas fa-edit"></i> Modifier
                        </a>
                    </div>
                </div>

                <!-- Alerte pour les messages -->
                <div id="alertContainer"></div>

                <!-- Section principale -->
                <div class="row mt-4" id="invoiceDetailsContainer">
                    <div class="col-md-12 mb-4">
                        <div class="card">
                            <div class="card-header bg-white">
                                <h5 class="card-title mb-0">Informations de la facture <span id="invoiceIdBadge" class="badge bg-secondary ms-2"></span></h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <!-- Informations générales -->
                                    <div class="col-md-6">
                                        <table class="table table-sm">
                                            <tbody>
                                                <tr>
                                                    <th>Date d'émission:</th>
                                                    <td id="dateEmission"></td>
                                                </tr>
                                                <tr>
                                                    <th>Date d'échéance:</th>
                                                    <td id="dateEcheance"></td>
                                                </tr>
                                                <tr>
                                                    <th>Statut:</th>
                                                    <td id="status"></td>
                                                </tr>
                                                <tr>
                                                    <th>Méthode de paiement:</th>
                                                    <td id="paymentMethod"></td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                    <!-- Informations financières -->
                                    <div class="col-md-6">
                                        <table class="table table-sm">
                                            <tbody>
                                                <tr>
                                                    <th>Montant HT:</th>
                                                    <td id="montantHT"></td>
                                                </tr>
                                                <tr>
                                                    <th>Montant TVA:</th>
                                                    <td id="montantTVA"></td>
                                                </tr>
                                                <tr>
                                                    <th>Montant TTC:</th>
                                                    <td id="montantTTC" class="fw-bold"></td>
                                                </tr>
                                                <tr>
                                                    <th>ID Devis:</th>
                                                    <td id="idDevis"></td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Informations du prestataire -->
                    <div class="col-md-6 mb-4">
                        <div class="card h-100">
                            <div class="card-header bg-white">
                                <h5 class="card-title mb-0">Prestataire</h5>
                            </div>
                            <div class="card-body" id="providerInfo">
                                <div class="text-center text-muted py-3">
                                    <div class="spinner-border spinner-border-sm" role="status">
                                        <span class="visually-hidden">Chargement...</span>
                                    </div>
                                    Chargement des données du prestataire...
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Informations de la société -->
                    <div class="col-md-6 mb-4">
                        <div class="card h-100">
                            <div class="card-header bg-white">
                                <h5 class="card-title mb-0">Société</h5>
                            </div>
                            <div class="card-body" id="companyInfo">
                                <div class="text-center text-muted py-3">
                                    <div class="spinner-border spinner-border-sm" role="status">
                                        <span class="visually-hidden">Chargement...</span>
                                    </div>
                                    Chargement des données de la société...
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Frais additionnels -->
                    <div class="col-12 mb-4">
                        <div class="card">
                            <div class="card-header bg-white">
                                <h5 class="card-title mb-0">Frais additionnels</h5>
                            </div>
                            <div class="card-body" id="additionalFeesContainer">
                                <div class="text-center text-muted py-3">
                                    <div class="spinner-border spinner-border-sm" role="status">
                                        <span class="visually-hidden">Chargement...</span>
                                    </div>
                                    Chargement des frais additionnels...
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <!-- Modal pour modifier le statut -->
    <div class="modal fade" id="changeStatusModal" tabindex="-1" aria-labelledby="changeStatusModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="changeStatusModalLabel">Modifier le statut de la facture</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="changeStatusForm">
                        <input type="hidden" id="modalInvoiceId" name="invoiceId">
                        <div class="mb-3">
                            <label for="statusSelect" class="form-label">Nouveau statut</label>
                            <select class="form-select" id="statusSelect" name="status">
                                <option value="Attente">En attente</option>
                                <option value="Payee">Payée</option>
                                <option value="Annulee">Annulée</option>
                            </select>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="button" class="btn btn-primary" id="saveStatusBtn">Enregistrer</button>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Récupérer l'ID de la facture depuis l'URL
            const params = new URLSearchParams(window.location.search);
            const invoiceId = params.get('id');

            if (!invoiceId) {
                showAlert('danger', 'Aucun ID de facture spécifié');
                return;
            }
            
            // Mettre à jour le lien du bouton modifier
            document.getElementById('modifyBtn').href = `modify.php?id=${invoiceId}`;

            // Charger les détails de la facture
            loadInvoiceDetails(invoiceId);

            // Configuration des boutons d'action
            document.getElementById('downloadPdf').addEventListener('click', function(e) {
                e.preventDefault();
                downloadInvoicePDF(invoiceId);
            });

            document.getElementById('changeStatusBtn').addEventListener('click', function(e) {
                e.preventDefault();
                showChangeStatusModal(invoiceId);
            });

            document.getElementById('saveStatusBtn').addEventListener('click', function() {
                const newStatus = document.getElementById('statusSelect').value;
                changeInvoiceStatus(invoiceId, newStatus);
            });
        });

        /**
         * Charge les détails de la facture depuis l'API
         * @param {string} invoiceId - L'identifiant de la facture
         */
        function loadInvoiceDetails(invoiceId) {
            // Appel à l'API pour récupérer les détails de la facture
            fetch(`../../api/invoice/getOne.php?facture_id=${invoiceId}`, {
                headers: {
                    'Authorization': 'Bearer ' + getToken()
                }
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Erreur lors de la récupération des détails de la facture');
                }
                return response.json();
            })
            .then(data => {
                // Afficher les détails de la facture
                displayInvoiceDetails(data);
                
                // Charger les informations associées
                loadProviderInfo(data.id_prestataire);
                loadCompanyInfo(invoiceId);
                loadAdditionalFees(invoiceId);
            })
            .catch(error => {
                console.error('Erreur:', error);
                showAlert('danger', 'Erreur lors du chargement des détails de la facture');
            });
        }

        /**
         * Affiche les détails de la facture dans l'interface
         * @param {Object} invoice - Les données de la facture
         */
        function displayInvoiceDetails(invoice) {
            // Mettre à jour le titre et l'ID
            document.getElementById('invoiceIdBadge').textContent = `#${invoice.facture_id}`;
            
            // Informations générales
            document.getElementById('dateEmission').textContent = formatDate(invoice.date_emission);
            document.getElementById('dateEcheance').textContent = formatDate(invoice.date_echeance);
            
            // Statut avec badge coloré
            const statusElement = document.getElementById('status');
            const statusBadge = getStatusBadge(invoice.statut);
            statusElement.innerHTML = statusBadge;
            
            document.getElementById('paymentMethod').textContent = invoice.methode_paiement || 'Non spécifié';
            
            // Informations financières
            document.getElementById('montantHT').textContent = `${invoice.montant_ht} €`;
            document.getElementById('montantTVA').textContent = `${invoice.montant_tva} €`;
            document.getElementById('montantTTC').textContent = `${invoice.montant} €`;
            document.getElementById('idDevis').textContent = invoice.id_devis;
        }

        /**
         * Charge les informations du prestataire associé à la facture
         * @param {string} providerId - L'identifiant du prestataire
         */
        function loadProviderInfo(providerId) {
            if (!providerId) {
                document.getElementById('providerInfo').innerHTML = 
                    '<div class="alert alert-info">Aucun prestataire associé à cette facture.</div>';
                return;
            }

            // Modifier cette ligne pour utiliser getProviderByInvoice à la place
            const invoiceId = new URLSearchParams(window.location.search).get('id');
            
            fetch(`../../api/invoice/getProviderByInvoice.php?facture_id=${invoiceId}`, {
                headers: {
                    'Authorization': 'Bearer ' + getToken()
                }
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Erreur lors de la récupération des informations du prestataire');
                }
                return response.json();
            })
            .then(data => {
                // Afficher les informations du prestataire
                document.getElementById('providerInfo').innerHTML = `
                    <table class="table table-sm">
                        <tbody>
                            <tr>
                                <th>ID:</th>
                                <td>${data.prestataire_id}</td>
                            </tr>
                            <tr>
                                <th>Nom:</th>
                                <td>${data.nom || 'Non spécifié'} ${data.prenom || ''}</td>
                            </tr>
                            <tr>
                                <th>Email:</th>
                                <td>${data.email || 'Non spécifié'}</td>
                            </tr>
                            <tr>
                                <th>Type:</th>
                                <td>${data.type || 'Non spécifié'}</td>
                            </tr>
                            <tr>
                                <th>Tarif:</th>
                                <td>${data.tarif ? data.tarif + ' €' : 'Non spécifié'}</td>
                            </tr>
                        </tbody>
                    </table>
                `;
            })
            .catch(error => {
                console.error('Erreur:', error);
                document.getElementById('providerInfo').innerHTML = `
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        Erreur lors du chargement des informations du prestataire
                    </div>
                `;
            });
        }

        /**
         * Charge les informations de la société associée à la facture
         * @param {string} invoiceId - L'identifiant de la facture
         */
        function loadCompanyInfo(invoiceId) {
            // Appeler l'API pour récupérer les informations de la société
            fetch(`../../api/invoice/getCompany.php?facture_id=${invoiceId}`, {
                headers: {
                    'Authorization': 'Bearer ' + getToken()
                }
            })
            .then(response => {
                if (!response.ok) {
                    if (response.status === 404) {
                        // Société non trouvée pour cette facture
                        document.getElementById('companyInfo').innerHTML = `
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle me-2"></i>
                                Aucune société associée à cette facture
                            </div>
                        `;
                        return Promise.reject('Aucune société trouvée');
                    }
                    throw new Error('Erreur lors de la récupération des informations de la société');
                }
                return response.json();
            })
            .then(data => {
                // Afficher les informations de la société
                document.getElementById('companyInfo').innerHTML = `
                    <table class="table table-sm">
                        <tbody>
                            <tr>
                                <th>ID:</th>
                                <td>${data.societe_id}</td>
                            </tr>
                            <tr>
                                <th>Nom:</th>
                                <td>${data.nom || 'Non spécifié'}</td>
                            </tr>
                            <tr>
                                <th>SIRET:</th>
                                <td>${data.siret || 'Non spécifié'}</td>
                            </tr>
                            <tr>
                                <th>Email:</th>
                                <td>${data.email || 'Non spécifié'}</td>
                            </tr>
                            <tr>
                                <th>Adresse:</th>
                                <td>${data.adresse || 'Non spécifiée'}</td>
                            </tr>
                            <tr>
                                <th>Contact:</th>
                                <td>${data.contact_person || 'Non spécifié'}</td>
                            </tr>
                        </tbody>
                    </table>
                `;
            })
            .catch(error => {
                if (error === 'Aucune société trouvée') {
                    // Déjà géré ci-dessus
                    return;
                }
                console.error('Erreur:', error);
                document.getElementById('companyInfo').innerHTML = `
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        Erreur lors du chargement des informations de la société
                    </div>
                `;
            });
        }

        /**
         * Charge les frais additionnels associés à la facture
         * @param {string} invoiceId - L'identifiant de la facture
         */
        function loadAdditionalFees(invoiceId) {
            // Appeler l'API pour récupérer les frais additionnels
            fetch(`../../api/invoice/getOtherFees.php?facture_id=${invoiceId}`, {
                headers: {
                    'Authorization': 'Bearer ' + getToken()
                }
            })
            .then(response => {
                if (!response.ok) {
                    if (response.status === 404) {
                        // Pas de frais trouvés
                        document.getElementById('additionalFeesContainer').innerHTML = `
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle me-2"></i>
                                Aucun frais additionnel associé à cette facture
                            </div>
                        `;
                        return Promise.reject('Aucun frais trouvé');
                    }
                    throw new Error('Erreur lors de la récupération des frais additionnels');
                }
                return response.json();
            })
            .then(data => {
                if (!data || data.length === 0) {
                    document.getElementById('additionalFeesContainer').innerHTML = `
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i>
                            Aucun frais additionnel associé à cette facture
                        </div>
                    `;
                    return;
                }

                // Construire le tableau des frais
                let tableHtml = `
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Nom</th>
                                    <th>Description</th>
                                    <th>Montant</th>
                                    <th>Type</th>
                                </tr>
                            </thead>
                            <tbody>
                `;

                data.forEach(fee => {
                    tableHtml += `
                        <tr>
                            <td>${fee.nom || 'Non spécifié'}</td>
                            <td>${fee.description || 'Non spécifiée'}</td>
                            <td>${fee.montant ? fee.montant + ' €' : 'Non spécifié'}</td>
                            <td>${fee.est_abonnement ? 'Abonnement' : 'Ponctuel'}</td>
                        </tr>
                    `;
                });

                tableHtml += `
                            </tbody>
                        </table>
                    </div>
                `;

                document.getElementById('additionalFeesContainer').innerHTML = tableHtml;
            })
            .catch(error => {
                if (error === 'Aucun frais trouvé') {
                    // Déjà géré ci-dessus
                    return;
                }
                console.error('Erreur:', error);
                document.getElementById('additionalFeesContainer').innerHTML = `
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        Erreur lors du chargement des frais additionnels
                    </div>
                `;
            });
        }

        /**
         * Télécharge le PDF de la facture
         * @param {string} invoiceId - L'identifiant de la facture
         */
        function downloadInvoicePDF(invoiceId) {
            // Dans un environnement réel, rediriger vers l'API qui génère le PDF
            window.open(`../../api/invoice/generatePDF.php?facture_id=${invoiceId}`, '_blank');
            
            // Afficher un message de succès
            showAlert('success', 'Téléchargement du PDF lancé');
        }

        /**
         * Affiche le modal pour changer le statut de la facture
         * @param {string} invoiceId - L'identifiant de la facture
         */
        function showChangeStatusModal(invoiceId) {
            document.getElementById('modalInvoiceId').value = invoiceId;
            
            // Récupérer le statut actuel depuis l'élément HTML
            const statusText = document.getElementById('status').textContent.trim();
            let currentStatus = 'Attente';
            
            if (statusText.includes('Payée')) {
                currentStatus = 'Payee';
            } else if (statusText.includes('Annulée')) {
                currentStatus = 'Annulee';
            }
            
            document.getElementById('statusSelect').value = currentStatus;
            
            // Afficher le modal
            const modal = new bootstrap.Modal(document.getElementById('changeStatusModal'));
            modal.show();
        }

        /**
         * Change le statut d'une facture via l'API
         * @param {string} invoiceId - L'identifiant de la facture
         * @param {string} status - Le nouveau statut
         */
        function changeInvoiceStatus(invoiceId, status) {
            fetch('../../api/invoice/modifyState.php', {
                method: 'PATCH',
                headers: {
                    'Content-Type': 'application/json',
                    'Authorization': 'Bearer ' + getToken()
                },
                body: JSON.stringify({
                    facture_id: invoiceId,
                    statut: status
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.message && data.message.includes('Modified')) {
                    // Fermer le modal
                    const modal = bootstrap.Modal.getInstance(document.getElementById('changeStatusModal'));
                    modal.hide();
                    
                    // Montrer un message de succès
                    showAlert('success', 'Statut de la facture modifié avec succès');
                    
                    // Actualiser les détails de la facture
                    loadInvoiceDetails(invoiceId);
                } else {
                    showAlert('danger', 'Erreur lors de la modification du statut');
                }
            })
            .catch(error => {
                console.error('Erreur:', error);
                showAlert('danger', 'Une erreur est survenue lors de la modification du statut');
            });
        }

        /**
         * Affiche une alerte dans l'interface utilisateur
         * @param {string} type - Type d'alerte (success, danger, warning, info)
         * @param {string} message - Message à afficher
         */
        function showAlert(type, message) {
            const alertContainer = document.getElementById('alertContainer');
            const alert = document.createElement('div');
            alert.className = `alert alert-${type} alert-dismissible fade show`;
            alert.innerHTML = `
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            `;
            alertContainer.innerHTML = '';
            alertContainer.appendChild(alert);
            
            // Auto-fermer l'alerte après 5 secondes
            setTimeout(() => {
                alert.classList.remove('show');
                setTimeout(() => {
                    alert.remove();
                }, 150);
            }, 5000);
        }

        /**
         * Formate une date au format français
         * @param {string} dateStr - Chaîne de date à formater
         * @return {string} Date formatée ou texte par défaut
         */
        function formatDate(dateStr) {
            if (!dateStr) return 'Non spécifiée';
            const date = new Date(dateStr);
            return date.toLocaleDateString('fr-FR');
        }

        /**
         * Génère un badge HTML pour un statut
         * @param {string} status - Le statut de la facture
         * @return {string} HTML du badge
         */
        function getStatusBadge(status) {
            let badgeClass, statusText;
            
            switch(status) {
                case 'Payee':
                    badgeClass = 'bg-success';
                    statusText = 'Payée';
                    break;
                case 'Annulee':
                    badgeClass = 'bg-danger';
                    statusText = 'Annulée';
                    break;
                default:
                    badgeClass = 'bg-warning';
                    statusText = 'En attente';
            }
            
            return `<span class="badge ${badgeClass}">${statusText}</span>`;
        }
    </script>
</body>
</html>
