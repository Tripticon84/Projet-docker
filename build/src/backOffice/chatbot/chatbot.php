<?php
$title = "Gestion du Chatbot";
include_once "../includes/head.php";
?>

<body>
    <div class="container-fluid">
        <div class="row">
            <?php include_once "../includes/sidebar.php"; ?>
            <!-- Main Content -->
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 main-content">
                <!-- Header -->
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">Gestion du Chatbot</h1>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <a href="create.php" class="btn btn-sm btn-primary">
                            <i class="fas fa-plus"></i> Ajouter une question
                        </a>
                    </div>
                </div>

                <!-- Statistiques rapides -->
                <div class="row mb-4">
                    <div class="col-md-4">
                        <div class="stat-card">
                            <div class="stat-icon bg-primary-subtle text-primary">
                                <i class="fas fa-question-circle"></i>
                            </div>
                            <p class="text-muted mb-0">Questions principales</p>
                            <h3 id="mainQuestionsCount">-</h3>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="stat-card">
                            <div class="stat-icon bg-success-subtle text-success">
                                <i class="fas fa-reply"></i>
                            </div>
                            <p class="text-muted mb-0">Sous-questions</p>
                            <h3 id="subQuestionsCount">-</h3>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="stat-card">
                            <div class="stat-icon bg-info-subtle text-info">
                                <i class="fas fa-comment-dots"></i>
                            </div>
                            <p class="text-muted mb-0">Réponses totales</p>
                            <h3 id="totalAnswersCount">-</h3>
                        </div>
                    </div>
                </div>

                <!-- Questions principales Table -->
                <div class="card mt-4">
                    <div class="card-header bg-white d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0">Questions principales</h5>
                        <div class="btn-group">
                            <button class="btn btn-sm btn-outline-secondary" onclick="refreshMainQuestions()">
                                <i class="fas fa-sync-alt"></i> Actualiser
                            </button>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th scope="col">ID</th>
                                        <th scope="col">Question</th>
                                        <th scope="col">Réponse</th>
                                        <th scope="col">Sous-questions</th>
                                        <th scope="col" class="text-end">Actions</th>
                                    </tr>
                                </thead>
                                <tbody id="mainQuestionsList">
                                    <tr>
                                        <td colspan="5" class="text-center">Chargement des questions...</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="card-footer bg-white d-flex justify-content-between align-items-center">
                        <nav aria-label="Table navigation">
                            <ul class="pagination pagination-sm mb-0" id="paginationList"></ul>
                        </nav>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <!-- Modal pour afficher les détails d'une question -->
    <div class="modal fade" id="questionDetailModal" tabindex="-1" aria-labelledby="questionDetailModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="questionDetailModalLabel">Détails de la question</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <p><strong>ID:</strong> <span id="modal-question-id"></span></p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>Parent ID:</strong> <span id="modal-parent-id">-</span></p>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="modal-question-text" class="form-label">Question:</label>
                        <textarea class="form-control" id="modal-question-text" rows="2"></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="modal-answer-text" class="form-label">Réponse:</label>
                        <textarea class="form-control" id="modal-answer-text" rows="4"></textarea>
                    </div>
                    <div id="subQuestionsSection" class="mt-4">
                        <h6>Sous-questions:</h6>
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Question</th>
                                        <th>Réponse</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody id="subQuestionsTable">
                                    <tr>
                                        <td colspan="4" class="text-center">Aucune sous-question</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <div class="mt-2">
                            <button type="button" class="btn btn-sm btn-outline-primary" id="addSubQuestionBtn">
                                <i class="fas fa-plus"></i> Ajouter une sous-question
                            </button>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fermer</button>
                    <button type="button" class="btn btn-danger me-2" id="deleteQuestionBtn">Supprimer</button>
                    <button type="button" class="btn btn-primary" id="saveChangesBtn">Enregistrer les modifications</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal pour ajouter une sous-question -->
    <div class="modal fade" id="addSubQuestionModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Ajouter une sous-question</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="subQuestionText" class="form-label">Question:</label>
                        <textarea class="form-control" id="subQuestionText" rows="2" required></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="subQuestionAnswer" class="form-label">Réponse:</label>
                        <textarea class="form-control" id="subQuestionAnswer" rows="3" required></textarea>
                    </div>
                    <input type="hidden" id="parentQuestionId">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="button" class="btn btn-primary" id="saveSubQuestionBtn">Ajouter</button>
                </div>
            </div>
        </div>
    </div>

    <script>
        let currentPage = 1;
        let currentModal = null;
        let currentParentId = null;

        document.addEventListener('DOMContentLoaded', function() {
            fetchMainQuestions();

            // Configurer le bouton pour ajouter des sous-questions
            document.getElementById('addSubQuestionBtn').addEventListener('click', function() {
                const parentId = document.getElementById('modal-question-id').textContent;
                document.getElementById('parentQuestionId').value = parentId;
                const subQuestionModal = new bootstrap.Modal(document.getElementById('addSubQuestionModal'));
                subQuestionModal.show();
            });

            // Configurer le bouton pour sauvegarder une sous-question
            document.getElementById('saveSubQuestionBtn').addEventListener('click', function() {
                saveSubQuestion();
            });

            // Configurer le bouton de sauvegarde des modifications
            document.getElementById('saveChangesBtn').addEventListener('click', function() {
                saveQuestion();
            });

            // Configurer le bouton de suppression
            document.getElementById('deleteQuestionBtn').addEventListener('click', function() {
                deleteQuestion();
            });
        });

        function fetchMainQuestions(page = 1) {
            currentPage = page;
            const questionsList = document.getElementById('mainQuestionsList');
            questionsList.innerHTML = '<tr><td colspan="5" class="text-center">Chargement des questions...</td></tr>';


            // Récupérer uniquement les questions principales (parent_id = null)
            fetch('../../api/chatbot/getAll.php', {
                headers: {
                    'Authorization': 'Bearer ' + getToken()
                }
            })
            .then(response => response.json())
            .then(data => {
                questionsList.innerHTML = '';

                // Filtrer pour ne récupérer que les questions principales (parent_id est null)
                const mainQuestions = data.filter(item => item.parent_id === null);
                const subQuestions = data.filter(item => item.parent_id !== null);

                // Mettre à jour les compteurs
                document.getElementById('mainQuestionsCount').textContent = mainQuestions.length;

                document.getElementById('subQuestionsCount').textContent = subQuestions.length;
                document.getElementById('totalAnswersCount').textContent = data.length;


                if (mainQuestions.length > 0) {
                    mainQuestions.forEach(question => {
                        // Compter le nombre de sous-questions pour cette question
                        const subQuestionsCount = subQuestions.filter(sq => sq.parent_id == question.chatbot_id).length;

                        const row = document.createElement('tr');
                        row.innerHTML = `
                            <td>${question.chatbot_id}</td>
                            <td>${truncateText(question.question, 50)}</td>
                            <td>${truncateText(question.answer, 50)}</td>
                            <td>${subQuestionsCount}</td>
                            <td class="text-end">
                                <button class="btn btn-sm btn-primary" onclick="viewQuestionDetails(${question.chatbot_id})">
                                    <i class="fas fa-edit"></i> Éditer
                                </button>
                            </td>
                        `;
                        questionsList.appendChild(row);
                    });

                } else {
                    questionsList.innerHTML = '<tr><td colspan="5" class="text-center">Aucune question trouvée</td></tr>';
                }
            })
            .catch(error => {
                console.error('Erreur:', error);
                questionsList.innerHTML = '<tr><td colspan="5" class="text-center text-danger">Erreur lors du chargement des questions</td></tr>';
            });
        }

        function viewQuestionDetails(questionId) {
            // Récupérer les détails de la question principale
            fetch(`../../api/chatbot/getOne.php?chatbot_id=${questionId}`, {
                headers: {
                    'Authorization': 'Bearer ' + getToken()
                }
            })
            .then(response => response.json())
            .then(question => {
                if (question) {
                    document.getElementById('modal-question-id').textContent = question.chatbot_id;
                    document.getElementById('modal-parent-id').textContent = question.parent_id || '-';
                    document.getElementById('modal-question-text').value = question.question;
                    document.getElementById('modal-answer-text').value = question.answer;

                    // Charger les sous-questions
                    fetch(`../../api/chatbot/getAllByParent.php?parent_id=${questionId}`, {
                        headers: {
                            'Authorization': 'Bearer ' + getToken()
                        }
                    })
                    .then(response => {
                        if (response.ok) return response.json();
                        return [];
                    })
                    .then(subQuestions => {
                        const subQuestionsTable = document.getElementById('subQuestionsTable');

                        if (subQuestions.length > 0) {
                            subQuestionsTable.innerHTML = '';
                            subQuestions.forEach(sq => {
                                const row = document.createElement('tr');
                                row.innerHTML = `
                                    <td>${sq.chatbot_id}</td>
                                    <td>${truncateText(sq.question, 30)}</td>
                                    <td>${truncateText(sq.answer, 30)}</td>
                                    <td>
                                        <button class="btn btn-sm btn-outline-primary" onclick="viewQuestionDetails(${sq.chatbot_id})">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button class="btn btn-sm btn-outline-danger" onclick="deleteSubQuestion(${sq.chatbot_id})">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </td>
                                `;
                                subQuestionsTable.appendChild(row);
                            });
                        } else {
                            subQuestionsTable.innerHTML = '<tr><td colspan="4" class="text-center">Aucune sous-question</td></tr>';
                        }

                        currentModal = new bootstrap.Modal(document.getElementById('questionDetailModal'));
                        currentModal.show();
                    });
                } else {
                    alert('Question non trouvée');
                }
            })
            .catch(error => {
                console.error('Erreur:', error);
                alert('Erreur lors du chargement des détails de la question');
            });
        }

        function saveQuestion() {
            const questionId = document.getElementById('modal-question-id').textContent;
            const questionText = document.getElementById('modal-question-text').value;
            const answerText = document.getElementById('modal-answer-text').value;

            if (!questionText || !answerText) {
                alert('La question et la réponse sont obligatoires');
                return;
            }

            fetch('../../api/chatbot/update.php', {
                method: 'PATCH',
                headers: {
                    'Content-Type': 'application/json',
                    'Authorization': 'Bearer ' + getToken()
                },
                body: JSON.stringify({
                    chatbot_id: questionId,
                    question: questionText,
                    answer: answerText
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.chatbot_id) {
                    alert('Question mise à jour avec succès');
                    currentModal.hide();
                    fetchMainQuestions(currentPage);
                } else {
                    alert('Erreur lors de la mise à jour de la question');
                }
            })
            .catch(error => {
                console.error('Erreur:', error);
                alert('Erreur lors de la mise à jour de la question');
            });
        }

        function saveSubQuestion() {
            const parentId = document.getElementById('parentQuestionId').value;
            const questionText = document.getElementById('subQuestionText').value;
            const answerText = document.getElementById('subQuestionAnswer').value;

            if (!questionText || !answerText) {
                alert('La question et la réponse sont obligatoires');
                return;
            }

            fetch('../../api/chatbot/create.php', {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                    'Authorization': 'Bearer ' + getToken()
                },
                body: JSON.stringify({
                    question: questionText,
                    answer: answerText,
                    parent_id: parentId
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.chatbot_id) {
                    alert('Sous-question ajoutée avec succès');
                    bootstrap.Modal.getInstance(document.getElementById('addSubQuestionModal')).hide();
                    // Rafraîchir les détails de la question parent
                    viewQuestionDetails(parentId);
                    fetchMainQuestions(currentPage);
                } else {
                    alert('Erreur lors de l\'ajout de la sous-question');
                }
            })
            .catch(error => {
                console.error('Erreur:', error);
                alert('Erreur lors de l\'ajout de la sous-question');
            });
        }

        function deleteQuestion() {
            const questionId = document.getElementById('modal-question-id').textContent;

            if (confirm('Êtes-vous sûr de vouloir supprimer cette question ? Cette action supprimera également toutes les sous-questions associées.')) {
                fetch('../../api/chatbot/delete.php', {
                    method: 'DELETE',
                    headers: {
                        'Content-Type': 'application/json',
                        'Authorization': 'Bearer ' + getToken()
                    },
                    body: JSON.stringify({
                        chatbot_id: questionId
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.chatbot_id) {
                        alert('Question supprimée avec succès');
                        currentModal.hide();
                        fetchMainQuestions(currentPage);
                    } else {
                        alert('Erreur lors de la suppression de la question');
                    }
                })
                .catch(error => {
                    console.error('Erreur:', error);
                    alert('Erreur lors de la suppression de la question');
                });
            }
        }

        function deleteSubQuestion(subQuestionId) {
            if (confirm('Êtes-vous sûr de vouloir supprimer cette sous-question ?')) {
                fetch('../../api/chatbot/delete.php', {
                    method: 'DELETE',
                    headers: {
                        'Content-Type': 'application/json',
                        'Authorization': 'Bearer ' + getToken()
                    },
                    body: JSON.stringify({
                        chatbot_id: subQuestionId
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.chatbot_id) {
                        alert('Sous-question supprimée avec succès');
                        // Rafraîchir les détails de la question parent
                        const parentId = document.getElementById('modal-question-id').textContent;
                        viewQuestionDetails(parentId);
                        fetchMainQuestions(currentPage);
                    } else {
                        alert('Erreur lors de la suppression de la sous-question');
                    }
                })
                .catch(error => {
                    console.error('Erreur:', error);
                    alert('Erreur lors de la suppression de la sous-question');
                });
            }
        }

        function refreshMainQuestions() {
            fetchMainQuestions(currentPage);
        }

        function updatePagination(hasMore) {
            const paginationList = document.getElementById('paginationList');
            paginationList.innerHTML = '';

            // Bouton précédent
            let prevItem = document.createElement('li');
            prevItem.className = 'page-item ' + (currentPage === 1 ? 'disabled' : '');
            prevItem.innerHTML = `<a class="page-link" href="#" onclick="fetchMainQuestions(${currentPage - 1}); return false;">Précédent</a>`;
            paginationList.appendChild(prevItem);

            // Bouton suivant
            let nextItem = document.createElement('li');
            nextItem.className = 'page-item ' + (!hasMore ? 'disabled' : '');
            nextItem.innerHTML = `<a class="page-link" href="#" onclick="fetchMainQuestions(${currentPage + 1}); return false;">Suivant</a>`;
            paginationList.appendChild(nextItem);
        }

        function truncateText(text, maxLength) {
            if (!text) return '';
            return text.length > maxLength ? text.substring(0, maxLength) + '...' : text;
        }
    </script>

    <style>
        .stat-card {
            position: relative;
            padding: 1rem;
            background-color: white;
            border-radius: 0.5rem;
            box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
            margin-bottom: 1rem;
            overflow: hidden;
        }

        .stat-icon {
            position: absolute;
            top: 1rem;
            right: 1rem;
            width: 2.5rem;
            height: 2.5rem;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.25rem;
        }

        .stat-card h3 {
            font-size: 1.5rem;
            font-weight: 700;
            margin-bottom: 0.25rem;
            margin-top: 0.5rem;
        }

        .table tr:hover {
            background-color: rgba(0, 123, 255, 0.05);
        }
    </style>
</body>

</html>
