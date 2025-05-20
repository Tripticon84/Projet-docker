<?php
$title = "Modification des Employés";
include_once "../includes/head.php";

// Récupérer l'ID de l'employé à modifier depuis l'URL
$employee_id = isset($_GET['id']) ? $_GET['id'] : null;

// Vérifier si un ID a été fourni
if (!$employee_id) {
    echo "<script>alert('ID d\'employé non spécifié'); window.location.href='employee.php';</script>";
    exit;
}
?>
<body class="container mt-5">
    <a href="employee.php" class="btn btn-secondary mb-3">&larr; Retour</a>
    <div class="card p-4 shadow-sm">
        <h2 class="text-center mb-4">Modifier un Employé</h2>
        <!-- Formulaire avec ID caché pour l'employé à modifier -->
        <form id="employeeForm">
            <input type="hidden" id="employee_id" value="<?php echo $employee_id; ?>">

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="nom" class="form-label">Nom :</label>
                    <input type="text" id="nom" name="nom" class="form-control" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label for="prenom" class="form-label">Prénom :</label>
                    <input type="text" id="prenom" name="prenom" class="form-control" required>
                </div>
            </div>

            <div class="mb-3">
                <label for="username" class="form-label">Identifiant :</label>
                <input type="text" id="username" name="username" class="form-control" required>
            </div>

            <div class="mb-3">
                <label for="role" class="form-label">Rôle :</label>
                <input type="text" id="role" name="role" class="form-control">
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="email" class="form-label">Email :</label>
                    <input type="email" id="email" name="email" class="form-control">
                </div>
                <div class="col-md-6 mb-3">
                    <label for="telephone" class="form-label">Téléphone :</label>
                    <input type="tel" id="telephone" name="telephone" class="form-control">
                </div>
            </div>

            <div class="mb-3">
                <label for="id_societe" class="form-label">ID Société :</label>
                <input type="number" id="id_societe" name="id_societe" class="form-control">
            </div>

            <div class="mb-3">
                <label for="password" class="form-label">Mot de passe :</label>
                <input type="password" id="password" name="password" class="form-control" minlength="8">
                <small class="form-text text-muted">Laissez vide pour conserver le mot de passe actuel</small>
            </div>

            <button type="submit" class="btn btn-primary w-100">Modifier Employé</button>
        </form>
        <p id="responseMessage" class="mt-3 text-center"></p>
    </div>

    <script>
        // Au chargement de la page, récupérer les données de l'employé
        document.addEventListener('DOMContentLoaded', function() {
            // Récupérer l'ID depuis le champ caché
            const employeeId = document.getElementById('employee_id').value;

            console.log("Chargement des données pour l'employé ID:", employeeId);

            // Appel API pour récupérer les données de l'employé
            fetch(`../../api/employee/read.php?id=${employeeId}`, {
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
                    console.log("Données employé récupérées:", data);

                    // Pré-remplir le formulaire avec les données
                    if (data) {
                        document.getElementById('nom').value = data.nom || '';
                        document.getElementById('prenom').value = data.prenom || '';
                        document.getElementById('username').value = data.username || '';
                        document.getElementById('role').value = data.role || '';
                        document.getElementById('email').value = data.email || '';
                        document.getElementById('telephone').value = data.telephone || '';
                        document.getElementById('id_societe').value = data.id_societe || '';
                    } else {
                        document.getElementById('responseMessage').textContent = "Erreur: Employé non trouvé";
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
        document.getElementById('employeeForm').addEventListener('submit', function(event) {
            event.preventDefault(); // Empêche le rechargement de la page

            // Récupérer les valeurs du formulaire
            const employeeId = document.getElementById('employee_id').value;
            const nom = document.getElementById('nom').value;
            const prenom = document.getElementById('prenom').value;
            const username = document.getElementById('username').value;
            const role = document.getElementById('role').value;
            const email = document.getElementById('email').value;
            const telephone = document.getElementById('telephone').value;
            const idSociete = document.getElementById('id_societe').value;
            const password = document.getElementById('password').value; // Peut être vide
            const responseMessage = document.getElementById('responseMessage');

            // Préparer les données à envoyer
            const updateData = {
                id: employeeId,
                nom: nom,
                prenom: prenom,
                username: username,
                role: role,
                email: email,
                telephone: telephone,
                id_societe: idSociete ? parseInt(idSociete) : null
            };

            // Ajouter le mot de passe uniquement s'il est fourni
            if (password && password.trim() !== '') {
                updateData.password = password;
                console.log("Mise à jour avec nouveau mot de passe");
            } else {
                console.log("Mise à jour sans changer le mot de passe");
            }

            console.log("Envoi des données pour mise à jour:", updateData);
            // Appel API pour mettre à jour l'employé
            fetch('../../api/employee/modify.php', {
                method: 'PATCH',
                headers: {
                    'Content-Type': 'application/json',
                    'Authorization': 'Bearer ' + getToken()
                },
                body: JSON.stringify(updateData)
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Erreur réseau: ' + response.status);
                }
                return response.json();
            })
            .then(data => {
                console.log("Réponse de l'API:", data);

                // Afficher un message de succès ou d'erreur
                if (data.success) {
                    responseMessage.textContent = "Employé modifié avec succès";
                    responseMessage.classList.add("text-success");
                    responseMessage.classList.remove("text-danger");

                    // Redirection optionnelle
                    // setTimeout(() => window.location.href = 'employee.php', 1500);
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
