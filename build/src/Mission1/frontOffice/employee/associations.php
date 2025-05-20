<?php
$title = "Associations";

require_once $_SERVER['DOCUMENT_ROOT'] . '/frontOffice/employee/includes/head.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/frontOffice/employee/includes/header.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/api/dao/employee.php';

$collaborateurId = $_SESSION['collaborateur_id'];
$associations = getAllAssociations();
$myAssociations = getEmployeeAssociations($collaborateurId);

// Créer un tableau des IDs des associations auxquelles le collaborateur participe
$participatingIds = array_map(function($assoc) {
    return $assoc['association_id'];
}, $myAssociations ?: []);

?>

<div class="container mt-4">
    <h2>Associations</h2>
    
    <!-- Ajouter la configuration Stripe -->
    <?php $stripePublishableKey = "pk_test_51PAXnVP0arAN6IC0UPtpOnaMS4O1Qp153mO28fvjMR3Qzq486KDOiScwYdv3svHGcARvLi2MuE9dh7shZtRnd5cW00Vx0swc4q"; ?>
    <script src="https://js.stripe.com/v3/"></script>

    <div class="row" id="associations-list">
        <?php if (empty($associations)): ?>
            <div class="col-12">
                <div class="alert alert-info">
                    Aucune association n'est disponible pour le moment.
                </div>
            </div>
        <?php else: ?>
            <?php foreach ($associations as $association): ?>
                <div class="col-md-6 col-lg-4 mb-4">
                    <div class="card h-100">
                        <?php if ($association['banniere']): ?>
                            <img src="/uploads/associations/<?= htmlspecialchars($association['banniere']) ?>"
                                 class="card-img-top" alt="Bannière de l'association">
                        <?php endif; ?>

                        <div class="card-body">
                            <div class="d-flex align-items-center mb-3">
                                <?php if ($association['logo']): ?>
                                    <img src="/uploads/associations/<?= htmlspecialchars($association['logo']) ?>"
                                         class="me-2" alt="Logo" style="width: 40px; height: 40px; object-fit: cover;">
                                <?php endif; ?>
                                <h5 class="card-title mb-0"><?= htmlspecialchars($association['name']) ?></h5>
                            </div>

                            <p class="card-text"><?= htmlspecialchars($association['description']) ?></p>
                            <p class="text-muted">
                                <small>Créée le: <?= date('d/m/Y', strtotime($association['date_creation'])) ?></small>
                            </p>
                        </div>

                        <div class="card-footer bg-transparent">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <?php if (in_array($association['association_id'], $participatingIds)): ?>
                                        <button class="btn btn-danger btn-sm"
                                                onclick="unsubscribeFromAssociation(<?= $collaborateurId ?>, <?= $association['association_id'] ?>)">
                                            Se désinscrire
                                        </button>
                                        <span class="badge bg-success ms-2">Membre</span>
                                    <?php else: ?>
                                        <button class="btn btn-primary btn-sm"
                                                onclick="subscribeToAssociation(<?= $collaborateurId ?>, <?= $association['association_id'] ?>)">
                                            Rejoindre
                                        </button>
                                    <?php endif; ?>
                                </div>
                                <button class="btn btn-outline-primary btn-sm" 
                                        onclick="openDonationModal(<?= $association['association_id'] ?>, '<?= htmlspecialchars($association['name']) ?>')">
                                    Faire un don
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<!-- Modal pour les dons -->
<div class="modal fade" id="donationModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Faire un don</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Votre don pour: <span id="associationName"></span></p>
                <div class="mb-3">
                    <label for="donationAmount" class="form-label">Montant (€)</label>
                    <input type="number" class="form-control" id="donationAmount" min="1" step="1" value="10">
                </div>
                <div id="stripe-payment-element"></div>
                <button id="submit-donation" class="btn btn-primary w-100 mt-3" style="display:none;">
                    Confirmer le don
                </button>
            </div>
        </div>
    </div>
</div>

<script>
let stripe = Stripe('<?php echo $stripePublishableKey; ?>');
let elements;
let currentAssociationId;

function openDonationModal(associationId, associationName) {
    currentAssociationId = associationId;
    document.getElementById('associationName').textContent = associationName;
    
    const modal = new bootstrap.Modal(document.getElementById('donationModal'));
    modal.show();
    
    setupPayment();
}

async function setupPayment() {
    const amount = document.getElementById('donationAmount').value;
    
    try {
        const response = await fetch('/api/association/create-donation-intent.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                amount: amount,
                associationId: currentAssociationId
            })
        });

        const data = await response.json();
        
        if (data.error) {
            throw new Error(data.error);
        }

        elements = stripe.elements({
            clientSecret: data.clientSecret,
            appearance: {
                theme: 'stripe',
                variables: {
                    colorPrimary: '#0d6efd',
                }
            }
        });

        const paymentElement = elements.create('payment');
        paymentElement.mount('#stripe-payment-element');

        document.getElementById('submit-donation').style.display = 'block';
        document.getElementById('submit-donation').addEventListener('click', handleDonation);
    } catch (error) {
        console.error('Erreur:', error);
        alert('Une erreur est survenue lors de l\'initialisation du paiement.');
    }
}

async function handleDonation(e) {
    e.preventDefault();
    
    const button = document.getElementById('submit-donation');
    button.disabled = true;
    button.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Traitement...';

    try {
        const result = await stripe.confirmPayment({
            elements,
            confirmParams: {
                return_url: window.location.origin + '/frontOffice/employee/donation-status.php?association_id=' + currentAssociationId,
            },
        });

        if (result.error) {
            throw new Error(result.error.message);
        }
    } catch (error) {
        button.disabled = false;
        button.innerHTML = 'Confirmer le don';
        alert(error.message);
    }
}

// Mettre à jour le paiement quand le montant change
document.getElementById('donationAmount').addEventListener('change', setupPayment);
</script>

<script src="/data/static/js/employee.js"></script>
<?php require_once $_SERVER['DOCUMENT_ROOT'] . '/frontOffice/employee/includes/footer.php'; ?>
