// Charger les informations de la société
function loadCompanyInfo(societyId) {
  fetch(`/api/company/getOne.php?societe_id=${societyId}`, {
    method: "GET",
    headers: {
      Authorization: "Bearer " + getToken(),
    },
  })
    .then((response) => response.json())
    .then((data) => {
      companyData = data;
      document.getElementById(
        "company-name"
      ).textContent = `Tableau de bord - ${data.nom}`;

      const companyInfoDiv = document.getElementById("company-info");
      companyInfoDiv.innerHTML = `
                <h4>${data.nom}</h4>
                <p><strong>Contact:</strong> ${data.contact_person}</p>
                <p><strong>Email:</strong> ${data.email}</p>
                <p><strong>Téléphone:</strong> ${data.telephone}</p>
                <p><strong>Adresse:</strong> ${data.adresse}</p>
                <p><strong>SIRET:</strong> ${data.siret}</p>
                <p><strong>Date de création:</strong> ${new Date(
                  data.date_creation
                ).toLocaleDateString("fr-FR")}</p>
            `;
    })
    .catch((error) => {
      console.error(
        "Erreur lors du chargement des informations de la société:",
        error
      );
      document.getElementById(
        "company-info"
      ).innerHTML = `<div class="alert alert-danger">Erreur lors du chargement des informations</div>`;
    });
}

// Charger les contrats avec filtres optionnels
function loadContracts(societyId, filters = {}) {
  // Construire l'URL avec les filtres
  let url = `/api/company/getContract.php?societe_id=${societyId}`;

  // Ajouter les filtres s'ils sont définis
  if (filters.status) url += `&statut=${filters.status}`;
  if (filters.startDate) url += `&date_debut=${filters.startDate}`;
  if (filters.endDate) url += `&date_fin=${filters.endDate}`;

  fetch(url, {
    method: "GET",
    headers: {
      Authorization: "Bearer " + getToken(),
    },
  })
    .then((response) => response.json())
    .then((data) => {
      const tableBody = document.getElementById("contracts-table");

      if (data.length === 0) {
        tableBody.innerHTML =
          '<tr><td colspan="6" class="text-center">Aucun contrat trouvé</td></tr>';
        return;
      }

      tableBody.innerHTML = "";
      data.forEach((contract) => {
        tableBody.innerHTML += `
                    <tr>
                        <td>${contract.devis_id}</td>
                        <td>${new Date(contract.date_debut).toLocaleDateString("fr-FR")}</td>
                        <td>${new Date(contract.date_fin).toLocaleDateString("fr-FR")}</td>
                        <td><span class="badge bg-${getStatusBadge(contract.statut)}">${contract.statut}</span></td>
                        <td>${contract.montant} €</td>
                        <td>${contract.montant_ht} €</td>
                        <td>${contract.montant_tva} €</td>
                        <td>
                            <button class="btn btn-sm btn-info" onclick="viewContract(${
                              contract.devis_id})">
                                <i class="fas fa-eye"></i>
                            </button>
                        </td>
                    </tr>
                `;
      });
    })
    .catch((error) => {
      console.error("Erreur lors du chargement des contrats:", error);
      document.getElementById(
        "contracts-table"
      ).innerHTML = `<tr><td colspan="6" class="text-center text-danger">Erreur lors du chargement des contrats</td></tr>`;
    });
}

// Fonction pour appliquer les filtres de contrats
function applyContractFilters(societyId) {
  const filters = {
    status: document.getElementById("statusFilter").value,
    startDate: document.getElementById("dateStartFilter").value,
    endDate: document.getElementById("dateEndFilter").value,
  };

  loadContracts(societyId, filters);
}

// Fonction pour réinitialiser les filtres de contrats
function resetContractFilters(societyId) {
  // Réinitialiser le formulaire
  document.getElementById("contractFilterForm").reset();

  // Recharger tous les contrats sans filtre
  loadContracts(societyId);
}

// Fonction pour voir les détails d'un contrat
function viewContract(id) {
  // Appeler la fonction existante viewContractDetails pour afficher les détails du contrat
  viewContractDetails(id);
}

// Fonction pour voir les détails d'un contrat
function viewContractDetails(id) {
  fetch(`/api/estimate/getOne.php?devis_id=${id}`, {
    method: "GET",
    headers: {
      Authorization: "Bearer " + getToken(),
    },
  })
    .then((response) => response.json())
    .then((contract) => {
      const detailsContainer = document.getElementById("contract-details");
      detailsContainer.innerHTML = `
              <div class="row mb-3">
                  <div class="col-md-6">
                      <p><strong>ID:</strong> ${contract.devis_id}</p>
                      <p><strong>Date de début:</strong> ${new Date(
                        contract.date_debut
                      ).toLocaleDateString("fr-FR")}</p>
                      <p><strong>Date de fin:</strong> ${new Date(
                        contract.date_fin
                      ).toLocaleDateString("fr-FR")}</p>
                  </div>
                  <div class="col-md-6">
                      <p><strong>Statut:</strong> <span class="badge bg-${getStatusBadge(
                        contract.statut
                      )}">${contract.statut}</span></p>
                      <p><strong>Montant TTC:</strong> ${contract.montant} €</p>
                      <p><strong>Montant HT:</strong> ${contract.montant_ht} €</p>
                      <p><strong>Montant TVA:</strong> ${contract.montant_tva} €</p>
                  </div>
              </div>
              </div>
          `;


      // Afficher le modal
      const modal = new bootstrap.Modal(
        document.getElementById("viewContractModal")
      );
      modal.show();
    })
    .catch((error) => {
      console.error("Erreur lors du chargement des détails du contrat:", error);
      alert("Erreur lors du chargement des détails du contrat");
    });
}

// Charger les abonnements
function loadSubscriptions(societyId) {
  fetch(`/api/company/getOtherCosts.php?societe_id=${societyId}`, {
    method: "GET",
    headers: {
      Authorization: "Bearer " + getToken(),
    },
  })
    .then(response => {
      if (!response.ok) {
        throw new Error('Erreur lors du chargement des abonnements.');
      }
      return response.json();
    })
    .then(data => {
      const tableBody = document.getElementById('subscriptions-table');

      // Filtrer les abonnements (est_abonnement = 1)
      const subscriptions = data.filter(item => item.est_abonnement == 1);

      console.log(subscriptions);

      if (subscriptions.length === 0) {
        tableBody.innerHTML = `
          <tr>
            <td colspan="5" class="text-center">
              <div class="alert alert-info mb-0">
                <i class="fas fa-info-circle me-2"></i>
                Vous n'avez aucun abonnement actif pour le moment.
              </div>
            </td>
          </tr>
        `;
        return;
      }

      let html = '';
      subscriptions.forEach(subscription => {
        html += `
          <tr>
            <td>${subscription.nom}</td>
            <td>${parseFloat(subscription.montant).toLocaleString('fr-FR', {style: 'currency', currency: 'EUR'})}</td>
            <td>${subscription.description || 'N/A'}</td>
            <td>${new Date(subscription.date_creation).toLocaleDateString('fr-FR')}</td>
            <td>
              <button class="btn btn-sm btn-info view-cost" data-id="${subscription.frais_id}" data-type="subscription">
                <i class="fas fa-eye"></i> Voir
              </button>
            </td>
          </tr>
        `;
      });

      tableBody.innerHTML = html;

      // Ajouter les écouteurs d'événements pour les boutons "Voir"
      setTimeout(() => {
        document.querySelectorAll('.view-cost').forEach(button => {
          button.addEventListener('click', function() {
            const costId = this.getAttribute('data-id');
            const costType = this.getAttribute('data-type');
            viewCostDetails(costId, costType);
          });
        });
      }, 100);
    })
    .catch(error => {
      console.error(error);
      document.getElementById('subscriptions-table').innerHTML = `
        <tr>
          <td colspan="5" class="text-center text-danger">
            Erreur lors du chargement des abonnements. Veuillez réessayer plus tard.
          </td>
        </tr>
      `;
    });
}

// Fonction pour voir les détails d'un frais ou abonnement
function viewCostDetails(costId, costType) {
  fetch(`/api/fees/getOne.php?frais_id=${costId}`, {
    method: "GET",
    headers: {
      Authorization: "Bearer " + getToken(),
    },
  })
    .then(response => {
      if (!response.ok) {
        throw new Error('Erreur lors de la récupération des détails.');
      }
      return response.json();
    })
    .then(data => {
      const costDetails = document.getElementById('cost-details');
      const modalTitle = document.getElementById('viewCostModalLabel');

      modalTitle.textContent = data.est_abonnement == 1 ? 'Détails de l\'abonnement' : 'Détails du frais';

      const creationDate = new Date(data.date_creation).toLocaleDateString('fr-FR');
      const amount = parseFloat(data.montant).toLocaleString('fr-FR', {style: 'currency', currency: 'EUR'});

      costDetails.innerHTML = `
        <div class="mb-3">
          <h6>Nom:</h6>
          <p>${data.nom}</p>
        </div>
        <div class="mb-3">
          <h6>Montant:</h6>
          <p>${amount}</p>
        </div>
        <div class="mb-3">
          <h6>Description:</h6>
          <p>${data.description || 'Aucune description disponible'}</p>
        </div>
        <div class="mb-3">
          <h6>Date de création:</h6>
          <p>${creationDate}</p>
        </div>
        <div class="mb-3">
          <h6>Type:</h6>
          <p>${parseInt(data.est_abonnement) === 1 ? 'Abonnement' : 'Frais ponctuel'}</p>
        </div>
        <div class="mb-3">
          <h6>Devis associé:</h6>
          <p>Devis #${data.devis[0].devis_id} (${data.devis[0].statut})</p>
        </div>
      `;

      // Afficher le modal
      const modal = new bootstrap.Modal(document.getElementById('viewCostModal'));
      modal.show();
    })
    .catch(error => {
      console.error('Erreur lors de la récupération des détails:', error);
      alert('Erreur lors de la récupération des détails. Veuillez réessayer.');
    });
}

// Charger les autres frais
function loadOtherCosts(societyId) {
  fetch(`/api/company/getOtherCosts.php?societe_id=${societyId}`, {
    method: "GET",
    headers: {
      Authorization: "Bearer " + getToken(),
    },
  })
    .then(response => {
      if (!response.ok) {
        throw new Error('Erreur lors du chargement des frais.');
      }
      return response.json();
    })
    .then(data => {
      data.filter(cost => cost.est_abonnement === 0).forEach(cost => console.log(cost));
      const tableBody = document.getElementById('costs-table');

      if (data.length === 0) {
        tableBody.innerHTML = `
          <tr>
            <td colspan="5" class="text-center">
              <div class="alert alert-info mb-0">
                <i class="fas fa-info-circle me-2"></i>
                Vous n'avez aucun frais enregistré pour le moment.
              </div>
            </td>
          </tr>
        `;
        return;
      }

      let html = '';
      data.forEach(cost => {
        if (cost.est_abonnement === 0) { // Seulement les frais non-abonnements
          html += `
            <tr>
              <td>${cost.nom}</td>
              <td>${parseFloat(cost.montant).toLocaleString('fr-FR', {style: 'currency', currency: 'EUR'})}</td>
              <td>${cost.description || 'N/A'}</td>
              <td>${new Date(cost.date_creation).toLocaleDateString('fr-FR')}</td>
              <td>
                <button class="btn btn-sm btn-info view-cost" data-id="${cost.frais_id}" data-type="cost">
                  <i class="fas fa-eye"></i> Voir
                </button>
              </td>
            </tr>
          `;
        }
      });

      tableBody.innerHTML = html || `
        <tr>
          <td colspan="5" class="text-center">
            <div class="alert alert-info mb-0">
              <i class="fas fa-info-circle me-2"></i>
              Aucun frais non-abonnement trouvé.
            </div>
          </td>
        </tr>
      `;

      // Ajouter les écouteurs d'événements pour les boutons "Voir"
      setTimeout(() => {
        document.querySelectorAll('.view-cost').forEach(button => {
          button.addEventListener('click', function() {
            const costId = this.getAttribute('data-id');
            const costType = this.getAttribute('data-type');
            viewCostDetails(costId, costType);
          });
        });
      }, 100);
    })
    .catch(error => {
      console.error(error);
      document.getElementById('costs-table').innerHTML = `
        <tr>
          <td colspan="5" class="text-center text-danger">
            Erreur lors du chargement des frais. Veuillez réessayer plus tard.
          </td>
        </tr>
      `;
    });
}

// Charger les collaborateurs
function loadEmployees(societyId) {
  fetch(`/api/company/getAllEmployee.php?societe_id=${societyId}`, {
    method: "GET",
    headers: {
      Authorization: "Bearer " + getToken(),
    },
  })
    .then((response) => response.json())
    .then((data) => {
      const tableBody = document.getElementById("employees-table");

      if (data.length === 0) {
        tableBody.innerHTML =
          '<tr><td colspan="8" class="text-center">Aucun collaborateur trouvé</td></tr>';
        return;
      }

      tableBody.innerHTML = "";
      data.forEach((employee) => {
        tableBody.innerHTML += `
                    <tr>
                        <td>${employee.collaborateur_id}</td>
                        <td>${employee.nom}</td>
                        <td>${employee.prenom}</td>
                        <td>${employee.username}</td>
                        <td>${employee.role}</td>
                        <td>${employee.email}</td>
                        <td>${employee.telephone}</td>
                        <td>
                            <button class="btn btn-sm btn-warning" onclick="editEmployee(${employee.collaborateur_id}, '${employee.nom}', '${employee.prenom}', '${employee.username}', '${employee.role}', '${employee.email}', '${employee.telephone}')">
                                <i class="fas fa-edit"></i>
                            </button>
                        </td>
                    </tr>
                `;
      });
    })
    .catch((error) => {
      console.error("Erreur lors du chargement des collaborateurs:", error);
      document.getElementById(
        "employees-table"
      ).innerHTML = `<tr><td colspan="8" class="text-center text-danger">Erreur lors du chargement des collaborateurs</td></tr>`;
    });
}

// Charger uniquement les employés actifs (desactivate=0)
function loadActiveEmployees(societyId, filters = {}) {
  // Afficher indicateur de chargement
  const tableBody = document.getElementById('employees-table');
  tableBody.innerHTML = `
    <tr>
      <td colspan="9" class="text-center">
        <div class="spinner-border text-primary" role="status">
          <span class="visually-hidden">Chargement des collaborateurs...</span>
        </div>
      </td>
    </tr>`;

  // Construire l'URL avec les filtres
  let url = `/api/company/getAllEmployee.php?societe_id=${societyId}&desactivate=0`;

  if (filters.name) url += `&name=${encodeURIComponent(filters.name)}`;
  if (filters.role) url += `&role=${encodeURIComponent(filters.role)}`;
  if (filters.date) url += `&date=${filters.date}`;

  fetch(url, {
    method: 'GET',
    headers: {
      'Authorization': 'Bearer ' + getToken()
    }
  })
  .then(response => {
    if (!response.ok) {
      throw new Error('Erreur réseau lors du chargement des collaborateurs');
    }
    return response.json();
  })
  .then(data => {
    // Vider l'indicateur de chargement
    tableBody.innerHTML = '';
    
    // Vérifier si data est défini et n'est pas vide ou null
    if (!data || data.length === 0 || (Array.isArray(data) && data.length === 0)) {
      tableBody.innerHTML = `
        <tr>
          <td colspan="9" class="text-center">
            <div class="alert alert-info mb-0">
              <i class="fas fa-user-plus me-2"></i>
              Pas de collaborateur actif : ajoutez-en ! 
              <button class="btn btn-sm btn-primary ms-3" data-bs-toggle="modal" data-bs-target="#addEmployeeModal">
                <i class="fas fa-plus"></i> Ajouter un collaborateur
              </button>
            </div>
          </td>
        </tr>`;
      return;
    }

    // Parcourir les données et les afficher
    data.forEach(employee => {
      const dateCreation = employee.date_creation ? new Date(employee.date_creation).toLocaleDateString('fr-FR') : 'N/A';
      tableBody.innerHTML += `
        <tr>
          <td>${employee.collaborateur_id}</td>
          <td>${employee.nom}</td>
          <td>${employee.prenom}</td>
          <td>${employee.username}</td>
          <td>${employee.role}</td>
          <td>${employee.email}</td>
          <td>${employee.telephone}</td>
          <td>${dateCreation}</td>
          <td>
            <button class="btn btn-sm btn-info" onclick="viewEmployeeDetails(${employee.collaborateur_id})">
              <i class="fas fa-eye"></i> Voir
            </button>
            <button class="btn btn-sm btn-warning" onclick="editEmployee(${employee.collaborateur_id}, '${employee.nom}', '${employee.prenom}', '${employee.username}', '${employee.role}', '${employee.email}', '${employee.telephone}')">
              <i class="fas fa-edit"></i> Modifier
            </button>
            <button class="btn btn-sm btn-danger" onclick="confirmDeactivateEmployee(${employee.collaborateur_id})">
              <i class="fas fa-user-slash"></i> Désactiver
            </button>
          </td>
        </tr>
      `;
    });
  })
  .catch(error => {
    console.error('Erreur lors du chargement des collaborateurs actifs:', error);
    tableBody.innerHTML = `
      <tr>
        <td colspan="9" class="text-center text-danger">
          Erreur lors du chargement des collaborateurs actifs: ${error.message}
        </td>
      </tr>`;
  });
}

// Charger uniquement les employés désactivés (desactivate=1)
function loadInactiveEmployees(societyId, filters = {}) {
  // Afficher indicateur de chargement
  const tableBody = document.getElementById('employees-table');
  tableBody.innerHTML = `
    <tr>
      <td colspan="9" class="text-center">
        <div class="spinner-border text-primary" role="status">
          <span class="visually-hidden">Chargement des collaborateurs...</span>
        </div>
      </td>
    </tr>`;

  // Construire l'URL avec les filtres
  let url = `/api/company/getAllEmployee.php?societe_id=${societyId}&desactivate=1`;

  if (filters.name) url += `&name=${encodeURIComponent(filters.name)}`;
  if (filters.role) url += `&role=${encodeURIComponent(filters.role)}`;
  if (filters.date) url += `&date=${filters.date}`;

  fetch(url, {
    method: 'GET',
    headers: {
      'Authorization': 'Bearer ' + getToken()
    }
  })
  .then(response => {
    if (!response.ok) {
      throw new Error('Erreur réseau lors du chargement des collaborateurs');
    }
    return response.json();
  })
  .then(data => {
    // Vider l'indicateur de chargement
    tableBody.innerHTML = '';
    
    // Vérifier si data est défini et n'est pas vide ou null
    if (!data || data.length === 0 || (Array.isArray(data) && data.length === 0)) {
      tableBody.innerHTML = `
        <tr>
          <td colspan="9" class="text-center">
            <div class="alert alert-info mb-0">
              <i class="fas fa-info-circle me-2"></i>
              Aucun collaborateur désactivé disponible
            </div>
          </td>
        </tr>`;
      return;
    }

    // Parcourir les données et les afficher
    data.forEach(employee => {
      const dateCreation = employee.date_creation ? new Date(employee.date_creation).toLocaleDateString('fr-FR') : 'N/A';
      tableBody.innerHTML += `
        <tr>
          <td>${employee.collaborateur_id}</td>
          <td>${employee.nom}</td>
          <td>${employee.prenom}</td>
          <td>${employee.username}</td>
          <td>${employee.role}</td>
          <td>${employee.email}</td>
          <td>${employee.telephone}</td>
          <td>${dateCreation}</td>
          <td>
            <button class="btn btn-sm btn-info" onclick="viewEmployeeDetails(${employee.collaborateur_id})">
              <i class="fas fa-eye"></i> Voir
            </button>
            <button class="btn btn-sm btn-success" onclick="reactivateEmployee(${employee.collaborateur_id})">
              <i class="fas fa-user-check"></i> Réactiver
            </button>
          </td>
        </tr>
      `;
    });
  })
  .catch(error => {
    console.error('Erreur lors du chargement des collaborateurs désactivés:', error);
    tableBody.innerHTML = `
      <tr>
        <td colspan="9" class="text-center text-danger">
          Erreur lors du chargement des collaborateurs désactivés: ${error.message}
        </td>
      </tr>`;
  });
}

// Fonction pour charger les 5 dernières factures
function loadRecentInvoices(societyId) {
  fetch(`/api/company/getInvoices.php?societe_id=${societyId}&limit=5`, {
    method: "GET",
    headers: {
      Authorization: "Bearer " + getToken(),
    },
  })
    .then((response) => {
      if (!response.ok) {
        throw new Error("Erreur lors du chargement des factures");
      }
      return response.json();
    })
    .then((data) => {
      const tableBody = document.getElementById("invoices-table");
      if (data.length === 0) {
        tableBody.innerHTML =
          '<tr><td colspan="8" class="text-center">Aucune facture trouvée</td></tr>';
        return;
      }

      let html = "";
      data.forEach((invoice) => {
        const statusClass = getStatusBadge(invoice.statut);
        html += `
                <tr>
                    <td>${invoice.facture_id}</td>
                    <td>${new Date(invoice.date_emission).toLocaleDateString(
                      "fr-FR"
                    )}</td>
                    <td>${new Date(invoice.date_echeance).toLocaleDateString(
                      "fr-FR"
                    )}</td>
                    <td>${invoice.montant.toLocaleString("fr-FR", {
                      style: "currency",
                      currency: "EUR",
                    })} €</td>
                    <td>${invoice.montant_tva.toLocaleString("fr-FR", {
                      style: "currency",
                      currency: "EUR",
                    })} €</td>
                    <td>${invoice.montant_ht.toLocaleString("fr-FR", {
                      style: "currency",
                      currency: "EUR",
                    })} €</td>
                    <td><span class="badge bg-${statusClass}">${
          invoice.statut
        }</span></td>
                    <td>
                        <a href="/frontOffice/societe/facture-details.php?id=${
                          invoice.facture_id
                        }" class="btn btn-sm btn-info" title="Voir les détails">
                            <i class="fas fa-eye"></i>
                        </a>
                    </td>
                </tr>`;
      });
      tableBody.innerHTML = html;
    })
    .catch((error) => {
      console.error("Erreur:", error);
      document.getElementById("invoices-table").innerHTML =
        '<tr><td colspan="8" class="text-center text-danger">Erreur lors du chargement des factures</td></tr>';
    });
}

// Ajouter un employé
function addEmployee(societyId) {
  // Vérifier d'abord si la société peut ajouter un employé
  fetch(`/api/company/checkEmployeeLimit.php?societe_id=${societyId}`, {
    method: 'GET',
    headers: {
      'Authorization': 'Bearer ' + getToken()
    }
  })
  .then(response => response.json())
  .then(data => {
    if (!data.status) {
      alert(data.message);
      return;
    }
    
    // Si la limite n'est pas atteinte, continuer avec l'ajout de l'employé
    const formData = {
      nom: document.getElementById("nom").value,
      prenom: document.getElementById("prenom").value,
      username: document.getElementById("username").value,
      role: document.getElementById("role").value,
      email: document.getElementById("email").value,
      telephone: document.getElementById("telephone").value,
      password: document.getElementById("password").value,
      id_societe: societyId,
    };

    fetch("/api/employee/create.php", {
      method: "PUT",
      headers: {
        "Content-Type": "application/json",
        Authorization: "Bearer " + getToken(),
      },
      body: JSON.stringify(formData),
    })
    .then((response) => response.json())
    .then((data) => {
      if (data.id) {
        // Succès
        const modal = bootstrap.Modal.getInstance(
          document.getElementById("addEmployeeModal")
        );
        modal.hide();

        // Réinitialiser le formulaire
        document.getElementById("addEmployeeForm").reset();

        // Recharger la liste des employés
        refreshEmployeeList();
        
        // Mettre à jour les informations sur la limite
        refreshEmployeeLimitInfo();

        // Afficher un message de succès
        alert("Collaborateur ajouté avec succès!");
      } else {
        // Erreur
        alert(`Erreur: ${data.message || "Une erreur est survenue"}`);
      }
    })
    .catch((error) => {
      console.error("Erreur lors de l'ajout du collaborateur:", error);
      alert("Une erreur est survenue lors de l'ajout du collaborateur");
    });
  })
  .catch(error => {
    console.error('Erreur lors de la vérification de la limite d\'employés:', error);
    alert('Erreur lors de la vérification de votre limite d\'employés.');
  });
}

// Ouvrir le modal de modification d'un employé
function editEmployee(id, nom, prenom, username, role, email, telephone) {
  document.getElementById("edit_employee_id").value = id;
  document.getElementById("edit_nom").value = nom;
  document.getElementById("edit_prenom").value = prenom;
  document.getElementById("edit_username").value = username;
  document.getElementById("edit_role").value = role;
  document.getElementById("edit_email").value = email;
  document.getElementById("edit_telephone").value = telephone;
  document.getElementById("edit_password").value = "";

  const modal = new bootstrap.Modal(
    document.getElementById("editEmployeeModal")
  );
  modal.show();
}

// Mettre à jour un employé
function updateEmployee() {
  const formData = {
    id: document.getElementById("edit_employee_id").value,
    nom: document.getElementById("edit_nom").value,
    prenom: document.getElementById("edit_prenom").value,
    username: document.getElementById("edit_username").value,
    role: document.getElementById("edit_role").value,
    email: document.getElementById("edit_email").value,
    telephone: document.getElementById("edit_telephone").value,
  };

  // Ajouter le mot de passe uniquement s'il a été renseigné
  const password = document.getElementById("edit_password").value;
  if (password) {
    formData.password = password;
  }

  fetch("/api/employee/modify.php", {
    method: "PATCH",
    headers: {
      "Content-Type": "application/json",
      Authorization: "Bearer " + getToken(),
    },
    body: JSON.stringify(formData),
  })
    .then((response) => response.json())
    .then((data) => {
      if (data.success) {
        // Succès
        const modal = bootstrap.Modal.getInstance(
          document.getElementById("editEmployeeModal")
        );
        modal.hide();

        // Recharger la liste des employés
        loadEmployees(societyId);

        // Afficher un message de succès
        alert("Collaborateur modifié avec succès!");
      } else {
        // Erreur
        alert(`Erreur: ${data.message || "Une erreur est survenue"}`);
      }
    })
    .catch((error) => {
      console.error("Erreur lors de la modification du collaborateur:", error);
      alert("Une erreur est survenue lors de la modification du collaborateur");
    });
}

// Utilitaire pour obtenir la classe de badge en fonction du statut
function getStatusBadge(status) {
  switch (status) {
    case "accepté":
    case "Payee":
      return "success";
    case "envoyé":
    case "Attente":
      return "warning";
    case "refusé":
    case "Annulé":
      return "danger";
    case "brouillon":
      return "secondary";
    default:
      return "secondary";
  }
}

// Fonction pour charger les devis avec filtres optionnels
function loadEstimates(societyId, filters = {}) {
  // Construire l'URL avec les filtres
  let url = `/api/company/getEstimate.php?societe_id=${societyId}`;

  // Ajouter les filtres s'ils sont définis
  if (filters.status) url += `&statut=${filters.status}`;
  if (filters.startDate) url += `&date_debut=${filters.startDate}`;
  if (filters.endDate) url += `&date_fin=${filters.endDate}`;

  fetch(url, {
    method: "GET",
    headers: {
      Authorization: "Bearer " + getToken(),
    },
  })
    .then((response) => response.json())
    .then((data) => {
      const tableBody = document.getElementById("estimates-table");

      if (data.includes("not found")) {
        tableBody.innerHTML =
          '<tr><td colspan="8" class="text-center">Aucun devis trouvé</td></tr>';
        return;
      }

      tableBody.innerHTML = "";
      data.forEach((estimate) => {
        tableBody.innerHTML += `
          <tr>
            <td>${estimate.devis_id}</td>
            <td>${new Date(estimate.date_debut).toLocaleDateString("fr-FR")}</td>
            <td>${new Date(estimate.date_fin).toLocaleDateString("fr-FR")}</td>
            <td><span class="badge bg-${getStatusBadge(estimate.statut)}">${
          estimate.statut
        }</span></td>
            <td>${estimate.montant.toLocaleString("fr-FR", { style: "currency", currency: "EUR" })} €</td>
            <td>${estimate.montant_ht.toLocaleString("fr-FR", { style: "currency", currency: "EUR" })} €</td>
            <td>${estimate.montant_tva.toLocaleString("fr-FR", { style: "currency", currency: "EUR" })} €</td>
            <td>
              <button class="btn btn-sm btn-info" onclick="viewEstimateDetails(${estimate.devis_id})">
          <i class="fas fa-eye"></i>
              </button>
              ${estimate.statut === "envoyé"
            ? `<button class="btn btn-sm btn-success" onclick="approveEstimate(${estimate.devis_id})">
                <i class="fas fa-check"></i>
               </button>
               <button class="btn btn-sm btn-danger" onclick="rejectEstimate(${estimate.devis_id})">
                <i class="fas fa-times"></i>
               </button>`
            : ""
              }
              ${
          estimate.statut === "brouillon"
            ? `<button class="btn btn-sm btn-warning" onclick="editEstimateDetails(${estimate.devis_id})">
                <i class="fas fa-edit"></i>
               </button>`
            : ""
              }
              ${
          estimate.fichier
            ? `<a href="/uploads/devis/${estimate.fichier}" class="btn btn-sm btn-secondary" target="_blank">
                <i class="fas fa-file-pdf"></i>
               </a>`
            : ""
              }
            </td>
          </tr>
        `;
      });
    })
    .catch((error) => {
      console.error("Erreur lors du chargement des devis:", error);
      document.getElementById(
        "estimates-table"
      ).innerHTML = `<tr><td colspan="8" class="text-center text-danger">Erreur lors du chargement des devis</td></tr>`;
    });
}

// Fonction pour appliquer les filtres aux devis
function applyEstimateFilters(societyId) {
  const filters = {
    status: document.getElementById("statusFilter").value,
    startDate: document.getElementById("dateStartFilter").value,
    endDate: document.getElementById("dateEndFilter").value,
  };

  loadEstimates(societyId, filters);
}

// Fonction pour réinitialiser les filtres des devis
function resetEstimateFilters(societyId) {
  // Réinitialiser le formulaire
  document.getElementById("estimateFilterForm").reset();

  // Recharger tous les devis sans filtre
  loadEstimates(societyId);
}

// Fonction pour voir les détails d'un devis
function viewEstimateDetails(id) {
  fetch(`/api/estimate/getOne.php?devis_id=${id}`, {
    method: "GET",
    headers: {
      Authorization: "Bearer " + getToken(),
    },
  })
    .then((response) => response.json())
    .then((estimate) => {
      const detailsContainer = document.getElementById("estimate-details");

      let fichierLink = estimate.fichier
        ? `<a href="/uploads/devis/${estimate.fichier}" target="_blank" class="btn btn-sm btn-primary">
            <i class="fas fa-file-pdf"></i> Voir le PDF
           </a>`
        : '<span class="text-muted">Aucun fichier attaché</span>';

      detailsContainer.innerHTML = `
        <div class="row mb-3">
          <div class="col-md-6">
            <p><strong>ID:</strong> ${estimate.devis_id}</p>
            <p><strong>Date de début:</strong> ${new Date(estimate.date_debut).toLocaleDateString("fr-FR")}</p>
            <p><strong>Date de fin:</strong> ${new Date(estimate.date_fin).toLocaleDateString("fr-FR")}</p>
          </div>
          <div class="col-md-6">
            <p><strong>Statut:</strong> <span class="badge bg-${getStatusBadge(estimate.statut)}">${estimate.statut}</span></p>
            <p><strong>Montant TTC:</strong> ${estimate.montant} €</p>
            <p><strong>Montant HT:</strong> ${estimate.montant_ht} €</p>
            <p><strong>Montant TVA:</strong> ${estimate.montant_tva} €</p>
          </div>
        </div>
        <div class="row mb-3">
          <div class="col-12">
            <p><strong>Fichier:</strong> ${fichierLink}</p>
          </div>
        </div>
      `;

      // Stocker l'ID pour l'édition et la conversion
      document.getElementById("editEstimate").setAttribute("data-id", id);
      document.getElementById("convertToContract").setAttribute("data-id", id);

      // Afficher ou masquer le bouton de conversion selon le statut
      document.getElementById("convertToContract").style.display =
        estimate.statut === "accepté" ? "inline-block" : "none";

      // Afficher ou masquer le bouton d'édition selon le statut
      document.getElementById("editEstimate").style.display =
        estimate.statut === "brouillon" ? "inline-block" : "none";

      // Afficher le modal
      const modal = new bootstrap.Modal(document.getElementById("viewEstimateModal"));
      modal.show();
    })
    .catch((error) => {
      console.error("Erreur lors du chargement des détails du devis:", error);
      alert("Erreur lors du chargement des détails du devis");
    });
}

// Fonction pour éditer un devis
function editEstimateDetails(id) {
  fetch(`/api/estimate/getOne.php?devis_id=${id}`, {
    method: "GET",
    headers: {
      Authorization: "Bearer " + getToken(),
    },
  })
    .then((response) => response.json())
    .then((estimate) => {
      // Pré-remplir le formulaire d'ajout
      document.getElementById("start_date").value = estimate.date_debut;
      document.getElementById("end_date").value = estimate.date_fin;
      document.getElementById("montant").value = estimate.montant;
      document.getElementById("montant_ht").value = estimate.montant_ht;
      document.getElementById("montant_tva").value = estimate.montant_tva;
      document.getElementById("statut").value = estimate.statut;

      // Changer le titre et le bouton d'action du modal
      document.getElementById("addEstimateModalLabel").textContent = "Modifier le devis";
      document.getElementById("saveEstimate").textContent = "Mettre à jour";

      // Stocker l'ID pour la modification
      document.getElementById("saveEstimate").setAttribute("data-id", id);
      document.getElementById("saveEstimate").setAttribute("data-action", "edit");

      // Afficher le modal
      const modal = new bootstrap.Modal(document.getElementById("addEstimateModal"));
      modal.show();

      // Fermer le modal de détails s'il est ouvert
      const detailsModal = bootstrap.Modal.getInstance(document.getElementById("viewEstimateModal"));
      if (detailsModal) {
        detailsModal.hide();
      }
    })
    .catch((error) => {
      console.error("Erreur lors du chargement des détails du devis:", error);
      alert("Erreur lors du chargement des détails du devis");
    });
}

// Fonction pour approuver un devis
function approveEstimate(id) {
  if (confirm("Êtes-vous sûr de vouloir accepter ce devis ?")) {
    fetch(`/api/estimate/modifyState.php`, {
      method: "PATCH",
      headers: {
        "Content-Type": "application/json",
        Authorization: "Bearer " + getToken(),
      },
      body: JSON.stringify({
        devis_id: id,
        statut: "accepté"
      }),
    })
      .then((response) => response.json())
      .then((data) => {
        if (data.success) {
          loadEstimates(societyId);
          alert("Devis accepté avec succès!");
        } else {
          alert(`Erreur: ${data.message || "Une erreur est survenue"}`);
        }
      })
      .catch((error) => {
        console.error("Erreur lors de l'acceptation du devis:", error);
        alert("Une erreur est survenue lors de l'acceptation du devis");
      });
  }
}

// Fonction pour refuser un devis
function rejectEstimate(id) {
  if (confirm("Êtes-vous sûr de vouloir refuser ce devis ?")) {
    fetch(`/api/estimate/modifyState.php`, {
      method: "PATCH",
      headers: {
        "Content-Type": "application/json",
        Authorization: "Bearer " + getToken(),
      },
      body: JSON.stringify({
        devis_id: id,
        statut: "refusé"
      }),
    })
      .then((response) => response.json())
      .then((data) => {
        if (data.success) {
          // Recharger les devis
          loadEstimates(societyId);
          alert("Devis refusé avec succès!");
        } else {
          alert(`Erreur: ${data.message || "Une erreur est survenue"}`);
        }
      })
      .catch((error) => {
        console.error("Erreur lors du refus du devis:", error);
        alert("Une erreur est survenue lors du refus du devis");
      });
  }
}

// Fonction pour convertir un devis en contrat
function convertEstimateToContract(id) {
  if (confirm("Êtes-vous sûr de vouloir convertir ce devis en contrat ?")) {
    fetch(`/api/estimate/convertToContract.php`, {
      method: "PATCH",
      headers: {
        "Content-Type": "application/json",
        Authorization: "Bearer " + getToken(),
      },
      body: JSON.stringify({
        devis_id: id
      }),
    })
      .then((response) => response.json())
      .then((data) => {
        if (data.success) {
          // Fermer le modal
          const modal = bootstrap.Modal.getInstance(document.getElementById("viewEstimateModal"));
          modal.hide();

          // Recharger les devis
          loadEstimates(societyId);
          alert("Devis converti en contrat avec succès!");
        } else {
          alert(`Erreur: ${data.message || "Une erreur est survenue"}`);
        }
      })
      .catch((error) => {
        console.error("Erreur lors de la conversion du devis en contrat:", error);
        alert("Une erreur est survenue lors de la conversion du devis en contrat");
      });
  }
}

// Fonction pour réinitialiser le modal de devis
function resetEstimateModal() {
  document.getElementById("addEstimateModalLabel").textContent = "Ajouter un devis";
  document.getElementById("saveEstimate").textContent = "Enregistrer";
  document.getElementById("saveEstimate").removeAttribute("data-id");
  document.getElementById("saveEstimate").removeAttribute("data-action");
}

// Fonction pour ajouter/modifier un devis
function addNewEstimate() {
  const saveButton = document.getElementById("saveEstimate");
  const isEdit = saveButton.getAttribute("data-action") === "edit";
  const devisId = isEdit ? saveButton.getAttribute("data-id") : null;

  // Récupérer les données du formulaire
  const formData = {
    date_debut: document.getElementById("start_date").value,
    date_fin: document.getElementById("end_date").value,
    montant: parseFloat(document.getElementById("montant").value),
    montant_ht: parseFloat(document.getElementById("montant_ht").value),
    montant_tva: parseFloat(document.getElementById("montant_tva").value),
    statut: document.getElementById("statut").value,
    societe_id: societyId
  };

  // Ajouter l'ID du devis pour la modification
  if (isEdit) {
    formData.devis_id = devisId;
  }

  if (isEdit) {
    updateEstimate(formData);
  } else {
    createEstimate(formData);
  }
}

// Fonction pour créer un nouveau devis
function createEstimate(formData) {
  fetch("/api/estimate/create.php", {
    method: "PUT",
    headers: {
      "Content-Type": "application/json",
      Authorization: "Bearer " + getToken()
    },
    body: JSON.stringify(formData)
  })
  .then(response => response.json())
  .then(data => {
    if (data.success) {
      // Fermer le modal
      const modal = bootstrap.Modal.getInstance(document.getElementById("addEstimateModal"));
      modal.hide();

      // Réinitialiser le formulaire
      document.getElementById("addEstimateForm").reset();

      // Recharger les devis
      loadEstimates(societyId);

      // Afficher un message de succès
      alert("Devis créé avec succès !");
    } else {
      alert(`Erreur: ${data.message || "Une erreur est survenue"}`);
    }
  })
  .catch(error => {
    console.error("Erreur lors de la création du devis:", error);
    alert("Une erreur est survenue lors de la création du devis");
  });
}

// Fonction pour mettre à jour un devis existant
function updateEstimate(formData) {
  fetch("/api/estimate/update.php", {
    method: "PATCH",
    headers: {
      "Content-Type": "application/json",
      Authorization: "Bearer " + getToken()
    },
    body: JSON.stringify(formData)
  })
  .then(response => response.json())
  .then(data => {
    if (data.success) {
      // Fermer le modal
      const modal = bootstrap.Modal.getInstance(document.getElementById("addEstimateModal"));
      modal.hide();

      // Réinitialiser le formulaire et le modal
      document.getElementById("addEstimateForm").reset();
      resetEstimateModal();

      // Recharger les devis
      loadEstimates(societyId);

      // Afficher un message de succès
      alert("Devis mis à jour avec succès !");
    } else {
      alert(`Erreur: ${data.message || "Une erreur est survenue"}`);
    }
  })
  .catch(error => {
    console.error("Erreur lors de la mise à jour du devis:", error);
    alert("Une erreur est survenue lors de la mise à jour du devis");
  });
}

function downloadInvoicePDF(invoiceId) {
  // Construction de l'URL de l'API qui génère le PDF
  const url = `/api/invoice/generatePDFForCompany.php?facture_id=${invoiceId}&token=${getToken()}`;

  // Ouvrir l'URL dans un nouvel onglet
  window.open(url, '_blank');

  // Recharger les factures après un court délai
  setTimeout(() => {
    loadAllInvoices(societyId);
  }, 1000);
}


  // Fonction pour charger toutes les factures
  function loadAllInvoices(societyId) {
      fetch(`/api/company/getInvoices.php?societe_id=${societyId}`, {
          method: 'GET',
          headers: {
              'Authorization': 'Bearer ' + getToken()
          }
      })
      .then(response => response.json())
      .then(data => {
          const tableBody = document.getElementById('invoices-table');

          if (data.length === 0) {
              tableBody.innerHTML = '<tr><td colspan="8" class="text-center">Aucune facture trouvée</td></tr>';
              return;
          }

            tableBody.innerHTML = '';
            data.forEach(invoice => {
              const statusClass = getStatusBadge(invoice.statut);
              tableBody.innerHTML += `
                <tr>
                  <td>${invoice.facture_id}</td>
                  <td>${new Date(invoice.date_emission).toLocaleDateString('fr-FR')}</td>
                  <td>${new Date(invoice.date_echeance).toLocaleDateString('fr-FR')}</td>
                  <td>${invoice.montant.toLocaleString('fr-FR')} €</td>
                  <td>${invoice.montant_tva.toLocaleString('fr-FR')} €</td>
                  <td>${invoice.montant_ht.toLocaleString('fr-FR')} €</td>
                  <td><span class="badge bg-${statusClass}">${invoice.statut}</span></td>
                  <td>
                    <button class="btn btn-sm btn-info" onclick="viewInvoiceDetails(${invoice.facture_id})">
                      <i class="fas fa-eye"></i>
                    </button>
                    ${invoice.statut === 'Payee' ?
                      `<button class="btn btn-sm btn-danger" onclick="downloadInvoicePDF(${invoice.facture_id})">
                        <i class="fas fa-file-pdf"></i>
                      </button>` : ''}
                  </td>
                </tr>
              `;
            });
      })
      .catch(error => {
          console.error('Erreur lors du chargement des factures:', error);
          document.getElementById('invoices-table').innerHTML =
              `<tr><td colspan="8" class="text-center text-danger">Erreur lors du chargement des factures</td></tr>`;
      });
  }

  // Fonction pour appliquer les filtres
  function applyInvoiceFilters() {
      const status = document.getElementById('statusFilter').value;
      const startDate = document.getElementById('dateStartFilter').value;
      const endDate = document.getElementById('dateEndFilter').value;

      // Construire l'URL avec les filtres
      let url = `/api/company/getInvoices.php?societe_id=${societyId}`;
      if (status) url += `&statut=${status}`;
      if (startDate) url += `&date_debut=${startDate}`;
      if (endDate) url += `&date_fin=${endDate}`;

      fetch(url, {
          method: 'GET',
          headers: {
              'Authorization': 'Bearer ' + getToken()
          }
      })
      .then(response => response.json())
      .then(data => {
          const tableBody = document.getElementById('invoices-table');

          if (data.length === 0) {
              tableBody.innerHTML = '<tr><td colspan="8" class="text-center">Aucune facture trouvée avec ces critères</td></tr>';
              return;
          }

          // Afficher les résultats filtrés (même code que dans loadAllInvoices)
          tableBody.innerHTML = '';
          data.forEach(invoice => {
              const statusClass = getStatusBadge(invoice.statut);
              tableBody.innerHTML += `
                  <tr>
                      <td>${invoice.facture_id}</td>
                      <td>${new Date(invoice.date_emission).toLocaleDateString('fr-FR')}</td>
                      <td>${new Date(invoice.date_echeance).toLocaleDateString('fr-FR')}</td>
                      <td>${invoice.montant.toLocaleString('fr-FR')} €</td>
                      <td>${invoice.montant_tva.toLocaleString('fr-FR')} €</td>
                      <td>${invoice.montant_ht.toLocaleString('fr-FR')} €</td>
                      <td><span class="badge bg-${statusClass}">${invoice.statut}</span></td>
                      <td>
                          <button class="btn btn-sm btn-info" onclick="viewInvoiceDetails(${invoice.facture_id})">
                              <i class="fas fa-eye"></i>
                          </button>
                          ${invoice.statut !== 'Payee' ?
                              `<button class="btn btn-sm btn-success" onclick="markInvoiceAsPaid(${invoice.facture_id})">
                                  <i class="fas fa-check"></i>
                              </button>` : ''}
                          <button class="btn btn-sm btn-danger" onclick="downloadInvoicePDF(${invoice.facture_id})">
                              <i class="fas fa-file-pdf"></i>
                          </button>
                      </td>
                  </tr>
              `;
          });
      })
      .catch(error => {
          console.error('Erreur lors de l\'application des filtres:', error);
          document.getElementById('invoices-table').innerHTML =
              `<tr><td colspan="8" class="text-center text-danger">Erreur lors de l'application des filtres</td></tr>`;
      });
  }

  // Fonction pour voir les détails d'une facture
  function viewInvoiceDetails(id) {
      fetch(`/api/invoice/getOne.php?facture_id=${id}`, {
          method: 'GET',
          headers: {
              'Authorization': 'Bearer ' + getToken()
          }
      })
      .then(response => response.json())
      .then(invoice => {
          const detailsContainer = document.getElementById('invoice-details');
          detailsContainer.innerHTML = `
              <div class="row mb-3">
                  <div class="col-md-6">
                      <p><strong>ID:</strong> ${invoice.facture_id}</p>
                      <p><strong>Date d'émission:</strong> ${new Date(invoice.date_emission).toLocaleDateString('fr-FR')}</p>
                      <p><strong>Date d'échéance:</strong> ${new Date(invoice.date_echeance).toLocaleDateString('fr-FR')}</p>
                  </div>
                  <div class="col-md-6">
                      <p><strong>Montant HT:</strong> ${invoice.montant_ht.toLocaleString('fr-FR')} €</p>
                      <p><strong>TVA:</strong> ${invoice.montant_tva.toLocaleString('fr-FR')} €</p>
                      <p><strong>Montant TTC:</strong> ${invoice.montant.toLocaleString('fr-FR')} €</p>
                      <p><strong>Statut:</strong> <span class="badge bg-${getStatusBadge(invoice.statut)}">${invoice.statut}</span></p>
                  </div>
              </div>
              </div>
          `;

          // Désactiver le bouton "Marquer comme payée" si déjà payée
          const payWithStripeBtn = document.getElementById('payWithStripe');

          // Stocker l'ID de la facture courante pour le traitement du paiement
          currentInvoiceId = invoice.facture_id;

          if (invoice.statut === 'Payee') {
              // Si la facture est déjà payée, désactiver les boutons de paiement
              payWithStripeBtn.style.display = 'none';
          } else {
              // Afficher le bouton de paiement Stripe
              payWithStripeBtn.style.display = 'inline-block';
          }

          // Afficher le modal
          const modal = new bootstrap.Modal(document.getElementById('viewInvoiceModal'));
          modal.show();
      })
      .catch(error => {
          console.error('Erreur lors du chargement des détails de la facture:', error);
          alert('Erreur lors du chargement des détails de la facture');
      });
  }

  // Fonction pour marquer une facture comme payée
  function markInvoiceAsPaid(id) {
      if (confirm('Êtes-vous sûr de vouloir marquer cette facture comme payée ?')) {
          fetch(`/api/company/updateInvoiceStatus.php`, {
              method: 'POST',
              headers: {
                  'Content-Type': 'application/json',
                  'Authorization': 'Bearer ' + getToken()
              },
              body: JSON.stringify({
                  facture_id: id,
                  statut: 'Payee'
              })
          })
          .then(response => response.json())
          .then(data => {
              if (data.success) {
                  alert('Statut de la facture mis à jour avec succès !');

                  // Fermer le modal de détails s'il est ouvert
                  const detailModal = bootstrap.Modal.getInstance(document.getElementById('viewInvoiceModal'));
                  if (detailModal) {
                      detailModal.hide();
                  }

                  // Recharger les factures
                  loadAllInvoices(societyId);
              } else {
                  alert(`Erreur: ${data.message || 'Une erreur est survenue'}`);
              }
          })
          .catch(error => {
              console.error('Erreur lors de la mise à jour du statut:', error);
              alert('Une erreur est survenue lors de la mise à jour du statut');
          });
      }
  }

// Fonction pour afficher les détails d'un frais
function viewCost(id) {
  fetch(`/api/company/getOneCost.php?cost_id=${id}`, {
    method: "GET",
    headers: {
      Authorization: "Bearer " + getToken(),
    },
  })
    .then((response) => response.json())
    .then((cost) => {
      const detailsContainer = document.getElementById("otherCost-details");

      // Formater la date
      const dateCreation = cost.date_creation ? new Date(cost.date_creation).toLocaleDateString("fr-FR") : "N/A";

      detailsContainer.innerHTML = `
              <div class="row mb-3">
                  <div class="col-md-6">
                      <p><strong>ID:</strong> ${cost.other_cost_id}</p>
                      <p><strong>Nom:</strong> ${cost.name}</p>
                      <p><strong>Montant:</strong> ${cost.price.toLocaleString("fr-FR")} €</p>
                  </div>
                  <div class="col-md-6">
                      <p><strong>Facture associée:</strong> ${cost.facture_id}</p>
                      <p><strong>Date de création:</strong> ${dateCreation}</p>
                  </div>
              </div>
          `;

      // Préparer les boutons d'action avec l'ID
      document.getElementById("editOtherCost").setAttribute("data-id", cost.other_cost_id);
      document.getElementById("deleteOtherCost").setAttribute("data-id", cost.other_cost_id);

      // Afficher le modal
      const modal = new bootstrap.Modal(
        document.getElementById("viewOtherCostModal")
      );
      modal.show();
    })
    .catch((error) => {
      console.error("Erreur lors du chargement des détails du frais:", error);
      alert("Erreur lors du chargement des détails du frais");
    });
}

// Fonction pour charger les factures dans les listes déroulantes
function loadInvoicesForSelect() {
  fetch(`/api/company/getInvoices.php?societe_id=${societyId}`, {
    method: "GET",
    headers: {
      Authorization: "Bearer " + getToken()
    }
  })
  .then(response => response.json())
  .then(data => {
    const addSelect = document.getElementById('cost_facture');
    const editSelect = document.getElementById('edit_cost_facture');

    let options = '<option value="">Sélectionner une facture</option>';

    data.forEach(invoice => {
      options += `<option value="${invoice.facture_id}">Facture #${invoice.facture_id} - ${invoice.montant} €</option>`;
    });

    addSelect.innerHTML = options;
    editSelect.innerHTML = options;
  })
  .catch(error => {
    console.error("Erreur lors du chargement des factures:", error);
  });
}

// Fonction pour ajouter un nouveau frais
function addNewOtherCost() {
  const formData = {
    name: document.getElementById('cost_name').value,
    price: parseFloat(document.getElementById('cost_montant').value),
    facture_id: document.getElementById('cost_facture').value
  };

  fetch('/api/company/createOtherCost.php', {
    method: 'PUT',
    headers: {
      'Content-Type': 'application/json',
      'Authorization': 'Bearer ' + getToken()
    },
    body: JSON.stringify(formData)
  })
  .then(response => response.json())
  .then(data => {
    if (data.success) {
      // Fermer le modal
      const modal = bootstrap.Modal.getInstance(document.getElementById('addOtherCostModal'));
      modal.hide();

      // Réinitialiser le formulaire
      document.getElementById('addOtherCostForm').reset();

      // Recharger la liste
      loadOtherCosts(societyId);

      // Afficher un message de succès
      alert('Frais ajouté avec succès!');
    } else {
      alert(`Erreur: ${data.message || 'Une erreur est survenue'}`);
    }
  })
  .catch(error => {
    console.error('Erreur lors de l\'ajout du frais:', error);
    alert('Une erreur est survenue lors de l\'ajout du frais');
  });
}

// Fonction pour éditer un frais existant
function editOtherCostDetails(id) {
  fetch(`/api/company/getOneCost.php?cost_id=${id}`, {
    method: 'GET',
    headers: {
      'Authorization': 'Bearer ' + getToken()
    }
  })
  .then(response => response.json())
  .then(cost => {
    // Pré-remplir le formulaire
    document.getElementById('edit_other_cost_id').value = cost.other_cost_id;
    document.getElementById('edit_cost_name').value = cost.name;
    document.getElementById('edit_cost_montant').value = cost.price;
    document.getElementById('edit_cost_facture').value = cost.facture_id;

    // Fermer le modal de détails
    const detailsModal = bootstrap.Modal.getInstance(document.getElementById('viewOtherCostModal'));
    if (detailsModal) {
      detailsModal.hide();
    }

    // Ouvrir le modal d'édition
    const editModal = new bootstrap.Modal(document.getElementById('editOtherCostModal'));
    editModal.show();
  })
  .catch(error => {
    console.error('Erreur lors du chargement des détails du frais:', error);
    alert('Erreur lors du chargement des détails du frais');
  });
}

// Fonction pour mettre à jour un frais
function updateOtherCostDetails() {
  const formData = {
    other_cost_id: document.getElementById('edit_other_cost_id').value,
    name: document.getElementById('edit_cost_name').value,
    price: parseFloat(document.getElementById('edit_cost_montant').value),
    facture_id: document.getElementById('edit_cost_facture').value
  };

  fetch('/api/company/updateOtherCost.php', {
    method: 'PATCH',
    headers: {
      'Content-Type': 'application/json',
      'Authorization': 'Bearer ' + getToken()
    },
    body: JSON.stringify(formData)
  })
  .then(response => response.json())
  .then(data => {
    if (data.success) {
      // Fermer le modal
      const modal = bootstrap.Modal.getInstance(document.getElementById('editOtherCostModal'));
      modal.hide();

      // Recharger la liste
      loadOtherCosts(societyId);

      // Afficher un message de succès
      alert('Frais mis à jour avec succès!');
    } else {
      alert(`Erreur: ${data.message || 'Une erreur est survenue'}`);
    }
  })
  .catch(error => {
    console.error('Erreur lors de la mise à jour du frais:', error);
    alert('Une erreur est survenue lors de la mise à jour du frais');
  });
}

// Fonction pour supprimer un frais
function deleteOtherCost(id) {
  if (confirm('Êtes-vous sûr de vouloir supprimer ce frais?')) {
    fetch(`/api/company/deleteOtherCost.php`, {
      method: 'DELETE',
      headers: {
        'Content-Type': 'application/json',
        'Authorization': 'Bearer ' + getToken()
      },
      body: JSON.stringify({ other_cost_id: id })
    })
    .then(response => response.json())
    .then(data => {
      if (data.success) {
        // Fermer le modal de détails s'il est ouvert
        const detailModal = bootstrap.Modal.getInstance(document.getElementById('viewOtherCostModal'));
        if (detailModal) {
          detailModal.hide();
        }

        // Recharger la liste
        loadOtherCosts(societyId);

        // Afficher un message de succès
        alert('Frais supprimé avec succès!');
      } else {
        alert(`Erreur: ${data.message || 'Une erreur est survenue'}`);
      }
    })
    .catch(error => {
      console.error('Erreur lors de la suppression du frais:', error);
      alert('Une erreur est survenue lors de la suppression du frais');
    });
  }
}

// Fonction pour appliquer les filtres
function applyOtherCostFilters() {
  const nameFilter = document.getElementById('nameFilter').value;
  const dateStartFilter = document.getElementById('dateStartFilter').value;

  // Construire l'URL avec les filtres
  let url = `/api/company/getOtherCosts.php?societe_id=${societyId}`;
  if (nameFilter) url += `&name=${encodeURIComponent(nameFilter)}`;
  if (dateStartFilter) url += `&date_start=${dateStartFilter}`;

  fetch(url, {
    method: 'GET',
    headers: {
      'Authorization': 'Bearer ' + getToken()
    }
  })
  .then(response => response.json())
  .then(data => {
    const tableBody = document.getElementById('costs-table');

    if (data.length === 0) {
      tableBody.innerHTML = '<tr><td colspan="6" class="text-center">Aucun frais trouvé</td></tr>';
      return;
    }

    tableBody.innerHTML = '';
    data.forEach(cost => {
      const dateCreation = cost.date_creation ? new Date(cost.date_creation).toLocaleDateString('fr-FR') : 'N/A';
      tableBody.innerHTML += `
        <tr>
          <td>${cost.other_cost_id}</td>
          <td>${cost.name}</td>
          <td>${parseFloat(cost.price).toLocaleString('fr-FR')} €</td>
          <td>${cost.facture_id}</td>
          <td>${dateCreation}</td>
          <td>
            <button class="btn btn-sm btn-info" onclick="viewCost(${cost.other_cost_id})">
              <i class="fas fa-eye"></i>
            </button>
          </td>
        </tr>
      `;
    });
  })
  .catch(error => {
    console.error('Erreur lors de l\'application des filtres:', error);
    tableBody.innerHTML = '<tr><td colspan="6" class="text-center text-danger">Erreur lors du chargement des frais</td></tr>';
  });
}

// Fonction pour mettre à jour les compteurs de frais et abonnements
function updateCounters(societyId) {
  fetch(`/api/company/getOtherCosts.php?societe_id=${societyId}`, {
    method: 'GET',
    headers: {
      'Authorization': 'Bearer ' + getToken()
    }
  })
  .then(response => {
    if (!response.ok) {
      throw new Error('Erreur lors de la récupération des données');
    }
    return response.json();
  })
  .then(data => {
    // Filtrer les abonnements (est_abonnement = 1) et les frais (est_abonnement = 0)
    const subscriptions = data.filter(item => item.est_abonnement == 1);
    const costs = data.filter(item => item.est_abonnement == 0);

    // Mettre à jour les compteurs dans l'interface si les éléments existent
    const subscriptionsCounter = document.getElementById('total-subscriptions');
    const costsCounter = document.getElementById('total-costs');

    if (subscriptionsCounter) {
      subscriptionsCounter.textContent = subscriptions.length;
    }

    if (costsCounter) {
      costsCounter.textContent = costs.length;
    }
  })
  .catch(error => {
    console.error('Erreur lors de la mise à jour des compteurs:', error);
  });
}

// Fonction pour rafraîchir la liste des employés selon l'onglet actif
function refreshEmployeeList() {
  if (currentEmployeeStatus === 'active') {
    loadActiveEmployees(societyId);
  } else {
    loadInactiveEmployees(societyId);
  }
}

// Générer un nom d'utilisateur à partir du prénom et du nom
function generateUsername(prenom, nom) {
  if (!prenom || !nom) return '';

  // Prendre la première lettre du prénom et la concaténer avec le nom
  const firstLetter = prenom.charAt(0).toLowerCase();
  const lastName = nom.toLowerCase();

  // Nettoyer les caractères spéciaux et les espaces
  const cleanLastName = lastName.normalize("NFD")
    .replace(/[\u0300-\u036f]/g, "") // Supprimer les accents
    .replace(/[^a-z0-9]/g, "");      // Supprimer les caractères spéciaux et espaces

  return firstLetter + cleanLastName;
}

// Mettre à jour le champ username lorsque le nom ou le prénom change
function updateUsername() {
  const prenom = document.getElementById('prenom').value;
  const nom = document.getElementById('nom').value;

  if (prenom && nom) {
    const username = generateUsername(prenom, nom);
    document.getElementById('username').value = username;
  }
}

// Appliquer les filtres selon l'onglet actif
function applyEmployeeFilters() {
  const filters = {
    name: document.getElementById('nameFilter').value,
    role: document.getElementById('roleFilter').value,
    date: document.getElementById('dateFilter').value
  };

  if (currentEmployeeStatus === 'active') {
    loadActiveEmployees(societyId, filters);
  } else {
    loadInactiveEmployees(societyId, filters);
  }
}

// Afficher les détails d'un collaborateur
function viewEmployeeDetails(id) {
  fetch(`/api/employee/getOne.php?id=${id}`, {
    method: 'GET',
    headers: {
      'Authorization': 'Bearer ' + getToken()
    }
  })
  .then(response => response.json())
  .then(employee => {
    const detailsContainer = document.getElementById('employee-details');
    const dateCreation = employee.date_creation ? new Date(employee.date_creation).toLocaleDateString('fr-FR') : 'N/A';
    const dateActivite = employee.date_activite ? new Date(employee.date_activite).toLocaleDateString('fr-FR') : 'N/A';
    const statusText = employee.desactivate == 1 ? '<span class="badge bg-danger">Désactivé</span>' : '<span class="badge bg-success">Actif</span>';

    detailsContainer.innerHTML = `
      <div class="row mb-3">
        <div class="col-md-6">
          <p><strong>ID:</strong> ${employee.collaborateur_id}</p>
          <p><strong>Nom:</strong> ${employee.nom}</p>
          <p><strong>Prénom:</strong> ${employee.prenom}</p>
          <p><strong>Nom d'utilisateur:</strong> ${employee.username}</p>
        </div>
        <div class="col-md-6">
          <p><strong>Rôle:</strong> ${employee.role}</p>
          <p><strong>Email:</strong> ${employee.email}</p>
          <p><strong>Téléphone:</strong> ${employee.telephone}</p>
          <p><strong>Date d'ajout:</strong> ${dateCreation}</p>
          <p><strong>Dernière activité:</strong> ${dateActivite}</p>
          <p><strong>Statut:</strong> ${statusText}</p>
        </div>
      </div>
    `;

    // Stocker l'ID pour les actions
    document.getElementById('editEmployee').setAttribute('data-id', employee.collaborateur_id);
    document.getElementById('deactivateEmployee').setAttribute('data-id', employee.collaborateur_id);
    document.getElementById('reactivateEmployee').setAttribute('data-id', employee.collaborateur_id);

    // Stocker les données de l'employé pour l'édition rapide
    document.getElementById('editEmployee').setAttribute('data-employee', JSON.stringify(employee));

    // Afficher/masquer les boutons en fonction du statut
    if (employee.desactivate == 1) {
      document.getElementById('deactivateEmployee').style.display = 'none';
      document.getElementById('reactivateEmployee').style.display = 'inline-block';
      document.getElementById('editEmployee').style.display = 'none';
    } else {
      document.getElementById('deactivateEmployee').style.display = 'inline-block';
      document.getElementById('reactivateEmployee').style.display = 'none';
      document.getElementById('editEmployee').style.display = 'inline-block';
    }

    // Afficher le modal
    const modal = new bootstrap.Modal(document.getElementById('viewEmployeeModal'));
    modal.show();
  })
  .catch(error => {
    console.error('Erreur lors du chargement des détails du collaborateur:', error);
    alert('Erreur lors du chargement des détails du collaborateur');
  });
}

// Fonction pour ouvrir le modal d'édition à partir des données JSON
function openEditModal(employeeData) {
  document.getElementById('edit_employee_id').value = employeeData.collaborateur_id;
  document.getElementById('edit_nom').value = employeeData.nom;
  document.getElementById('edit_prenom').value = employeeData.prenom;
  document.getElementById('edit_username').value = employeeData.username;
  document.getElementById('edit_role').value = employeeData.role;
  document.getElementById('edit_email').value = employeeData.email;
  document.getElementById('edit_telephone').value = employeeData.telephone;
  document.getElementById('edit_password').value = '';

  // Fermer le modal de détails
  const viewModal = bootstrap.Modal.getInstance(document.getElementById('viewEmployeeModal'));
  if (viewModal) {
    viewModal.hide();
  }

  // Ouvrir le modal d'édition
  const editModal = new bootstrap.Modal(document.getElementById('editEmployeeModal'));
  editModal.show();
}

// Fonction pour charger les détails d'un employé via l'API
function fetchEmployeeDetails(id) {
  fetch(`/api/employee/getOne.php?id=${id}`, {
    method: 'GET',
    headers: {
      'Authorization': 'Bearer ' + getToken()
    }
  })
  .then(response => response.json())
  .then(employee => {
    openEditModal(employee);
  })
  .catch(error => {
    console.error('Erreur lors du chargement des détails du collaborateur:', error);
    alert('Erreur lors du chargement des détails du collaborateur');
  });
}

// Demande de confirmation avant de désactiver un collaborateur
function confirmDeactivateEmployee(id) {
  if (confirm('Êtes-vous sûr de vouloir désactiver ce collaborateur?')) {
    deactivateEmployee(id);
  }
}

// Désactiver un collaborateur
function deactivateEmployee(id) {
  // Afficher un message de chargement
  const loadingMessage = document.createElement('div');
  loadingMessage.className = 'position-fixed top-50 start-50 translate-middle bg-white p-3 rounded shadow';
  loadingMessage.innerHTML = '<div class="spinner-border text-primary me-2" role="status"></div> Désactivation en cours...';
  document.body.appendChild(loadingMessage);

  fetch('/api/employee/delete.php', {
    method: 'DELETE',
    headers: {
      'Content-Type': 'application/json',
      'Authorization': 'Bearer ' + getToken()
    },
    body: JSON.stringify({ id: id })
  })
  .then(response => {
    // Supprimer le message de chargement
    document.body.removeChild(loadingMessage);
    return response.json();
  })
  .then(data => {
    // Considérer comme un succès si soit data.success est true, soit si le message contient "desactivated" ou est un message positif
    if (data.success || 
      (data.message && (
        data.message.toLowerCase().includes("desactivated") || 
        data.message.toLowerCase().includes("désactivé") ||
        data.message.toLowerCase().includes("employee") ||
        data.message.toLowerCase().includes("collaborateur")
      ))
    ) {
      // Fermer le modal de détails s'il est ouvert
      const viewModal = bootstrap.Modal.getInstance(document.getElementById('viewEmployeeModal'));
      if (viewModal) {
        viewModal.hide();
      }

      // Recharger la liste des employés après un court délai pour laisser le temps à la BD de se mettre à jour
      setTimeout(() => {
        refreshEmployeeList();
      }, 500);

      // Afficher un message de succès
      alert('Collaborateur désactivé avec succès!');
    } else {
      // Erreur véritable
      alert(`Erreur: ${data.message || 'Une erreur est survenue'}`);
    }
  })
  .catch(error => {
    // Supprimer le message de chargement en cas d'erreur
    if (document.body.contains(loadingMessage)) {
      document.body.removeChild(loadingMessage);
    }
    
    console.error('Erreur lors de la désactivation du collaborateur:', error);
    alert('Une erreur est survenue lors de la désactivation du collaborateur');
  });
}

// Réactiver un collaborateur
function reactivateEmployee(id) {
  if (confirm('Êtes-vous sûr de vouloir réactiver ce collaborateur?')) {
    // Vérifier d'abord si la société peut ajouter un employé
    fetch(`/api/company/checkEmployeeLimit.php?societe_id=${societyId}`, {
      method: 'GET',
      headers: {
        'Authorization': 'Bearer ' + getToken()
      }
    })
    .then(response => response.json())
    .then(data => {
      if (!data.status) {
        alert(data.message);
        return;
      }
      
      // Si la limite n'est pas atteinte, continuer avec la réactivation
      // Afficher un message de chargement
      const loadingMessage = document.createElement('div');
      loadingMessage.className = 'position-fixed top-50 start-50 translate-middle bg-white p-3 rounded shadow';
      loadingMessage.innerHTML = '<div class="spinner-border text-primary me-2" role="status"></div> Réactivation en cours...';
      document.body.appendChild(loadingMessage);

      fetch('/api/employee/reactivate.php', {
        method: 'PATCH',
        headers: {
          'Content-Type': 'application/json',
          'Authorization': 'Bearer ' + getToken()
        },
        body: JSON.stringify({ id: id })
      })
      .then(response => {
        // Supprimer le message de chargement
        document.body.removeChild(loadingMessage);
        return response.json();
      })
      .then(data => {
        // Considérer comme un succès si soit data.success est true, soit si le message est positif
        if (data.success || 
          (data.message && (
            data.message.toLowerCase().includes("reactivated") || 
            data.message.toLowerCase().includes("réactivé") ||
            data.message.toLowerCase().includes("employee") ||
            data.message.toLowerCase().includes("collaborateur")
          ))
        ) {
          // Fermer le modal de détails s'il est ouvert
          const viewModal = bootstrap.Modal.getInstance(document.getElementById('viewEmployeeModal'));
          if (viewModal) {
            viewModal.hide();
          }

          // Recharger la liste des employés après un court délai
          setTimeout(() => {
            refreshEmployeeList();
            refreshEmployeeLimitInfo();
          }, 500);

          // Afficher un message de succès
          alert('Collaborateur réactivé avec succès!');
        } else {
          // Erreur véritable
          alert(`Erreur: ${data.message || 'Une erreur est survenue'}`);
        }
      })
      .catch(error => {
        // Supprimer le message de chargement en cas d'erreur
        if (document.body.contains(loadingMessage)) {
          document.body.removeChild(loadingMessage);
        }
        
        console.error('Erreur lors de la réactivation du collaborateur:', error);
        alert('Une erreur est survenue lors de la réactivation du collaborateur');
      });
    })
    .catch(error => {
      console.error('Erreur lors de la vérification de la limite d\'employés:', error);
      alert('Erreur lors de la vérification de votre limite d\'employés.');
    });
  }
}

// Fonction pour recharger les données de la limite d'employés après ajout/désactivation
function refreshEmployeeLimitInfo() {
  checkEmployeeLimit();
}

// Fonction pour vérifier la limite d'employés
function checkEmployeeLimit() {
  fetch(`/api/company/checkEmployeeLimit.php?societe_id=${societyId}`, {
    method: 'GET',
    headers: {
      'Authorization': 'Bearer ' + getToken()
    }
  })
  .then(response => response.json())
  .then(data => {
    employeeLimitInfo = data;
    
    // Mettre à jour l'affichage de la limite si les éléments existent
    const limitInfoElement = document.getElementById('employee-count-message');
    const upgradeButton = document.getElementById('upgrade-subscription-btn');
    
    if (!limitInfoElement || !upgradeButton) {
      return; // Si on n'est pas sur la page des employés, ne rien faire
    }
    
    if (data.status) {
      // Si la société peut ajouter des employés
      limitInfoElement.innerHTML = `Vous avez actuellement <strong>${data.current}</strong> collaborateurs actifs sur un maximum de <strong>${data.max}</strong> (${data.remaining} restants).`;
      upgradeButton.style.display = data.remaining < 3 ? 'inline-block' : 'none'; // Afficher le bouton si moins de 3 places restantes
    } else {
      // Si la limite est atteinte
      limitInfoElement.innerHTML = `<strong>Limite atteinte :</strong> ${data.message}`;
      upgradeButton.style.display = 'inline-block';
      document.getElementById('employee-limit-info').classList.remove('alert-info');
      document.getElementById('employee-limit-info').classList.add('alert-warning');
    }
    
    // Afficher les informations sur l'abonnement si disponibles
    if (data.subscription) {
      const subscriptionInfo = `<br><small>Abonnement actuel : ${data.subscription.nom} - Validité : ${new Date(data.subscription.date_debut).toLocaleDateString('fr-FR')} au ${new Date(data.subscription.date_fin).toLocaleDateString('fr-FR')}</small>`;
      limitInfoElement.innerHTML += subscriptionInfo;
    }
  })
  .catch(error => {
    console.error('Erreur lors de la vérification de la limite d\'employés:', error);
    const limitInfoElement = document.getElementById('employee-count-message');
    if (limitInfoElement) {
      limitInfoElement.innerHTML = 'Erreur lors du chargement des informations sur votre limite d\'employés.';
    }
  });
}
