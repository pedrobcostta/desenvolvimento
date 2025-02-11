<?php
require_once 'config/config.php';

// Initialize session
session_start();

?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Veículos Usados</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/styles.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@10/swiper-bundle.min.css" />
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="#">Veículos Usados</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link active" href="#">Início</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="admin/">Admin</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Hero Section with Swiper -->
    <div class="hero-section">
        <div class="swiper">
            <div class="swiper-wrapper">
                <div class="swiper-slide">
                    <div class="slide-content">
                        <h1>Encontre o carro dos seus sonhos</h1>
                        <p>Os melhores veículos usados com garantia de qualidade</p>
                    </div>
                </div>
                <!-- More slides will be added dynamically -->
            </div>
            <div class="swiper-pagination"></div>
            <div class="swiper-button-next"></div>
            <div class="swiper-button-prev"></div>
        </div>
    </div>

    <!-- Search Filters -->
    <div class="container mt-4">
        <div class="card">
            <div class="card-body">
                <form id="searchForm" class="row g-3">
                    <div class="col-md-3">
                        <input type="text" class="form-control" placeholder="Buscar por nome...">
                    </div>
                    <div class="col-md-2">
                        <select class="form-select" id="yearFilter">
                            <option value="">Ano</option>
                            <!-- Will be populated dynamically -->
                        </select>
                    </div>
                    <div class="col-md-2">
                        <select class="form-select" id="priceFilter">
                            <option value="">Preço</option>
                            <option value="asc">Menor preço</option>
                            <option value="desc">Maior preço</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <select class="form-select" id="mileageFilter">
                            <option value="">Quilometragem</option>
                            <option value="0-30000">Até 30.000 km</option>
                            <option value="30001-60000">30.001 - 60.000 km</option>
                            <option value="60001-100000">60.001 - 100.000 km</option>
                            <option value="100001+">Acima de 100.000 km</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-primary w-100">Buscar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Vehicle Listings -->
    <div class="container mt-4">
        <div class="row" id="vehicleList">
            <!-- Vehicle cards will be loaded here -->
        </div>
        <!-- Pagination -->
        <nav aria-label="Page navigation" class="mt-4">
            <ul class="pagination justify-content-center" id="pagination">
                <!-- Pagination will be generated dynamically -->
            </ul>
        </nav>
    </div>

    <!-- Vehicle Modal -->
    <div class="modal fade" id="vehicleModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <!-- Vehicle details will be loaded here -->
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/swiper@10/swiper-bundle.min.js"></script>
    <script src="assets/js/main.js"></script>
</body>
</html>
