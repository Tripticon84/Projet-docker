<?php
$title = "Modification des Administrateurs";
include_once "../includes/head.php";

// Récupérer l'ID de l'admin à modifier depuis l'URL
$admin_id = isset($_GET['id']) ? $_GET['id'] : null;

// Vérifier si un ID a été fourni
if (!$admin_id) {
    echo "<script>alert('ID d\'administrateur non spécifié'); window.location.href='admin.php';</script>";
    exit;
}
?>

<body class="container mt-5">
    <a href="admin.php" class="btn btn-secondary mb-3">&larr; Retour</a>
    <div class="card p-4 shadow-sm">
        <h2 class="text-center mb-4">Modifier un Administrateur</h2>
        <!-- Formulaire avec ID caché pour l'admin à modifier -->
        <form id="adminForm">
            <input type="hidden" id="admin_id" value="<?php echo $admin_id; ?>">

            <div class="mb-3">
                <label for="username" class="form-label">Identifiant :</label>
                <input type="text" id="username" name="username" class="form-control" required>
            </div>

            <div class="mb-3">
                <label for="password" class="form-label">Mot de passe :</label>
                <!-- Le mot de passe n'est plus requis pour permettre une mise à jour sans changer le mot de passe -->
                <input type="password" id="password" name="password" class="form-control" minlength="8">
                <small class="form-text text-muted">Laissez vide pour conserver le mot de passe actuel</small>
            </div>

            <button type="submit" class="btn btn-primary w-100">Modifier Admin</button>
        </form>
        <p id="responseMessage" class="mt-3 text-center"></p>
    </div>

    <script>
        // Au chargement de la page, récupérer les données de l'administrateur
        document.addEventListener('DOMContentLoaded', function() {
            // Récupérer l'ID depuis le champ caché
            const adminId = document.getElementById('admin_id').value;

            console.log("Chargement des données pour l'admin ID:", adminId);

            // Appel API pour récupérer les données de l'administrateur
            fetch(`../../api/admin/read.php?id=${adminId}`, {
                    headers: {
                        'Authorization': 'Bearer ' + getToken()
                    }
                })
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Erreur réseau: ' + response.status);
                    }
                    return response.json();
                })
                .then(data => {
                    console.log("Données admin récupérées:", data);

                    // Pré-remplir le formulaire avec les données
                    if (data && data.username) {
                        document.getElementById('username').value = data.username;
                        // Le mot de passe n'est jamais pré-rempli pour des raisons de sécurité
                    } else {
                        document.getElementById('responseMessage').textContent = "Erreur: Administrateur non trouvé";
                        document.getElementById('responseMessage').classList.add("text-danger");
                    }
                })
                .catch(error => {
                    console.error("Erreur lors de la récupération des données:", error);
                    document.getElementById('responseMessage').textContent = "Erreur: " + error.message;
                    document.getElementById('responseMessage').classList.add("text-danger");
                });
        });

        // Gestion de la soumission du formulaire
        document.getElementById('adminForm').addEventListener('submit', function(event) {
            event.preventDefault(); // Empêche le rechargement de la page

            // Récupérer les valeurs du formulaire
            const adminId = document.getElementById('admin_id').value;
            const username = document.getElementById('username').value;
            const password = document.getElementById('password').value; // Peut être vide
            const responseMessage = document.getElementById('responseMessage');

            // Préparer les données à envoyer
            const updateData = {
                id: adminId,
                username: username
            };

            // Ajouter le mot de passe uniquement s'il est fourni
            if (password && password.trim() !== '') {
                updateData.password = password;
                console.log("Mise à jour avec nouveau mot de passe");
            } else {
                console.log("Mise à jour sans changer le mot de passe");
            }
            console.log("Envoi des données pour mise à jour:", {
                id: adminId,
                username
            });

            // Appel API pour mettre à jour l'administrateur
            fetch('../../api/admin/modify.php', {
                    method: 'PATCH',
                    headers: {
                        'Content-Type': 'application/json',
                        'Authorization': 'Bearer ' + getToken()
                    },
                    body: JSON.stringify(updateData)
                })
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Erreur reseau: ' + response.status);
                    }
                    return response.json();
                })
                .then(data => {
                    console.log("Réponse de l'API:", data);

                    // Afficher un message de succès ou d'erreur
                    if (data.success) {
                        responseMessage.textContent = "Administrateur modifié avec succès";
                        responseMessage.classList.add("text-success");
                        responseMessage.classList.remove("text-danger");

                        // Redirection optionnelle
                        // setTimeout(() => window.location.href = 'admin.php', 1500);
                    } else {
                        responseMessage.textContent = "Erreur: " + (data.error || "Échec de la mise à jour");
                        responseMessage.classList.add("text-danger");
                        responseMessage.classList.remove("text-success");
                    }
                })
                .catch(error => {
                    console.error("Erreur lors de la mise à jour:", error);
                    responseMessage.textContent = "Erreur: " + error.message;
                    responseMessage.classList.add("text-danger");
                    responseMessage.classList.remove("text-success");
                });
        });
    </script>
</body>

</html>
