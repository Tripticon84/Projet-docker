<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Business Care - Bien-être et Cohésion en Entreprise</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <style>
        :root {
            --primary-color: #3a7bd5;
            --secondary-color: #6fc2c0;
            --dark-color: #333;
            --light-color: #f4f7fa;
        }

        .bg-primary-custom {
            background-color: var(--primary-color);
        }

        .bg-secondary-custom {
            background-color: var(--secondary-color);
        }

        .text-primary-custom {
            color: var(--primary-color);
        }

        .btn-primary-custom {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
            color: white;
        }

        .btn-primary-custom:hover {
            background-color: #2c62b3;
            border-color: #2c62b3;
            color: white;
        }

        .hero-section {
            background: linear-gradient(rgba(58, 123, 213, 0.8), rgba(111, 194, 192, 0.8)), url('/api/placeholder/1920/600');
            background-size: cover;
            background-position: center;
            color: white;
            padding: 100px 0;
        }

        .service-card {
            transition: transform 0.3s ease;
            height: 100%;
        }

        .service-card:hover {
            transform: translateY(-10px);
        }

        .icon-wrapper {
            font-size: 2.5rem;
            margin-bottom: 1rem;
            color: var(--primary-color);
        }

        .pricing-card {
            transition: transform 0.3s ease;
            height: 100%;
        }

        .pricing-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 10px 20px rgba(0,0,0,0.1);
        }

        .pricing-header {
            padding: 2rem;
            border-radius: 0.375rem 0.375rem 0 0;
        }

        .pricing-features {
            min-height: 300px;
        }

        .testimonial-card {
            height: 100%;
        }

        .footer {
            background-color: var(--dark-color);
            color: white;
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary-custom sticky-top">
        <div class="container">
            <a class="navbar-brand fw-bold" href="#">
                <i class="fas fa-heartbeat me-2"></i>Business Care
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link active" href="#home">Accueil</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#services">Services</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#tarifs">Tarifs</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#avantages">Avantages</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#espaces">Nos espaces</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#contact">Contact</a>
                    </li>
                    <li class="nav-item ms-2">
                        <a class="btn btn-light" href="#devis">Obtenir un devis</a>
                    </li>
                    <li class="nav-item dropdown ms-2">
                        <a class="btn btn-outline-light dropdown-toggle" href="#" id="connexionDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="fas fa-user me-1"></i>Connexion
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="connexionDropdown">
                            <li><a class="dropdown-item" href="/frontOffice/societe/login/login.php"><i class="fas fa-building me-2"></i>Espace société</a></li>
                            <li><a class="dropdown-item" href="/frontOffice/prestataire/login/login.php"><i class="fas fa-handshake me-2"></i>Espace prestataire</a></li>
                            <li><a class="dropdown-item" href="/frontOffice/employee/login/login.php"><i class="fas fa-user-tie me-2"></i>Espace employé</a></li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero-section text-center" id="home">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-md-10">
                    <h1 class="display-4 fw-bold mb-4">Améliorez la santé et le bien-être dans votre entreprise</h1>
                    <p class="lead mb-5">Business Care propose des solutions innovantes pour améliorer la qualité de vie au travail et booster la cohésion d'équipe. Découvrez comment notre approche globale peut transformer votre environnement professionnel.</p>
                    <div class="d-flex justify-content-center gap-3">
                        <a href="#devis" class="btn btn-light btn-lg">Demander un devis</a>
                        <a href="#services" class="btn btn-outline-light btn-lg">Découvrir nos services</a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Services Section -->
    <section class="py-5" id="services">
        <div class="container">
            <div class="text-center mb-5">
                <h2 class="fw-bold">Nos Services</h2>
                <p class="lead text-muted">Une approche holistique du bien-être au travail</p>
            </div>

            <div class="row g-4">
                <!-- Service 1 -->
                <div class="col-md-4">
                    <div class="card service-card h-100">
                        <div class="card-body text-center p-4">
                            <div class="icon-wrapper">
                                <i class="fas fa-brain"></i>
                            </div>
                            <h4 class="card-title">Santé mentale</h4>
                            <p class="card-text">Consultations individuelles avec des praticiens qualifiés, en présentiel ou en visioconférence. Signalement anonyme de situations critiques.</p>
                        </div>
                    </div>
                </div>

                <!-- Service 2 -->
                <div class="col-md-4">
                    <div class="card service-card h-100">
                        <div class="card-body text-center p-4">
                            <div class="icon-wrapper">
                                <i class="fas fa-users"></i>
                            </div>
                            <h4 class="card-title">Cohésion d'équipe</h4>
                            <p class="card-text">Organisation de défis sportifs, séances de yoga, mobilisation autour d'objectifs solidaires et autres activités pour renforcer les liens entre collaborateurs.</p>
                        </div>
                    </div>
                </div>

                <!-- Service 3 -->
                <div class="col-md-4">
                    <div class="card service-card h-100">
                        <div class="card-body text-center p-4">
                            <div class="icon-wrapper">
                                <i class="fas fa-chalkboard-teacher"></i>
                            </div>
                            <h4 class="card-title">Formation et sensibilisation</h4>
                            <p class="card-text">Webinars, ateliers et conférences sur des thématiques liées au bien-être, à la santé et à la qualité de vie au travail.</p>
                        </div>
                    </div>
                </div>

                <!-- Service 4 -->
                <div class="col-md-4">
                    <div class="card service-card h-100">
                        <div class="card-body text-center p-4">
                            <div class="icon-wrapper">
                                <i class="fas fa-hands-helping"></i>
                            </div>
                            <h4 class="card-title">Engagement solidaire</h4>
                            <p class="card-text">Accompagnement pour impliquer vos collaborateurs dans des actions associatives : dons financiers, dons matériels ou participation bénévole.</p>
                        </div>
                    </div>
                </div>

                <!-- Service 5 -->
                <div class="col-md-4">
                    <div class="card service-card h-100">
                        <div class="card-body text-center p-4">
                            <div class="icon-wrapper">
                                <i class="fas fa-running"></i>
                            </div>
                            <h4 class="card-title">Activités sportives</h4>
                            <p class="card-text">Programmes d'activités physiques adaptés à tous les niveaux, en individuel ou en groupe, pour favoriser le bien-être physique de vos collaborateurs.</p>
                        </div>
                    </div>
                </div>

                <!-- Service 6 -->
                <div class="col-md-4">
                    <div class="card service-card h-100">
                        <div class="card-body text-center p-4">
                            <div class="icon-wrapper">
                                <i class="fas fa-lightbulb"></i>
                            </div>
                            <h4 class="card-title">Conseils personnalisés</h4>
                            <p class="card-text">Conseils bien-être hebdomadaires, accès à notre chatbot et à notre bibliothèque de ressources pour accompagner vos collaborateurs au quotidien.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Pricing Section -->
    <section class="py-5 bg-light" id="tarifs">
        <div class="container">
            <div class="text-center mb-5">
                <h2 class="fw-bold">Nos Formules</h2>
                <p class="lead text-muted">Des solutions adaptées à toutes les entreprises</p>
            </div>

            <div class="row g-4">
                <!-- Starter Plan -->
                <div class="col-md-4">
                    <div class="card pricing-card h-100">
                        <div class="pricing-header bg-primary-custom text-white text-center">
                            <h3 class="fw-bold">Starter</h3>
                            <h4 class="fw-bold">180 €</h4>
                            <p>par salarié / an</p>
                        </div>
                        <div class="card-body p-4">
                            <div class="pricing-features">
                                <ul class="list-unstyled">
                                    <li class="mb-3"><i class="fas fa-check text-success me-2"></i>Jusqu'à 30 salariés</li>
                                    <li class="mb-3"><i class="fas fa-check text-success me-2"></i>2 activités avec prestataires</li>
                                    <li class="mb-3"><i class="fas fa-check text-success me-2"></i>1 RDV médical (présentiel/visio)</li>
                                    <li class="mb-3"><i class="fas fa-check text-success me-2"></i>RDV supplémentaires : 75€/rdv</li>
                                    <li class="mb-3"><i class="fas fa-check text-success me-2"></i>Chatbot : 6 questions</li>
                                    <li class="mb-3"><i class="fas fa-check text-success me-2"></i>Accès illimité aux fiches pratiques</li>
                                    <li class="mb-3"><i class="fas fa-times text-danger me-2"></i>Conseils hebdomadaires</li>
                                    <li class="mb-3"><i class="fas fa-check text-success me-2"></i>Accès illimité aux événements/communautés</li>
                                </ul>
                            </div>
                            <div class="text-center mt-4">
                                <a href="#devis" class="btn btn-primary-custom btn-lg w-100">Choisir</a>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Basic Plan -->
                <div class="col-md-4">
                    <div class="card pricing-card h-100 border-primary">
                        <div class="pricing-header bg-primary-custom text-white text-center">
                            <span class="badge bg-warning position-absolute top-0 end-0 mt-2 me-2">Populaire</span>
                            <h3 class="fw-bold">Basic</h3>
                            <h4 class="fw-bold">150 €</h4>
                            <p>par salarié / an</p>
                        </div>
                        <div class="card-body p-4">
                            <div class="pricing-features">
                                <ul class="list-unstyled">
                                    <li class="mb-3"><i class="fas fa-check text-success me-2"></i>Jusqu'à 250 salariés</li>
                                    <li class="mb-3"><i class="fas fa-check text-success me-2"></i>3 activités avec prestataires</li>
                                    <li class="mb-3"><i class="fas fa-check text-success me-2"></i>2 RDV médicaux (présentiel/visio)</li>
                                    <li class="mb-3"><i class="fas fa-check text-success me-2"></i>RDV supplémentaires : 75€/rdv</li>
                                    <li class="mb-3"><i class="fas fa-check text-success me-2"></i>Chatbot : 20 questions</li>
                                    <li class="mb-3"><i class="fas fa-check text-success me-2"></i>Accès illimité aux fiches pratiques</li>
                                    <li class="mb-3"><i class="fas fa-check text-success me-2"></i>Conseils hebdomadaires (non personnalisés)</li>
                                    <li class="mb-3"><i class="fas fa-check text-success me-2"></i>Accès illimité aux événements/communautés</li>
                                </ul>
                            </div>
                            <div class="text-center mt-4">
                                <a href="#devis" class="btn btn-primary-custom btn-lg w-100">Choisir</a>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Premium Plan -->
                <div class="col-md-4">
                    <div class="card pricing-card h-100">
                        <div class="pricing-header bg-primary-custom text-white text-center">
                            <h3 class="fw-bold">Premium</h3>
                            <h4 class="fw-bold">100 €</h4>
                            <p>par salarié / an</p>
                        </div>
                        <div class="card-body p-4">
                            <div class="pricing-features">
                                <ul class="list-unstyled">
                                    <li class="mb-3"><i class="fas fa-check text-success me-2"></i>À partir de 251 salariés</li>
                                    <li class="mb-3"><i class="fas fa-check text-success me-2"></i>4 activités avec prestataires</li>
                                    <li class="mb-3"><i class="fas fa-check text-success me-2"></i>3 RDV médicaux (présentiel/visio)</li>
                                    <li class="mb-3"><i class="fas fa-check text-success me-2"></i>RDV supplémentaires : 50€/rdv</li>
                                    <li class="mb-3"><i class="fas fa-check text-success me-2"></i>Chatbot : questions illimitées</li>
                                    <li class="mb-3"><i class="fas fa-check text-success me-2"></i>Accès illimité aux fiches pratiques</li>
                                    <li class="mb-3"><i class="fas fa-check text-success me-2"></i>Conseils hebdomadaires personnalisés</li>
                                    <li class="mb-3"><i class="fas fa-check text-success me-2"></i>Accès illimité aux événements/communautés</li>
                                </ul>
                            </div>
                            <div class="text-center mt-4">
                                <a href="#devis" class="btn btn-primary-custom btn-lg w-100">Choisir</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Advantages Section -->
    <section class="py-5" id="avantages">
        <div class="container">
            <div class="text-center mb-5">
                <h2 class="fw-bold">Pourquoi choisir Business Care ?</h2>
                <p class="lead text-muted">Des avantages concrets pour votre entreprise</p>
            </div>

            <div class="row g-4">
                <div class="col-md-6">
                    <div class="d-flex">
                        <div class="flex-shrink-0">
                            <i class="fas fa-check-circle text-primary-custom fa-2x"></i>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h4>Amélioration du bien-être</h4>
                            <p>Nos programmes ont démontré une amélioration significative du bien-être des collaborateurs, réduisant le stress et favorisant un environnement de travail positif.</p>
                        </div>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="d-flex">
                        <div class="flex-shrink-0">
                            <i class="fas fa-check-circle text-primary-custom fa-2x"></i>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h4>Réduction de l'absentéisme</h4>
                            <p>Les entreprises qui utilisent nos services constatent une diminution moyenne de 27% de l'absentéisme grâce à nos programmes de prévention santé.</p>
                        </div>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="d-flex">
                        <div class="flex-shrink-0">
                            <i class="fas fa-check-circle text-primary-custom fa-2x"></i>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h4>Amélioration de la cohésion d'équipe</h4>
                            <p>Nos activités de team building renforcent les liens entre collaborateurs, améliorant la communication et la productivité collective.</p>
                        </div>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="d-flex">
                        <div class="flex-shrink-0">
                            <i class="fas fa-check-circle text-primary-custom fa-2x"></i>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h4>Marque employeur valorisée</h4>
                            <p>Offrir ces services à vos collaborateurs renforce votre image d'employeur attentionné, facilitant l'attraction et la rétention des talents.</p>
                        </div>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="d-flex">
                        <div class="flex-shrink-0">
                            <i class="fas fa-check-circle text-primary-custom fa-2x"></i>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h4>Simplicité d'utilisation</h4>
                            <p>Notre plateforme intuitive permet une mise en place rapide et un suivi efficace des services, sans contrainte pour vos équipes RH.</p>
                        </div>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="d-flex">
                        <div class="flex-shrink-0">
                            <i class="fas fa-check-circle text-primary-custom fa-2x"></i>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h4>Engagement RSE</h4>
                            <p>Nos programmes d'engagement solidaire vous permettent de concrétiser vos ambitions RSE tout en renforçant l'implication de vos collaborateurs.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Spaces Section -->
    <section class="py-5 bg-light" id="espaces">
        <div class="container">
            <div class="text-center mb-5">
                <h2 class="fw-bold">Nos Espaces</h2>
                <p class="lead text-muted">Des lieux adaptés à vos besoins</p>
            </div>

            <div class="row g-4">
                <div class="col-md-4">
                    <div class="card h-100">
                        <img src="/api/placeholder/600/400" class="card-img-top" alt="Bureau Paris 1er">
                        <div class="card-body">
                            <h5 class="card-title">Paris 1er - Siège</h5>
                            <p class="card-text">110, rue de Rivoli, 75001 Paris</p>
                            <p class="card-text"><small class="text-muted">Salles de conférence, ateliers, box individuels</small></p>
                        </div>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="card h-100">
                        <img src="/api/placeholder/600/400" class="card-img-top" alt="Bureau Troyes">
                        <div class="card-body">
                            <h5 class="card-title">Troyes</h5>
                            <p class="card-text">13, rue Antoine Parmentier, Troyes</p>
                            <p class="card-text"><small class="text-muted">Espaces modulables, salles de conférence</small></p>
                        </div>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="card h-100">
                        <img src="/api/placeholder/600/400" class="card-img-top" alt="Bureau Nice">
                        <div class="card-body">
                            <h5 class="card-title">Nice</h5>
                            <p class="card-text">8, rue Beaumont, Nice</p>
                            <p class="card-text"><small class="text-muted">Espaces polyvalents, terrasse pour activités</small></p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="text-center mt-4">
                <a href="#" class="btn btn-outline-primary">Voir tous nos espaces</a>
            </div>
        </div>
    </section>

    <!-- Testimonials -->
    <section class="py-5">
        <div class="container">
            <div class="text-center mb-5">
                <h2 class="fw-bold">Ils nous font confiance</h2>
                <p class="lead text-muted">Découvrez ce que nos clients disent de nous</p>
            </div>

            <div class="row g-4">
                <div class="col-md-4">
                    <div class="card testimonial-card h-100">
                        <div class="card-body">
                            <div class="mb-3">
                                <i class="fas fa-star text-warning"></i>
                                <i class="fas fa-star text-warning"></i>
                                <i class="fas fa-star text-warning"></i>
                                <i class="fas fa-star text-warning"></i>
                                <i class="fas fa-star text-warning"></i>
                            </div>
                            <p class="card-text">"Depuis que nous avons fait appel à Business Care, le taux de satisfaction de nos équipes a augmenté de 32%. Un investissement rentabilisé sur tous les plans."</p>
                            <div class="d-flex align-items-center mt-3">
                                <img src="/api/placeholder/50/50" class="rounded-circle me-3" alt="Client">
                                <div>
                                    <h6 class="mb-0">Sophie Martin</h6>
                                    <small class="text-muted">DRH, TechInnovate</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="card testimonial-card h-100">
                        <div class="card-body">
                            <div class="mb-3">
                                <i class="fas fa-star text-warning"></i>
                                <i class="fas fa-star text-warning"></i>
                                <i class="fas fa-star text-warning"></i>
                                <i class="fas fa-star text-warning"></i>
                                <i class="fas fa-star text-warning"></i>
                            </div>
                            <p class="card-text">"Les programmes de cohésion proposés par Business Care ont transformé notre culture d'entreprise. Nos équipes sont plus soudées et plus performantes."</p>
                            <div class="d-flex align-items-center mt-3">
                                <img src="/api/placeholder/50/50" class="rounded-circle me-3" alt="Client">
                                <div>
                                    <h6 class="mb-0">Thomas Dubois</h6>
                                    <small class="text-muted">CEO, GreenSolutions</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="card testimonial-card h-100">
                        <div class="card-body">
                            <div class="mb-3">
                                <i class="fas fa-star text-warning"></i>
                                <i class="fas fa-star text-warning"></i>
                                <i class="fas fa-star text-warning"></i>
                                <i class="fas fa-star text-warning"></i>
                                <i class="fas fa-star-half-alt text-warning"></i>
                            </div>
                            <p class="card-text">"L'accompagnement personnalisé et la qualité des prestataires font toute la différence. Notre taux d'absentéisme a diminué de 27% en 6 mois."</p>
                            <div class="d-flex align-items-center mt-3">
                                <img src="/api/placeholder/50/50" class="rounded-circle me-3" alt="Client">
                                <div>
                                    <h6 class="mb-0">Élise Moreau</h6>
                                    <small class="text-muted">Responsable RH, MediaGroup</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="py-5 bg-secondary-custom text-white" id="devis">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-8 mb-4 mb-lg-0">
                    <h2 class="fw-bold">Prêt à améliorer le bien-être de vos collaborateurs ?</h2>
                    <p class="lead mb-0">Demandez un devis personnalisé et découvrez comment Business Care peut transformer votre entreprise.</p>
                </div>
                <div class="col-lg-4 text-lg-end">
                    <button type="button" class="btn btn-light btn-lg" data-bs-toggle="modal" data-bs-target="#devisModal">
                        Demander un devis
                    </button>
                </div>
            </div>
        </div>
    </section>

    <!-- Contact Section -->
    <section class="py-5" id="contact">
        <div class="container">
            <div class="row">
                <div class="col-lg-6 mb-4 mb-lg-0">
                    <h2 class="fw-bold mb-4">Contactez-nous</h2>
                    <p class="mb-4">Vous avez des questions sur nos services ? Notre équipe est à votre disposition pour vous répondre et vous accompagner dans vos projets de bien-être en entreprise.</p>

                    <div class="d-flex mb-3">
                        <div class="flex-shrink-0">
                            <i class="fas fa-map-marker-alt text-primary-custom fa-2x"></i>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h5>Siège social</h5>
                            <p>110, rue de Rivoli, 75001 Paris</p>
                        </div>
                    </div>

                    <div class="d-flex mb-3">
                        <div class="flex-shrink-0">
                            <i class="fas fa-phone text-primary-custom fa-2x"></i>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h5>Téléphone</h5>
                            <p>+33 (0)1 XX XX XX XX</p>
                        </div>
                    </div>

                    <div class="d-flex mb-3">
                        <div class="flex-shrink-0">
                            <i class="fas fa-envelope text-primary-custom fa-2x"></i>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h5>Email</h5>
                            <p>contact@businesscare.com</p>
                        </div>
                    </div>
                </div>

                <div class="col-lg-6">
                    <h2 class="fw-bold mb-4">Envoyez-nous un message</h2>
                    <form>
                        <div class="mb-3">
                            <label for="name" class="form-label">Nom</label>
                            <input type="text" class="form-control" id="name" required>
                        </div>
                        <div class="mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="email" required>
                        </div>
                        <div class="mb-3">
                            <label for="message" class="form-label">Message</label>
                            <textarea class="form-control" id="message" rows="5" required></textarea>
                        </div>
                        <button type="submit" class="btn btn-primary-custom">Envoyer</button>
                    </form>
                </div>
