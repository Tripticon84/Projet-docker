<?php
$current_page = basename($_SERVER['SCRIPT_NAME']); ?>

<!-- Sidebar -->
<div class="col-md-3 col-lg-2 d-md-block sidebar collapse">
    <div class="position-sticky pt-3 d-flex flex-column" style="height: max-content;">
        <div class="text-center mb-4">
            <h3>Business Care</h3>
            <h6>Administration</h6>
        </div>
        <ul class="nav flex-column">
            <li class="nav-item">
                <a class="nav-link <?= ($current_page == 'home.php') ? 'active' : '' ?>" href="/backOffice/home.php">
                    <i class="fas fa-home"></i> Tableau de bord
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?= ($current_page == 'company.php') ? 'active' : '' ?>" href="/backOffice/company/company.php">
                    <i class="fas fa-building"></i> Sociétés clientes
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?= ($current_page == 'contract.php') ? 'active' : '' ?>" href="/backOffice/contract/contract.php">
                    <i class="fas fa-file-contract"></i> Contrats
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?= ($current_page == 'employee.php') ? 'active' : '' ?>" href="/backOffice/employee/employee.php">
                    <i class="fas fa-users"></i> Collaborateurs clients
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?= ($current_page == 'provider.php') ? 'active' : '' ?>" href="/backOffice/provider/provider.php">
                    <i class="fas fa-user-tie"></i> Prestataires
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?= ($current_page == 'association.php') ? 'active' : '' ?>" href="/backOffice/association/association.php">
                    <i class="fas fa-handshake"></i> Associations
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?= ($current_page == 'place.php') ? 'active' : '' ?>" href="/backOffice/place/place.php">
                    <i class="fas fa-map-marker-alt"></i> Lieux
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?= ($current_page == 'provider_activity.php') ? 'active' : '' ?>" href="/backOffice/activity/activity.php">
                    <i class="fas fa-tasks"></i> Activité des prestataires
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?= ($current_page == 'event.php') ? 'active' : '' ?>" href="/backOffice/event/event.php">
                    <i class="fas fa-calendar-alt"></i> Événements
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?= ($current_page == 'invoice.php') ? 'active' : '' ?>" href="/backOffice/invoice/invoice.php">
                    <i class="fas fa-file-invoice"></i> Factures
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?= ($current_page == 'admin.php') ? 'active' : '' ?>" href="/backOffice/admin/admin.php">
                    <i class="fas fa-home"></i> Gestion Admins
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?= ($current_page == 'chatbot.php') ? 'active' : '' ?>" href="/backOffice/chatbot/chatbot.php">
                    <i class="fas fa-robot"></i> Chatbot
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?= ($current_page == 'advice.php') ? 'active' : '' ?>" href="/backOffice/advice/advice.php">
                    <i class="fas fa-lightbulb"></i> Conseils
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?= ($current_page == 'report.php') ? 'active' : '' ?>" href="/backOffice/report/report.php">
                    <i class="fas fa-exclamation-triangle me-2"></i> Signalements
                </a>
            </li>
        </ul>
        <ul class="nav flex-column mt-auto">
            <li class="nav-item">
                <a class="nav-link" href="/backOffice/login/logout.php">
                    <i class="fas fa-sign-out-alt"></i> Se déconnecter
                </a>
            </li>
        </ul>
    </div>
</div>
