document.addEventListener('DOMContentLoaded', function() {
    // Initialize Swiper
    const swiper = new Swiper('.swiper', {
        loop: true,
        autoplay: {
            delay: 5000,
            disableOnInteraction: false,
        },
        pagination: {
            el: '.swiper-pagination',
            clickable: true,
        },
        navigation: {
            nextEl: '.swiper-button-next',
            prevEl: '.swiper-button-prev',
        },
    });

    // Search Form Handler
    const searchForm = document.getElementById('searchForm');
    searchForm.addEventListener('submit', function(e) {
        e.preventDefault();
        const formData = new FormData(this);
        fetchVehicles(formData);
    });

    // Fetch Vehicles Function
    async function fetchVehicles(formData, page = 1) {
        try {
            const params = new URLSearchParams(formData);
            params.append('page', page);
            
            const response = await fetch(`api/vehicles.php?${params.toString()}`);
            const data = await response.json();
            
            if (data.success) {
                displayVehicles(data.vehicles);
                updatePagination(data.pagination);
            } else {
                showError('Erro ao carregar veículos');
            }
        } catch (error) {
            console.error('Error:', error);
            showError('Erro ao carregar veículos');
        }
    }

    // Display Vehicles Function
    function displayVehicles(vehicles) {
        const vehicleList = document.getElementById('vehicleList');
        vehicleList.innerHTML = '';

        vehicles.forEach(vehicle => {
            const card = createVehicleCard(vehicle);
            vehicleList.appendChild(card);
        });
    }

    // Create Vehicle Card Function
    function createVehicleCard(vehicle) {
        const col = document.createElement('div');
        col.className = 'col-md-4 col-sm-6';
        
        col.innerHTML = `
            <div class="card vehicle-card" data-vehicle-id="${vehicle.id}">
                <img src="${vehicle.primary_image || 'assets/images/placeholder.jpg'}" 
                     class="card-img-top" 
                     alt="${vehicle.name}">
                <div class="card-body">
                    <h5 class="card-title">${vehicle.name}</h5>
                    <p class="price">R$ ${formatPrice(vehicle.price)}</p>
                    <div class="vehicle-stats">
                        <span>${vehicle.year}</span>
                        <span>${formatMileage(vehicle.mileage)} km</span>
                    </div>
                </div>
            </div>
        `;

        // Add click event to open modal
        col.querySelector('.vehicle-card').addEventListener('click', () => {
            openVehicleModal(vehicle.id);
        });

        return col;
    }

    // Update Pagination Function
    function updatePagination(pagination) {
        const paginationEl = document.getElementById('pagination');
        paginationEl.innerHTML = '';

        for (let i = 1; i <= pagination.total_pages; i++) {
            const li = document.createElement('li');
            li.className = `page-item ${pagination.current_page === i ? 'active' : ''}`;
            
            li.innerHTML = `
                <a class="page-link" href="#" data-page="${i}">${i}</a>
            `;

            li.querySelector('a').addEventListener('click', (e) => {
                e.preventDefault();
                const formData = new FormData(searchForm);
                fetchVehicles(formData, i);
            });

            paginationEl.appendChild(li);
        }
    }

    // Open Vehicle Modal Function
    async function openVehicleModal(vehicleId) {
        try {
            const response = await fetch(`api/vehicle.php?id=${vehicleId}`);
            const data = await response.json();
            
            if (data.success) {
                const modal = document.getElementById('vehicleModal');
                const modalTitle = modal.querySelector('.modal-title');
                const modalBody = modal.querySelector('.modal-body');
                
                modalTitle.textContent = data.vehicle.name;
                modalBody.innerHTML = `
                    <div id="vehicleCarousel" class="carousel slide" data-bs-ride="carousel">
                        <div class="carousel-inner">
                            ${data.vehicle.images.map((img, index) => `
                                <div class="carousel-item ${index === 0 ? 'active' : ''}">
                                    <img src="${img}" class="d-block w-100" alt="Vehicle Image ${index + 1}">
                                </div>
                            `).join('')}
                        </div>
                        ${data.vehicle.images.length > 1 ? `
                            <button class="carousel-control-prev" type="button" data-bs-target="#vehicleCarousel" data-bs-slide="prev">
                                <span class="carousel-control-prev-icon"></span>
                            </button>
                            <button class="carousel-control-next" type="button" data-bs-target="#vehicleCarousel" data-bs-slide="next">
                                <span class="carousel-control-next-icon"></span>
                            </button>
                        ` : ''}
                    </div>
                    <div class="vehicle-details">
                        <dl class="row">
                            <dt class="col-sm-3">Modelo</dt>
                            <dd class="col-sm-9">${data.vehicle.model}</dd>
                            
                            <dt class="col-sm-3">Ano</dt>
                            <dd class="col-sm-9">${data.vehicle.year}</dd>
                            
                            <dt class="col-sm-3">Quilometragem</dt>
                            <dd class="col-sm-9">${formatMileage(data.vehicle.mileage)} km</dd>
                            
                            <dt class="col-sm-3">Preço</dt>
                            <dd class="col-sm-9">R$ ${formatPrice(data.vehicle.price)}</dd>
                        </dl>
                        <div class="description mt-4">
                            <h6>Descrição</h6>
                            <p>${data.vehicle.description || 'Sem descrição disponível.'}</p>
                        </div>
                    </div>
                `;

                const modalInstance = new bootstrap.Modal(modal);
                modalInstance.show();
            } else {
                showError('Erro ao carregar detalhes do veículo');
            }
        } catch (error) {
            console.error('Error:', error);
            showError('Erro ao carregar detalhes do veículo');
        }
    }

    // Utility Functions
    function formatPrice(price) {
        return parseFloat(price).toLocaleString('pt-BR', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        });
    }

    function formatMileage(mileage) {
        return parseInt(mileage).toLocaleString('pt-BR');
    }

    function showError(message) {
        // You can implement a better error notification system
        alert(message);
    }

    // Initial load
    fetchVehicles(new FormData(searchForm));
});
