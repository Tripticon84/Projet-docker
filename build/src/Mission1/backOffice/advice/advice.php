<?php
$title = "Gestion des conseils";
include_once "../includes/head.php";

// Vérification de la session après inclusion de head.php
if (!isset($_SESSION['admin_id'])) {
    header('Location: ../login.php');
    exit;
}
?>

<body>
    <div class="container-fluid">
        <div class="row">
            <?php include_once "../includes/sidebar.php"; ?>
            <!-- Main Content -->
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 main-content">
                <!-- Header -->
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">Gestion des conseils</h1>
                    <!-- Suppression du bouton "Nouveau conseil" car seuls les collaborateurs peuvent créer des conseils -->
                </div>

                <!-- Main Advice Table -->
                <div class="card mt-4">
                    <div class="card-header bg-white d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0">Liste des demandes de conseil</h5>
                        <div class="card-tools">
                            <span class="badge bg-info">Les conseils sont créés par les collaborateurs</span>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th scope="col" width="40">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" id="selectAll">
                                            </div>
                                        </th>
                                        <th scope="col">Question</th>
                                        <th scope="col">Collaborateur</th>
                                        <th scope="col">Date</th>
                                        <th scope="col">État</th>
                                        <th scope="col" class="text-end" width="80">Actions</th>
                                    </tr>
                                </thead>
                                <tbody id="adviceList">
                                    <!-- Les conseils seront insérés ici par JavaScript -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

            </main>
        </div>
    </div>
    <script>
        // Fetch Advice List
        fetch('../../api/advice/getAll.php')
            .then(response => response.json())
            .then(data => {
                const adviceList = document.getElementById('adviceList');
                data.forEach(advice => {
                    const row = document.createElement('tr');
                    
                    // Déterminer l'état du conseil
                    const status = advice.reponse ? 
                        '<span class="badge bg-success">Répondu</span>' : 
                        '<span class="badge bg-warning">En attente</span>';
                    
                    // Formatage de la date
                    const dateCreation = new Date(advice.date_creation);
                    const formattedDate = dateCreation.toLocaleDateString('fr-FR') + ' ' + dateCreation.toLocaleTimeString('fr-FR', {hour: '2-digit', minute:'2-digit'});
                    
                    row.innerHTML = `
                        <td>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox">
                            </div>
                        </td>
                        <td>
                            <div class="advice-card">
                                <div class="advice-info">
                                    <div class="advice-question">${advice.question.substring(0, 50)}${advice.question.length > 50 ? '...' : ''}</div>
                                    <div class="advice-id">ID-${advice.conseil_id}</div>
                                </div>
                            </div>
                        </td>
                        <td>${advice.collaborateur_prenom} ${advice.collaborateur_nom}</td>
                        <td>${formattedDate}</td>
                        <td>${status}</td>
                        <td class="text-end">
                            <div class="dropdown">
                                <button class="btn btn-sm btn-outline-secondary" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                    <i class="fas fa-ellipsis-v"></i>
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end">
                                    <li><a class="dropdown-item" href="edit.php?id=${advice.conseil_id}"><i class="fas fa-edit me-2"></i>Modifier</a></li>
                                    ${!advice.reponse ? 
                                        `<li><a class="dropdown-item" href="answer.php?id=${advice.conseil_id}"><i class="fas fa-reply me-2"></i>Répondre</a></li>` : 
                                        ''
                                    }
                                    <li><a class="dropdown-item text-danger" href="#" onclick="deleteAdvice(${advice.conseil_id})"><i class="fas fa-trash me-2"></i>Supprimer</a></li>
                                </ul>
                            </div>
                        </td>
                    `;
                    adviceList.appendChild(row);
                });
            })
            .catch(error => console.error('Erreur lors de la récupération des conseils:', error));

        function deleteAdvice(adviceId) {
            if (confirm('Êtes-vous sûr de vouloir supprimer ce conseil ?')) {
                fetch('../../api/advice/delete.php', {
                        method: 'DELETE',
                        headers: {
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify({
                            conseil_id: adviceId
                        })
                    })
                    .then(response => response.json())
                    .then(data => {
                        // Vérifie si la réponse contient un message contenant "succès"
                        if (data.message && data.message.includes('succès')) {
                            alert('Conseil supprimé avec succès.');
                            location.reload();
                        } else {
                            alert('Erreur lors de la suppression. Veuillez réessayer.');
                        }
                    })
                    .catch(error => {
                        console.error('Erreur:', error);
                        alert('Une erreur est survenue lors de la suppression.');
                    });
            }
        }
    </script>
    <style>
        .advice-card {
            display: flex;
            align-items: center;
            padding: 8px 0;
        }

        .advice-info {
            display: flex;
            flex-direction: column;
        }

        .advice-question {
            font-weight: 600;
            color: #333;
        }

        .advice-id {
            font-size: 0.8rem;
            color: #6c757d;
        }

        .table tr:hover {
            background-color: rgba(0, 123, 255, 0.05);
        }
    </style>
</body>

</html>
