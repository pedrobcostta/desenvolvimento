<?php
require_once '../config/config.php';
require_once '../include/Vehicle.php';
require_once '../include/Image.php';

session_start();

$vehicle = new Vehicle();
$imageHandler = new Image();

// Check if ID is provided
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: index.php');
    exit;
}

$vehicleId = (int)$_GET['id'];
$vehicleData = $vehicle->getVehicle($vehicleId);

if (!$vehicleData) {
    header('Location: index.php');
    exit;
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $data = [
            'name' => $_POST['name'],
            'model' => $_POST['model'],
            'year' => (int)$_POST['year'],
            'mileage' => (int)$_POST['mileage'],
            'price' => (float)$_POST['price'],
            'description' => $_POST['description'],
            'replace_images' => isset($_POST['replace_images']) ? true : false
        ];

        $newImages = [];
        if (!empty($_FILES['images']['name'][0])) {
            foreach ($_FILES['images']['tmp_name'] as $key => $tmp_name) {
                if ($_FILES['images']['error'][$key] === UPLOAD_ERR_OK) {
                    $fileData = [
                        'name' => $_FILES['images']['name'][$key],
                        'type' => $_FILES['images']['type'][$key],
                        'tmp_name' => $tmp_name,
                        'error' => $_FILES['images']['error'][$key],
                        'size' => $_FILES['images']['size'][$key]
                    ];
                    
                    $imagePath = $imageHandler->processUpload($fileData);
                    if ($imagePath) {
                        $newImages[] = $imagePath;
                    }
                }
            }
        }

        if ($vehicle->updateVehicle($vehicleId, $data, $newImages)) {
            $_SESSION['success_message'] = 'Veículo atualizado com sucesso!';
            header('Location: index.php');
            exit;
        }

    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Veículo - Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../assets/css/admin.css">
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="index.php">Painel Administrativo</a>
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
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Editar Veículo</h5>
            </div>
            <div class="card-body">
                <?php if (isset($error)): ?>
                    <div class="alert alert-danger">
                        <?php echo htmlspecialchars($error); ?>
                    </div>
                <?php endif; ?>

                <form action="" method="POST" enctype="multipart/form-data" id="vehicleForm">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="name" class="form-label">Nome do Veículo</label>
                            <input type="text" class="form-control" id="name" name="name" required
                                   value="<?php echo htmlspecialchars($vehicleData['name']); ?>">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="model" class="form-label">Modelo</label>
                            <input type="text" class="form-control" id="model" name="model" required
                                   value="<?php echo htmlspecialchars($vehicleData['model']); ?>">
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label for="year" class="form-label">Ano</label>
                            <input type="number" class="form-control" id="year" name="year" required
                                   min="1900" max="<?php echo date('Y') + 1; ?>"
                                   value="<?php echo $vehicleData['year']; ?>">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="mileage" class="form-label">Quilometragem</label>
                            <input type="number" class="form-control" id="mileage" name="mileage" required
                                   min="0" value="<?php echo $vehicleData['mileage']; ?>">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="price" class="form-label">Preço</label>
                            <div class="input-group">
                                <span class="input-group-text">R$</span>
                                <input type="number" class="form-control" id="price" name="price" required
                                       min="0" step="0.01" value="<?php echo $vehicleData['price']; ?>">
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="description" class="form-label">Descrição</label>
                        <textarea class="form-control" id="description" name="description" rows="4"><?php echo htmlspecialchars($vehicleData['description'] ?? ''); ?></textarea>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Imagens Atuais</label>
                        <div class="row g-2" id="currentImages">
                            <?php foreach ($vehicleData['images'] as $image): ?>
                                <div class="col-6 col-md-4 col-lg-3">
                                    <div class="position-relative">
                                        <img src="<?php echo htmlspecialchars($image); ?>" 
                                             class="img-fluid rounded" 
                                             alt="Vehicle Image">
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <div class="mb-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="replace_images" name="replace_images">
                            <label class="form-check-label" for="replace_images">
                                Substituir todas as imagens existentes
                            </label>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Adicionar Novas Imagens</label>
                        <div class="drag-drop-zone" id="dragDropZone">
                            <i class="bi bi-cloud-upload"></i>
                            <p>Arraste as imagens aqui ou clique para selecionar</p>
                            <input type="file" id="images" name="images[]" multiple accept="image/*" class="d-none">
                        </div>
                        <div id="imagePreviewContainer" class="mt-3 row g-2"></div>
                    </div>

                    <div class="d-flex justify-content-end gap-2">
                        <a href="index.php" class="btn btn-secondary">Cancelar</a>
                        <button type="submit" class="btn btn-primary">Salvar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const dragDropZone = document.getElementById('dragDropZone');
            const fileInput = document.getElementById('images');
            const imagePreviewContainer = document.getElementById('imagePreviewContainer');
            const replaceImagesCheckbox = document.getElementById('replace_images');
            const currentImagesContainer = document.getElementById('currentImages');
            
            // Handle drag and drop
            ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
                dragDropZone.addEventListener(eventName, preventDefaults, false);
            });

            function preventDefaults(e) {
                e.preventDefault();
                e.stopPropagation();
            }

            ['dragenter', 'dragover'].forEach(eventName => {
                dragDropZone.addEventListener(eventName, highlight, false);
            });

            ['dragleave', 'drop'].forEach(eventName => {
                dragDropZone.addEventListener(eventName, unhighlight, false);
            });

            function highlight(e) {
                dragDropZone.classList.add('dragover');
            }

            function unhighlight(e) {
                dragDropZone.classList.remove('dragover');
            }

            // Handle file drop
            dragDropZone.addEventListener('drop', handleDrop, false);

            function handleDrop(e) {
                const dt = e.dataTransfer;
                const files = dt.files;
                handleFiles(files);
            }

            // Handle file select
            dragDropZone.addEventListener('click', () => fileInput.click());
            fileInput.addEventListener('change', function() {
                handleFiles(this.files);
            });

            function handleFiles(files) {
                imagePreviewContainer.innerHTML = '';
                Array.from(files).forEach(file => {
                    if (!file.type.startsWith('image/')) return;
                    
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        const preview = document.createElement('div');
                        preview.className = 'col-6 col-md-4 col-lg-3';
                        preview.innerHTML = `
                            <div class="position-relative">
                                <img src="${e.target.result}" class="img-fluid rounded" alt="Preview">
                            </div>
                        `;
                        imagePreviewContainer.appendChild(preview);
                    };
                    reader.readAsDataURL(file);
                });
            }

            // Handle replace images checkbox
            replaceImagesCheckbox.addEventListener('change', function() {
                currentImagesContainer.style.opacity = this.checked ? '0.5' : '1';
            });

            // Form validation
            const form = document.getElementById('vehicleForm');
            form.addEventListener('submit', function(e) {
                if (!form.checkValidity()) {
                    e.preventDefault();
                    e.stopPropagation();
                }
                form.classList.add('was-validated');
            });
        });
    </script>
</body>
</html>
