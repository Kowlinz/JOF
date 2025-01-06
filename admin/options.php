<?php
session_start();
if (!isset($_SESSION["user"])) {
    header("Location: ../login-staff.php");
}

include 'db_connect.php';

// Handle haircut deletion request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete_haircut') {
    if (!isset($_POST['haircut_id'])) {
        die(json_encode(['success' => false, 'message' => 'No haircut ID provided']));
    }

    $haircut_id = $_POST['haircut_id'];

    $check_stmt = $conn->prepare("SELECT COUNT(*) as count FROM appointment_tbl WHERE hcID = ?");
    $check_stmt->bind_param("i", $haircut_id);
    $check_stmt->execute();
    $result = $check_stmt->get_result();
    $row = $result->fetch_assoc();

    if ($row['count'] > 0) {
        $check_stmt->close();
        die(json_encode([
            'success' => false, 
            'message' => 'This haircut cannot be deleted because it is being used in existing appointments.'
        ]));
    }

    $check_stmt->close();

    $delete_stmt = $conn->prepare("DELETE FROM haircut_tbl WHERE hcID = ?");
    $delete_stmt->bind_param("i", $haircut_id);

    try {
        if ($delete_stmt->execute()) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to delete haircut']);
        }
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }

    $delete_stmt->close();
    exit();
}

// Handle get haircuts request
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action']) && $_GET['action'] === 'get_haircuts') {
    $category = $_GET['category'] ?? 'Basic';
    $sql = "SELECT * FROM haircut_tbl WHERE hcCategory = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $category);
    $stmt->execute();
    $result = $stmt->get_result();

    ob_start();
    while($row = $result->fetch_assoc()) {
        ?>
        <div class="haircut-item" data-category="<?php echo $row['hcCategory']; ?>" data-id="<?php echo $row['hcID']; ?>">
            <div class="position-relative">
                <img src="data:image/jpeg;base64,<?php echo base64_encode($row['hcImage']); ?>" 
                     alt="<?php echo $row['hcName']; ?>">
                <button class="delete-btn btn btn-danger" style="display: none;">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <p class="text-center mt-2"><?php echo $row['hcName']; ?></p>
        </div>
        <?php
    }
    $html = ob_get_clean();
    $stmt->close();
    echo $html;
    exit();
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="icon" type="image/png" href="css/images/favicon-32x32.png">
    <link rel="stylesheet" href="css/table.css">
    <link rel="stylesheet" href="css/calendar.css">
    <title>Options</title>
    <style>
        body {
            background-color: #090909;
        }
        .dashboard {
            color: white;
        }
        /* Sidebar styling */
        .sidebar {
            background-color: #F3CD32 !important;
            min-height: 100vh;
        }
        .list-group-item {
            background-color: transparent !important;
            border: none !important;
            color: black !important;
            border-radius: 10px !important;
            margin-bottom: 5px;
        }
        .list-group-item:hover {
            background-color: rgba(0, 0, 0, 0.1) !important;
        }
        .list-group-item.active {
            background-color: black !important;
            color: #F3CD32 !important;
            border-radius: 10px !important;
        }
        .list-group-item.active i,
        .list-group-item.active span {
            color: #F3CD32 !important;
        }
        /* Avatar container styling */
        .avatar-container {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 20px 0;
        }
        .avatar-container .logo {
            margin-bottom: 20px;
        }
        .avatar-container img.avatar {
            display: block;
            margin: 0 auto;
        }
        .avatar-container h5 {
            margin-top: 10px;
            text-align: center;
        }
        .gallery-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 20px;
            padding: 20px;
            background-color: white;
            border-radius: 12px;
            max-width: 800px;
        }
        .haircut-item img {
            width: 100%;
            aspect-ratio: 1/1;
            object-fit: cover;
            border-radius: 8px;
        }
        .haircut-item p {
            color: black;
            margin-top: 8px;
            font-weight: 500;
        }
        .delete-btn {
            position: absolute;
            top: 10px;
            right: 10px;
            border-radius: 50%;
            padding: 5px 10px;
        }
        .nav-tabs {
            border-bottom: none;
        }
        .nav-tabs .nav-link {
            color: #F3CD32;
            position: relative;
            background-color: transparent;
            border: none;
        }
        .nav-tabs .nav-link.active {
            color: #F3CD32;
            background-color: transparent;
        }
        .nav-tabs .nav-link:hover {
            background-color: transparent;
            color: #F3CD32;
        }
        .nav-tabs .nav-link.active::after {
            content: '';
            position: absolute;
            left: 0;
            right: 0;
            bottom: -5px;
            height: 2px;
            background-color: #F3CD32;
        }
        .modal-content {
            border-radius: 10px;
            overflow: hidden;
        }
        .modal-header {
            background-color: #F3CD32;
            color: black;
        }
        .modal-title {
            font-weight: bold;
        }
        .form-control,
        .form-select {
            border: 2px solid #F3CD32;
            border-radius: 5px;
        }
        .form-control:focus,
        .form-select:focus {
            border-color: #F3CD32;
            box-shadow: 0 0 0 0.25rem rgba(243, 205, 50, 0.25);
        }
        .btn-warning {
            background-color: #F3CD32;
            border-color: #F3CD32;
            color: black;
            font-weight: bold;
        }
        .btn-warning:hover {
            background-color: #e0b91d;
            border-color: #e0b91d;
        }
        /* Dark mode styles for the modal */
        .modal-content {
            background-color: #1f1f1f;
            color: #fff;
        }
        .modal-header {
            background-color: #1f1f1f;
            border-bottom: 1px solid #444;
        }
        .modal-title {
            color: #fff;
        }
        .form-label {
            color: #fff;
        }
        .form-control,
        .form-select {
            background-color: #333;
            color: #fff;
            border: 1px solid #444;
        }
        .form-control:focus,
        .form-select:focus {
            background-color: #333;
            color: #fff;
            border-color: #F3CD32;
            box-shadow: 0 0 0 0.25rem rgba(243, 205, 50, 0.25);
        }
        .btn-close {
            filter: invert(1) grayscale(100%) brightness(200%);
        }
        .services-container {
            max-width: 800px;
        }
        .btn-active {
            background-color: gray;
            border-color: gray;
            color: white;
        }
        /* Add these new styles for layout management */
        .main-content {
            margin-left: 260px; /* Increased width of sidebar */
            padding: 20px;
        }

        /* Media query for smaller screens */
        @media (max-width: 991.98px) {
            .main-content {
                margin-left: 0;
            }
            .sidebar {
                position: fixed;
                top: 0;
                left: -240px; /* Hide sidebar by default on mobile */
                width: 240px;
                z-index: 1000;
                transition: left 0.3s ease;
            }
            .sidebar.show {
                left: 0;
            }
        }

        /* Add a container for all the content */
        .content-wrapper {
            position: relative;
            min-height: 100vh;
        }
    </style>
</head>
<body>
    <!-- Add the mobile toggle button -->
    <button class="mobile-toggle d-lg-none" onclick="toggleSidebar()">
        <i class="fas fa-bars"></i>
    </button>

    <div class="content-wrapper">
        <!-- Sidebar -->
        <nav id="sidebarMenu" class="collapse d-lg-block sidebar collapse">
            <div class="position-sticky">
                <div class="list-group list-group-flush mx-3 mt-5">
                    <div class="avatar-container">
                        <img src="css/images/jof_logo_black.png" alt="logo" width="55" height="55" class="logo">
                        <img src="css/images/admin.jpg" alt="Avatar" width="140" height="140" style="border: 5px solid #000000; border-radius: 50%;" class="avatar">
                        <h5>Admin</h5>
                    </div>
                    <a href="a_dashboard.php" class="list-group-item list-group-item-action py-2 ripple">
                        <i class="fa-solid fa-border-all fa-fw me-3"></i><span>Dashboard</span>
                    </a>
                    <a href="appointments.php" class="list-group-item list-group-item-action py-2 ripple">
                        <i class="fa-solid fa-users fa-fw me-3"></i><span>Appointment</span>
                    </a>
                    <a href="a_history.php" class="list-group-item list-group-item-action py-2 ripple">
                        <i class="fa-solid fa-clock-rotate-left fa-fw me-3"></i><span>History</span>
                    </a>
                    <a href="earnings.php" class="list-group-item list-group-item-action py-2 ripple">
                        <i class="fa-solid fa-money-bill-trend-up fa-fw me-3"></i><span>Earnings</span>
                    </a>
                    <a href="barbers.php" class="list-group-item list-group-item-action py-2 ripple">
                        <i class="fa-solid fa-scissors fa-fw me-3"></i><span>Barbers</span>
                    </a>
                    <a href="options.php" class="list-group-item list-group-item-action py-2 ripple active">
                        <i class="fa-solid fa-gear fa-fw me-3"></i><span>Options</span>
                    </a>
                    <a href="../logout-staff.php" class="list-group-item list-group-item-action py-2 ripple">
                        <i class="fa-solid fa-right-from-bracket fa-fw me-3"></i><span>Log Out</span>
                    </a>
                </div>
            </div>
        </nav>

        <!-- Main content -->
        <div class="main-content">
            <h1 class="dashboard mb-3 ms-0">Edit Haircut Gallery</h1>
            <!-- Add Photo Button -->
            <button class="btn btn-warning" id="addPhotoBtn">
                + Add Haircut
            </button>

            <button class="btn btn-warning ms-2" id="editGalleryBtn">
                Edit
            </button>

            <div class="haircut-gallery mt-1">
                <ul class="nav nav-tabs">
                    <li class="nav-item">
                        <a class="nav-link active" href="#Basic">Basic</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#Premium">Specialized</a>
                    </li>
                </ul>
                
                <div class="gallery-grid mt-3">
                    <?php
                    // Fetch haircuts from database with category filter
                    $sql = "SELECT * FROM haircut_tbl WHERE hcCategory = 'Basic'"; // Default to showing Basic
                    $result = $conn->query($sql);
                    
                    while($row = $result->fetch_assoc()) {
                        ?>
                        <div class="haircut-item" data-category="<?php echo $row['hcCategory']; ?>" data-id="<?php echo $row['hcID']; ?>">
                            <div class="position-relative">
                                <img src="data:image/jpeg;base64,<?php echo base64_encode($row['hcImage']); ?>" 
                                     alt="<?php echo $row['hcName']; ?>">
                                <button class="delete-btn btn btn-danger" style="display: none;">
                                    <i class="fas fa-times"></i>
                                </button>
                            </div>
                            <p class="text-center mt-2"><?php echo $row['hcName']; ?></p>
                        </div>
                        <?php
                    }
                    ?>
                </div>
                
                <button class="btn btn-warning confirm-btn mt-3" id="confirmGalleryEditBtn" style="display: none;">
                    Confirm
                </button>
            </div>

            <h1 class="dashboard mb-3 ms-0">Edit Services</h1>

            <div class="d-flex justify-content-between align-items-center mb-3">
                <div>
                    <!-- Add Services Button -->
                    <button class="btn btn-warning" id="addServiceBtn">
                        + Add Services
                    </button>

                    <!-- Move Confirm Button to the left of Edit Services Button -->
                    <button class="btn btn-warning ms-2" id="confirmServicesEditBtn" style="display: none;">
                        Confirm
                    </button>

                    <!-- Edit Services Button -->
                    <button class="btn btn-warning ms-2" id="editServicesBtn">
                        Edit
                    </button>
                </div>
            </div>

            <div class="services-container">
                <table class="table table-striped" style="border-radius: 8px; overflow: hidden;">
                    <thead>
                        <tr>
                            <th>Service Name</th>
                            <th>Price</th>
                            <th class="actions-column" style="display: none;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        // Fetch services from the database
                        $sql = "SELECT * FROM service_tbl";
                        $result = $conn->query($sql);

                        while ($row = $result->fetch_assoc()) {
                            ?>
                            <tr>
                                <td><?php echo $row['serviceName']; ?></td>
                                <td><?php echo $row['servicePrice']; ?> PHP</td>
                                <td class="actions-column" style="display: none;">
                                    <button class="btn btn-danger btn-sm delete-service-btn" data-id="<?php echo $row['serviceID']; ?>">
                                        <i class="fas fa-times"></i>
                                    </button>
                                    <button class="btn btn-warning btn-sm edit-service-btn" data-id="<?php echo $row['serviceID']; ?>" data-name="<?php echo $row['serviceName']; ?>" data-price="<?php echo $row['servicePrice']; ?>">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                </td>
                            </tr>
                            <?php
                        }
                        ?>
                    </tbody>
                </table>
            </div>

            <!-- Confirm Button -->
            <div class="text-center mb-3">
                <button class="btn btn-warning" id="confirmEditBtn" style="display: none;">
                    Confirm
                </button>
            </div>

            <!-- Add Service Modal -->
            <div class="modal fade" id="addServiceModal" tabindex="-1" aria-labelledby="addServiceModalLabel" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="addServiceModalLabel">Add New Service</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <form id="addServiceForm">
                                <div class="mb-3">
                                    <label for="serviceName" class="form-label">Service Name</label>
                                    <input type="text" class="form-control" id="serviceName" required>
                                </div>
                                <div class="mb-3">
                                    <label for="servicePrice" class="form-label">Price</label>
                                    <input type="number" class="form-control" id="servicePrice" required>
                                </div>
                                <div class="text-center">
                                    <button type="submit" class="btn btn-warning">Add Service</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Edit Service Modal -->
            <div class="modal fade" id="editServiceModal" tabindex="-1" aria-labelledby="editServiceModalLabel" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="editServiceModalLabel">Edit Service</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <form id="editServiceForm">
                                <input type="hidden" id="editServiceID">
                                <div class="mb-3">
                                    <label for="editServiceName" class="form-label">Service Name</label>
                                    <input type="text" class="form-control" id="editServiceName" required>
                                </div>
                                <div class="mb-3">
                                    <label for="editServicePrice" class="form-label">Price</label>
                                    <input type="number" class="form-control" id="editServicePrice" required>
                                </div>
                                <div class="text-center">
                                    <button type="submit" class="btn btn-warning">Update Service</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <h1 class="dashboard mb-3 ms-0">Edit Add-ons</h1>
            <!-- Add Add-ons Button -->
            <button class="btn btn-warning" id="addAddonBtn">
                + Add Add-ons
            </button>

            <button class="btn btn-warning ms-2" id="editAddonsBtn">
                Edit
            </button>

            <!-- Add-ons Table -->
            <div class="addons-container mt-3 mb-4 pb-4">
                <table class="table table-striped" style="border-radius: 8px; overflow: hidden; width: 800px; padding-bottom: 1rem;">
                    <thead>
                        <tr>
                            <th>Add-on Name</th>
                            <th>Price</th>
                            <th class="actions-column" style="display: none;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        // Fetch add-ons from the database
                        $sql = "SELECT * FROM addon_tbl";
                        $result = $conn->query($sql);

                        while ($row = $result->fetch_assoc()) {
                            ?>
                            <tr>
                                <td><?php echo $row['addonName']; ?></td>
                                <td><?php echo $row['addonPrice']; ?> PHP</td>
                                <td class="actions-column" style="display: none;">
                                    <button class="btn btn-danger btn-sm delete-addon-btn" data-id="<?php echo $row['addonID']; ?>">
                                        <i class="fas fa-times"></i>
                                    </button>
                                    <button class="btn btn-warning btn-sm edit-addon-btn" data-id="<?php echo $row['addonID']; ?>" data-name="<?php echo $row['addonName']; ?>" data-price="<?php echo $row['addonPrice']; ?>">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                </td>
                            </tr>
                            <?php
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Add this button for mobile menu toggle -->
    <button class="btn btn-warning d-lg-none position-fixed" 
            style="top: 10px; right: 10px; z-index: 1001;" 
            onclick="document.getElementById('sidebarMenu').classList.toggle('show')">
        <i class="fas fa-bars"></i>
    </button>

    <div class="modal fade" id="errorModal" tabindex="-1" aria-labelledby="errorModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="errorModalLabel">Error</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="errorModalBody">
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="addHaircutModal" tabindex="-1" aria-labelledby="addHaircutModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addHaircutModalLabel">Add New Haircut</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="addHaircutForm">
                        <div class="mb-3">
                            <label for="haircutName" class="form-label">Haircut Name</label>
                            <input type="text" class="form-control" id="haircutName" required>
                        </div>
                        <div class="mb-3">
                            <label for="haircutCategory" class="form-label">Category</label>
                            <select class="form-select" id="haircutCategory" required>
                                <option value="Basic">Basic</option>
                                <option value="Specialized">Specialized</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="haircutPhoto" class="form-label">Upload Photo</label>
                            <input type="file" class="form-control" id="haircutPhoto" accept="image/*" required>
                        </div>
                        <div class="text-center">
                            <button type="submit" class="btn btn-warning">Add Haircut</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.min.js"></script>
    
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const tabs = document.querySelectorAll('.nav-link');
        const errorModal = new bootstrap.Modal(document.getElementById('errorModal'));

        tabs.forEach(tab => {
            tab.addEventListener('click', function(e) {
                e.preventDefault();
                tabs.forEach(t => t.classList.remove('active'));
                this.classList.add('active');
                const category = this.getAttribute('href').replace('#', '');
                
                fetch(`options.php?action=get_haircuts&category=${category}`)
                    .then(response => response.text())
                    .then(html => {
                        document.querySelector('.gallery-grid').innerHTML = html;
                        attachDeleteListeners();
                    });
            });
        });

        // Edit button functionality
        document.getElementById('editGalleryBtn').addEventListener('click', function() {
            const deleteBtns = document.querySelectorAll('.delete-btn');
            const confirmBtn = document.querySelector('.confirm-btn');
            
            deleteBtns.forEach(btn => {
                btn.style.display = btn.style.display === 'none' ? 'block' : 'none';
            });
            
            confirmBtn.style.display = confirmBtn.style.display === 'none' ? 'block' : 'none';

            // Change the color of the Edit Gallery button
            this.classList.toggle('btn-active'); // Toggle the active class
        });

        // Confirm button functionality
        document.querySelector('.confirm-btn').addEventListener('click', function() {
            const deleteBtns = document.querySelectorAll('.delete-btn');
            const confirmBtn = this;

            // Hide delete buttons and confirm button
            deleteBtns.forEach(btn => {
                btn.style.display = 'none';
            });
            confirmBtn.style.display = 'none';
        });

        // Function to attach delete event listeners
        function attachDeleteListeners() {
            document.querySelectorAll('.delete-btn').forEach(btn => {
                btn.addEventListener('click', function() {
                    if (confirm('Are you sure you want to delete this haircut?')) {
                        const haircut = this.closest('.haircut-item');
                        const haircut_id = haircut.dataset.id;

                        const formData = new FormData();
                        formData.append('action', 'delete_haircut');
                        formData.append('haircut_id', haircut_id);

                        fetch('options.php', {
                            method: 'POST',
                            body: formData
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                haircut.remove();
                            } else {
                                document.getElementById('errorModalBody').textContent = data.message;
                                errorModal.show();
                            }
                        })
                        .catch(error => {
                            console.error('Error:', error);
                            document.getElementById('errorModalBody').textContent = 'An error occurred while deleting the haircut.';
                            errorModal.show();
                        });
                    }
                });
            });
        }

        // Initial attachment of delete listeners
        attachDeleteListeners();

        // Open the Add Haircut modal when the "Add Photo" button is clicked
        document.getElementById('addPhotoBtn').addEventListener('click', function() {
            const addHaircutModal = new bootstrap.Modal(document.getElementById('addHaircutModal'));
            addHaircutModal.show();
        });

        // Handle form submission
        document.getElementById('addHaircutForm').addEventListener('submit', function(event) {
            event.preventDefault();

            const haircutName = document.getElementById('haircutName').value;
            const haircutPhoto = document.getElementById('haircutPhoto').files[0];

            // Create a FormData object to send the form data
            const formData = new FormData();
            formData.append('haircutName', haircutName);
            formData.append('haircutPhoto', haircutPhoto);

            // Send the form data to the server using fetch
            fetch('add_haircut.php', {
                method: 'POST',
                body: formData
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    // Refresh the gallery grid
                    const activeTab = document.querySelector('.nav-link.active');
                    const category = activeTab.getAttribute('href').replace('#', '');
                    fetch(`options.php?action=get_haircuts&category=${category}`)
                        .then(response => response.text())
                        .then(html => {
                            document.querySelector('.gallery-grid').innerHTML = html;
                            attachDeleteListeners();
                        });

                    // Close the modal
                    const addHaircutModal = bootstrap.Modal.getInstance(document.getElementById('addHaircutModal'));
                    addHaircutModal.hide();

                    // Reset the form
                    document.getElementById('addHaircutForm').reset();
                } else {
                    // Show error message
                    document.getElementById('errorModalBody').textContent = data.message;
                    errorModal.show();
                }
            })
            .catch(error => {
                console.error('Error:', error);
                document.getElementById('errorModalBody').textContent = 'An error occurred while adding the haircut. Please check the console for more details.';
                errorModal.show();
            });
        });

        // Open the Add Service modal when the "Add Services" button is clicked
        document.getElementById('addServiceBtn').addEventListener('click', function() {
            const addServiceModal = new bootstrap.Modal(document.getElementById('addServiceModal'));
            addServiceModal.show();
        });

        // Handle form submission for adding a new service
        document.getElementById('addServiceForm').addEventListener('submit', function(event) {
            event.preventDefault();

            const serviceName = document.getElementById('serviceName').value;
            const servicePrice = document.getElementById('servicePrice').value;

            // Send the form data to the server using fetch
            fetch('add_service.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `serviceName=${serviceName}&servicePrice=${servicePrice}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Refresh the services table
                    location.reload();
                } else {
                    // Show error message
                    document.getElementById('errorModalBody').textContent = data.message;
                    errorModal.show();
                }
            })
            .catch(error => {
                console.error('Error:', error);
                document.getElementById('errorModalBody').textContent = 'An error occurred while adding the service. Please check the console for more details.';
                errorModal.show();
            });
        });

        // Edit Services button functionality
        document.getElementById('editServicesBtn').addEventListener('click', function() {
            const actionsColumn = document.querySelectorAll('.actions-column');
            
            actionsColumn.forEach(column => {
                column.style.display = column.style.display === 'none' ? 'table-cell' : 'none';
            });

            // Show the Confirm button
            const confirmButton = document.getElementById('confirmEditBtn');
            confirmButton.style.display = confirmButton.style.display === 'none' ? 'inline-block' : 'none';

            // Change the color of the Edit Services button
            this.classList.toggle('btn-active'); // Toggle the active class
        });

        // Confirm button functionality
        document.getElementById('confirmEditBtn').addEventListener('click', function() {
            const actionsColumn = document.querySelectorAll('.actions-column');
            
            actionsColumn.forEach(column => {
                column.style.display = 'none'; // Hide action buttons
            });

            // Hide the Confirm button
            this.style.display = 'none';
        });

        // Delete service functionality
        document.querySelectorAll('.delete-service-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                if (confirm('Are you sure you want to delete this service?')) {
                    const serviceID = this.dataset.id;

                    fetch('delete_service.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                        },
                        body: `serviceID=${serviceID}`
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            // Remove the service row from the table
                            this.closest('tr').remove();
                        } else {
                            alert('Error deleting service: ' + data.message);
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('An error occurred while deleting the service.');
                    });
                }
            });
        });

        // Edit service functionality
        document.querySelectorAll('.edit-service-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                const serviceID = this.dataset.id;
                const serviceName = this.dataset.name;
                const servicePrice = this.dataset.price;

                // Populate the modal fields
                document.getElementById('editServiceID').value = serviceID;
                document.getElementById('editServiceName').value = serviceName;
                document.getElementById('editServicePrice').value = servicePrice;

                // Show the modal
                const editServiceModal = new bootstrap.Modal(document.getElementById('editServiceModal'));
                editServiceModal.show();
            });
        });

        // Handle form submission for editing a service
        document.getElementById('editServiceForm').addEventListener('submit', function(event) {
            event.preventDefault();

            const serviceID = document.getElementById('editServiceID').value;
            const serviceName = document.getElementById('editServiceName').value;
            const servicePrice = document.getElementById('editServicePrice').value;

            // Send the updated data to the server using fetch
            fetch('edit_service.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `serviceID=${serviceID}&serviceName=${serviceName}&servicePrice=${servicePrice}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Update the table row
                    const row = document.querySelector(`button[data-id="${serviceID}"]`).closest('tr');
                    row.querySelector('td:nth-child(1)').textContent = serviceName;
                    row.querySelector('td:nth-child(2)').textContent = servicePrice + ' PHP';

                    // Close the modal
                    const editServiceModal = bootstrap.Modal.getInstance(document.getElementById('editServiceModal'));
                    editServiceModal.hide();
                } else {
                    alert('Error updating service: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred while updating the service.');
            });
        });
    });
    </script>

    <script>
        function toggleSidebar() {
            const sidebar = document.getElementById('sidebarMenu');
            sidebar.classList.toggle('show');
        }

        // Close sidebar when clicking outside
        document.addEventListener('click', function(event) {
            const sidebar = document.getElementById('sidebarMenu');
            const toggle = document.querySelector('.mobile-toggle');
            if (!sidebar.contains(event.target) && !toggle.contains(event.target)) {
                sidebar.classList.remove('show');
            }
        });
    </script>

</body>
</html>
