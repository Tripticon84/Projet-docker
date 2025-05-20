<?php
// La session est déjà démarrée dans head.php
// Removed login redirect that was causing 404 error

$title = "Répondre à une demande de conseil";
include_once "../includes/head.php";

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
                    <h1 class="h2">Répondre à une demande de conseil</h1>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <a type="button" class="btn btn-sm btn-secondary" href="advice.php">
                            <i class="fas fa-arrow-left"></i> Retour à la liste
                        </a>
                    </div>
                </div>

                <!-- Answer Form Card -->
                <div class="card mt-4">
                    <div class="card-header bg-white">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-reply me-2"></i>
                            Répondre au conseil #<span id="adviceId"><?= $advice_id ?></span>
                        </h5>
                    </div>
                    <div class="card-body">
                        <div id="adviceInfo" class="mb-3">
                            <p><strong>Date de création:</strong> <span id="dateCreation"></span></p>
                        </div>
                        <div id="adviceDetails" class="mb-4">
                            <h6 class="fw-bold">Question du collaborateur:</h6>
                            <div id="question" class="p-3 bg-light rounded mb-4 border"></div>
                            
                            <div class="mb-3">
                                <label for="reponse" class="form-label">Votre réponse:</label>
                                <textarea class="form-control" id="reponse" rows="6" placeholder="Entrez votre réponse ici..."></textarea>
                            </div>
                            
                            <div class="d-flex justify-content-end">
                                <button id="submitAnswer" class="btn btn-success">
                                    <i class="fas fa-paper-plane me-1"></i>
                                    Envoyer la réponse
                                </button>
                            </div>
                        </div>
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
                    document.getElementById('question').textContent = advice.question;
                    
                    // Afficher la date de création formatée
                    const dateCreation = new Date(advice.date_creation);
                    document.getElementById('dateCreation').textContent = dateCreation.toLocaleDateString('fr-FR') + ' ' + 
                        dateCreation.toLocaleTimeString('fr-FR', {hour: '2-digit', minute:'2-digit'});
                    
                    // Si le conseil a déjà une réponse, la précharger
                    if (advice.reponse) {
                        document.getElementById('reponse').value = advice.reponse;
                    }
                })
                .catch(error => {
                    console.error('Erreur:', error);
                    document.getElementById('adviceDetails').innerHTML = 
                        '<div class="alert alert-danger">Erreur lors du chargement des données</div>';
                });
            
            // Gestion de la soumission de la réponse
            document.getElementById('submitAnswer').addEventListener('click', function() {
                const reponse = document.getElementById('reponse').value;
                
                if (!reponse.trim()) {
                    alert('Veuillez entrer une réponse');
                    return;
                }
                
                fetch('../../api/advice/answer.php', {
                    method: 'POST',
                    body: JSON.stringify({
                        conseil_id: adviceId,
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
                    alert('Une erreur est survenue lors de l\'envoi de la réponse');
                });
            });
        });
    </script>
</body>
</html>
