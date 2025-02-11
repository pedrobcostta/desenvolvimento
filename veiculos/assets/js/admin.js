document.addEventListener('DOMContentLoaded', function() {
    // Variables for vehicle management
    let currentPage = 1;
    let deleteVehicleId = null;
    const vehicleList = document.getElementById('vehicleList');
    const pagination = document.getElementById('pagination');
    const deleteModal = new bootstrap.Modal(document.getElementById('deleteModal'));
    
    // Load vehicles on page load
    loadVehicles(currentPage);

    // Load vehicles function
    async function loadVehicles(page) {
        try {
            vehicleList.classList.add('loading');
            const response = await fetch(`../api/vehicles.php?page=${page}`);
            const data = await response.json();
            
            if (data.success) {
                displayVehicles(data.vehicles);
                updatePagination(data.pagination);
                currentPage = page;
            } else {
                showError('Erro ao carregar veículos');
            }
        } catch (error) {
            console.error('Error:', error);
            showError('Erro ao carregar veículos');
        } finally {
            vehicleList.classList.remove('loading');
        }
    }

    // Display vehicles in the table
    function displayVehicles(vehicles) {
        vehicleList.innerHTML = vehicles.map(vehicle => `
            <tr>
                <td>${vehicle.id}</td>
                <td>
                    <img src="${vehicle.primary_image || '../assets/images/placeholder.jpg'}" 
                         alt="${vehicle.name}"
                         class="thumbnail">
                </td>
                <td>${vehicle.name}</td>
                <td>${vehicle.model}</td>
                <td>${vehicle.year}</td>
                <td>R$ ${formatPrice(vehicle.price)}</td>
                <td>${vehicle.views}</td>
                <td>
                    <div class="btn-group btn-group-actions">
                        <a href="edit.php?id=${vehicle.id}" 
                           class="btn btn-sm btn-primary">
                            <i class="bi bi-pencil"></i>
                        </a>
                        <button type="button" 
                                class="btn btn-sm btn-danger"
                                onclick="showDeleteModal(${vehicle.id})">
                            <i class="bi bi-trash"></i>
                        </button>
                    </div>
                </td>
            </tr>
        `).join('');
    }

    // Update pagination
    function updatePagination(paginationData) {
        const totalPages = paginationData.total_pages;
        const currentPage = paginationData.current_page;
        
        let paginationHtml = '';
        
        // Previous button
        paginationHtml += `
            <li class="page-item ${currentPage === 1 ? 'disabled' : ''}">
                <a class="page-link" href="#" data-page="${currentPage - 1}">
                    <i class="bi bi-chevron-left"></i>
                </a>
            </li>
        `;
        
        // Page numbers
        for (let i = 1; i <= totalPages; i++) {
            paginationHtml += `
                <li class="page-item ${currentPage === i ? 'active' : ''}">
                    <a class="page-link" href="#" data-page="${i}">${i}</a>
                </li>
            `;
        }
        
        // Next button
        paginationHtml += `
            <li class="page-item ${currentPage === totalPages ? 'disabled' : ''}">
                <a class="page-link" href="#" data-page="${currentPage + 1}">
                    <i class="bi bi-chevron-right"></i>
                </a>
            </li>
        `;
        
        pagination.innerHTML = paginationHtml;
        
        // Add click events to pagination links
        pagination.querySelectorAll('.page-link').forEach(link => {
            link.addEventListener('click', (e) => {
                e.preventDefault();
                const page = parseInt(e.currentTarget.dataset.page);
                if (!isNaN(page) && page > 0 && page <= totalPages) {
                    loadVehicles(page);
                }
            });
        });
    }

    // Delete vehicle functions
    window.showDeleteModal = function(vehicleId) {
        deleteVehicleId = vehicleId;
        deleteModal.show();
    };

    document.getElementById('confirmDelete').addEventListener('click', async function() {
        if (!deleteVehicleId) return;
        
        try {
            const response = await fetch(`../api/delete_vehicle.php`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ id: deleteVehicleId })
            });
            
            const data = await response.json();
            
            if (data.success) {
                showSuccess('Veículo excluído com sucesso');
                loadVehicles(currentPage);
            } else {
                showError(data.error || 'Erro ao excluir veículo');
            }
        } catch (error) {
            console.error('Error:', error);
            showError('Erro ao excluir veículo');
        } finally {
            deleteModal.hide();
            deleteVehicleId = null;
        }
    });

    // Utility functions
    function formatPrice(price) {
        return parseFloat(price).toLocaleString('pt-BR', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        });
    }

    function showSuccess(message) {
        // You can implement a better success notification system
        alert(message);
    }

    function showError(message) {
        // You can implement a better error notification system
        alert(message);
    }
});
