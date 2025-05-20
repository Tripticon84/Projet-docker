<?php
$title = "Création des Employés";
include_once "../includes/head.php";
?>
<body class="container mt-5">
    <a href="employee.php" class="btn btn-secondary mb-3">&larr; Retour</a>
    <div class="card p-4 shadow-sm">
        <h2 class="text-center mb-4">Créer un Employé</h2>
        <form id="employeeForm">
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

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="username" class="form-label">Identifiant :</label>
                    <input type="text" id="username" name="username" class="form-control" required>
                </div>

                <div class="col-md-6 mb-3">
                    <label for="role" class="form-label">Rôle :</label>
                    <input type="text" id="role" name="role" class="form-control" required>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="email" class="form-label">Email :</label>
                    <input type="email" id="email" name="email" class="form-control" required>
                </div>

                <div class="col-md-6 mb-3">
                    <label for="telephone" class="form-label">Téléphone :</label>
                    <input type="tel" id="telephone" name="telephone" class="form-control" required>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="password" class="form-label">Mot de passe :</label>
                    <input type="password" id="password" name="password" class="form-control" required minlength="8">
                </div>

                <div class="col-md-6 mb-3">
                    <label for="id_societe" class="form-label">ID Société :</label>
                    <input type="number" id="id_societe" name="id_societe" class="form-control" required>
                </div>
            </div>

            <button type="submit" class="btn btn-primary w-100">Créer Employé</button>
        </form>
        <p id="responseMessage" class="mt-3 text-center"></p>
    </div>

    <script>
        document.getElementById('employeeForm').addEventListener('submit', function(event) {
            event.preventDefault();

            const formData = {
                nom: document.getElementById('nom').value,
                prenom: document.getElementById('prenom').value,
                username: document.getElementById('username').value,
                role: document.getElementById('role').value,
                email: document.getElementById('email').value,
                telephone: document.getElementById('telephone').value,
                password: document.getElementById('password').value,
                id_societe: document.getElementById('id_societe').value
            };

            const responseMessage = document.getElementById('responseMessage');

            console.log("Envoi des données :", formData);

            fetch('../../api/employee/create.php', {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                    'Authorization': 'Bearer ' + getToken()
                },
                body: JSON.stringify(formData)
            })
            .then(response => response.json())
            .then(data => {
                console.log("Réponse JSON :", data);
                if (data.id) {
                    responseMessage.textContent = "Employé créé avec succès. ID: " + data.id;
                    responseMessage.classList.add("text-success");
                    responseMessage.classList.remove("text-danger");
                    document.getElementById('employeeForm').reset();
                } else {
                    responseMessage.textContent = "Erreur: " + data.error;
                    responseMessage.classList.add("text-danger");
                    responseMessage.classList.remove("text-success");
                }
            })
            .catch(error => {
                console.error("Erreur fetch :", error);
                responseMessage.textContent = error.message;
                responseMessage.classList.add("text-danger");
                responseMessage.classList.remove("text-success");
            });
        });
    </script>
</body>
</html>
