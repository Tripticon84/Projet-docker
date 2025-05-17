<?php
// La session est déjà démarrée dans head.php
$title = "Modifier un conseil";
include_once "../includes/head.php";

// Removed login redirect that was causing 404 error

// Vérifier si un ID est fourni
if (!isset($_GET['id']) || empty($_GET['id'])) {
    echo '<div class="container-fluid px-4"><div class="alert alert-danger">ID de conseil non spécifié.</div></div>';
    exit;
}

$advice_id = $_GET['id'];
?>

<body>
    <div class="container-fluid">
        <div class="row">
            <?php include_once "../includes/sidebar.php"; ?>
            <!-- Main Content -->
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 main-content">
                <!-- Header -->
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">Modifier un conseil</h1>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <a type="button" class="btn btn-sm btn-secondary" href="advice.php">
                            <i class="fas fa-arrow-left"></i> Retour à la liste
                        </a>
                    </div>
                </div>

                <!-- Edit Form Card -->
                <div class="card mt-4">
                    <div class="card-header bg-white">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-edit me-2"></i>
                            Modifier le conseil #<span id="adviceId"><?= $advice_id ?></span>
                        </h5>
                    </div>
                    <div class="card-body">
                        <div id="adviceInfo" class="mb-3">
                            <p><strong>Date de création:</strong> <span id="dateCreation"></span></p>
                            <p id="dateReponseContainer" style="display: none;"><strong>Date de réponse:</strong> <span id="dateReponse"></span></p>
                        </div>
                        <form id="editAdviceForm">
                            <div class="mb-3">
                                <label for="question" class="form-label">Question:</label>
                                <textarea class="form-control" id="question" rows="4" required></textarea>
                            </div>
                            
                            <div class="mb-3">
                                <label for="reponse" class="form-label">Réponse:</label>
                                <textarea class="form-control" id="reponse" rows="6"></textarea>
                            </div>
                            
                            <div class="d-flex justify-content-end">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save me-1"></i>
                                    Enregistrer les modifications
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const adviceId = document.getElementById('adviceId').textContent;
            
            // Charger les détails du conseil
            fetch(`../../api/advice/getOne.php?id=${adviceId}`)
                .then(response => response.json())
                .then(advice => {
                    document.getElementById('question').value = advice.question;
                    
                    // Afficher la date de création formatée
                    const dateCreation = new Date(advice.date_creation);
                    document.getElementById('dateCreation').textContent = dateCreation.toLocaleDateString('fr-FR') + ' ' + 
                        dateCreation.toLocaleTimeString('fr-FR', {hour: '2-digit', minute:'2-digit'});
                    
                    if (advice.reponse) {
                        document.getElementById('reponse').value = advice.reponse;
                        
                        // Afficher la date de réponse si elle existe
                        if (advice.date_reponse) {
                            const dateReponse = new Date(advice.date_reponse);
                            document.getElementById('dateReponse').textContent = dateReponse.toLocaleDateString('fr-FR') + ' ' + 
                                dateReponse.toLocaleTimeString('fr-FR', {hour: '2-digit', minute:'2-digit'});
                            document.getElementById('dateReponseContainer').style.display = 'block';
                        }
                    }
                })
                .catch(error => {
                    console.error('Erreur:', error);
                    alert('Erreur lors du chargement des données du conseil');
                });
            
            // Gestion de la soumission du formulaire
            document.getElementById('editAdviceForm').addEventListener('submit', function(e) {
                e.preventDefault();
                
                const question = document.getElementById('question').value;
                const reponse = document.getElementById('reponse').value;
                
                fetch('../../api/advice/update.php', {
                    method: 'PUT',
                    body: JSON.stringify({
                        conseil_id: adviceId,
                        question: question,
                        reponse: reponse,
                        id_admin: <?= $_SESSION['admin_id'] ?>
                    }),
                    headers: {
                        'Content-Type': 'application/json'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    alert(data.message);
                    if (data.message.includes('succès')) {
                        window.location.href = 'advice.php';
                    }
                })
                .catch(error => {
                    console.error('Erreur:', error);
                    alert('Une erreur est survenue lors de la mise à jour du conseil');
                });
            });
        });
    </script>
</body>
</html>
