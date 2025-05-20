<?php
$title = "Connexion";

require_once $_SERVER['DOCUMENT_ROOT'] . '/frontOffice/societe/includes/head.php';

?>

<style>
    body {
        background: linear-gradient(135deg, #3a7bd5, #6fc2c0);
        background-attachment: fixed;
    }
    
    .login-card {
        border-radius: 15px;
        box-shadow: rgba(58, 123, 213, 0.4) 5px 5px, 
                    rgba(58, 123, 213, 0.3) 10px 10px, 
                    rgba(58, 123, 213, 0.2) 15px 15px, 
                    rgba(58, 123, 213, 0.1) 20px 20px, 
                    rgba(58, 123, 213, 0.05) 25px 25px;
        overflow: hidden;
        backdrop-filter: blur(10px);
        background-color: rgba(255, 255, 255, 0.95);
        transition: all 0.3s ease;
        position: relative;
        z-index: 1;
    }
    
    .login-card:hover {
        transform: translateY(-5px);
        box-shadow: rgba(111, 194, 192, 0.4) 5px 5px, 
                    rgba(111, 194, 192, 0.3) 10px 10px, 
                    rgba(111, 194, 192, 0.2) 15px 15px, 
                    rgba(111, 194, 192, 0.1) 20px 20px, 
                    rgba(111, 194, 192, 0.05) 25px 25px;
    }
    
    .card-header {
        border-radius: 15px 15px 0 0 !important;
        padding: 1.5rem !important;
        background: linear-gradient(to right, #3a7bd5, #6fc2c0) !important;
        border: none;
    }
    
    .login-btn {
        background: linear-gradient(to right, #3a7bd5, #6fc2c0);
        border: none;
        transition: all 0.3s ease;
    }
    
    .login-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(58, 123, 213, 0.4);
    }
    
    .divider {
        display: flex;
        align-items: center;
        margin: 1.5rem 0;
    }
    
    .divider::before, .divider::after {
        content: "";
        flex: 1;
        border-bottom: 1px solid #ced4da;
    }
    
    .divider span {
        padding: 0 10px;
        color: #6c757d;
        font-size: 0.9rem;
    }
</style>

<div class="container d-flex align-items-center justify-content-center" style="min-height: 100vh;">
    <div class="row justify-content-center w-100">
        <div class="col-md-6 col-lg-5">
            <div class="card login-card shadow">
                <div class="card-header text-white">
                    <h3 class="mb-0 text-center">Espace Société</h3>
                </div>
                <div class="card-body p-4">
                    <div class="text-center mb-4">
                        <img src="/data/static/logo.png" alt="Business Care Logo" class="img-fluid" style="max-height: 120px;">
                        <h4 class="mt-3 fw-bold text-primary">Connexion à votre espace client</h4>
                    </div>

                    <form method="post" action="login_process.php">
                        <div class="mb-4">
                            <label for="email" class="form-label">Email professionnel</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light"><i class="fas fa-envelope text-primary"></i></span>
                                <input type="email" class="form-control" id="email" name="email" placeholder="Votre adresse email" required>
                            </div>
                        </div>

                        <div class="mb-4">
                            <label for="password" class="form-label">Mot de passe</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light"><i class="fas fa-lock text-primary"></i></span>
                                <input type="password" class="form-control" id="password" name="password" placeholder="Votre mot de passe" required>
                            </div>
                            <div class="text-end mt-1">
                                <a href="#" class="text-decoration-none small">Mot de passe oublié ?</a>
                            </div>
                        </div>

                        <div class="d-grid mt-4">
                            <button type="submit" class="btn login-btn text-white btn-lg">
                                <i class="fas fa-sign-in-alt me-2"></i>Connexion
                            </button>
                        </div>
                    </form>
                    
                    <div class="divider">
                        <span>OU</span>
                    </div>
                    
                    <div class="d-grid">
                        <a href="/frontOffice/societe/register/register.php" class="btn btn-outline-primary btn-lg">
                            <i class="fas fa-user-plus me-2"></i>Créer un compte entreprise
                        </a>
                    </div>
                    
                    <div class="text-center mt-4">
                        <a href="/frontOffice/index.php" class="btn btn-outline-secondary">
                            <i class="fas fa-home me-2"></i>Retour à l'accueil
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
