<?php
$title = "Ajouter une question au Chatbot";
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
                    <h1 class="h2">Ajouter une question au Chatbot</h1>
                </div>

                <div class="card">
                    <div class="card-header bg-white">
                        <h5 class="card-title mb-0">Nouvelle question</h5>
                    </div>
                    <div class="card-body">
                        <form id="createQuestionForm">
                            <div class="mb-3">
                                <label for="questionText" class="form-label">Question <span class="text-danger">*</span></label>
                                <textarea class="form-control" id="questionText" rows="2" required></textarea>
                                <div class="form-text">Saisissez la question qui sera posée par l'utilisateur</div>
                            </div>

                            <div class="mb-3">
                                <label for="answerText" class="form-label">Réponse <span class="text-danger">*</span></label>
                                <textarea class="form-control" id="answerText" rows="4" required></textarea>
                                <div class="form-text">Saisissez la réponse que le chatbot donnera à cette question</div>
                            </div>

                            <div class="mb-3">
                                <label for="parentQuestion" class="form-label">Question parente (optionnel)</label>
                                <select class="form-select" id="parentQuestion">
                                    <option value="">Aucune (question principale)</option>
                                    <!-- Les options seront chargées dynamiquement -->
                                </select>
                                <div class="form-text">Si cette question est une sous-question, sélectionnez sa question parente</div>
                            </div>

                            <div class="d-flex justify-content-between mt-4">
                                <a href="chatbot.php" class="btn btn-secondary">Annuler</a>
                                <button type="submit" class="btn btn-primary">Enregistrer la question</button>
                            </div>
                        </form>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Charger la liste des questions parentes possibles
            loadParentQuestions();

            // Gestionnaire de soumission du formulaire
            document.getElementById('createQuestionForm').addEventListener('submit', function(e) {
                e.preventDefault();
                createQuestion();
            });
        });

        function loadParentQuestions() {
            const parentSelect = document.getElementById('parentQuestion');

            fetch('../../api/chatbot/getAll.php', {
                headers: {
                    'Authorization': 'Bearer ' + getToken()
                }
            })
            .then(response => response.json())
            .then(data => {
                // Filtrer pour ne récupérer que les questions principales et sous-questions de premier niveau
                const mainQuestions = data.filter(item => item.parent_id === null);

                if (mainQuestions.length > 0) {
                    mainQuestions.forEach(question => {
                        const option = document.createElement('option');
                        option.value = question.chatbot_id;
                        option.textContent = truncateText(question.question, 50);
                        parentSelect.appendChild(option);
                    });
                }
            })
            .catch(error => {
                console.error('Erreur lors du chargement des questions parentes:', error);
                alert('Erreur lors du chargement des questions parentes');
            });
        }

        function createQuestion() {
            const questionText = document.getElementById('questionText').value;
            const answerText = document.getElementById('answerText').value;
            const parentId = document.getElementById('parentQuestion').value;

            if (!questionText || !answerText) {
                alert('La question et la réponse sont obligatoires');
                return;
            }

            const requestData = {
                question: questionText,
                answer: answerText
            };

            // Ajouter le parent_id seulement s'il est sélectionné
            if (parentId) {
                requestData.parent_id = parentId;
            }

            fetch('../../api/chatbot/create.php', {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                    'Authorization': 'Bearer ' + getToken()
                },
                body: JSON.stringify(requestData)
            })
            .then(response => response.json())
            .then(data => {
                if (data.chatbot_id) {
                    alert('Question ajoutée avec succès');
                    window.location.href = 'chatbot.php';
                } else {
                    alert('Erreur lors de l\'ajout de la question');
                }
            })
            .catch(error => {
                console.error('Erreur:', error);
                alert('Erreur lors de l\'ajout de la question');
            });
        }

        function truncateText(text, maxLength) {
            if (!text) return '';
            return text.length > maxLength ? text.substring(0, maxLength) + '...' : text;
        }
    </script>
</body>

</html>
