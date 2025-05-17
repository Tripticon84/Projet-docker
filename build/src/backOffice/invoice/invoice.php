<?php
$title = "Gestion des Factures";
include_once "../includes/head.php";
?>

<body>
    <div class="container-fluid">
        <div class="row">
            <?php include_once "../includes/sidebar.php"; ?>
            <!-- Main Content -->
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 main-content">
                <!-- Header -->
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">Gestion des Factures</h1>
                </div>

                <!-- Pending Invoices Table -->
                <div class="card mt-4">
                    <div class="card-header bg-white d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0">Factures en attente</h5>
                        <div class="d-flex">
                            <div class="dropdown me-2">
                                <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="sortDropdown">
                                    <li><a class="dropdown-item" href="#">Date (récent)</a></li>
                                    <li><a class="dropdown-item" href="#">Date (ancien)</a></li>
                                    <li><a class="dropdown-item" href="#">Montant (croissant)</a></li>
                                    <li><a class="dropdown-item" href="#">Montant (décroissant)</a></li>
                                </ul>
                            </div>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th scope="col">ID</th>
                                        <th scope="col">Prestataire</th>
                                        <th scope="col">Date émission</th>
                                        <th scope="col">Échéance</th>
                                        <th scope="col">Montant TTC</th>
                                        <th scope="col">Statut</th>
                                        <th scope="col" class="text-end">Actions</th>
                                    </tr>
                                </thead>
                                <tbody id="pendingInvoicesList">
                                    <!-- Les factures en attente seront insérées ici par JavaScript -->
                                    <tr>
                                        <td colspan="7" class="text-center">Chargement des factures...</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- All Invoices Table -->
                <div class="card mt-4">
                    <div class="card-header bg-white d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0">Toutes les factures</h5>
                        <div class="d-flex mt-2 mt-sm-0 align-items-center">
                            <a type="button" class="btn btn-sm btn-primary me-2" href="create.php">
                                <i class="fas fa-plus"></i> Créer une facture
                            </a>
                            <div class="dropdown">
                                <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="sortDropdownAll">
                                    <li><a class="dropdown-item" href="#">Date (récent)</a></li>
                                    <li><a class="dropdown-item" href="#">Date (ancien)</a></li>
                                    <li><a class="dropdown-item" href="#">Montant (croissant)</a></li>
                                    <li><a class="dropdown-item" href="#">Montant (décroissant)</a></li>
                                </ul>
                            </div>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th scope="col">ID</th>
                                        <th scope="col">Prestataire</th>
                                        <th scope="col">Date émission</th>
                                        <th scope="col">Échéance</th>
                                        <th scope="col">Montant TTC</th>
                                        <th scope="col">Statut</th>
                                        <th scope="col" class="text-end">Actions</th>
                                    </tr>
                                </thead>
                                <tbody id="allInvoicesList">
                                    <!-- Toutes les factures seront insérées ici par JavaScript -->
                                    <tr>
                                        <td colspan="7" class="text-center">Chargement des factures...</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="card-footer bg-white d-flex justify-content-between align-items-center">
                        <div>
                            <span class="text-muted small" id="paginationInfo">Chargement des données...</span>
                        </div>
                        <nav aria-label="Table navigation">
                            <ul class="pagination pagination-sm mb-0" id="paginationList"></ul>
                        </nav>
                    </div>
                </div>

                <!-- Quick Action Cards -->
                <div class="row mt-4">
                    <div class="col-12">
                        <h5>Actions rapides</h5>
                    </div>
                    <div class="col-md-4">
                        <div class="card text-center p-3 mb-3">
                            <div class="mb-3">
                                <i class="fas fa-file-invoice fa-2x text-primary"></i>
                            </div>
                            <h6>Créer une facture</h6>
                            <a href="create.php" class="btn btn-sm btn-outline-primary mt-2">Nouvelle facture</a>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card text-center p-3 mb-3">
                            <div class="mb-3">
                                <i class="fas fa-file-export fa-2x text-success"></i>
                            </div>
                            <h6>Exporter les factures</h6>
                            <a href="#" class="btn btn-sm btn-outline-success mt-2">Exporter</a>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card text-center p-3 mb-3">
                            <div class="mb-3">
                                <i class="fas fa-chart-bar fa-2x text-warning"></i>
                            </div>
                            <h6>Statistiques des factures</h6>
                            <a href="#" class="btn btn-sm btn-outline-warning mt-2">Voir stats</a>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <!-- Modal pour changer le statut d'une facture -->
    <div class="modal fade" id="changeStatusModal" tabindex="-1" aria-labelledby="changeStatusModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="changeStatusModalLabel">Modifier le statut de la facture</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="changeStatusForm">
                        <input type="hidden" id="invoiceId" name="invoiceId">
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
            fetchPendingInvoices();
            fetchAllInvoices();

            // Écouteur pour le bouton de sauvegarde du statut
            document.getElementById('saveStatusBtn').addEventListener('click', function() {
                const invoiceId = document.getElementById('invoiceId').value;
                const status = document.getElementById('statusSelect').value;
                changeInvoiceStatus(invoiceId, status);
            });
        });

        function fetchPendingInvoices() {
            const pendingInvoicesList = document.getElementById('pendingInvoicesList');
            pendingInvoicesList.innerHTML = '<tr><td colspan="7" class="text-center">Chargement des factures...</td></tr>';

            fetch('../../api/invoice/getAll.php', {
                headers: {
                    'Authorization': 'Bearer ' + getToken()
                }
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Erreur lors de la récupération des factures');
                }
                return response.json();
            })
            .then(data => {
                pendingInvoicesList.innerHTML = '';
                if (data && data.length > 0) {
                    // Filtrer uniquement les factures en attente
                    const pendingInvoices = data.filter(invoice => invoice.statut === 'Attente');

                    if (pendingInvoices.length > 0) {
                        pendingInvoices.forEach(invoice => {
                            displayInvoiceRow(invoice, pendingInvoicesList);
                        });
                    } else {
                        pendingInvoicesList.innerHTML = '<tr><td colspan="7" class="text-center">Aucune facture en attente</td></tr>';
                    }
                } else {
                    pendingInvoicesList.innerHTML = '<tr><td colspan="7" class="text-center">Aucune facture trouvée</td></tr>';
                }
            })
            .catch(error => {
                console.error('Erreur:', error);
                pendingInvoicesList.innerHTML = '<tr><td colspan="7" class="text-center text-danger">Erreur lors du chargement des factures</td></tr>';
            });
        }

        let currentPage = 1;

        function fetchAllInvoices(page = 1) {
            currentPage = page;
            const allInvoicesList = document.getElementById('allInvoicesList');
            allInvoicesList.innerHTML = '<tr><td colspan="7" class="text-center">Chargement des factures...</td></tr>';

            let limit = 5;
            let offset = (page - 1) * limit;
            let url = `../../api/invoice/getAll.php?limit=${limit}&offset=${offset}`;

            fetch(url, {
            headers: {
                'Authorization': 'Bearer ' + getToken()
            }
            })
            .then(response => {
            if (!response.ok) {
                throw new Error('Erreur lors de la récupération des factures');
            }
            return response.json();
            })
            .then(data => {
            allInvoicesList.innerHTML = '';
            if (data && data.length > 0) {
                // Exclure les factures avec le statut 'Attente'
                const filteredInvoices = data.filter(invoice => invoice.statut !== 'Attente');

                if (filteredInvoices.length > 0) {
                filteredInvoices.forEach(invoice => {
                    displayInvoiceRow(invoice, allInvoicesList);
                });

                document.getElementById('paginationInfo').textContent = `Affichage de 1-${filteredInvoices.length} factures`;
                updatePagination(data.length === limit);
                } else {
                allInvoicesList.innerHTML = '<tr><td colspan="7" class="text-center">Aucune facture trouvée</td></tr>';
                document.getElementById('paginationInfo').textContent = 'Aucune facture trouvée';
                }
            } else {
                allInvoicesList.innerHTML = '<tr><td colspan="7" class="text-center">Aucune facture trouvée</td></tr>';
                document.getElementById('paginationInfo').textContent = 'Aucune facture trouvée';
            }
            })
            .catch(error => {
            console.error('Erreur:', error);
            allInvoicesList.innerHTML = '<tr><td colspan="7" class="text-center text-danger">Erreur lors du chargement des factures</td></tr>';
            document.getElementById('paginationInfo').textContent = 'Erreur lors du chargement des données';
            });
        }

        function displayInvoiceRow(invoice, container) {
            const row = document.createElement('tr');

            // Définir la classe de la ligne en fonction du statut
            if (invoice.statut === 'Payee') {
                row.classList.add('table-success');
            } else if (invoice.statut === 'Annulee') {
                row.classList.add('table-danger');
            } else if (isLateInvoice(invoice.date_echeance)) {
                row.classList.add('table-warning');
            }

            row.innerHTML = `
                <td>${invoice.facture_id}</td>
                <td>${invoice.id_prestataire}</td>
                <td>${formatDate(invoice.date_emission)}</td>
                <td>${formatDate(invoice.date_echeance)}</td>
                <td>${invoice.montant} €</td>
                <td>
                    <span class="badge ${getStatusBadgeClass(invoice.statut)}">${getStatusLabel(invoice.statut)}</span>
                </td>
                <td class="text-end">
                    <div class="dropdown">
                        <button class="btn btn-sm btn-outline-secondary" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="fas fa-ellipsis-v"></i>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item" href="#" onclick="showChangeStatusModal(${invoice.facture_id}, '${invoice.statut}'); return false;"><i class="fas fa-exchange-alt me-2"></i>Changer statut</a></li>
                            <li><a class="dropdown-item" href="modify.php?id=${invoice.facture_id}"><i class="fas fa-edit me-2"></i>Modifier</a></li>
                            <li><a class="dropdown-item" href="#" onclick="viewDetails(${invoice.facture_id}); return false;"><i class="fas fa-eye me-2"></i>Voir détails</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item text-danger" href="#" onclick="downloadPdf(${invoice.facture_id}); return false;"><i class="fas fa-file-pdf me-2"></i>Télécharger PDF</a></li>
                        </ul>
                    </div>
                </td>
            `;
            container.appendChild(row);
        }

        function updatePagination(hasMore) {
            const paginationList = document.getElementById('paginationList');
            paginationList.innerHTML = '';
            // Bouton précédent
            let prevItem = document.createElement('li');
            prevItem.className = 'page-item ' + (currentPage === 1 ? 'disabled' : '');
            prevItem.innerHTML = `<a class="page-link" href="#" onclick="fetchAllInvoices(${currentPage - 1}); return false;">Précédent</a>`;
            paginationList.appendChild(prevItem);
            // Bouton suivant
            let nextItem = document.createElement('li');
            nextItem.className = 'page-item ' + (!hasMore ? 'disabled' : '');
            nextItem.innerHTML = `<a class="page-link" href="#" onclick="fetchAllInvoices(${currentPage + 1}); return false;">Suivant</a>`;
            paginationList.appendChild(nextItem);
        }

        function showChangeStatusModal(invoiceId, currentStatus) {
            document.getElementById('invoiceId').value = invoiceId;
            document.getElementById('statusSelect').value = currentStatus;

            const modal = new bootstrap.Modal(document.getElementById('changeStatusModal'));
            modal.show();
        }

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
                    alert('Statut de la facture modifié avec succès.');

                    // Fermer le modal
                    const modal = bootstrap.Modal.getInstance(document.getElementById('changeStatusModal'));
                    modal.hide();

                    // Rafraîchir les listes de factures
                    fetchPendingInvoices();
                    fetchAllInvoices();
                } else {
                    alert('Erreur lors de la modification du statut. Veuillez réessayer.');
                }
            })
            .catch(error => {
                console.error('Erreur:', error);
                alert('Une erreur est survenue lors de la modification du statut.');
            });
        }

        function viewDetails(invoiceId) {
            // Rediriger vers la page de détails avec l'ID de la facture
            window.location.href = `details.php?id=${invoiceId}`;
        }

        function downloadPdf(invoiceId) {
            // Simuler un téléchargement de PDF
            alert('Téléchargement du PDF de la facture #' + invoiceId);
        }

        function getStatusBadgeClass(status) {
            switch(status) {
                case 'Payee': return 'bg-success';
                case 'Annulee': return 'bg-danger';
                default: return 'bg-warning';
            }
        }

        function getStatusLabel(status) {
            switch(status) {
                case 'Payee': return 'Payée';
                case 'Annulee': return 'Annulée';
                default: return 'En attente';
            }
        }

        function isLateInvoice(dueDate) {
            if (!dueDate) return false;
            const today = new Date();
            const due = new Date(dueDate);
            return due < today;
        }

        function formatDate(dateStr) {
            if (!dateStr) return '-';
            const date = new Date(dateStr);
            return date.toLocaleDateString('fr-FR');
        }
    </script>

    <style>
        .table tr:hover {
            background-color: rgba(0, 123, 255, 0.05);
        }
    </style>
</body>
</html>
