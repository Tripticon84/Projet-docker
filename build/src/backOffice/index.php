<?php
$title = "Connexion";

include_once "includes/head.php";

?>
<script>
    if (getToken() !== null) {
        window.location.href = "home.php";
    }
</script>
<div class="container mt-5 d-flex align-items-center justify-content-center" style="min-height: 100vh;">
    <div class="row justify-content-center">
        <div class="w-100 m-5">
            <div class="card">
                <h1 class="text-center">Business Care</h1>
                <div class="card-header">
                    <h3 class="text-center">Connexion</h3>
                </div>
                <div class="card-body">
                    <form action="login/login_process.php" method="post">
                        <div class="mb-3">
                            <label for="username" class="form-label">Identifiant</label>
                            <input type="text" class="form-control" id="username" name="username" required>
                        </div>
                        <div class="mb-3">
                            <label for="password" class="form-label">Mot de passe</label>
                            <input type="password" class="form-control" id="password" name="password" required>
                        </div>
                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary">Se connecter</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
