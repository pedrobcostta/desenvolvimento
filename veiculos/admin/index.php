<?php
require_once '../config/config.php';
require_once '../include/Vehicle.php';

session_start();

// Initialize Vehicle class
$vehicle = new Vehicle();

// Get statistics for dashboard
$stats = $vehicle->getStats();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Veículos Usados</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../assets/css/admin.css">
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="#">Painel Administrativo</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="../">Ver Site</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <!-- Dashboard Stats -->
        <div class="row mb-4">
            <div class="col-md-4">
                <div class="card stats-card">
                    <div class="card-body">
                        <h5 class="card-title">Total de Veículos</h5>
                        <p class="card-text display-4"><?php echo $stats['total_vehicles'] ?? 0; ?></p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card stats-card">
                    <div class="card-body">
                        <h5 class="card-title">Mais Visualizado</h5>
                        <?php if (isset($stats['most_viewed'])): ?>
                            <p class="card-text">
                                <?php echo htmlspecialchars($stats['most_viewed']['name']); ?><br>
                                <small class="text-muted"><?php echo $stats['most_viewed']['views']; ?> visualizações</small>
                            </p>
                        <?php else: ?>
                            <p class="card-text text-muted">Nenhum veículo cadastrado</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card stats-card">
                    <div class="card-body">
                        <h5 class="card-title">Menos Visualizado</h5>
                        <?php if (isset($stats['least_viewed'])): ?>
                            <p class="card-text">
                                <?php echo htmlspecialchars($stats['least_viewed']['name']); ?><br>
                                <small class="text-muted"><?php echo $stats['least_viewed']['views']; ?> visualizações</small>
                            </p>
                        <?php else: ?>
                            <p class="card-text text-muted">Nenhum veículo cadastrado</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Vehicle Management -->
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Gerenciar Veículos</h5>
                <a href="add.php" class="btn btn-primary">
                    <i class="bi bi-plus-lg"></i> Novo Veículo
                </a>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Imagem</th>
                                <th>Nome</th>
                                <th>Modelo</th>
                                <th>Ano</th>
                                <th>Preço</th>
                                <th>Visualizações</th>
                                <th>Ações</th>
                            </tr>
                        </thead>
                        <tbody id="vehicleList">
                            <!-- Vehicle list will be loaded here via AJAX -->
                        </tbody>
                    </table>
                </div>
                <!-- Pagination -->
                <nav aria-label="Page navigation" class="mt-4">
                    <ul class="pagination justify-content-center" id="pagination">
                        <!-- Pagination will be generated dynamically -->
                    </ul>
                </nav>
            </div>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div class="modal fade" id="deleteModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Confirmar Exclusão</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Tem certeza que deseja excluir este veículo? Esta ação não pode ser desfeita.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-danger" id="confirmDelete">Excluir</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/js/admin.js"></script>
</body>
</html>
